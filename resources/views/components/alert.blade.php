@props([
    'type' => 'info',
    'title' => null,
    'dismissible' => false,
])

@php
    $typeConfig = match($type) {
        'success' => [
            'border' => 'border-emerald-200',
            'bg' => 'bg-emerald-50/80',
            'text' => 'text-emerald-900',
            'icon' => '<svg class="h-5 w-5 shrink-0 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>'
        ],
        'error', 'danger' => [
            'border' => 'border-rose-200',
            'bg' => 'bg-rose-50/80',
            'text' => 'text-rose-900',
            'icon' => '<svg class="h-5 w-5 shrink-0 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>'
        ],
        'warning' => [
            'border' => 'border-amber-200',
            'bg' => 'bg-amber-50/80',
            'text' => 'text-amber-900',
            'icon' => '<svg class="h-5 w-5 shrink-0 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>'
        ],
        default => [
            'border' => 'border-sky-200',
            'bg' => 'bg-sky-50/80',
            'text' => 'text-sky-900',
            'icon' => '<svg class="h-5 w-5 shrink-0 text-sky-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>'
        ],
    };
@endphp

<div {{ $attributes->merge(['class' => "flex items-start gap-3 rounded-xl border p-4 text-sm {$typeConfig['border']} {$typeConfig['bg']} {$typeConfig['text']}"]) }} role="alert">
    <div class="mt-0.5">
        {!! $typeConfig['icon'] !!}
    </div>
    <div class="flex-1">
        @if($title)
            <p class="font-bold text-sm mb-0.5">{{ $title }}</p>
        @endif
        <div class="leading-relaxed">
            {{ $slot }}
        </div>
    </div>
</div>
