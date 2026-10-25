<?php

namespace App\Services;

use App\Events\InvoiceCreated;
use App\Models\Invoice;
use App\Models\InvoiceLine;
use App\Models\Lease;
use App\Models\Scopes\OwnerScope;
use App\Models\TaxRate;
use App\Support\Money;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;

class InvoiceGenerationService
{
    public function generate(?string $date = null, ?int $leaseId = null, bool $dryRun = false): array
    {
        $leases = Lease::withoutGlobalScope(OwnerScope::class)->with(['tenant', 'unit', 'charges'])->where('status', 'active')->whereNotNull('next_invoice_date')->when($leaseId, fn ($q) => $q->whereKey($leaseId))->get();
        $created = [];
        $errors = [];
        foreach ($leases as $lease) {
            try {
                $owner = $lease->owner;
                $today = Carbon::parse($date ?: now($owner->timezone)->toDateString(), $owner->timezone);
                $count = 0;
                while ($lease->next_invoice_date && Carbon::parse($lease->next_invoice_date)->lte($today) && $count < 36) {
                    $period = $this->period($lease, Carbon::parse($lease->next_invoice_date));
                    if ($dryRun) {
                        $created[] = $period['start']->toDateString();
                        $lease->next_invoice_date = $period['next']->toDateString();
                        $count++;

                        continue;
                    }$created[] = $this->create($lease, $owner, $period, $today)->invoice_number;
                    $lease->refresh();
                    $count++;
                }
            } catch (\Throwable $e) {
                $errors[$lease->id] = $e->getMessage();
            }
        }

        return compact('created', 'errors');
    }

    private function period(Lease $lease, Carbon $start): array
    {
        $months = ['monthly' => 1, 'quarterly' => 3, 'semiannual' => 6, 'yearly' => 12][$lease->billing_cycle];
        $anchor = $lease->billing_day;
        if ($lease->prorate_first_period && $start->day !== ($anchor ?: $start->day) && $start->equalTo(Carbon::parse($lease->start_date))) {
            $next = $anchor === 0 ? $start->copy()->startOfMonth()->addMonth()->endOfMonth() : $start->copy()->startOfMonth()->addMonth()->day(min($anchor, $start->copy()->startOfMonth()->addMonth()->daysInMonth));
            $end = $next->copy()->subDay();
            $nextStart = $next;
        } else {
            $end = $anchor === 0 ? $start->copy()->addMonths($months - 1)->endOfMonth() : $start->copy()->addMonths($months);
            $end = $anchor === 0 ? $end : $end->copy()->subDay();
            $nextStart = $end->copy()->addDay();
        }if ($lease->end_date && $end->gt(Carbon::parse($lease->end_date))) {
            $end = Carbon::parse($lease->end_date);
        }if ($lease->end_date && $start->gt(Carbon::parse($lease->end_date))) {
            $nextStart = null;
        }

        return ['start' => $start, 'end' => $end, 'next' => $nextStart];
    }

    private function create(Lease $lease, $owner, array $period, Carbon $today): Invoice
    {
        return DB::transaction(function () use ($lease, $owner, $period, $today) {
            $key = 'lease:'.$lease->id.':'.$period['start']->toDateString();
            if ($existing = Invoice::withoutGlobalScope(OwnerScope::class)->where('recurring_key', $key)->first()) {
                return $existing;
            }$number = app(NumberingService::class)->next($owner->id, 'invoice');
            $rows = [];
            $subtotal = 0;
            $taxTotal = 0;
            $sort = 0;
            $months = ['monthly' => 1, 'quarterly' => 3, 'semiannual' => 6, 'yearly' => 12][$lease->billing_cycle];
            $fullEnd = $lease->billing_day === 0
                ? $period['start']->copy()->addMonths($months - 1)->endOfMonth()
                : $period['start']->copy()->startOfMonth()->addMonths($months)->subDay();
            $fullDays = $period['start']->copy()->startOfMonth()->diffInDays($fullEnd) + 1;
            foreach ($lease->charges as $charge) {
                if (! $charge->is_active || Carbon::parse($charge->starts_on)->gt($period['end']) || ($charge->ends_on && Carbon::parse($charge->ends_on)->lt($period['start']))) {
                    continue;
                }$amount = $charge->amount;
                $quantity = 1;
                $line = $amount;
                if ($charge->frequency === 'every_cycle') {
                    $useStart = max(Carbon::parse($charge->starts_on)->timestamp, $period['start']->timestamp);
                    $useEnd = min(($charge->ends_on ? Carbon::parse($charge->ends_on) : $period['end'])->timestamp, $period['end']->timestamp);
                    $days = (int) (Carbon::createFromTimestamp($useStart)->diffInDays(Carbon::createFromTimestamp($useEnd)) + 1);
                    $line = $this->round($amount * $days, $fullDays);
                    $quantity = 1;
                } elseif (in_array($charge->frequency, ['monthly', 'quarterly', 'yearly'], true)) {
                    $step = ['monthly' => 1, 'quarterly' => 3, 'yearly' => 12][$charge->frequency];
                    $cursor = Carbon::parse($charge->starts_on);
                    $quantity = 0;
                    while ($cursor->lte($period['end'])) {
                        if ($cursor->gte($period['start'])) {
                            $quantity++;
                        }$cursor->addMonths($step);
                    }if ($quantity === 0) {
                        continue;
                    }$line = $amount * $quantity;
                } elseif ($charge->frequency === 'once') {
                    if (InvoiceLine::withoutGlobalScope(OwnerScope::class)->where('lease_charge_id', $charge->id)->exists()) {
                        continue;
                    }if (Carbon::parse($charge->starts_on)->lt($period['start']) || Carbon::parse($charge->starts_on)->gt($period['end'])) {
                        continue;
                    }
                }$taxRate = $charge->tax_rate_id ? TaxRate::withoutGlobalScope(OwnerScope::class)->find($charge->tax_rate_id) : null;
                $rate = (string) ($taxRate?->rate ?? '0');
                $tax = 0;
                $net = $line;
                if ($taxRate && $rate !== '0.000' && $rate !== '0') {
                    $rateParts = Money::parse($rate, 'X', 3)->minor;
                    $tax = $taxRate->is_inclusive ? $line - $this->round($line * 100000, 100000 + $rateParts) : $this->round($line * $rateParts, 100000);
                    $net = $taxRate->is_inclusive ? $line - $tax : $line;
                }$total = $net + $tax;
                $subtotal += $net;
                $taxTotal += $tax;
                $rows[] = ['owner_id' => $owner->id, 'charge_type_id' => $charge->charge_type_id, 'lease_charge_id' => $charge->id, 'description' => $charge->description ?: $charge->chargeType?->name ?: 'Charge', 'quantity' => $quantity, 'unit_price' => $amount, 'line_subtotal' => $net, 'tax_rate_id' => $charge->tax_rate_id, 'tax_rate_value' => $rate, 'tax_amount' => $tax, 'line_total' => $total, 'sort_order' => $sort++];
            }$invoice = Model::withoutEvents(fn () => Invoice::create(['owner_id' => $owner->id, 'invoice_number' => $number, 'invoice_type' => 'invoice', 'source' => 'recurring', 'recurring_key' => $key, 'lease_id' => $lease->id, 'tenant_id' => $lease->tenant_id, 'unit_id' => $lease->unit_id, 'period_start' => $period['start'], 'period_end' => $period['end'], 'issue_date' => $today->toDateString(), 'due_date' => max($today->toDateString(), $period['start']->copy()->addDays($lease->due_after_days)->toDateString()), 'status' => 'issued', 'payment_status' => 'unpaid', 'subtotal' => $subtotal, 'tax_total' => $taxTotal, 'total' => $subtotal + $taxTotal, 'amount_paid' => 0, 'balance_due' => $subtotal + $taxTotal, 'currency_code' => $owner->currency_code]));
            foreach ($rows as $row) {
                Model::withoutEvents(fn () => $invoice->lines()->create($row));
            }DB::table('leases')->where('id', $lease->id)->update(['next_invoice_date' => $lease->end_date && $period['end']->gte(Carbon::parse($lease->end_date)) ? null : $period['next']]);
            Event::dispatch(new InvoiceCreated($invoice));

            return $invoice;
        });
    }

    private function round(int $n, int $d): int
    {
        $q = intdiv(abs($n), $d);
        $r = abs($n) % $d;
        if ($r >= intdiv($d, 2) + ($d % 2)) {
            $q++;
        }

        return $n < 0 ? -$q : $q;
    }
}
