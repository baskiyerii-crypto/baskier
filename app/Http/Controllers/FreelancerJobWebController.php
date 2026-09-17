<?php

namespace App\Http\Controllers;

use App\Domain\OrderStatus;
use App\Domain\PaymentStatus;
use App\Models\FreelancerJobBid;
use App\Models\FreelancerJobListing;
use App\Models\Order;
use App\Models\Setting;
use App\Models\Vendor;
use Database\Seeders\PlatformVendorSeeder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FreelancerJobWebController extends Controller
{
    public function create(Request $request)
    {
        if (! $request->user()->isCustomer() || $request->user()->isFreelancer()) {
            abort(403, 'Yalnızca müşteriler hizmet teklifi talebi oluşturabilir.');
        }

        $selectedCategory = $request->get('category');
        $allowed = ['logo', 'wordpress', 'brochure', 'digital', 'other'];
        if (! in_array($selectedCategory, $allowed, true)) {
            $selectedCategory = null;
        }

        return view('freelancer-jobs.create', compact('selectedCategory'));
    }

    public function store(Request $request)
    {
        if (! $request->user()->isCustomer() || $request->user()->isFreelancer()) {
            abort(403, 'Yalnızca müşteriler hizmet teklifi talebi oluşturabilir.');
        }

        $validated = $request->validate([
            'category' => ['required', 'in:logo,wordpress,brochure,digital,other'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'budget_min' => ['nullable', 'numeric', 'min:0'],
            'budget_max' => ['nullable', 'numeric', 'min:0'],
        ]);

        $validated['user_id'] = $request->user()->id;
        $validated['status'] = 'open';
        $job = FreelancerJobListing::create($validated);

        return redirect()->route('service-requests.show', $job)->with('success', 'Hizmet talebiniz yayınlandı.');
    }

    public function myListings(Request $request)
    {
        $jobs = $request->user()->freelancerJobListings()->withCount('bids')->latest()->paginate(15);

        return view('freelancer-jobs.my', compact('jobs'));
    }

    public function storeBid(Request $request, FreelancerJobListing $job)
    {
        if ($job->status !== 'open') {
            return back()->with('error', 'Hizmet talebi kapalı.');
        }
        if ($job->user_id === $request->user()->id) {
            return back()->with('error', 'Kendi talebinize teklif veremezsiniz.');
        }

        $user = $request->user();
        $vendor = $user->vendor;
        $isVerified = ($vendor && $vendor->hasActiveFreelancerModule() && ($vendor->verification_status === 'verified' || $vendor->hasApprovedTaxPlate() || $vendor->documents()->where('status', 'approved')->exists()))
            || ($user->freelancerProfile && $user->freelancerProfile->is_verified);

        if (! $isVerified) {
            return back()->with('error', 'Yalnızca aktif ve doğrulanmış freelancerlar teklif verebilir.');
        }

        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:0'],
            'delivery_days' => ['nullable', 'integer', 'min:1', 'max:365'],
            'proposal' => ['nullable', 'string', 'max:5000'],
        ]);

        FreelancerJobBid::create([
            'freelancer_job_listing_id' => $job->id,
            'user_id' => $request->user()->id,
            'amount' => $validated['amount'],
            'delivery_days' => $validated['delivery_days'] ?? null,
            'proposal' => $validated['proposal'] ?? null,
            'status' => 'pending',
        ]);

        return back()->with('success', 'Teklifiniz gönderildi.');
    }

    public function selectBid(Request $request, FreelancerJobListing $job, FreelancerJobBid $bid)
    {
        if ($job->user_id !== $request->user()->id || ! $request->user()->isCustomer()) {
            abort(403, 'Yalnızca talep sahibi müşteri teklifi seçebilir.');
        }
        if ($bid->freelancer_job_listing_id !== $job->id || $bid->status !== 'pending') {
            return back()->with('error', 'Bu teklif seçilemez.');
        }

        $platformVendor = Vendor::where('slug', PlatformVendorSeeder::SLUG)->first();
        if (! $platformVendor) {
            return back()->with('error', 'Platform yapılandırması eksik. Yöneticiye bildirin.');
        }

        $order = DB::transaction(function () use ($job, $bid, $platformVendor) {
            $bid->update(['status' => 'selected']);
            $job->bids()->where('id', '!=', $bid->id)->update(['status' => 'rejected']);
            $job->update(['status' => 'closed', 'closed_at' => now()]);

            $commissions = app(\App\Services\CommissionService::class);
            [$rate, $commissionAmount, $vendorAmount] = $commissions->calculate((float) $bid->amount, 'freelancer');
            $waitDays = Setting::commissionWaitDays();

            $newOrder = Order::create([
                'order_number' => Order::generateOrderNumber(),
                'user_id' => $job->user_id,
                'vendor_id' => $platformVendor->id,
                'contractor_user_id' => $bid->user_id,
                'type' => 'freelancer',
                'freelancer_job_id' => $job->id,
                'status' => OrderStatus::PENDING_PAYMENT,
                'payment_status' => PaymentStatus::PENDING,
                'subtotal' => $bid->amount,
                'commission_rate' => $rate,
                'commission_amount' => $commissionAmount,
                'vendor_amount' => $vendorAmount,
                'paid_at' => null,
                'commission_ready_at' => null,
                'shipping_address' => 'Freelancer proje: ' . $job->title,
            ]);

            $newOrder->items()->create([
                'name' => $job->title . ' (freelancer)',
                'price' => $bid->amount,
                'quantity' => 1,
            ]);

            return $newOrder;
        });

        return redirect()->route('account.orders.show', $order)
            ->with('success', 'Teklif seçildi ve sipariş oluşturuldu.');
    }
}