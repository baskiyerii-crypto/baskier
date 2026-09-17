@props([
    'responsive' => true,
])

<div class="{{ $responsive ? 'w-full overflow-x-auto rounded-xl border border-[#DEDAD2] bg-white shadow-xs' : '' }}">
    <table {{ $attributes->merge(['class' => 'w-full text-left text-sm border-collapse']) }}>
        {{ $slot }}
    </table>
</div>
