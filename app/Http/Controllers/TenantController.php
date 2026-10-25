<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Services\AttachmentService;
use Illuminate\Http\Request;

class TenantController extends Controller
{
    public function index(Request $r)
    {
        $this->authorize('viewAny', Tenant::class);
        $q = Tenant::query()->when($r->search, fn ($q, $v) => $q->where(fn ($x) => $x->where('name', 'like', '%'.$v.'%')->orWhere('email', 'like', '%'.$v.'%')))->when($r->status, fn ($q, $v) => $q->where('status', $v));

        return view('tenants.index', ['tenants' => $q->latest('id')->paginate(15)]);
    }

    public function create()
    {
        $this->authorize('create', Tenant::class);

        return view('tenants.form', ['tenant' => new Tenant]);
    }

    public function store(Request $r)
    {
        $this->authorize('create', Tenant::class);
        $data = $this->validateData($r);
        $tenant = Tenant::create($data);

        return redirect()->route('admin.tenants.show', $tenant);
    }

    public function show(Tenant $tenant)
    {
        $this->authorize('view', $tenant);

        return view('tenants.show', compact('tenant'));
    }

    public function edit(Tenant $tenant)
    {
        $this->authorize('update', $tenant);

        return view('tenants.form', compact('tenant'));
    }

    public function update(Request $r, Tenant $tenant)
    {
        $this->authorize('update', $tenant);
        $tenant->update($this->validateData($r));

        return redirect()->route('admin.tenants.show', $tenant);
    }

    public function destroy(Tenant $tenant)
    {
        $this->authorize('delete', $tenant);
        $tenant->delete();

        return redirect()->route('admin.tenants.index');
    }

    public function document(Request $r, Tenant $tenant, AttachmentService $service)
    {
        $this->authorize('update', $tenant);
        $r->validate(['document' => 'required|file|max:10240|mimes:jpg,jpeg,png,pdf']);
        $service->store($r->file('document'), $tenant, $r->user());

        return back();
    }

    private function validateData(Request $r): array
    {
        return $r->validate(['type' => 'required|in:individual,company', 'name' => 'required|string|max:150', 'company_name' => 'nullable|string|max:150', 'email' => 'nullable|email', 'phone' => 'nullable|string', 'alt_phone' => 'nullable|string', 'id_document_type' => 'nullable|string|max:40', 'id_document_number' => 'nullable|string', 'date_of_birth' => 'nullable|date', 'emergency_contact_name' => 'nullable|string', 'emergency_contact_phone' => 'nullable|string', 'previous_address' => 'nullable|string', 'status' => 'required|in:prospect,active,former', 'portal_enabled' => 'boolean', 'notes' => 'nullable|string']);
    }
}
