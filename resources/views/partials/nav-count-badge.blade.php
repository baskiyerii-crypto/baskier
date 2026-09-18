@php $count = (int) ($count ?? 0); @endphp
@if($count > 0)
    @if(($variant ?? 'bootstrap') === 'tailwind')
        <span class="ml-1 inline-flex min-w-[1.25rem] items-center justify-center rounded-full bg-amber-400 px-1.5 py-0.5 text-[10px] font-bold text-slate-900">{{ $count > 99 ? '99+' : $count }}</span>
    @else
        <span class="badge bg-warning text-dark">{{ $count > 99 ? '99+' : $count }}</span>
    @endif
@endif
