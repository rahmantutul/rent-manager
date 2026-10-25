<?php

namespace App\Http\Controllers;

use App\Models\ChargeType;
use App\Models\Lease;
use App\Models\Tenant;
use App\Models\Unit;
use App\Services\NumberingService;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LeaseController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', Lease::class);

        return view('leases.index', ['leases' => Lease::with(['tenant', 'unit'])->latest('id')->paginate(15)]);
    }

    public function create()
    {
        $this->authorize('create', Lease::class);

        return view('leases.form', ['lease' => new Lease, 'tenants' => Tenant::whereIn('status', ['prospect', 'active'])->get(), 'units' => Unit::where('status', 'vacant')->get(), 'chargeTypes' => ChargeType::where('is_active', true)->get()]);
    }

    public function store(Request $r, NumberingService $numbering)
    {
        $this->authorize('create', Lease::class);
        $d = $this->validateData($r);
        $unit = Unit::findOrFail($d['unit_id']);
        $tenant = Tenant::findOrFail($d['tenant_id']);
        $lease = null;
        DB::transaction(function () use (&$lease, $d, $r, $numbering, $unit) {
            $d['lease_number'] = $numbering->next($r->user()->owner_id, 'lease');
            $d['owner_id'] = $r->user()->owner_id;
            $lease = Lease::create($d);
            $rent = ChargeType::where('name', 'Rent')->firstOrFail();
            $lease->charges()->create(['owner_id' => $lease->owner_id, 'charge_type_id' => $rent->id, 'amount' => $unit->default_rent, 'frequency' => 'every_cycle', 'starts_on' => $d['start_date'], 'is_active' => true]);
            foreach ((array) $r->input('co_tenants', []) as $id) {
                $lease->coTenants()->attach($id);
            }
        });

        return redirect()->route('admin.leases.show', $lease);
    }

    public function show(Lease $lease)
    {
        $this->authorize('view', $lease);

        return view('leases.show', compact('lease'));
    }

    public function activate(Lease $lease)
    {
        $this->authorize('update', $lease);
        abort_if($lease->status !== 'draft', 422);
        try {
            DB::transaction(function () use ($lease) {
                $lease->update(['status' => 'active', 'active_unit_id' => $lease->unit_id, 'next_invoice_date' => $lease->start_date]);
                $lease->unit()->update(['status' => 'occupied']);
            });
        } catch (QueryException) {
            return back()->withErrors(['lease' => __('app.lease_unit_active')]);
        }

return back();
    }

    public function changeRent(Request $r, Lease $lease)
    {
        $this->authorize('update', $lease);
        $d = $r->validate(['amount' => 'required|integer|min:1', 'starts_on' => 'required|date']);
        $current = $lease->charges()->whereHas('chargeType', fn ($q) => $q->where('name', 'Rent'))->where('is_active', true)->latest('starts_on')->firstOrFail();
        DB::transaction(function () use ($current, $d, $lease) {
            $date = Carbon::parse($d['starts_on']);
            $current->update(['ends_on' => $date->copy()->subDay(), 'is_active' => false]);
            $lease->charges()->create(['owner_id' => $lease->owner_id, 'charge_type_id' => $current->charge_type_id, 'amount' => $d['amount'], 'frequency' => 'every_cycle', 'starts_on' => $date, 'is_active' => true]);
        });

        return back();
    }

    public function terminate(Request $r, Lease $lease)
    {
        $this->authorize('update', $lease);
        $d = $r->validate(['move_out_date' => 'required|date', 'termination_reason' => 'nullable|string']);
        DB::transaction(function () use ($lease, $d) {
            $lease->update(['move_out_date' => $d['move_out_date'], 'end_date' => $d['move_out_date'], 'status' => 'terminated', 'active_unit_id' => null, 'termination_reason' => $d['termination_reason'] ?? null]);
            $lease->unit()->update(['status' => 'vacant']);
        });

        return back();
    }

    private function validateData(Request $r): array
    {
        return $r->validate(['unit_id' => 'required|exists:units,id', 'tenant_id' => 'required|exists:tenants,id', 'start_date' => 'required|date', 'end_date' => 'nullable|date|after_or_equal:start_date', 'billing_cycle' => 'required|in:monthly,quarterly,semiannual,yearly', 'billing_day' => 'required|integer|between:0,28', 'due_after_days' => 'required|integer|min:0', 'grace_days' => 'required|integer|min:0', 'auto_invoice' => 'boolean', 'prorate_first_period' => 'boolean', 'security_deposit' => 'required|integer|min:0', 'late_fee_rule_id' => 'nullable|exists:late_fee_rules,id', 'notice_period_days' => 'nullable|integer|min:0', 'terms' => 'nullable|string']);
    }
}
