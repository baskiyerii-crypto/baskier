@php($isEdit = isset($contract))

<div class="row g-3">
    <div class="col-md-4">
        <label class="form-label">Key</label>
        <input type="text" name="key" class="form-control" value="{{ old('key', $contract->key ?? '') }}" maxlength="64" required>
        <div class="form-text">Örn: <code>terms</code>, <code>privacy</code>, <code>vendor_agreement</code>, <code>distance_sales</code></div>
    </div>
    <div class="col-md-8">
        <label class="form-label">Başlık</label>
        <input type="text" name="title" class="form-control" value="{{ old('title', $contract->title ?? '') }}" required>
    </div>
    <div class="col-md-4">
        <label class="form-label">Hedef</label>
        <select name="audience" class="form-select" required>
            @php($aud = old('audience', $contract->audience ?? 'all'))
            <option value="all" @selected($aud === 'all')>all</option>
            <option value="customer" @selected($aud === 'customer')>customer</option>
            <option value="vendor" @selected($aud === 'vendor')>vendor</option>
        </select>
    </div>
    <div class="col-md-4">
        <label class="form-label">Sürüm</label>
        <input type="number" name="version" class="form-control" value="{{ old('version', $contract->version ?? 1) }}" min="1" max="9999" required>
    </div>
    <div class="col-md-4 d-flex align-items-end gap-3">
        <div class="form-check">
            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" @checked(old('is_active', $contract->is_active ?? true))>
            <label class="form-check-label" for="is_active">Aktif</label>
        </div>
        @if($isEdit)
        <div class="form-check">
            <input class="form-check-input" type="checkbox" id="republish" name="republish" value="1" @checked(old('republish'))>
            <label class="form-check-label" for="republish">Satıcılara yeniden yayınla</label>
        </div>
        @endif
    </div>
    <div class="col-12">
        <label class="form-label">İçerik (HTML)</label>
        <textarea name="content_html" class="form-control" rows="16" placeholder="<h2>Başlık</h2>...">{{ old('content_html', $contract->content_html ?? '') }}</textarea>
        <div class="form-text">WYSIWYG yok; basit HTML girilir.</div>
    </div>
</div>

