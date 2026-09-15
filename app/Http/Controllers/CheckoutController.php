<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Models\BillingProfile;
use App\Models\Contract;
use App\Models\OrderContractAcceptance;
use App\Services\MarketplaceOrderService;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CheckoutController extends Controller
{
    public function __construct(
        private MarketplaceOrderService $orderService,
        private PaymentService $paymentService
    ) {}

    public function index(Request $request)
    {
        $user = $request->user();
        $items = $user->cartItems()->with(['product.vendor', 'product.category', 'variant'])->get();
        if ($items->isEmpty()) {
            return redirect()->route('cart.index')->with('info', 'Sepetiniz boş.');
        }
        $total = '0';
        foreach ($items as $item) {
            $total = bcadd($total, $item->lineTotal(), 2);
        }
        $addresses = $user->addresses()->orderByDesc('is_default')->get();
        $defaultBillingAddress = $user->addresses()->where('is_billing_default', true)->latest()->first();
        $billingProfile = $user->billingProfiles()->latest('updated_at')->first();
        $distanceSalesContract = Contract::query()->where('key', 'distance_sales')->where('is_active', true)->first();
        $carriers = app(\App\Services\BasitKargoService::class)->carriers();

        return view('checkout.index', compact('items', 'total', 'addresses', 'billingProfile', 'defaultBillingAddress', 'distanceSalesContract', 'carriers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'shipping_address_id' => ['required', 'exists:addresses,id'],
            'use_shipping_for_billing' => ['nullable', 'boolean'],
            'billing_address_id' => ['nullable', 'exists:addresses,id'],
            'payment_method' => ['required', Rule::in(['credit_card', 'bank_transfer', 'cash_on_delivery'])],
            'accept_distance_sales' => ['accepted'],
            'card_holder_name' => ['nullable', 'string', 'max:120'],
            'card_number' => ['nullable', 'string', 'max:32'],
            'card_expiry' => ['nullable', 'string', 'max:7'],
            'card_cvc' => ['nullable', 'string', 'max:4'],
            'bank_iban' => ['nullable', 'string', 'max:34'],
            'invoice_type' => ['required', Rule::in(['individual', 'corporate'])],
            'invoice_full_name' => ['required', 'string', 'max:255'],
            'invoice_email' => ['required', 'email', 'max:190'],
            'invoice_phone' => ['required', 'string', 'max:11', 'regex:/^[0-9]{10,11}$/'],
            'invoice_identity_number' => ['nullable', 'string', 'max:16'],
            'invoice_company_name' => ['nullable', 'string', 'max:255'],
            'invoice_tax_number' => ['nullable', 'string', 'max:16'],
            'invoice_tax_office' => ['nullable', 'string', 'max:120'],
        ]);

        if ($validated['invoice_type'] === 'corporate') {
            $request->validate([
                'invoice_company_name' => ['required', 'string', 'max:255'],
                'invoice_tax_number' => ['required', 'string', 'max:16'],
                'invoice_tax_office' => ['required', 'string', 'max:120'],
            ]);
        }
        if ($validated['payment_method'] === 'credit_card') {
            $request->validate([
                'card_holder_name' => ['required', 'string', 'max:120'],
                'card_number' => ['required', 'string', 'min:12', 'max:32'],
                'card_expiry' => ['required', 'string', 'max:7'],
                'card_cvc' => ['required', 'string', 'min:3', 'max:4'],
            ]);
        }
        if ($validated['payment_method'] === 'bank_transfer') {
            $request->validate([
                'bank_iban' => ['required', 'string', 'min:10', 'max:34'],
            ]);
        }

        $shippingAddress = Address::findOrFail($validated['shipping_address_id']);
        if ($shippingAddress->user_id !== $request->user()->id) {
            abort(403);
        }

        $useShippingForBilling = (bool) ($validated['use_shipping_for_billing'] ?? false);
        $billingAddress = $shippingAddress;
        if (! $useShippingForBilling && ! empty($validated['billing_address_id'])) {
            $billingAddress = Address::findOrFail($validated['billing_address_id']);
            if ($billingAddress->user_id !== $request->user()->id) {
                abort(403);
            }
        } elseif (! $useShippingForBilling) {
            $billingAddress = $request->user()->addresses()->where('is_billing_default', true)->latest()->first() ?? $shippingAddress;
        }

        $invoiceData = [
            'invoice_type' => $validated['invoice_type'],
            'invoice_full_name' => $validated['invoice_full_name'],
            'invoice_email' => $validated['invoice_email'],
            'invoice_phone' => $validated['invoice_phone'],
            'invoice_identity_number' => $validated['invoice_type'] === 'individual'
                ? ($validated['invoice_identity_number'] ?? null)
                : null,
            'invoice_company_name' => $validated['invoice_type'] === 'corporate'
                ? ($validated['invoice_company_name'] ?? null)
                : null,
            'invoice_tax_number' => $validated['invoice_type'] === 'corporate'
                ? ($validated['invoice_tax_number'] ?? null)
                : null,
            'invoice_tax_office' => $validated['invoice_type'] === 'corporate'
                ? ($validated['invoice_tax_office'] ?? null)
                : null,
        ];
        $paymentData = [
            'payment_method' => $validated['payment_method'],
        ];

        BillingProfile::query()->updateOrCreate(
            ['user_id' => $request->user()->id, 'invoice_type' => $invoiceData['invoice_type']],
            [
                'full_name' => $invoiceData['invoice_full_name'],
                'email' => $invoiceData['invoice_email'],
                'phone' => $invoiceData['invoice_phone'],
                'identity_number' => $invoiceData['invoice_identity_number'],
                'company_name' => $invoiceData['invoice_company_name'],
                'tax_number' => $invoiceData['invoice_tax_number'],
                'tax_office' => $invoiceData['invoice_tax_office'],
            ],
        );

        try {
            $orders = $this->orderService->createPaidOrdersFromCart(
                $request->user(),
                $shippingAddress,
                $billingAddress,
                $invoiceData,
                $paymentData
            );

            $contract = Contract::query()->where('key', 'distance_sales')->where('is_active', true)->first();
            foreach ($orders as $order) {
                if ($contract) {
                    OrderContractAcceptance::create([
                        'user_id' => $request->user()->id,
                        'order_id' => $order->id,
                        'contract_id' => $contract->id,
                        'ip' => $request->ip(),
                        'scrolled_at' => $request->input('contract_scrolled_at') ? now() : now(),
                        'accepted_at' => now(),
                    ]);
                }
                if (($paymentData['payment_method'] ?? '') === 'credit_card') {
                    try {
                        $this->paymentService->chargeCard($order, [
                            'card_holder_name' => $validated['card_holder_name'] ?? '',
                            'card_number' => $validated['card_number'] ?? '',
                            'card_expiry' => $validated['card_expiry'] ?? '',
                            'card_cvc' => $validated['card_cvc'] ?? '',
                        ]);
                    } catch (\Throwable $e) {
                        return redirect()->route('account.orders.index')
                            ->with('error', 'Sipariş oluşturuldu ancak kart ödemesi başarısız: '.$e->getMessage());
                    }
                } else {
                    $this->paymentService->recordDemoPayment($order);
                }
            }
        } catch (\InvalidArgumentException $e) {
            return redirect()->route('cart.index')->with('error', $e->getMessage());
        }

        $first = $orders[0]->order_number;
        $msg = count($orders) > 1
            ? count($orders) . ' sipariş oluşturuldu. İlk sipariş no: #' . $first
            : 'Siparişiniz alındı. Sipariş no: #' . $first;

        return redirect()->route('account.orders.index')->with('success', $msg);
    }
}
