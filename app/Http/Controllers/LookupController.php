<?php

namespace App\Http\Controllers;

use App\Models\ChargeType;
use App\Models\LateFeeRule;
use App\Models\NumberingSequence;
use App\Models\TaxRate;
use Illuminate\Http\Request;

class LookupController extends Controller
{
    public function index(string $type)
    {
        $map = ['charge-types' => ChargeType::class, 'tax-rates' => TaxRate::class, 'late-fee-rules' => LateFeeRule::class, 'numbering' => NumberingSequence::class];
        abort_unless(isset($map[$type]), 404);

        return view('lookups.index', ['type' => $type, 'rows' => $map[$type]::latest('id')->get()]);
    }

    public function store(Request $r, string $type)
    {
        $map = ['charge-types' => [ChargeType::class, ['name' => 'required', 'kind' => 'required', 'is_active' => 'boolean']], 'tax-rates' => [TaxRate::class, ['name' => 'required', 'rate' => 'required|numeric|between:0,100']], 'late-fee-rules' => [LateFeeRule::class, ['name' => 'required', 'type' => 'required', 'repeat_every' => 'required']], 'numbering' => [NumberingSequence::class, ['doc_type' => 'required', 'prefix' => 'required', 'format' => 'required']]];
        abort_unless(isset($map[$type]), 404);
        [$class,$rules] = $map[$type];
        $data = $r->validate($rules);
        $class::create($data);

        return back()->with('status', __('app.saved'));
    }

    public function destroy(string $type, $id)
    {
        $class = ['charge-types' => ChargeType::class, 'tax-rates' => TaxRate::class, 'late-fee-rules' => LateFeeRule::class, 'numbering' => NumberingSequence::class][$type] ?? null;
        abort_unless($class, 404);
        $row = $class::findOrFail($id);
        abort_if($row instanceof ChargeType && $row->is_system, 403);
        $row->delete();

        return back();
    }
}
