@extends('layouts.vendor')
@section('title', 'Ekip')
@section('content')
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
<h1 class="h5">Açık hava ekibi</h1>
<table class="table">
    <thead><tr><th>Kişi</th><th>Rol</th></tr></thead>
    <tbody>
    @foreach($members as $m)
        <tr><td>{{ $m->user?->name }} ({{ $m->user?->email }})</td><td>{{ $m->staff_role }}</td></tr>
    @endforeach
    </tbody>
</table>
<form method="POST" action="{{ route('vendor.outdoor.staff.invite') }}" class="card p-3" style="max-width:480px;">
    @csrf
    <div class="mb-2"><input name="name" class="form-control" placeholder="Ad" required></div>
    <div class="mb-2"><input name="email" type="email" class="form-control" placeholder="E-posta" required></div>
    <div class="mb-2">
        <select name="staff_role" class="form-select">
            <option value="ops">Operasyon</option>
            <option value="field">Saha</option>
        </select>
    </div>
    <div class="mb-2"><input name="password" class="form-control" placeholder="Şifre (yeni hesap)"></div>
    <button class="btn btn-primary btn-sm">Davet et</button>
</form>
@endsection
