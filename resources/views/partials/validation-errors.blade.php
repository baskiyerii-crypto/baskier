@if($errors->any())
    <div class="alert alert-danger mb-4" role="alert" tabindex="-1" data-validation-summary>
        <p class="font-semibold mb-2">Lütfen aşağıdaki bilgileri kontrol edin:</p>
        <ul class="list-disc pl-5 mb-0">
            @foreach($errors->all() as $message)<li>{{ $message }}</li>@endforeach
        </ul>
    </div>
@endif
