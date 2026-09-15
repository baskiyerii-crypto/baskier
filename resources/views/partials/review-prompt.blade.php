@if(!empty($pendingReviewOrder))
<div class="fixed inset-0 z-[70] flex items-center justify-center bg-slate-900/60 p-4">
    <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-xl">
        <h2 class="text-lg font-bold text-slate-900">Hizmeti değerlendirin</h2>
        <p class="mt-2 text-sm text-slate-600">#{{ $pendingReviewOrder->order_number }} siparişiniz için değerlendirme zorunludur. Onaylayana kadar bu pencere açılmaya devam eder.</p>
        <form method="post" action="{{ route('account.orders.review', $pendingReviewOrder) }}" class="mt-4 space-y-3">
            @csrf
            <label class="block text-xs font-semibold text-slate-600">Puan</label>
            <select name="rating" class="by-input w-full" required>
                @for($i=5;$i>=1;$i--)
                    <option value="{{ $i }}">{{ $i }} yıldız</option>
                @endfor
            </select>
            <textarea name="comment" class="by-input w-full" rows="3" placeholder="Yorumunuz (isteğe bağlı)"></textarea>
            <button class="by-btn-primary w-full" type="submit">Değerlendirmeyi gönder</button>
        </form>
    </div>
</div>
@endif
