@extends('layouts.admin')
@section('title', 'Blog')
@section('content')
<div class="d-flex justify-content-between mb-3">
    <form class="d-flex gap-2">
        <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
            <option value="">Tüm durumlar</option>
            <option value="draft" @selected(request('status')==='draft')>Taslak</option>
            <option value="published" @selected(request('status')==='published')>Yayında</option>
        </select>
        <input type="text" name="category" value="{{ request('category') }}" class="form-control form-control-sm" placeholder="Kategori filtre">
        <button class="btn btn-sm btn-outline-secondary">Filtrele</button>
    </form>
    <a href="{{ route('admin.blog.import') }}" class="btn btn-primary btn-sm">Excel/CSV içe aktar</a>
</div>
<div class="card table-responsive">
    <table class="table mb-0">
        <thead><tr><th>Başlık</th><th>Kategori</th><th>Durum</th><th>AI</th><th>Tarih</th></tr></thead>
        <tbody>
        @forelse($posts as $post)
            <tr>
                <td>{{ $post->title }}</td>
                <td>{{ $post->category ?? '—' }}</td>
                <td>{{ $post->status }}</td>
                <td>{{ $post->ai_humanized ? 'Evet' : 'Hayır' }}</td>
                <td class="small">{{ optional($post->published_at ?? $post->created_at)->format('d.m.Y') }}</td>
            </tr>
        @empty
            <tr><td colspan="5" class="text-muted">Yazı yok.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
<div class="mt-3">{{ $posts->links() }}</div>
@endsection
