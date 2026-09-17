@extends('layouts.app')

@section('title', 'Hizmet Teklifi Al')

@section('content')
<div class="content-shell py-4" style="max-width:640px;">
    <h1 class="h5 mb-4">Hizmet Teklifi Al</h1>
    <form action="{{ route('service-requests.store') }}" method="post" class="bg-white rounded-4 shadow-sm p-4">
        @csrf
        <div class="mb-3">
            <label class="form-label">{{ __('ui.category') }}</label>
            <select name="category" class="form-select" required>
                @foreach(\App\Support\FreelancerCategories::keys() as $key)
                    <option value="{{ $key }}" @selected(old('category', $selectedCategory ?? '') === $key)>{{ \App\Support\FreelancerCategories::label($key) }}</option>
                @endforeach
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">{{ __('home.job_title') }}</label>
            <input type="text" name="title" class="form-control" value="{{ old('title') }}" required maxlength="255">
        </div>
        <div class="mb-3">
            <label class="form-label">{{ __('home.description') }}</label>
            <textarea name="description" class="form-control" rows="5">{{ old('description') }}</textarea>
        </div>
        <div class="row g-2">
            <div class="col-md-6 mb-3">
                <label class="form-label">{{ __('home.budget') }} min (₺)</label>
                <input type="number" step="0.01" name="budget_min" class="form-control" value="{{ old('budget_min') }}">
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">{{ __('home.budget') }} max (₺)</label>
                <input type="number" step="0.01" name="budget_max" class="form-control" value="{{ old('budget_max') }}">
            </div>
        </div>
        <button type="submit" class="btn btn-warning rounded-pill">{{ __('home.publish') }}</button>
    </form>
</div>
@endsection
