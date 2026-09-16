@extends('layouts.account')
@section('title', __('panel.new_direct_quote'))
@section('content')
<form method="post" action="{{ route('customer.direct-quotes.store') }}" class="by-card p-6 max-w-xl space-y-4">
    @csrf
    <div>
        <label class="form-label">{{ __('panel.vendor') }}</label>
        <select name="vendor_id" class="form-select" required>
            @foreach($vendors as $v)
                <option value="{{ $v->id }}">{{ $v->name }}</option>
            @endforeach
        </select>
        @if($vendors->isEmpty())
            <p class="text-sm text-slate-500 mt-2">{{ __('panel.no_past_vendors') }}</p>
        @endif
    </div>
    <div>
        <label class="form-label">{{ __('panel.title') }}</label>
        <input name="title" class="form-control" required>
    </div>
    <div>
        <label class="form-label">{{ __('panel.body') }}</label>
        <textarea name="body" class="form-control" rows="5"></textarea>
    </div>
    <button class="by-btn-primary" @if($vendors->isEmpty()) disabled @endif>{{ __('panel.submit_for_approval') }}</button>
</form>
@endsection
