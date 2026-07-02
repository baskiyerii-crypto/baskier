<?php

namespace App\Http\Controllers;

use App\Models\FreelancerJobListing;
use Illuminate\Http\Request;

class FreelancerJobController extends Controller
{
    public function index(Request $request)
    {
        $query = FreelancerJobListing::where('status', 'open')
            ->with('user')
            ->latest();

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        $jobs = $query->paginate(12)->withQueryString();
        $currentCategory = $request->category;

        return view('freelancer-jobs.index', compact('jobs', 'currentCategory'));
    }

    public function show(Request $request, FreelancerJobListing $job)
    {
        if ($job->status !== 'open' && $request->user()?->id !== $job->user_id) {
            abort(404);
        }
        $job->load(['user', 'bids' => fn ($q) => $q->with('user')->latest()]);
        $job->loadCount('bids');

        return view('freelancer-jobs.show', compact('job'));
    }
}
