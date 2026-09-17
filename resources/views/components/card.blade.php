@props([
    'padding' => 'p-6',
    'hover' => false,
    'variant' => 'default', // default (white), tinted (canvas), muted
])

@php
    $bgClass = match($variant) {
        'tinted' => 'bg-[#F7F5F0]',
        'muted' => 'bg-slate-50',
        default => 'bg-white',
    };

    $hoverClass = $hover ? 'transition duration-150 ease-out hover:-translate-y-0.5 hover:shadow-md' : '';
@endphp

<div {{ $attributes->merge(['class' => "rounded-xl border border-[#DEDAD2] {$bgClass} {$padding} shadow-xs {$hoverClass}"]) }}>
    {{ $slot }}
</div>
