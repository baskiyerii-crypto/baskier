@extends('layouts.account')

@section('title', 'Adres düzenle')

@section('content')
<div style="max-width:560px;">
    <h1 class="h5 mb-4">Adres düzenle</h1>
    <form action="{{ route('account.adresler.update', $address) }}" method="post" class="bg-white rounded-4 shadow-sm p-4">
        @csrf @method('PUT')
        <div class="mb-3">
            <label class="form-label">Etiket</label>
            <input type="text" name="label" class="form-control" value="{{ old('label', $address->label) }}">
        </div>
        <div class="mb-3">
            <label class="form-label">Ad Soyad</label>
            <input type="text" name="full_name" class="form-control" value="{{ old('full_name', $address->full_name) }}" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Telefon</label>
            <input type="text" name="phone" class="form-control" value="{{ old('phone', $address->phone) }}" required>
        </div>
        <div class="row g-2">
            <div class="col-md-6 mb-3">
                <label class="form-label">İl</label>
                <input type="text" name="city" class="form-control" value="{{ old('city', $address->city) }}" required>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">İlçe</label>
                <input type="text" name="district" class="form-control" value="{{ old('district', $address->district) }}">
            </div>
        </div>
        <div class="mb-3">
            <label class="form-label">Adres satırı</label>
            <input type="text" name="line1" class="form-control" value="{{ old('line1', $address->line1) }}" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Adres satırı 2</label>
            <input type="text" name="line2" class="form-control" value="{{ old('line2', $address->line2) }}">
        </div>
        <div class="mb-3">
            <label class="form-label">Posta kodu</label>
            <input type="text" name="postal_code" class="form-control" value="{{ old('postal_code', $address->postal_code) }}">
        </div>
        <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" name="is_default" id="isd" value="1" @checked(old('is_default', $address->is_default))>
            <label class="form-check-label" for="isd">Varsayılan adres</label>
        </div>
        <button type="submit" class="btn btn-warning rounded-pill">Güncelle</button>
    </form>
@endsection
