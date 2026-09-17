@props([
    'vendor' => null,
    'level' => null,
    'size' => 'md',
    'showLabel' => true,
])

@php
    $lvl = $level !== null ? (int) $level : (int) ($vendor?->trust_level ?? 0);
    if ($vendor && $vendor->is_suspended) {
        $lvl = 0;
    }

    $iconSize = match($size) {
        'sm' => '14',
        'lg' => '20',
        default => '16',
    };

    $badgeClass = match($lvl) {
        1 => 'border-sky-200 bg-sky-50 text-sky-800',
        2 => 'border-emerald-200 bg-emerald-50 text-emerald-800',
        3 => 'border-amber-200 bg-amber-50 text-amber-900 font-bold',
        default => 'border-slate-200 bg-slate-50 text-slate-500',
    };

    $label = \App\Domain\TrustLevel::label($lvl);
    $badgeName = \App\Domain\TrustLevel::badgeName($lvl);
@endphp

@if($lvl > 0)
    <span class="inline-flex items-center gap-1 rounded-full border px-2 py-0.5 text-xs font-semibold {{ $badgeClass }} trust-badge" title="{{ $label }}" data-trust-level="{{ $lvl }}">
        {{-- Tik Simgeleri --}}
        <span class="inline-flex items-center -space-x-1" aria-hidden="true">
            @for($i = 1; $i <= $lvl; $i++)
                <svg class="inline-block shrink-0" width="{{ $iconSize }}" height="{{ $iconSize }}" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" />
                </svg>
            @endfor
        </span>

        @if($showLabel)
            <span class="tracking-tight text-[11px]">{{ $badgeName }}</span>
        @endif
    </span>
@endif
