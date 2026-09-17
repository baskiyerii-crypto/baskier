@props([
    'name',
    'label' => null,
    'required' => false,
    'error' => null,
    'hint' => null,
    'disabled' => false,
])

@php
    $actualError = $error ?? ($errors->has($name) ? $errors->first($name) : null);
    $hasError = !empty($actualError);
    $selectId = $attributes->get('id', 'select_' . $name . '_' . Str::random(4));
@endphp

<div class="w-full">
    @if($label)
        <label for="{{ $selectId }}" class="block mb-1.5 text-xs font-semibold text-[#182023]">
            {{ $label }}
            @if($required)
                <span class="text-rose-500 font-bold" aria-hidden="true">*</span>
            @endif
        </label>
    @endif

    <div class="relative">
        <select
            id="{{ $selectId }}"
            name="{{ $name }}"
            {{ $required ? 'required' : '' }}
            {{ $disabled ? 'disabled' : '' }}
            {{ $attributes->merge([
                'class' => 'w-full min-h-[44px] rounded-lg border bg-white px-3.5 py-2.5 text-sm text-[#182023] transition duration-150 ease-out outline-none appearance-none ' . 
                    ($hasError 
                        ? 'border-rose-400 focus:border-rose-500 focus:ring-2 focus:ring-rose-500/20' 
                        : 'border-[#DEDAD2] hover:border-[#182023]/40 focus:border-[#C2410C] focus:ring-2 focus:ring-[#C2410C]/20') . 
                    ($disabled ? ' bg-slate-50 opacity-60 cursor-not-allowed' : '')
            ]) }}
        >
            {{ $slot }}
        </select>
        <span class="pointer-events-none absolute right-3.5 top-1/2 -translate-y-1/2 text-[#596166]">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="m6 9 6 6 6-6"/></svg>
        </span>
    </div>

    @if($hasError)
        <p class="mt-1 text-xs text-rose-600 font-medium" role="alert">{{ $actualError }}</p>
    @elseif($hint)
        <p class="mt-1 text-xs text-[#596166]">{{ $hint }}</p>
    @endif
</div>
