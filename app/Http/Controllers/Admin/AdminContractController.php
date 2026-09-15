<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Contract;
use App\Services\ContractPublishService;
use Illuminate\Http\Request;

class AdminContractController extends Controller
{
    public function index()
    {
        $contracts = Contract::orderBy('key')->paginate(20);

        return view('admin.contracts.index', compact('contracts'));
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
