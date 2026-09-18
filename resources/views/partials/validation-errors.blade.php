@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show shadow-sm mb-4" role="alert" tabindex="-1" data-validation-summary>
        <div class="d-flex align-items-center gap-2 font-semibold mb-2">
            <span class="fs-5">⚠️</span>
            <strong>Lütfen aşağıdaki form bilgilerini kontrol edin:</strong>
        </div>
        <ul class="mb-0 ps-4 small">
            @foreach($errors->all() as $message)
                <li>{{ $message }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Kapat"></button>
    </div>
@endif
