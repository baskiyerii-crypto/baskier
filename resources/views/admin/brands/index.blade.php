@extends('layouts.admin')
@section('title', 'Alt markalar')
@section('content')
<div class="d-flex justify-content-between mb-3">
    <div>
        <h1 class="h5 mb-0">Alt markalarımız</h1>
        <p class="text-muted small mb-0">Ana sayfa altında logo ve site linki olarak görünür.</p>
    </div>
</div>
<div class="card p-4 mb-4" style="max-width:640px;">
    <form method="POST" action="{{ route('admin.brands.store') }}" enctype="multipart/form-data">
        @csrf
        <div class="mb-2"><input name="name" class="form-control" placeholder="Marka adı" required></div>
        <div class="mb-2"><input name="website_url" class="form-control" placeholder="https://"></div>
        <div class="mb-2"><input type="number" name="sort_order" class="form-control" placeholder="Sıra" value="0"></div>
        <div class="mb-2"><input type="file" name="logo" class="form-control" accept="image/*"></div>
        <label class="form-check"><input type="checkbox" name="is_active" value="1" class="form-check-input" checked> Aktif</label>
        <button class="btn btn-primary btn-sm mt-2">Ekle</button>
    </form>
</div>
<div class="card table-responsive">
    <table class="table mb-0">
        <thead><tr><th>Logo</th><th>Ad</th><th>Link</th><th></th></tr></thead>
        <tbody>
        @forelse($brands as $b)
            <tr>
                <td>@if($b->logoUrl())<img src="{{ $b->logoUrl() }}" alt="" style="height:32px">@endif</td>
                <td>{{ $b->name }}</td>
                <td class="small">{{ $b->website_url }}</td>
                <td>
                    <form method="POST" action="{{ route('admin.brands.destroy', $b) }}" onsubmit="return confirm('Silinsin mi?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-sm btn-outline-danger">Sil</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="4" class="text-muted">Henüz alt marka yok.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
{{ $brands->links() }}
@endsection
