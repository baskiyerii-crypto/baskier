@props([
    'title',
    'eyebrow' => null,
    'description' => null,
])

<div {{ $attributes->merge(['class' => 'flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between mb-6 pb-4 border-b border-[#DEDAD2]']) }}>
    <div>
        @if($eyebrow)
            <p class="text-xs font-bold uppercase tracking-wider text-[#596166] mb-1">
                {{ $eyebrow }}
            </p>
        @endif
        <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-[#182023]">
            {{ $title }}
        </h1>
        @if($description)
            <p class="mt-1.5 text-sm text-[#596166] max-w-2xl leading-relaxed">
                {{ $description }}
            </p>
        @endif
    </div>

    @if(isset($actions))
        <div class="flex flex-wrap items-center gap-2 shrink-0">
            {{ $actions }}
        </div>
    @endif
</div>
