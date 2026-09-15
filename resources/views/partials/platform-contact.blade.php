@php
    $platformAddress = \App\Models\Setting::get('platform_address');
    $platformPhone = \App\Models\Setting::get('platform_phone');
    $platformEmail = \App\Models\Setting::get('platform_email');
    $platformMap = \App\Models\Setting::get('platform_map_embed_url');
    $platformIg = \App\Models\Setting::get('platform_social_instagram');
    $platformWeb = \App\Models\Setting::get('platform_social_website');
    $hasContact = filled($platformAddress) || filled($platformPhone) || filled($platformEmail) || filled($platformIg) || filled($platformWeb) || filled($platformMap);
@endphp
@if($hasContact)
    <div class="mt-4 space-y-2 text-sm text-slate-600">
        @if($platformAddress)
            <p>{{ $platformAddress }}</p>
        @endif
        @if($platformPhone)
            <p><a class="hover:text-slate-900" href="tel:{{ preg_replace('/\s+/', '', $platformPhone) }}">{{ $platformPhone }}</a></p>
        @endif
        @if($platformEmail)
            <p><a class="hover:text-slate-900" href="mailto:{{ $platformEmail }}">{{ $platformEmail }}</a></p>
        @endif
        @if($platformIg)
            <p><a class="hover:text-slate-900" href="{{ str_starts_with($platformIg, 'http') ? $platformIg : 'https://instagram.com/'.ltrim($platformIg, '@') }}" target="_blank" rel="noopener">Instagram</a></p>
        @endif
        @if($platformWeb)
            <p><a class="hover:text-slate-900" href="{{ $platformWeb }}" target="_blank" rel="noopener">Website</a></p>
        @endif
    </div>
    @if($platformMap)
        <div class="mt-4 overflow-hidden rounded-2xl border border-slate-200">
            <iframe src="{{ $platformMap }}" class="h-40 w-full border-0" loading="lazy" referrerpolicy="no-referrer-when-downgrade" title="Harita"></iframe>
        </div>
    @endif
@endif
