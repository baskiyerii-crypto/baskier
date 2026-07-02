<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Order;
use Illuminate\Http\Request;

class VendorOrderController extends ApiController
{
    public function show(Request $request, Order $order)
    {
        $this->authorize('view', $order);

        $order->load(['vendor', 'user', 'items.product', 'contractor', 'quote', 'designApprovals']);

        return $this->ok($order);
    }
}

