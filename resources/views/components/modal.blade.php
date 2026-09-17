@props([
    'id',
    'title' => null,
    'maxWidth' => 'max-w-lg', // max-w-md, max-w-lg, max-w-xl, max-w-2xl
])

<div
    id="{{ $id }}"
    class="fixed inset-0 z-50 flex items-center justify-center p-4 opacity-0 pointer-events-none transition-opacity duration-200 ease-out modal-container"
    role="dialog"
    aria-modal="true"
    aria-labelledby="{{ $id }}_title"
    tabindex="-1"
    data-modal
>
    {{-- Backdrop --}}
    <div class="fixed inset-0 bg-[#182023]/60 backdrop-blur-xs transition-opacity modal-backdrop" data-modal-close></div>

    {{-- Dialog Box --}}
    <div class="relative w-full {{ $maxWidth }} rounded-2xl bg-white border border-[#DEDAD2] p-6 shadow-xl transition-all duration-200 ease-out scale-95 modal-box">
        @if($title)
            <div class="flex items-center justify-between border-b border-[#DEDAD2] pb-4 mb-4">
                <h3 id="{{ $id }}_title" class="text-base font-bold text-[#182023] tracking-tight">
                    {{ $title }}
                </h3>
                <button type="button" class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-[#596166] hover:bg-[#F7F5F0] hover:text-[#182023] transition" data-modal-close aria-label="Kapat">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M18 6 6 18M6 6l12 12"/></svg>
                </button>
            </div>
        @endif

        <div class="modal-body">
            {{ $slot }}
        </div>

        @if(isset($footer))
            <div class="mt-6 flex flex-wrap items-center justify-end gap-2 border-t border-[#DEDAD2] pt-4">
                {{ $footer }}
            </div>
        @endif
    </div>
</div>
