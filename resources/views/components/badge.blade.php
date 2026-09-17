@props([
    'variant' => 'neutral',
    'size' => 'md',
])

@php
    $sizeClass = match($size) {
        'sm' => 'px-2 py-0.5 text-[10px]',
        'lg' => 'px-3 py-1 text-xs',
        default => 'px-2.5 py-0.5 text-xs',
    };

    $variantClass = match($variant) {
        'primary' => 'bg-orange-50 text-[#C2410C] border-orange-200',
        'success' => 'bg-emerald-50 text-emerald-800 border-emerald-200',
        'warning' => 'bg-amber-50 text-amber-800 border-amber-200',
        'danger' => 'bg-rose-50 text-rose-800 border-rose-200',
        'info' => 'bg-sky-50 text-sky-800 border-sky-200',
        default => 'bg-[#F7F5F0] text-[#182023] border-[#DEDAD2]',
    };
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center gap-1 font-semibold rounded-md border {$sizeClass} {$variantClass}"]) }}>
    {{ $slot }}
</span>
