<?php

namespace App\Http\Controllers;

use App\Models\Contract;

class ContractController extends Controller
{
    public function show(string $key)
    {
        $contract = Contract::where('key', $key)->where('is_active', true)->firstOrFail();
        return view('contracts.show', compact('contract'));
    }
}

