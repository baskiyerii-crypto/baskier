<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Contract;
use App\Models\ContractVendorAcceptance;
use App\Models\Vendor;
use App\Services\ContractPublishService;
use App\Services\LegalTemplateService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Schema;

class AdminContractController extends Controller
{
    public function index(Request $request)
    {
        $contracts = Schema::hasTable('contracts')
            ? Contract::orderBy('key')->paginate(20)
            : new LengthAwarePaginator([], 0, 20);

        $vendorStatuses = collect();
        if (Schema::hasTable('contract_vendor_acceptances') && Schema::hasTable('vendors')) {
            $cols = ['id', 'name', 'contract_suspended_at'];
            if (Schema::hasColumn('vendors', 'is_suspended')) {
                $cols[] = 'is_suspended';
            }
            $vendorStatuses = Vendor::query()
                ->orderBy('name')
                ->get($cols)
                ->map(function (Vendor $vendor) {
                    $rows = ContractVendorAcceptance::query()
                        ->where('vendor_id', $vendor->id)
                        ->get();
                    $pending = $rows->whereIn('status', ['pending', 'expired'])->count();
                    $accepted = $rows->where('status', 'accepted')->count();

                    return [
                        'vendor' => $vendor,
                        'pending' => $pending,
                        'accepted' => $accepted,
                        'missing' => $pending === 0 && $accepted === 0,
                        'suspended' => (bool) $vendor->contract_suspended_at || (bool) $vendor->is_suspended,
                    ];
                });

            $filter = $request->query('status');
            $vendorStatuses = match ($filter) {
                'pending' => $vendorStatuses->filter(fn ($r) => $r['pending'] > 0),
                'accepted' => $vendorStatuses->filter(fn ($r) => $r['accepted'] > 0 && $r['pending'] === 0),
                'missing' => $vendorStatuses->filter(fn ($r) => $r['missing']),
                'suspended' => $vendorStatuses->filter(fn ($r) => $r['suspended']),
                default => $vendorStatuses,
            };
        }

        return view('admin.contracts.index', compact('contracts', 'vendorStatuses'));
    }

    public function generateTemplates(LegalTemplateService $legal, ContractPublishService $publisher)
    {
        $count = $legal->seedOrRefresh(true);
        foreach (Contract::query()->whereIn('key', array_keys($legal->templates()))->get() as $contract) {
            $publisher->publishToVendors($contract);
        }

        return back()->with('success', $count.' sözleşme şablonu oluşturuldu/güncellendi.');
    }

    public function create()
    {
        return view('admin.contracts.create');
    }

    public function store(Request $request, ContractPublishService $publisher)
    {
        $validated = $request->validate([
            'key' => ['required', 'string', 'max:64', 'unique:contracts,key'],
            'title' => ['required', 'string', 'max:255'],
            'audience' => ['required', 'in:all,customer,vendor'],
            'version' => ['required', 'integer', 'min:1', 'max:9999'],
            'is_active' => ['boolean'],
            'content_html' => ['nullable', 'string'],
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        $contract = Contract::create($validated);
        $sent = $publisher->publishToVendors($contract);

        return redirect()->route('admin.contracts.index')
            ->with('success', __('panel.contract_saved_sent', ['count' => $sent]));
    }

    public function edit(Contract $contract)
    {
        return view('admin.contracts.edit', compact('contract'));
    }

    public function update(Request $request, Contract $contract, ContractPublishService $publisher)
    {
        $validated = $request->validate([
            'key' => ['required', 'string', 'max:64', 'unique:contracts,key,'.$contract->id],
            'title' => ['required', 'string', 'max:255'],
            'audience' => ['required', 'in:all,customer,vendor'],
            'version' => ['required', 'integer', 'min:1', 'max:9999'],
            'is_active' => ['boolean'],
            'content_html' => ['nullable', 'string'],
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        $versionChanged = (int) $contract->version !== (int) $validated['version'];
        $contract->update($validated);

        $sent = 0;
        if ($versionChanged || $request->boolean('republish')) {
            $sent = $publisher->publishToVendors($contract->fresh());
        }

        return redirect()->route('admin.contracts.index')
            ->with('success', __('panel.contract_updated_sent', ['count' => $sent]));
    }
}
