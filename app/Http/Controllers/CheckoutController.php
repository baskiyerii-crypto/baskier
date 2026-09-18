<?php

namespace App\Http\Controllers;

use App\Domain\OrderStatus;
use App\Models\Address;
use App\Models\BillingProfile;
use App\Models\Contract;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\CheckoutService;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CheckoutController extends Controller
{
    public function __construct(
        private CheckoutService $checkoutService,
        private PaymentService $paymentService
    ) {}

    public function index(Request $request)
    {
        $user = $request->user();

        // Support isolated Quick Buy session
        $quickBuy = $request->session()->get('quick_buy');
        $customItem = null;
        if ($quickBuy && ! empty($quickBuy['product_id'])) {
            $product = Product::with(['vendor', 'category'])->find($quickBuy['product_id']);
            if ($product) {
                $variant = ! empty($quickBuy['variant_id'])
                    ? ProductVariant::find($quickBuy['variant_id'])
                    : null;
                $quantity = max(1, (int) ($quickBuy['quantity'] ?? 1));

                $basePrice = (string) $product->price;
                $adj = $variant ? (string) ($variant->price_adjustment ?? 0) : '0';
                $unitPrice = bcadd($basePrice, $adj, 2);
                $lineTotal = bcmul($unitPrice, (string) $quantity, 2);

                $customItem = (object) [
                    'product' => $product,
                    'variant' => $variant,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'line_total' => $lineTotal,
                ];
            }
        }

        if (! $customItem) {
            $items = $user->cartItems()->with(['product.vendor', 'product.category', 'variant'])->get();
            if ($items->isEmpty()) {
                return redirect()->route('cart.index')->with('info', 'Sepetiniz boş.');
            }
            $total = '0.00';
            foreach ($items as $item) {
                $total = bcadd($total, $item->lineTotal(), 2);
            }
        } else {
            $items = collect([$customItem]);
            $total = $customItem->line_total;
        }

        $addresses = $user->addresses()->orderByDesc('is_default')->get();
        $defaultBillingAddress = $user->addresses()->where('is_billing_default', true)->latest()->first();
        $billingProfile = $user->billingProfiles()->latest('updated_at')->first();
        $distanceSalesContract = Contract::query()->where('key', 'distance_sales')->where('is_active', true)->first();
        $kvkkContract = Contract::query()->where('key', 'kvkk')->where('is_active', true)->first();
        $carriers = app(\App\Services\BasitKargoService::class)->carriers();
        $paymentProvider = $this->paymentService->provider();

        return view('checkout.index', compact(
            'items',
            'total',
            'addresses',
            'billingProfile',
            'defaultBillingAddress',
            'distanceSalesContract',
            'kvkkContract',
            'carriers',
            'paymentProvider',
            'quickBuy'
        ));
    }

    public function store(Request $request)
    {
        $methods = ['credit_card', 'bank_transfer', 'cash_on_delivery'];
        $validated = $request->validate([
            'shipping_address_id' => ['required', 'exists:addresses,id'],
            'use_shipping_for_billing' => ['nullable', 'boolean'],
            'billing_address_id' => ['nullable', 'exists:addresses,id'],
            'payment_method' => ['required', Rule::in($methods)],
            'accept_distance_sales' => ['accepted'],
            'accept_kvkk' => ['accepted'],
            'idempotency_key' => ['nullable', 'string', 'max:100'],
            'bank_iban' => ['nullable', 'string', 'max:34'],
            'contract_scrolled_at' => ['required', 'string', 'max:40'],
            'kvkk_scrolled_at' => ['required', 'string', 'max:40'],
            'invoice_type' => ['required', Rule::in(['individual', 'corporate'])],
            'invoice_full_name' => ['required', 'string', 'max:255'],
            'invoice_email' => ['required', 'email', 'max:190'],
            'invoice_phone' => ['required', 'string', 'max:15'],
            'invoice_identity_number' => ['nullable', 'string', 'max:16'],
            'invoice_company_name' => ['nullable', 'string', 'max:255'],
            'invoice_tax_number' => ['nullable', 'string', 'max:16'],
            'invoice_tax_office' => ['nullable', 'string', 'max:120'],
        ]);

        if (($validated['payment_method'] ?? '') === 'bank_transfer') {
            $ibanRule = app()->getLocale() === 'tr'
                ? ['required', 'regex:/^TR[0-9]{24}$/i']
                : ['required', 'regex:/^[A-Z]{2}[0-9A-Z]{13,32}$/i', 'max:34'];
            $request->validate(['bank_iban' => $ibanRule]);
        }

        if ($validated['invoice_type'] === 'corporate') {
            $request->validate([
                'invoice_company_name' => ['required', 'string', 'max:255'],
                'invoice_tax_number' => ['required', 'string', 'max:16'],
                'invoice_tax_office' => ['required', 'string', 'max:120'],
            ]);
        }

        // Check if this is an isolated Quick Buy session
        $quickBuy = $request->session()->get('quick_buy');
        $customItems = null;
        if ($quickBuy && ! empty($quickBuy['product_id'])) {
            $customItems = [[
                'product_id' => (int) $quickBuy['product_id'],
                'variant_id' => ! empty($quickBuy['variant_id']) ? (int) $quickBuy['variant_id'] : null,
                'quantity' => max(1, (int) ($quickBuy['quantity'] ?? 1)),
            ]];
        }

        $result = $this->checkoutService->processCheckout($request->user(), array_merge($validated, [
            'custom_items' => $customItems,
            'idempotency_key' => $validated['idempotency_key'] ?? $request->input('idempotency_key'),
        ]));

        if (! $result['success']) {
            return back()->withInput()->with('error', $result['error'] ?? 'Sipariş oluşturulamadı.');
        }

        // Clear quick buy session once successfully dispatched
        if ($customItems) {
            $request->session()->forget('quick_buy');
        }

        // If payment gateway requires hosted form or redirect (iyzico)
        if (! empty($result['requires_action'])) {
            if (! empty($result['payment_page_url'])) {
                return redirect()->away($result['payment_page_url']);
            }
            if (! empty($result['checkout_form_content'])) {
                return response()->view('checkout.iyzico', [
                    'checkoutFormContent' => $result['checkout_form_content'],
                ]);
            }
        }

        // Manual / offline methods (Havale / Kapıda Ödeme)
        $orders = $result['orders'] ?? [];
        $count = count($orders);
        $first = $orders[0]->order_number ?? '';
        $msg = $count > 1
            ? "{$count} adet siparişiniz alındı (İlk sipariş no: #{$first}). Ödemeniz onaylandıktan sonra üretime başlanacaktır."
            : "Siparişiniz alındı (Sipariş no: #{$first}). Ödemeniz onaylandıktan sonra üretime başlanacaktır.";

        return redirect()->route('account.orders.index')->with('success', $msg);
    }

    public function quickBuy(Request $request, Product $product)
    {
        $validated = $request->validate([
            'variant_id' => ['nullable', 'exists:product_variants,id'],
            'quantity' => ['nullable', 'integer', 'min:1', 'max:999'],
        ]);

        $request->session()->put('quick_buy', [
            'product_id' => $product->id,
            'variant_id' => $validated['variant_id'] ?? null,
            'quantity' => $validated['quantity'] ?? 1,
        ]);

        return redirect()->route('checkout.index');
    }

    public function cancelQuickBuy(Request $request)
    {
        $request->session()->forget('quick_buy');

        return redirect()->route('checkout.index');
    }

    public function shopifyReturn(Request $request, \App\Models\Order $order)
    {
        abort(404, 'Shopify entegrasyonu kapalıdır.');
    }
}
