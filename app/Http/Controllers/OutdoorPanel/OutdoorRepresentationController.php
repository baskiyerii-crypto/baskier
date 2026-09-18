<?php

namespace App\Http\Controllers\OutdoorPanel;

use App\Http\Controllers\Controller;
use App\Models\OohRepresentation;
use App\Services\OutdoorRepresentationService;
use Illuminate\Http\Request;
use RuntimeException;

class OutdoorRepresentationController extends Controller
{
    public function __construct(private OutdoorRepresentationService $representations) {}

    public function index(Request $request)
    {
        $vendor = $this->vendor($request);
        $asOwner = collect();
        $asAgency = collect();
        if ($this->representations->tablesReady()) {
            $asOwner = $vendor->outdoorRepresentationsAsOwner()->with('agency.user')->latest()->get();
            $asAgency = $vendor->outdoorRepresentationsAsAgency()->with('owner.user')->latest()->get();
        }

        return view('outdoor-panel.representations', compact('vendor', 'asOwner', 'asAgency'));
    }

    public function invite(Request $request)
    {
        $vendor = $this->vendor($request);
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'exclusive' => ['sometimes', 'boolean'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);
        try {
            $this->representations->invite(
                $vendor,
                $validated['email'],
                (bool) ($validated['exclusive'] ?? false),
                $validated['notes'] ?? null
            );
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Davet gönderildi. Karşı tarafın onaylaması gerekir.');
    }

    public function accept(Request $request, OohRepresentation $representation)
    {
        $vendor = $this->vendor($request);
        try {
            $this->representations->accept($representation, $vendor);
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Temsil bağı onaylandı.');
    }

    public function revoke(Request $request, OohRepresentation $representation)
    {
        $vendor = $this->vendor($request);
        try {
            $this->representations->revoke($representation, $vendor);
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Temsil bağı iptal edildi.');
    }

    private function vendor(Request $request)
    {
        $vendor = $request->user()->vendor;
        if (! $vendor || ! $vendor->hasOutdoorTrack()) {
            abort(403, 'Açık hava hesabı değil.');
        }

        return $vendor;
    }
}
