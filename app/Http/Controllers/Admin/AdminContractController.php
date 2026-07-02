<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Contract;
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

    public function store(Request $request)
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
        Contract::create($validated);

        return redirect()->route('admin.contracts.index')->with('success', 'Sözleşme eklendi.');
    }

    public function edit(Contract $contract)
    {
        return view('admin.contracts.edit', compact('contract'));
    }

    public function update(Request $request, Contract $contract)
    {
        $validated = $request->validate([
            'key' => ['required', 'string', 'max:64', 'unique:contracts,key,' . $contract->id],
            'title' => ['required', 'string', 'max:255'],
            'audience' => ['required', 'in:all,customer,vendor'],
            'version' => ['required', 'integer', 'min:1', 'max:9999'],
            'is_active' => ['boolean'],
            'content_html' => ['nullable', 'string'],
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        $contract->update($validated);

        return redirect()->route('admin.contracts.index')->with('success', 'Sözleşme güncellendi.');
    }
}

