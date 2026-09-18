@extends($layout ?? 'layouts.vendor')
@section('title', 'Shopier ödemesi')
@section('content')
<p class="small text-muted">Shopier ödeme sayfasına yönlendiriliyorsunuz…</p>
<form id="shopier-form" method="POST" action="{{ $form['url'] }}">
    @foreach($form['fields'] as $name => $value)
        <input type="hidden" name="{{ $name }}" value="{{ $value }}">
    @endforeach
</form>
<script>document.getElementById('shopier-form').submit();</script>
@endsection
