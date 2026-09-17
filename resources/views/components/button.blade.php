@props([
    'variant' => 'primary',
    'size' => 'md',
    'href' => null,
    'type' => 'submit',
    'disabled' => false,
    'fullWidth' => false,
])

@php
    $baseClasses = 'inline-flex items-center justify-center gap-2 font-semibold transition duration-150 ease-out focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 disabled:opacity-50 disabled:cursor-not-allowed select-none';

    $sizeClasses = match($size) {
        'sm' => 'min-h-[36px] px-3.5 py-1.5 text-xs rounded-lg',
        'lg' => 'min-h-[48px] px-6 py-3 text-base rounded-lg',
        default => 'min-h-[44px] px-5 py-2.5 text-sm rounded-lg',
    };

    $variantClasses = match($variant) {
        'primary' => 'bg-[#C2410C] hover:bg-[#9A3412] text-white shadow-xs focus-visible:outline-[#C2410C]',
        'secondary' => 'bg-white hover:bg-[#F7F5F0] text-[#182023] border border-[#DEDAD2] shadow-xs focus-visible:outline-[#182023]',
        'outline' => 'bg-transparent hover:bg-orange-50 text-[#C2410C] border border-[#C2410C] focus-visible:outline-[#C2410C]',
        'danger' => 'bg-rose-600 hover:bg-rose-700 text-white shadow-xs focus-visible:outline-rose-600',
        'ghost' => 'bg-transparent hover:bg-black/5 text-[#596166] hover:text-[#182023] focus-visible:outline-[#182023]',
        default => 'bg-[#C2410C] hover:bg-[#9A3412] text-white shadow-xs focus-visible:outline-[#C2410C]',
    };

    $widthClass = $fullWidth ? 'w-full' : '';

    $classes = "{$baseClasses} {$sizeClasses} {$variantClasses} {$widthClass}";
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $disabled ? 'disabled' : '' }} {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </button>
@endif
