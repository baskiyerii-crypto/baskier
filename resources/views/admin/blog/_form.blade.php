<form method="post" action="{{ $action }}" class="card p-4">
    @csrf
    @if(($method ?? 'POST') !== 'POST')
        @method($method)
    @endif
    <div class="mb-3">
        <label class="form-label">{{ __('panel.title') }}</label>
        <input type="text" name="title" class="form-control" value="{{ old('title', $post->title) }}" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Slug</label>
        <input type="text" name="slug" class="form-control" value="{{ old('slug', $post->slug) }}" placeholder="auto">
    </div>
    <div class="mb-3">
        <label class="form-label">{{ __('panel.category') }}</label>
        <input type="text" name="category" class="form-control" value="{{ old('category', $post->category) }}">
    </div>
    <div class="mb-3">
        <label class="form-label">Meta title</label>
        <input type="text" name="meta_title" class="form-control" value="{{ old('meta_title', $post->meta_title) }}">
    </div>
    <div class="mb-3">
        <label class="form-label">Meta description</label>
        <textarea name="meta_description" class="form-control" rows="2">{{ old('meta_description', $post->meta_description) }}</textarea>
    </div>
    <div class="mb-3">
        <label class="form-label">{{ __('panel.body') }}</label>
        <textarea name="body" class="form-control" rows="12" required>{{ old('body', $post->body) }}</textarea>
    </div>
    <div class="row g-3 mb-3">
        <div class="col-md-4">
            <label class="form-label">{{ __('panel.status') }}</label>
            <select name="status" class="form-select">
                <option value="draft" @selected(old('status', $post->status) === 'draft')>{{ __('panel.draft') }}</option>
                <option value="published" @selected(old('status', $post->status) === 'published')>{{ __('panel.published') }}</option>
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label">{{ __('panel.published_at') }}</label>
            <input type="datetime-local" name="published_at" class="form-control"
                   value="{{ old('published_at', optional($post->published_at)->format('Y-m-d\TH:i')) }}">
        </div>
    </div>
    <div class="d-flex gap-2">
        <button class="btn btn-primary">{{ __('panel.save') }}</button>
        <a href="{{ route('admin.blog.index') }}" class="btn btn-outline-secondary">{{ __('panel.cancel') }}</a>
    </div>
</form>
