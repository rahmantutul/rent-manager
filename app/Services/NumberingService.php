<?php

namespace App\Services;

use App\Models\Owner;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class NumberingService
{
    public function next(int $ownerId, string $docType): string
    {
        return DB::transaction(function () use ($ownerId, $docType) {
            $owner = Owner::findOrFail($ownerId);
            $row = DB::table('numbering_sequences')->where('owner_id', $ownerId)->where('doc_type', $docType)->lockForUpdate()->first();
            if (! $row) {
                throw new \RuntimeException('Numbering sequence is not configured.');
            }$now = Carbon::now($owner->timezone);
            $last = $row->last_reset_on ? Carbon::parse($row->last_reset_on, $owner->timezone) : null;
            $reset = $row->reset_policy === 'yearly' && (! $last || $last->year !== $now->year) || $row->reset_policy === 'monthly' && (! $last || $last->format('Y-m') !== $now->format('Y-m'));
            $n = $reset ? 1 : $row->next_number;
            DB::table('numbering_sequences')->where('id', $row->id)->update(['next_number' => $n + 1, 'last_reset_on' => $now->toDateString()]);
            $format = str_replace(['{prefix}', '[prefix]'], $row->prefix, $row->format);
            $format = str_replace(['{YYYY}', '[YYYY]'], $now->format('Y'), $format);
            $format = str_replace(['{YY}', '[YY]'], $now->format('y'), $format);
            $format = str_replace(['{MM}', '[MM]'], $now->format('m'), $format);

            return preg_replace_callback('/[\{\[]seq:(\d+)[\}\]]/', fn ($m) => str_pad((string) $n, (int) $m[1], '0', STR_PAD_LEFT), $format);
        });
    }
}
