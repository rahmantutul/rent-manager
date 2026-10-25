<?php

namespace App\Services;

use App\Models\Owner;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class OwnerBootstrap
{
    public function run(Owner|int $owner): void
    {
        $o = $owner instanceof Owner ? $owner : Owner::findOrFail($owner);
        Model::withoutEvents(function () use ($o) {
            DB::transaction(function () use ($o) {
                foreach ([['Rent', 'rent'], ['Late fee', 'late_fee'], ['Security deposit', 'deposit']] as [$name,$kind]) {
                    DB::table('charge_types')->updateOrInsert(['owner_id' => $o->id, 'name' => $name], ['kind' => $kind, 'is_system' => 1, 'is_active' => 1]);
                } foreach (['invoice' => ['INV-', 'INV-{YYYY}-{seq:5}'], 'credit_note' => ['CN-', 'CN-{YYYY}-{seq:5}'], 'payment' => ['RCT-', 'RCT-{YYYY}-{seq:5}'], 'lease' => ['LSE-', 'LSE-{YYYY}-{seq:4}']] as $type => $v) {
                    DB::table('numbering_sequences')->updateOrInsert(['owner_id' => $o->id, 'doc_type' => $type], ['prefix' => $v[0], 'format' => $v[1], 'next_number' => 1, 'reset_policy' => 'never']);
                }
            });
        });
    }
}
