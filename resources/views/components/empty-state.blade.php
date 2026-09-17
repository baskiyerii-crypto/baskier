@props([
    'title' => 'Kayıt bulunamadı',
    'message' => 'Aramanıza veya filtrenize uygun sonuç bulunamadı.',
    'actionText' => null,
    'actionUrl' => null,
    'icon' => null,
])

<div {{ $attributes->merge(['class' => 'rounded-xl border border-dashed border-[#DEDAD2] bg-white p-8 sm:p-12 text-center my-4']) }}>
    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-[#F7F5F0] text-[#596166] mb-4">
        @if($icon)
            {!! $icon !!}
        @else
            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        @endif
    </div>

    <h3 class="text-base font-bold text-[#182023] mb-1">
        {{ $title }}
    </h3>

    <p class="text-sm text-[#596166] max-w-md mx-auto mb-5 leading-relaxed">
        {{ $message }}
    </p>

    @if($actionText && $actionUrl)
        <a href="{{ $actionUrl }}" class="inline-flex min-h-[40px] items-center justify-center gap-2 rounded-lg bg-[#C2410C] px-4 py-2 text-xs font-bold text-white shadow-xs hover:bg-[#9A3412] transition">
            {{ $actionText }}
        </a>
    @endif

    {{ $slot }}
</div>
