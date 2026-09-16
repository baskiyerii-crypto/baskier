<?php

namespace App\Http\Controllers;

use App\Domain\OrderStatus;
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
    public function create()
    {
        return view('freelancer-jobs.create');
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

        return redirect()->route('freelancer-jobs.show', $job)->with('success', 'İlanınız yayında.');
    }

    public function myListings(Request $request)
    {
        $jobs = $request->user()->freelancerJobListings()->withCount('bids')->latest()->paginate(15);

        return view('freelancer-jobs.my', compact('jobs'));
    }

    public function storeBid(Request $request, FreelancerJobListing $job)
    {
        if ($job->status !== 'open') {
            return back()->with('error', 'İlan kapalı.');
        }
        if ($job->user_id === $request->user()->id) {
            return back()->with('error', 'Kendi ilanınıza teklif veremezsiniz.');
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
        if ($job->user_id !== $request->user()->id) {
            abort(403);
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

            $rate = Setting::commissionRate();
            $subtotal = $bid->amount;
            $commissionAmount = round($subtotal * $rate / 100, 2);
            $vendorAmount = $subtotal - $commissionAmount;
            $waitDays = Setting::commissionWaitDays();

            $newOrder = Order::create([
                'order_number' => Order::generateOrderNumber(),
                'user_id' => $job->user_id,
                'vendor_id' => $platformVendor->id,
                'contractor_user_id' => $bid->user_id,
                'type' => 'freelancer',
                'freelancer_job_id' => $job->id,
                'status' => OrderStatus::CONFIRMED,
                'payment_status' => 'paid',
                'subtotal' => $subtotal,
                'commission_rate' => $rate,
                'commission_amount' => $commissionAmount,
                'vendor_amount' => $vendorAmount,
                'paid_at' => now(),
                'commission_ready_at' => now()->addDays($waitDays),
                'shipping_address' => 'Freelancer proje: ' . $job->title,
            ]);

            $newOrder->items()->create([
                'name' => $job->title . ' (freelancer)',
                'price' => $subtotal,
                'quantity' => 1,
            ]);

            return $newOrder;
        });

        return redirect()->route('account.orders.show', $order)
            ->with('success', 'Teklif seçildi ve sipariş oluşturuldu.');
    }
}