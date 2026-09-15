@extends('layouts.admin')
@section('title', __('panel.blog'))
@section('content')
<div class="d-flex justify-content-between mb-3 flex-wrap gap-2">
    <form class="d-flex gap-2">
        <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
            <option value="">{{ __('panel.all_statuses') }}</option>
            <option value="draft" @selected(request('status')==='draft')>{{ __('panel.draft') }}</option>
            <option value="published" @selected(request('status')==='published')>{{ __('panel.published') }}</option>
        </select>
        <input type="text" name="category" value="{{ request('category') }}" class="form-control form-control-sm" placeholder="{{ __('panel.category') }}">
        <button class="btn btn-sm btn-outline-secondary">{{ __('panel.filter') }}</button>
    </form>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.blog.create') }}" class="btn btn-success btn-sm">{{ __('panel.blog_new') }}</a>
        <a href="{{ route('admin.blog.import') }}" class="btn btn-primary btn-sm">{{ __('panel.blog_import') }}</a>
    </div>
</div>
<div class="card overflow-hidden">
    <table class="table mb-0">
        <thead><tr><th>{{ __('panel.title') }}</th><th>{{ __('panel.category') }}</th><th>{{ __('panel.status') }}</th><th>AI</th><th>{{ __('panel.date') }}</th><th></th></tr></thead>
        <tbody>
        @forelse($posts as $post)
            <tr>
                <td>{{ $post->title }}</td>
                <td>{{ $post->category ?? '—' }}</td>
                <td>{{ $post->status }}</td>
                <td>{{ $post->ai_humanized ? __('panel.yes') : __('panel.no') }}</td>
                <td class="small">{{ optional($post->published_at ?? $post->created_at)->format('d.m.Y') }}</td>
                <td class="text-end text-nowrap">
                    <a href="{{ route('admin.blog.edit', $post) }}" class="btn btn-sm btn-outline-primary">{{ __('panel.edit') }}</a>
                    <form method="post" action="{{ route('admin.blog.destroy', $post) }}" class="d-inline" onsubmit="return confirm('{{ __('panel.confirm_delete') }}')">
                        @csrf @method('DELETE')
                        <button class="btn btn-sm btn-outline-danger">{{ __('panel.delete') }}</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="6" class="text-muted">{{ __('panel.no_posts') }}</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
<div class="mt-3">{{ $posts->links() }}</div>
@endsection
