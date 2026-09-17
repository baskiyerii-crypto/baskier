@props([
    'name',
    'label' => null,
    'type' => 'text',
    'value' => null,
    'placeholder' => null,
    'required' => false,
    'error' => null,
    'hint' => null,
    'disabled' => false,
    'readonly' => false,
])

@php
    $actualError = $error ?? ($errors->has($name) ? $errors->first($name) : null);
    $hasError = !empty($actualError);
    $inputId = $attributes->get('id', 'input_' . $name . '_' . Str::random(4));
    $val = old($name, $value);
@endphp

<div class="w-full">
    @if($label)
        <label for="{{ $inputId }}" class="block mb-1.5 text-xs font-semibold text-[#182023]">
            {{ $label }}
            @if($required)
                <span class="text-rose-500 font-bold" aria-hidden="true">*</span>
            @endif
        </label>
    @endif

    <div class="relative">
        <input
            id="{{ $inputId }}"
            name="{{ $name }}"
            type="{{ $type }}"
            value="{{ $val }}"
            placeholder="{{ $placeholder }}"
            {{ $required ? 'required' : '' }}
            {{ $disabled ? 'disabled' : '' }}
            {{ $readonly ? 'readonly' : '' }}
            {{ $attributes->merge([
                'class' => 'w-full min-h-[44px] rounded-lg border bg-white px-3.5 py-2.5 text-sm text-[#182023] placeholder-[#596166]/60 transition duration-150 ease-out outline-none ' . 
                    ($hasError 
                        ? 'border-rose-400 focus:border-rose-500 focus:ring-2 focus:ring-rose-500/20' 
                        : 'border-[#DEDAD2] hover:border-[#182023]/40 focus:border-[#C2410C] focus:ring-2 focus:ring-[#C2410C]/20') . 
                    ($disabled ? ' bg-slate-50 opacity-60 cursor-not-allowed' : '')
            ]) }}
        />
    </div>

    @if($hasError)
        <p class="mt-1 text-xs text-rose-600 font-medium" role="alert">{{ $actualError }}</p>
    @elseif($hint)
        <p class="mt-1 text-xs text-[#596166]">{{ $hint }}</p>
    @endif
</div>
