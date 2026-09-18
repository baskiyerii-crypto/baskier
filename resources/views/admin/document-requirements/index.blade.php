@extends('layouts.admin')
@section('title', 'Belge şablonları')
@section('content')
@php
    $audienceLabels = [
        'physical' => 'Fiziki satıcı',
        'freelancer' => 'Freelancer',
        'outdoor_owner' => 'Açık hava sahip',
        'outdoor_agency' => 'Açık hava ajans',
        'municipality' => 'Belediye',
    ];
@endphp
<p class="small text-muted mb-3">Satıcı ve açık hava panellerinde hangi evrakların zorunlu / dosya yüklemeli olduğunu buradan yönetin.</p>

<div class="card p-3 mb-4">
    <h2 class="h6">Yeni satır</h2>
    <form method="POST" action="{{ route('admin.document-requirements.store') }}" class="row g-2 align-items-end">
        @csrf
        <div class="col-md-3">
            <label class="form-label small">Hedef</label>
            <select name="audience" class="form-select form-select-sm" required>
                @foreach($audiences as $a)
                    <option value="{{ $a }}">{{ $audienceLabels[$a] ?? $a }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label small">Tür</label>
            <select name="document_type" class="form-select form-select-sm" required>
                @foreach($types as $key => $label)
                    <option value="{{ $key }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label small">Etiket</label>
            <input name="label" class="form-control form-control-sm" required>
        </div>
        <div class="col-md-1">
            <label class="form-label small">Sıra</label>
            <input type="number" name="sort_order" class="form-control form-control-sm" value="10">
        </div>
        <div class="col-md-2">
            <label class="form-check small"><input type="checkbox" name="required" value="1" class="form-check-input"> Zorunlu</label>
            <label class="form-check small"><input type="checkbox" name="requires_file" value="1" class="form-check-input" checked> Dosya</label>
        </div>
        <div class="col-12"><button class="btn btn-sm btn-primary">Ekle / güncelle</button></div>
    </form>
</div>

@foreach($audiences as $audience)
    <div class="card p-3 mb-3">
        <h3 class="h6">{{ $audienceLabels[$audience] ?? $audience }}</h3>
        @php $rows = $groups[$audience] ?? collect(); @endphp
        @if($rows->isEmpty())
            <p class="small text-muted mb-0">Satır yok.</p>
        @else
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0">
                    <thead><tr><th>Tür</th><th>Etiket</th><th>Zorunlu</th><th>Dosya</th><th>Aktif</th><th></th></tr></thead>
                    <tbody>
                    @foreach($rows as $row)
                        <tr>
                            <td class="small">{{ $row->document_type }}</td>
                            <td>
                                <form method="POST" action="{{ route('admin.document-requirements.update', $row) }}" class="row g-1 align-items-center">
                                    @csrf @method('PUT')
                                    <div class="col-md-5"><input name="label" class="form-control form-control-sm" value="{{ $row->label }}"></div>
                                    <div class="col-md-1"><input type="number" name="sort_order" class="form-control form-control-sm" value="{{ $row->sort_order }}"></div>
                                    <div class="col-md-2">
                                        <label class="form-check small mb-0"><input type="checkbox" name="required" value="1" class="form-check-input" @checked($row->required)> Zorunlu</label>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-check small mb-0"><input type="checkbox" name="requires_file" value="1" class="form-check-input" @checked($row->requires_file)> Dosya</label>
                                    </div>
                                    <div class="col-md-1">
                                        <label class="form-check small mb-0"><input type="checkbox" name="is_active" value="1" class="form-check-input" @checked($row->is_active)> Aktif</label>
                                    </div>
                                    <div class="col-md-1"><button class="btn btn-sm btn-outline-primary">Kaydet</button></div>
                                </form>
                            </td>
                            <td colspan="3"></td>
                            <td>
                                <form method="POST" action="{{ route('admin.document-requirements.destroy', $row) }}" onsubmit="return confirm('Silinsin mi?');">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger">Sil</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
@endforeach
@endsection
