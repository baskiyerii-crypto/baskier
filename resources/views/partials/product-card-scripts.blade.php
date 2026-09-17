{{-- Reusable Product Card Interactive Modal & AJAX Add-to-Cart Script --}}

{{-- Toast Container --}}
<div id="by-ajax-toast-container" class="fixed bottom-20 right-6 z-[9999] flex flex-col gap-2 pointer-events-none md:bottom-8"></div>

{{-- Variant Selection Modal --}}
<div id="by-variant-modal" class="fixed inset-0 z-[9998] hidden items-center justify-center bg-slate-900/60 p-4 backdrop-blur-xs">
    <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl transition-all">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <h3 id="by-variant-modal-title" class="text-base font-bold text-slate-900 truncate">Ürün Seçenekleri</h3>
            <button type="button" id="by-variant-modal-close" class="rounded-lg p-1 text-slate-400 hover:bg-slate-100 hover:text-slate-700">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>

        <form id="by-variant-form" class="mt-4 space-y-4">
            <div>
                <label for="by-variant-select" class="block text-xs font-semibold text-slate-700 mb-1">Varyant / Seçenek</label>
                <select id="by-variant-select" name="variant_id" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-orange-500" required>
                    {{-- Dynamically populated --}}
                </select>
            </div>

            <div>
                <label for="by-variant-qty" class="block text-xs font-semibold text-slate-700 mb-1">Adet</label>
                <input type="number" id="by-variant-qty" name="quantity" min="1" max="999" value="1" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-orange-500" required />
            </div>

            <div class="pt-2">
                <button type="submit" id="by-variant-submit-btn" class="by-btn-primary w-full py-2.5 text-sm font-bold">
                    Sepete Ekle
                </button>
            </div>
        </form>
    </div>
</div>

<script>
(function() {
    function showToast(message, type = 'success') {
        const container = document.getElementById('by-ajax-toast-container');
        if (!container) return;

        const toast = document.createElement('div');
        toast.className = `pointer-events-auto flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium shadow-lg transition-all duration-300 transform translate-y-2 opacity-0 ${
            type === 'success' ? 'bg-slate-900 text-white border border-slate-800' : 'bg-rose-600 text-white'
        }`;
        
        toast.innerHTML = `
            <span>${message}</span>
        `;
        container.appendChild(toast);

        requestAnimationFrame(() => {
            toast.classList.remove('translate-y-2', 'opacity-0');
        });

        setTimeout(() => {
            toast.classList.add('opacity-0', 'translate-y-2');
            setTimeout(() => toast.remove(), 300);
        }, 3200);
    }

    function updateCartCount(count) {
        document.querySelectorAll('.cart-count-badge').forEach(el => {
            el.textContent = count;
            el.classList.remove('hidden');
        });
    }

    // Direct single-item AJAX add-to-cart
    document.addEventListener('click', function(e) {
        const btn = e.target.closest('.ajax-add-to-cart-trigger');
        if (!btn) return;

        e.preventDefault();
        const url = btn.dataset.url;
        if (!url) return;

        const originalText = btn.textContent;
        btn.disabled = true;
        btn.textContent = 'Ekleniyor...';

        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';

        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': token,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ quantity: 1 })
        })
        .then(async response => {
            if (response.status === 401) {
                window.location.href = '{{ route("login") }}';
                return;
            }
            const data = await response.json();
            if (!response.ok) {
                throw new Error(data.message || 'Ürün sepete eklenemedi.');
            }
            return data;
        })
        .then(data => {
            if (data && data.status === 'success') {
                showToast(data.message || 'Ürün sepete eklendi.');
                if (data.cart_count !== undefined) {
                    updateCartCount(data.cart_count);
                }
            }
        })
        .catch(err => {
            showToast(err.message || 'Bir hata oluştu.', 'error');
        })
        .finally(() => {
            btn.disabled = false;
            btn.textContent = originalText;
        });
    });

    // Variant modal handling
    const modal = document.getElementById('by-variant-modal');
    const modalClose = document.getElementById('by-variant-modal-close');
    const modalTitle = document.getElementById('by-variant-modal-title');
    const variantSelect = document.getElementById('by-variant-select');
    const variantQty = document.getElementById('by-variant-qty');
    const variantForm = document.getElementById('by-variant-form');
    let currentActionUrl = '';

    function closeModal() {
        if (!modal) return;
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    if (modalClose) {
        modalClose.addEventListener('click', closeModal);
    }
    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === modal) closeModal();
        });
    }

    document.addEventListener('click', function(e) {
        const btn = e.target.closest('.open-variant-modal-trigger');
        if (!btn || !modal) return;

        e.preventDefault();
        const productName = btn.dataset.productName;
        currentActionUrl = btn.dataset.actionUrl;
        let variants = [];
        try {
            variants = JSON.parse(btn.dataset.variants || '[]');
        } catch (err) {
            variants = [];
        }

        modalTitle.textContent = productName;
        variantSelect.innerHTML = '';
        variants.forEach(v => {
            const opt = document.createElement('option');
            opt.value = v.id;
            opt.textContent = `${v.name} - ₺${v.price} (${v.stock > 0 ? v.stock + ' adet' : 'Tükendi'})`;
            if (v.stock <= 0) opt.disabled = true;
            variantSelect.appendChild(opt);
        });

        variantQty.value = 1;
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    });

    if (variantForm) {
        variantForm.addEventListener('submit', function(e) {
            e.preventDefault();
            if (!currentActionUrl) return;

            const submitBtn = document.getElementById('by-variant-submit-btn');
            const originalText = submitBtn.textContent;
            submitBtn.disabled = true;
            submitBtn.textContent = 'Ekleniyor...';

            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
            const payload = {
                variant_id: variantSelect.value,
                quantity: parseInt(variantQty.value, 10) || 1
            };

            fetch(currentActionUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': token,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(payload)
            })
            .then(async response => {
                if (response.status === 401) {
                    window.location.href = '{{ route("login") }}';
                    return;
                }
                const data = await response.json();
                if (!response.ok) {
                    throw new Error(data.message || 'Ürün sepete eklenemedi.');
                }
                return data;
            })
            .then(data => {
                if (data && data.status === 'success') {
                    showToast(data.message || 'Ürün sepete eklendi.');
                    if (data.cart_count !== undefined) {
                        updateCartCount(data.cart_count);
                    }
                    closeModal();
                }
            })
            .catch(err => {
                showToast(err.message || 'Bir hata oluştu.', 'error');
            })
            .finally(() => {
                submitBtn.disabled = false;
                submitBtn.textContent = originalText;
            });
        });
    }
})();
</script>
