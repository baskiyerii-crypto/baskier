<?php

namespace App\Http\Controllers\Api\V1;

use App\Domain\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\FreelancerJobBid;
use App\Models\FreelancerJobListing;
use App\Models\Order;
use App\Models\Setting;
use App\Models\Vendor;
use Database\Seeders\PlatformVendorSeeder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FreelancerJobController extends Controller
{
    public function index(Request $request)
    {
        $query = FreelancerJobListing::where('status', 'open')->with('user')->latest();
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        return response()->json($query->paginate(20)->withQueryString());
    }

    public function show(Request $request, FreelancerJobListing $job)
    {
        if ($job->status !== 'open' && $request->user()?->id !== $job->user_id) {
            abort(404);
        }
        $job->load([
            'user',
            'bids' => fn ($q) => $q->with('user')->latest(),
        ]);
        $job->loadCount('bids');

        return response()->json($job);
    }

    public function store(Request $request)
    {
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

        return response()->json($job, 201);
    }

    public function storeBid(Request $request, FreelancerJobListing $job)
    {
        if ($job->status !== 'open') {
            return response()->json(['message' => 'İlan kapalı.'], 422);
        }
        if ($job->user_id === $request->user()->id) {
            return response()->json(['message' => 'Kendi ilanınıza teklif veremezsiniz.'], 422);
        }

        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:0'],
            'delivery_days' => ['nullable', 'integer', 'min:1', 'max:365'],
            'proposal' => ['nullable', 'string', 'max:5000'],
        ]);

        $bid = FreelancerJobBid::create([
            'freelancer_job_listing_id' => $job->id,
            'user_id' => $request->user()->id,
            'amount' => $validated['amount'],
            'delivery_days' => $validated['delivery_days'] ?? null,
            'proposal' => $validated['proposal'] ?? null,
            'status' => 'pending',
        ]);

        return response()->json($bid->load('user'), 201);
    }

    public function selectBid(Request $request, FreelancerJobListing $job, FreelancerJobBid $bid)
    {
        if ($job->user_id !== $request->user()->id) {
            abort(403);
        }
        if ($bid->freelancer_job_listing_id !== $job->id || $bid->status !== 'pending') {
            return response()->json(['message' => 'Geçersiz teklif.'], 400);
        }

        $platformVendor = Vendor::where('slug', PlatformVendorSeeder::SLUG)->first();
        if (! $platformVendor) {
            return response()->json(['message' => 'Platform satıcı kaydı eksik. php artisan db:seed --class=PlatformVendorSeeder'], 500);
        }

        return DB::transaction(function () use ($job, $bid, $request, $platformVendor) {
            $bid->update(['status' => 'selected']);
            $job->bids()->where('id', '!=', $bid->id)->update(['status' => 'rejected']);
            $job->update(['status' => 'closed', 'closed_at' => now()]);

            $commissions = app(\App\Services\CommissionService::class);
            [$rate, $commissionAmount, $vendorAmount] = $commissions->calculate((float) $bid->amount, 'freelancer');
            $waitDays = Setting::commissionWaitDays();

            $order = Order::create([
                'order_number' => Order::generateOrderNumber(),
                'user_id' => $job->user_id,
                'vendor_id' => $platformVendor->id,
                'contractor_user_id' => $bid->user_id,
                'type' => 'freelancer',
                'freelancer_job_id' => $job->id,
                'status' => OrderStatus::CONFIRMED,
                'payment_status' => 'paid',
                'subtotal' => $bid->amount,
                'commission_rate' => $rate,
                'commission_amount' => $commissionAmount,
                'vendor_amount' => $vendorAmount,
                'paid_at' => now(),
                'commission_ready_at' => now()->addDays($waitDays),
                'shipping_address' => 'Freelancer proje: ' . $job->title,
            ]);

            $order->items()->create([
                'name' => $job->title . ' (freelancer)',
                'price' => $bid->amount,
                'quantity' => 1,
            ]);

            return response()->json(['order' => $order->load('contractor'), 'job' => $job->fresh()]);
        });
    }

    /**
     * Web’deki “İlanlarım” ile aynı kapsam.
     */
    public function myListings(Request $request)
    {
        $jobs = FreelancerJobListing::where('user_id', $request->user()->id)
            ->with('user')
            ->latest()
            ->paginate(20);

        return response()->json($jobs);
    }
}
