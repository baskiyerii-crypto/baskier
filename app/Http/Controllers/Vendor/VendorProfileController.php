<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\VendorProfileChangeRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class VendorProfileController extends Controller
{
    public function edit(Request $request)
    {
        $vendor = $request->user()->vendor;
        abort_unless($vendor, 403);

        return view('vendor.profile.edit', compact('vendor'));
    }

    public function update(Request $request)
    {
        $vendor = $request->user()->vendor;
        abort_unless($vendor, 403);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'phone' => ['nullable', 'string', 'max:32'],
            'email' => ['nullable', 'email', 'max:255'],
            'city' => ['nullable', 'string', 'max:120'],
            'district' => ['nullable', 'string', 'max:120'],
            'address' => ['nullable', 'string', 'max:500'],
            'map_embed_url' => ['nullable', 'string', 'max:500'],
            'map_lat' => ['nullable', 'numeric'],
            'map_lng' => ['nullable', 'numeric'],
            'logo' => ['nullable', 'image', 'max:2048'],
            'cover_image' => ['nullable', 'image', 'max:4096'],
            'social_instagram' => ['nullable', 'string', 'max:255'],
            'social_website' => ['nullable', 'url', 'max:255'],
        ]);

        $payload = collect($validated)->except(['logo', 'cover_image', 'social_instagram', 'social_website'])->all();
        $payload['social_links'] = array_filter([
            'instagram' => $validated['social_instagram'] ?? null,
            'website' => $validated['social_website'] ?? null,
        ]);

        if ($request->hasFile('logo')) {
            $payload['logo'] = $request->file('logo')->store('vendor-logos/'.$vendor->id, 'public');
        }
        if ($request->hasFile('cover_image')) {
            $payload['cover_image'] = $request->file('cover_image')->store('vendor-covers/'.$vendor->id, 'public');
        }

        $vendor->update(['profile_pending_payload' => $payload]);
        VendorProfileChangeRequest::create([
            'vendor_id' => $vendor->id,
            'payload' => $payload,
            'status' => 'pending',
        ]);

        return back()->with('success', __('panel.profile_change_submitted'));
    }
}
