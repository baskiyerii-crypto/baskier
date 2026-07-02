import React from 'react';
import { createRoot } from 'react-dom/client';
import { BrowserRouter, Link, NavLink, Navigate, Route, Routes, useParams } from 'react-router-dom';
import axios from 'axios';

const token = () => localStorage.getItem('api_token');
const api = axios.create({
    baseURL: '/api/v1',
    headers: { Accept: 'application/json' },
});
api.interceptors.request.use((config) => {
    const t = token();
    if (t) {
        config.headers.Authorization = `Bearer ${t}`;
    }
    return config;
});

function unwrapPayload(response) {
    const d = response.data;
    if (d && typeof d.success === 'boolean' && 'data' in d) {
        return d.data;
    }

    return d;
}

const ORDER_STATUS_TR = {
    pending: 'Beklemede',
    confirmed: 'Onaylandı',
    design_review: 'Tasarım inceleme',
    in_production: 'Üretimde',
    ready_to_ship: 'Kargoya hazır',
    shipped: 'Kargoda',
    delivered: 'Teslim edildi',
    completed: 'Tamamlandı',
    cancelled: 'İptal',
    disputed: 'Uyuşmazlık',
};

function trOrderStatus(s) {
    return ORDER_STATUS_TR[s] || s;
}

function useMe() {
    const [user, setUser] = React.useState(null);
    const [err, setErr] = React.useState('');
    React.useEffect(() => {
        if (!token()) {
            setUser(null);
            return;
        }
        api.get('/auth/user')
            .then((r) => setUser(unwrapPayload(r)))
            .catch(() => setErr('Oturum geçersiz'));
    }, []);
    return { user, err };
}

function unwrapPage(response) {
    const d = response.data;
    if (d && typeof d.success === 'boolean' && d.data && typeof d.data === 'object') {
        return d.data;
    }
    return d;
}

function Shell({ children, title }) {
    const { user } = useMe();
    return (
        <div className="min-h-screen bg-gradient-to-br from-slate-50 via-orange-50/40 to-slate-100">
            <header className="sticky top-0 z-20 border-b border-white/60 bg-white/80 backdrop-blur-md shadow-sm">
                <div className="mx-auto flex max-w-5xl flex-wrap items-center justify-between gap-3 px-4 py-3">
                    <Link to="/" className="text-lg font-bold tracking-tight text-slate-900">
                        Baskı<span className="text-orange-500">Yeri</span>
                    </Link>
                    <nav className="flex flex-wrap items-center gap-1 text-sm font-medium">
                        <NavLink to="/" end className={({ isActive }) => `rounded-full px-3 py-1.5 ${isActive ? 'bg-orange-500 text-slate-900 shadow-sm' : 'text-slate-600 hover:bg-white/80'}`}>Özet</NavLink>
                        {user?.role === 'customer' ? (
                            <>
                                <NavLink to="/customer/dashboard" className={({ isActive }) => `rounded-full px-3 py-1.5 ${isActive ? 'bg-orange-500 text-slate-900 shadow-sm' : 'text-slate-600 hover:bg-white/80'}`}>Panel</NavLink>
                                <NavLink to="/customer/orders" className={({ isActive }) => `rounded-full px-3 py-1.5 ${isActive ? 'bg-orange-500 text-slate-900 shadow-sm' : 'text-slate-600 hover:bg-white/80'}`}>Siparişler</NavLink>
                                <NavLink to="/customer/price-estimate" className={({ isActive }) => `rounded-full px-3 py-1.5 ${isActive ? 'bg-orange-500 text-slate-900 shadow-sm' : 'text-slate-600 hover:bg-white/80'}`}>İş hesaplama</NavLink>
                                <NavLink to="/customer/quote-requests" className={({ isActive }) => `rounded-full px-3 py-1.5 ${isActive ? 'bg-orange-500 text-slate-900 shadow-sm' : 'text-slate-600 hover:bg-white/80'}`}>Teklif taleplerim</NavLink>
                                <NavLink to="/customer/favorites" className={({ isActive }) => `rounded-full px-3 py-1.5 ${isActive ? 'bg-orange-500 text-slate-900 shadow-sm' : 'text-slate-600 hover:bg-white/80'}`}>Favoriler</NavLink>
                                <NavLink to="/customer/addresses" className={({ isActive }) => `rounded-full px-3 py-1.5 ${isActive ? 'bg-orange-500 text-slate-900 shadow-sm' : 'text-slate-600 hover:bg-white/80'}`}>Adresler</NavLink>
                                <NavLink to="/cart" className={({ isActive }) => `rounded-full px-3 py-1.5 ${isActive ? 'bg-orange-500 text-slate-900 shadow-sm' : 'text-slate-600 hover:bg-white/80'}`}>Sepet</NavLink>
                                <NavLink to="/support" className={({ isActive }) => `rounded-full px-3 py-1.5 ${isActive ? 'bg-orange-500 text-slate-900 shadow-sm' : 'text-slate-600 hover:bg-white/80'}`}>Destek</NavLink>
                            </>
                        ) : null}
                        {user?.role === 'vendor' ? (
                            <>
                                <NavLink to="/vendor/dashboard" className={({ isActive }) => `rounded-full px-3 py-1.5 ${isActive ? 'bg-orange-500 text-slate-900 shadow-sm' : 'text-slate-600 hover:bg-white/80'}`}>Panel</NavLink>
                                <NavLink to="/vendor/products" className={({ isActive }) => `rounded-full px-3 py-1.5 ${isActive ? 'bg-orange-500 text-slate-900 shadow-sm' : 'text-slate-600 hover:bg-white/80'}`}>Ürünler</NavLink>
                                <NavLink to="/vendor/orders" className={({ isActive }) => `rounded-full px-3 py-1.5 ${isActive ? 'bg-orange-500 text-slate-900 shadow-sm' : 'text-slate-600 hover:bg-white/80'}`}>Siparişler</NavLink>
                                <NavLink to="/vendor/quote-requests" className={({ isActive }) => `rounded-full px-3 py-1.5 ${isActive ? 'bg-orange-500 text-slate-900 shadow-sm' : 'text-slate-600 hover:bg-white/80'}`}>Teklif talepleri</NavLink>
                                <NavLink to="/vendor/payouts" className={({ isActive }) => `rounded-full px-3 py-1.5 ${isActive ? 'bg-orange-500 text-slate-900 shadow-sm' : 'text-slate-600 hover:bg-white/80'}`}>Ödeme talebi</NavLink>
                            </>
                        ) : null}
                        {user?.role === 'admin' ? (
                            <>
                                <NavLink to="/admin" className={({ isActive }) => `rounded-full px-3 py-1.5 ${isActive ? 'bg-orange-500 text-slate-900 shadow-sm' : 'text-slate-600 hover:bg-white/80'}`}>Yönetim</NavLink>
                                <NavLink to="/admin/support" className={({ isActive }) => `rounded-full px-3 py-1.5 ${isActive ? 'bg-orange-500 text-slate-900 shadow-sm' : 'text-slate-600 hover:bg-white/80'}`}>Destek</NavLink>
                                <NavLink to="/admin/payout-requests" className={({ isActive }) => `rounded-full px-3 py-1.5 ${isActive ? 'bg-orange-500 text-slate-900 shadow-sm' : 'text-slate-600 hover:bg-white/80'}`}>Ödeme talepleri</NavLink>
                            </>
                        ) : null}
                        <NavLink to="/messages" className={({ isActive }) => `rounded-full px-3 py-1.5 ${isActive ? 'bg-orange-500 text-slate-900 shadow-sm' : 'text-slate-600 hover:bg-white/80'}`}>Mesajlar</NavLink>
                        <NavLink to="/notifications" className={({ isActive }) => `rounded-full px-3 py-1.5 ${isActive ? 'bg-orange-500 text-slate-900 shadow-sm' : 'text-slate-600 hover:bg-white/80'}`}>Bildirimler</NavLink>
                    </nav>
                </div>
            </header>
            <main className="mx-auto max-w-5xl px-4 py-8">
                {title ? <h1 className="mb-6 text-2xl font-bold tracking-tight text-slate-900">{title}</h1> : null}
                {children}
            </main>
        </div>
    );
}

function LoginPage() {
    const [email, setEmail] = React.useState('');
    const [password, setPassword] = React.useState('');
    const [msg, setMsg] = React.useState('');
    const submit = async (e) => {
        e.preventDefault();
        setMsg('');
        try {
            const { data } = await axios.post('/api/v1/auth/login', {
                email,
                password,
                device_name: 'web-spa',
            });
            const payload = data && typeof data.success === 'boolean' ? data.data : data;
            localStorage.setItem('api_token', payload.token);
            window.location.href = '/app';
        } catch (err) {
            setMsg(err.response?.data?.message || 'Giriş başarısız');
        }
    };
    return (
        <Shell title="Giriş">
            <div className="mx-auto max-w-md rounded-2xl border border-slate-200/80 bg-white/90 p-8 shadow-xl backdrop-blur">
                <form onSubmit={submit} className="space-y-4">
                    <label className="block text-sm font-medium text-slate-700">E-posta</label>
                    <input className="w-full rounded-xl border border-slate-200 bg-slate-50/80 px-4 py-3 outline-none ring-orange-400 focus:ring-2" value={email} onChange={(e) => setEmail(e.target.value)} type="email" required />
                    <label className="block text-sm font-medium text-slate-700">Şifre</label>
                    <input className="w-full rounded-xl border border-slate-200 bg-slate-50/80 px-4 py-3 outline-none ring-orange-400 focus:ring-2" value={password} onChange={(e) => setPassword(e.target.value)} type="password" required />
                    {msg ? <p className="text-sm text-red-600">{msg}</p> : null}
                    <button type="submit" className="w-full rounded-full bg-gradient-to-r from-orange-400 to-orange-500 py-3 font-semibold text-slate-900 shadow-md transition hover:opacity-95">Giriş yap</button>
                </form>
            </div>
        </Shell>
    );
}

function DashboardHome() {
    const { user, err } = useMe();
    if (!token()) {
        return <Navigate to="/login" replace />;
    }
    if (err) {
        return <Shell title="Hata"><p className="text-red-600">{err}</p></Shell>;
    }
    if (!user) {
        return <Shell title="Yükleniyor"><p className="text-slate-500">Yükleniyor…</p></Shell>;
    }
    return (
        <Shell title="Özet">
            <div className="rounded-2xl border border-slate-200/80 bg-white/90 p-8 shadow-lg backdrop-blur">
                <p className="text-lg text-slate-700">
                    Merhaba, <strong className="text-slate-900">{user.name}</strong>
                </p>
                <p className="mt-2 inline-block rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-slate-600">Rol: {user.role}</p>
                <p className="mt-6 text-sm leading-relaxed text-slate-600">
                    Bu panel doğrudan API ile konuşur. Web vitrin (Blade), satıcı / müşteri panelleri ve mobil uygulama aynı backend’i kullanır.
                </p>
            </div>
        </Shell>
    );
}

function CustomerDashboardPage() {
    const [data, setData] = React.useState(null);
    const [err, setErr] = React.useState('');
    React.useEffect(() => {
        if (!token()) return;
        api.get('/customer/dashboard')
            .then((r) => setData(r.data))
            .catch((e) => setErr(e.response?.data?.message || e.message || 'Yüklenemedi'));
    }, []);
    if (!token()) return <Navigate to="/login" replace />;
    if (err) return <Shell title="Müşteri paneli"><p className="text-red-600">{err}</p></Shell>;
    if (!data) return <Shell title="Müşteri paneli"><p className="text-slate-500">Yükleniyor…</p></Shell>;

    return (
        <Shell title="Müşteri paneli">
            <div className="grid gap-4 md:grid-cols-3">
                <div className="rounded-2xl border border-slate-200/80 bg-white/90 p-6 shadow-lg">
                    <p className="text-xs font-semibold uppercase tracking-wide text-slate-500">Sipariş</p>
                    <p className="mt-2 text-2xl font-bold text-slate-900">{data.orders_count ?? 0}</p>
                </div>
                <div className="rounded-2xl border border-slate-200/80 bg-white/90 p-6 shadow-lg">
                    <p className="text-xs font-semibold uppercase tracking-wide text-slate-500">Sepet</p>
                    <p className="mt-2 text-2xl font-bold text-slate-900">{data.cart_count ?? 0}</p>
                </div>
                <div className="rounded-2xl border border-slate-200/80 bg-white/90 p-6 shadow-lg">
                    <p className="text-xs font-semibold uppercase tracking-wide text-slate-500">Favori</p>
                    <p className="mt-2 text-2xl font-bold text-slate-900">{data.favorites_count ?? 0}</p>
                </div>
            </div>
        </Shell>
    );
}

function OrdersList({ vendor }) {
    const [rows, setRows] = React.useState([]);
    const [loading, setLoading] = React.useState(true);
    React.useEffect(() => {
        const path = vendor ? '/vendor/orders' : '/orders';
        api.get(path).then((r) => setRows(Array.isArray(unwrapPayload(r)) ? unwrapPayload(r) : [])).finally(() => setLoading(false));
    }, [vendor]);
    if (!token()) {
        return <Navigate to="/login" replace />;
    }
    return (
        <Shell title={vendor ? 'Satıcı siparişleri' : 'Siparişlerim'}>
            {loading ? <p className="text-slate-500">Yükleniyor…</p> : (
                <ul className="space-y-3">
                    {rows.map((o) => (
                        <li key={o.id} className="rounded-2xl border border-slate-200/80 bg-white/90 px-5 py-4 shadow-sm backdrop-blur transition hover:shadow-md">
                            <Link className="font-semibold text-orange-600 hover:underline" to={vendor ? `/vendor/orders/${o.id}` : `/orders/${o.id}`}>#{o.order_number}</Link>
                            <span className="ml-3 rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-medium text-slate-700">{trOrderStatus(o.status)}</span>
                            <span className="ml-3 text-sm font-bold text-slate-800">₺{Number(o.subtotal).toFixed(2)}</span>
                        </li>
                    ))}
                </ul>
            )}
        </Shell>
    );
}

function VendorOrderDetailPage() {
    const { id } = useParams();
    const [o, setO] = React.useState(null);
    const [msg, setMsg] = React.useState('');
    const [nextStatus, setNextStatus] = React.useState('');

    const load = () => {
        if (!id) return;
        setMsg('');
        api.get(`/vendor/orders/${id}`)
            .then((r) => setO(unwrapPayload(r)))
            .catch((e) => {
                setO(null);
                setMsg(e.response?.data?.message || e.message || 'Yüklenemedi');
            });
    };

    React.useEffect(() => {
        if (!token()) return;
        load();
    }, [id]);

    const updateStatus = async () => {
        if (!nextStatus) return;
        setMsg('');
        try {
            await api.patch(`/vendor/orders/${id}/status`, { status: nextStatus });
            setNextStatus('');
            load();
        } catch (e) {
            setMsg(e.response?.data?.message || e.message || 'Güncellenemedi');
        }
    };

    if (!token()) return <Navigate to="/login" replace />;
    if (!o) return <Shell title="Sipariş (satıcı)"><p className="text-sm text-red-600">{msg || 'Sipariş bulunamadı.'}</p></Shell>;

    return (
        <Shell title={`Sipariş (satıcı) #${o.order_number}`}>
            {msg ? <p className="mb-4 text-sm text-red-600">{msg}</p> : null}
            <div className="rounded-2xl border border-slate-200/80 bg-white/90 p-6 shadow-lg">
                <p className="text-sm text-slate-500">Durum</p>
                <p className="text-xl font-bold text-slate-900">{trOrderStatus(o.status)}</p>
                <p className="mt-2 text-sm text-slate-600">
                    Müşteri: <strong>{o.user?.name}</strong> · Tutar: <strong>₺{Number(o.subtotal).toFixed(2)}</strong>
                </p>
                <div className="mt-4 flex flex-wrap items-center gap-2">
                    <select className="rounded-full border border-slate-200 bg-white/80 px-4 py-2 text-sm" value={nextStatus} onChange={(e) => setNextStatus(e.target.value)}>
                        <option value="">Yeni durum seç</option>
                        {Object.keys(ORDER_STATUS_TR).map((s) => (
                            <option key={s} value={s}>{s}</option>
                        ))}
                    </select>
                    <button type="button" onClick={updateStatus} className="rounded-full bg-slate-900 px-5 py-2 text-sm font-semibold text-white" disabled={!nextStatus}>
                        Durumu güncelle
                    </button>
                </div>
            </div>
        </Shell>
    );
}

function OrderDetailPage() {
    const { id } = useParams();
    const [o, setO] = React.useState(null);
    const [me, setMe] = React.useState(null);
    const [rev, setRev] = React.useState('');
    const [msg, setMsg] = React.useState('');
    const load = () => {
        if (!id) return;
        api.get(`/orders/${id}`).then((r) => setO(unwrapPayload(r))).catch(() => setO(null));
    };
    React.useEffect(() => {
        load();
    }, [id]);
    React.useEffect(() => {
        if (!token()) return;
        api.get('/auth/user').then((r) => setMe(unwrapPayload(r))).catch(() => setMe(null));
    }, []);
    const approve = async (daId) => {
        setMsg('');
        try {
            await api.post(`/design-approvals/${daId}/approve`);
            load();
        } catch (e) {
            setMsg(e.response?.data?.message || e.message);
        }
    };
    const sendRevision = async (daId) => {
        setMsg('');
        try {
            await api.post(`/design-approvals/${daId}/revision`, { customer_feedback: rev });
            setRev('');
            load();
        } catch (e) {
            setMsg(e.response?.data?.message || e.message);
        }
    };
    if (!token()) {
        return <Navigate to="/login" replace />;
    }
    if (!o) {
        return <Shell title="Sipariş"><p className="text-slate-500">Yükleniyor…</p></Shell>;
    }
    const approvals = Array.isArray(o.design_approvals) ? o.design_approvals : [];
    const isCustomer = me?.role === 'customer';
    return (
        <Shell title={`Sipariş #${o.order_number}`}>
            <div className="space-y-6">
                <div className="rounded-2xl border border-slate-200/80 bg-white/90 p-6 shadow-lg backdrop-blur">
                    <p className="text-sm text-slate-500">Durum</p>
                    <p className="text-xl font-bold text-slate-900">{trOrderStatus(o.status)}</p>
                    <p className="mt-2 text-sm text-slate-600">
                        Ödeme: <strong>{o.payment_status}</strong> · Tutar: <strong>₺{Number(o.subtotal).toFixed(2)}</strong>
                    </p>
                </div>
                {msg ? <p className="text-sm text-red-600">{msg}</p> : null}
                {approvals.length > 0 ? (
                    <div className="rounded-2xl border border-slate-200/80 bg-white/90 p-6 shadow-lg">
                        <h2 className="mb-4 text-sm font-bold uppercase tracking-wider text-slate-500">Tasarım onayı</h2>
                        <ul className="space-y-4">
                            {approvals.map((da) => (
                                <li key={da.id} className="rounded-xl border border-orange-100 bg-orange-50/50 p-4">
                                    <p className="text-sm font-medium text-slate-800">
                                        Tur {da.round} · {da.status}
                                    </p>
                                    {isCustomer && da.status === 'pending' ? (
                                        <div className="mt-3 flex flex-wrap gap-2">
                                            <button type="button" onClick={() => approve(da.id)} className="rounded-full bg-emerald-500 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-emerald-600">
                                                Onayla
                                            </button>
                                            <input
                                                className="min-w-[200px] flex-1 rounded-full border border-slate-200 px-4 py-2 text-sm"
                                                placeholder="Revizyon notu"
                                                value={rev}
                                                onChange={(e) => setRev(e.target.value)}
                                            />
                                            <button type="button" onClick={() => sendRevision(da.id)} className="rounded-full bg-slate-800 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-900">
                                                Revizyon gönder
                                            </button>
                                        </div>
                                    ) : null}
                                </li>
                            ))}
                        </ul>
                    </div>
                ) : null}
            </div>
        </Shell>
    );
}

function CustomerQuoteRequestsPage() {
    const [rows, setRows] = React.useState([]);
    const [loading, setLoading] = React.useState(true);
    const [err, setErr] = React.useState('');

    const load = () => {
        setErr('');
        setLoading(true);
        api.get('/quote-requests')
            .then((r) => {
                const payload = unwrapPayload(r);
                const list = Array.isArray(payload?.data) ? payload.data : (Array.isArray(payload) ? payload : []);
                setRows(list);
            })
            .catch((e) => setErr(e.response?.data?.message || e.message || 'Yüklenemedi'))
            .finally(() => setLoading(false));
    };

    React.useEffect(() => {
        if (!token()) return;
        load();
    }, []);

    if (!token()) return <Navigate to="/login" replace />;

    return (
        <Shell title="Teklif taleplerim">
            <div className="mb-6 flex flex-wrap items-center justify-between gap-3">
                <Link to="/customer/quote-requests/new" className="rounded-full bg-orange-500 px-5 py-2.5 text-sm font-semibold text-slate-900 shadow-md hover:opacity-95">
                    + Yeni talep
                </Link>
                <button type="button" onClick={load} className="rounded-full border border-slate-200 bg-white/80 px-4 py-2 text-sm">
                    Yenile
                </button>
            </div>
            {err ? <p className="mb-4 text-sm text-red-600">{err}</p> : null}
            {loading ? <p className="text-slate-500">Yükleniyor…</p> : (
                <ul className="space-y-3">
                    {rows.map((qr) => (
                        <li key={qr.id} className="rounded-2xl border border-slate-200/80 bg-white/90 px-5 py-4 shadow-sm">
                            <div className="flex flex-wrap items-center justify-between gap-3">
                                <div className="min-w-0">
                                    <div className="truncate font-semibold text-slate-900">{qr.title}</div>
                                    <div className="mt-1 text-xs text-slate-500">
                                        {qr.category?.name ? `Kategori: ${qr.category.name}` : null}
                                        {qr.status ? ` · Durum: ${qr.status}` : null}
                                    </div>
                                </div>
                                <Link className="rounded-full bg-slate-900 px-4 py-2 text-xs font-semibold text-white" to={`/customer/quote-requests/${qr.id}`}>
                                    Detay
                                </Link>
                            </div>
                        </li>
                    ))}
                    {!rows.length ? <li className="text-sm text-slate-500">Henüz talep yok.</li> : null}
                </ul>
            )}
        </Shell>
    );
}

function CustomerQuoteRequestNewPage() {
    const [cats, setCats] = React.useState([]);
    const [categoryId, setCategoryId] = React.useState('');
    const [title, setTitle] = React.useState('');
    const [description, setDescription] = React.useState('');
    const [city, setCity] = React.useState('');
    const [district, setDistrict] = React.useState('');
    const [address, setAddress] = React.useState('');
    const [msg, setMsg] = React.useState('');

    React.useEffect(() => {
        if (!token()) return;
        api.get('/categories')
            .then((r) => setCats(Array.isArray(unwrapPayload(r)) ? unwrapPayload(r) : []))
            .catch(() => setCats([]));
    }, []);

    const submit = async (e) => {
        e.preventDefault();
        setMsg('');
        try {
            const r = await api.post('/quote-requests', {
                category_id: parseInt(categoryId, 10),
                title,
                description: description || null,
                city: city || null,
                district: district || null,
                address: address || null,
            });
            const payload = unwrapPayload(r);
            const id = payload?.id || r.data?.id;
            window.location.href = `/app/customer/quote-requests/${id}`;
        } catch (ex) {
            setMsg(ex.response?.data?.message || ex.message || 'Kaydedilemedi');
        }
    };

    if (!token()) return <Navigate to="/login" replace />;

    return (
        <Shell title="Yeni teklif talebi">
            <form onSubmit={submit} className="mx-auto max-w-2xl space-y-4 rounded-2xl border border-slate-200/80 bg-white/90 p-8 shadow-lg">
                {msg ? <p className="text-sm text-red-600">{msg}</p> : null}
                <div>
                    <label className="block text-xs font-medium text-slate-600">Kategori</label>
                    <select className="mt-1 w-full rounded-xl border px-4 py-3" value={categoryId} onChange={(e) => setCategoryId(e.target.value)} required>
                        <option value="">Seçin</option>
                        {cats.map((c) => (
                            <option key={c.id} value={c.id}>{c.name}</option>
                        ))}
                    </select>
                </div>
                <div>
                    <label className="block text-xs font-medium text-slate-600">Başlık</label>
                    <input className="mt-1 w-full rounded-xl border px-4 py-3" value={title} onChange={(e) => setTitle(e.target.value)} required />
                </div>
                <div>
                    <label className="block text-xs font-medium text-slate-600">Açıklama</label>
                    <textarea className="mt-1 w-full rounded-xl border px-4 py-3" rows={5} value={description} onChange={(e) => setDescription(e.target.value)} />
                </div>
                <div className="grid gap-3 md:grid-cols-2">
                    <div>
                        <label className="block text-xs font-medium text-slate-600">Şehir</label>
                        <input className="mt-1 w-full rounded-xl border px-4 py-3" value={city} onChange={(e) => setCity(e.target.value)} />
                    </div>
                    <div>
                        <label className="block text-xs font-medium text-slate-600">İlçe</label>
                        <input className="mt-1 w-full rounded-xl border px-4 py-3" value={district} onChange={(e) => setDistrict(e.target.value)} />
                    </div>
                </div>
                <div>
                    <label className="block text-xs font-medium text-slate-600">Adres (opsiyonel)</label>
                    <input className="mt-1 w-full rounded-xl border px-4 py-3" value={address} onChange={(e) => setAddress(e.target.value)} />
                </div>
                <button type="submit" className="w-full rounded-full bg-orange-500 py-3 font-semibold text-slate-900">
                    Talep oluştur
                </button>
            </form>
        </Shell>
    );
}

function CustomerQuoteRequestDetailPage() {
    const { id } = useParams();
    const [qr, setQr] = React.useState(null);
    const [err, setErr] = React.useState('');
    const [loading, setLoading] = React.useState(true);
    const [selectMsg, setSelectMsg] = React.useState('');

    const load = () => {
        if (!id) return;
        setErr('');
        setSelectMsg('');
        setLoading(true);
        api.get(`/quote-requests/${id}`)
            .then((r) => setQr(unwrapPayload(r)))
            .catch((e) => {
                setQr(null);
                setErr(e.response?.data?.message || e.message || 'Yüklenemedi');
            })
            .finally(() => setLoading(false));
    };

    React.useEffect(() => {
        if (!token()) return;
        load();
    }, [id]);

    const selectQuote = async (quoteId) => {
        setSelectMsg('');
        try {
            await api.post(`/quote-requests/${id}/quotes/${quoteId}/select`);
            setSelectMsg('Teklif seçildi, sipariş oluşturuldu.');
            load();
        } catch (e) {
            setSelectMsg(e.response?.data?.message || e.message || 'Seçilemedi');
        }
    };

    if (!token()) return <Navigate to="/login" replace />;
    if (loading) return <Shell title="Teklif talebi"><p className="text-slate-500">Yükleniyor…</p></Shell>;
    if (!qr) return <Shell title="Teklif talebi"><p className="text-sm text-red-600">{err || 'Bulunamadı'}</p></Shell>;

    const quotes = Array.isArray(qr.quotes) ? qr.quotes : [];

    return (
        <Shell title="Teklif talebi">
            {selectMsg ? <p className={`mb-4 text-sm ${selectMsg.includes('oluşturuldu') ? 'text-emerald-700' : 'text-red-600'}`}>{selectMsg}</p> : null}
            <div className="mb-6 rounded-2xl border border-slate-200/80 bg-white/90 p-6 shadow-lg">
                <div className="text-lg font-bold text-slate-900">{qr.title}</div>
                <div className="mt-1 text-sm text-slate-600">
                    {qr.category?.name ? `Kategori: ${qr.category.name}` : null}
                    {qr.status ? ` · Durum: ${qr.status}` : null}
                </div>
                {qr.description ? <p className="mt-3 whitespace-pre-wrap text-sm text-slate-700">{qr.description}</p> : null}
            </div>

            <div className="rounded-2xl border border-slate-200/80 bg-white/90 p-6 shadow-lg">
                <h2 className="mb-4 text-sm font-bold uppercase tracking-wider text-slate-500">Gelen teklifler</h2>
                {!quotes.length ? (
                    <p className="text-sm text-slate-500">Henüz teklif yok.</p>
                ) : (
                    <ul className="space-y-3">
                        {quotes.map((q) => (
                            <li key={q.id} className="rounded-xl border border-slate-200 bg-white/80 p-4">
                                <div className="flex flex-wrap items-center justify-between gap-2">
                                    <div>
                                        <div className="font-semibold text-slate-900">{q.vendor?.name || `Vendor #${q.vendor_id}`}</div>
                                        <div className="text-xs text-slate-500">
                                            Durum: {q.status} {q.delivery_days ? `· ${q.delivery_days} gün` : ''}
                                        </div>
                                    </div>
                                    {q.status === 'pending' && qr.status === 'open' ? (
                                        <button type="button" onClick={() => selectQuote(q.id)} className="rounded-full bg-emerald-500 px-4 py-2 text-xs font-semibold text-white">
                                            Bu teklifi seç
                                        </button>
                                    ) : null}
                                </div>
                                <div className="mt-2 text-sm font-bold text-slate-800">₺{Number(q.amount).toFixed(2)}</div>
                                {q.note ? <p className="mt-2 whitespace-pre-wrap text-sm text-slate-700">{q.note}</p> : null}
                                {q.included_notes ? <p className="mt-2 whitespace-pre-wrap text-xs text-slate-600"><strong>Dahil:</strong> {q.included_notes}</p> : null}
                                {q.excluded_notes ? <p className="mt-1 whitespace-pre-wrap text-xs text-slate-600"><strong>Hariç:</strong> {q.excluded_notes}</p> : null}
                            </li>
                        ))}
                    </ul>
                )}
            </div>
        </Shell>
    );
}

function NotificationsPage() {
    const [rows, setRows] = React.useState([]);
    React.useEffect(() => {
        if (!token()) return;
        api.get('/notifications').then((r) => {
            const page = unwrapPage(r);
            const list = Array.isArray(page?.data) ? page.data : (Array.isArray(page) ? page : []);
            setRows(list);
        });
    }, []);
    if (!token()) {
        return <Navigate to="/login" replace />;
    }
    return (
        <Shell title="Bildirimler">
            <ul className="space-y-3">
                {rows.map((n) => (
                    <li key={n.id} className="rounded-2xl border border-slate-200/80 bg-white/90 px-4 py-3 text-sm shadow-sm">
                        <div className="flex items-start justify-between gap-3">
                            <pre className="min-w-0 flex-1 whitespace-pre-wrap text-slate-700">{JSON.stringify(n.data, null, 2)}</pre>
                            {n.read_at ? (
                                <span className="shrink-0 rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-700">Okundu</span>
                            ) : (
                                <button
                                    type="button"
                                    className="shrink-0 rounded-full bg-orange-500 px-3 py-1.5 text-xs font-semibold text-slate-900"
                                    onClick={() => {
                                        api.post(`/notifications/${n.id}/read`).then(() => {
                                            setRows((prev) => prev.map((x) => (x.id === n.id ? { ...x, read_at: new Date().toISOString() } : x)));
                                        });
                                    }}
                                >
                                    Okundu yap
                                </button>
                            )}
                        </div>
                    </li>
                ))}
            </ul>
        </Shell>
    );
}

function MessagesPage() {
    const [rows, setRows] = React.useState([]);
    React.useEffect(() => {
        if (!token()) return;
        api.get('/conversations').then((r) => {
            const page = unwrapPage(r);
            const list = Array.isArray(page?.data) ? page.data : (Array.isArray(page) ? page : []);
            setRows(list);
        });
    }, []);
    if (!token()) {
        return <Navigate to="/login" replace />;
    }
    return (
        <Shell title="Mesajlar">
            <ul className="space-y-3">
                {rows.map((c) => (
                    <li key={c.id} className="rounded-2xl border border-slate-200/80 bg-white/90 px-4 py-3 text-sm shadow-sm">
                        <Link className="font-semibold text-orange-600 hover:underline" to={`/messages/${c.id}`}>Konuşma #{c.id}</Link>
                        {c.order_id ? <span className="ml-2 text-xs text-slate-500">(sipariş {c.order_id})</span> : null}
                    </li>
                ))}
            </ul>
        </Shell>
    );
}

function ConversationDetailPage() {
    const { id } = useParams();
    const [c, setC] = React.useState(null);
    const [body, setBody] = React.useState('');
    const [msg, setMsg] = React.useState('');

    const load = () => {
        if (!id) return;
        setMsg('');
        api.get(`/conversations/${id}`)
            .then((r) => setC(unwrapPayload(r)))
            .catch((e) => {
                setC(null);
                setMsg(e.response?.data?.message || e.message || 'Yüklenemedi');
            });
    };

    React.useEffect(() => {
        if (!token()) return;
        load();
    }, [id]);

    const send = async (e) => {
        e.preventDefault();
        setMsg('');
        try {
            await api.post(`/conversations/${id}/messages`, { body });
            setBody('');
            load();
        } catch (e2) {
            setMsg(e2.response?.data?.message || e2.message || 'Gönderilemedi');
        }
    };

    if (!token()) return <Navigate to="/login" replace />;
    if (!c) return <Shell title="Konuşma"><p className="text-sm text-red-600">{msg || 'Konuşma bulunamadı.'}</p></Shell>;

    const messages = Array.isArray(c.messages) ? c.messages : [];

    return (
        <Shell title={`Konuşma #${c.id}`}>
            {msg ? <p className="mb-4 text-sm text-red-600">{msg}</p> : null}
            <div className="mb-6 space-y-3">
                {messages.map((m) => (
                    <div key={m.id} className={`rounded-xl border p-4 text-sm shadow-sm ${m.blocked ? 'border-rose-200 bg-rose-50/60' : 'border-slate-200 bg-white/90'}`}>
                        <p className="text-xs text-slate-500">{m.user?.name}</p>
                        <p className="mt-1 whitespace-pre-wrap text-slate-800">{m.display_body || m.body}</p>
                        {m.blocked ? <p className="mt-2 text-xs text-rose-700">Bu mesaj moderasyondan geçti.</p> : null}
                    </div>
                ))}
            </div>
            <form onSubmit={send} className="flex flex-wrap gap-2">
                <input className="min-w-[260px] flex-1 rounded-full border px-4 py-2 text-sm" value={body} onChange={(e) => setBody(e.target.value)} placeholder="Mesaj yazın…" required />
                <button type="submit" className="rounded-full bg-orange-500 px-6 py-2 font-semibold text-slate-900">Gönder</button>
            </form>
        </Shell>
    );
}

function VendorQuoteRequestsPage() {
    const [rows, setRows] = React.useState([]);
    const [loading, setLoading] = React.useState(true);
    const [err, setErr] = React.useState('');

    React.useEffect(() => {
        if (!token()) return;
        setErr('');
        setLoading(true);
        api.get('/vendor/matched-quote-requests')
            .then((r) => {
                const payload = unwrapPayload(r);
                const list = Array.isArray(payload?.data) ? payload.data : (Array.isArray(payload) ? payload : []);
                setRows(list);
            })
            .catch((e) => setErr(e.response?.data?.message || e.message || 'Yüklenemedi'))
            .finally(() => setLoading(false));
    }, []);

    if (!token()) return <Navigate to="/login" replace />;

    return (
        <Shell title="Teklif talepleri (satıcı)">
            {err ? <p className="mb-4 text-sm text-red-600">{err}</p> : null}
            {loading ? <p className="text-slate-500">Yükleniyor…</p> : (
                <ul className="space-y-3">
                    {rows.map((qr) => (
                        <li key={qr.id} className="rounded-2xl border border-slate-200/80 bg-white/90 px-5 py-4 shadow-sm">
                            <div className="flex flex-wrap items-center justify-between gap-3">
                                <div className="min-w-0">
                                    <div className="truncate font-semibold text-slate-900">{qr.title}</div>
                                    <div className="mt-1 text-xs text-slate-500">
                                        {qr.category?.name ? `Kategori: ${qr.category.name}` : null}
                                        {qr.city ? ` · ${qr.city}` : null}
                                    </div>
                                </div>
                                <div className="flex items-center gap-2">
                                    {qr.has_paid_meeting ? (
                                        <span className="rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-800">Görüşme aktif</span>
                                    ) : (
                                        <span className="rounded-full bg-amber-100 px-2.5 py-1 text-xs font-semibold text-amber-800">Görüşme gerekli</span>
                                    )}
                                    <Link className="rounded-full bg-slate-900 px-4 py-2 text-xs font-semibold text-white" to={`/vendor/quote-requests/${qr.id}`}>
                                        Detay
                                    </Link>
                                </div>
                            </div>
                        </li>
                    ))}
                    {!rows.length ? <li className="text-sm text-slate-500">Eşleşen açık talep yok.</li> : null}
                </ul>
            )}
        </Shell>
    );
}

function VendorQuoteRequestDetailPage() {
    const { id } = useParams();
    const [qr, setQr] = React.useState(null);
    const [me, setMe] = React.useState(null);
    const [amount, setAmount] = React.useState('');
    const [deliveryDays, setDeliveryDays] = React.useState('');
    const [note, setNote] = React.useState('');
    const [includedNotes, setIncludedNotes] = React.useState('');
    const [excludedNotes, setExcludedNotes] = React.useState('');
    const [msg, setMsg] = React.useState('');
    const [loading, setLoading] = React.useState(true);

    const load = async () => {
        if (!id) return;
        setLoading(true);
        setMsg('');
        try {
            const listResp = await api.get('/vendor/matched-quote-requests?page=1');
            const payload = unwrapPayload(listResp);
            const list = Array.isArray(payload?.data) ? payload.data : [];
            const found = list.find((x) => String(x.id) === String(id));
            setQr(found || null);
            if (found?.my_quote) {
                setAmount(found.my_quote.amount ?? '');
                setDeliveryDays(found.my_quote.delivery_days ?? '');
                setNote(found.my_quote.note ?? '');
                setIncludedNotes(found.my_quote.included_notes ?? '');
                setExcludedNotes(found.my_quote.excluded_notes ?? '');
            }
        } catch (e) {
            setQr(null);
            setMsg(e.response?.data?.message || e.message || 'Yüklenemedi');
        } finally {
            setLoading(false);
        }
    };

    React.useEffect(() => {
        if (!token()) return;
        api.get('/auth/user').then((r) => setMe(unwrapPayload(r))).catch(() => setMe(null));
    }, []);

    React.useEffect(() => {
        if (!token()) return;
        load();
    }, [id]);

    const purchaseMeeting = async () => {
        setMsg('');
        try {
            await api.post(`/vendor/quote-requests/${id}/meeting`);
            await load();
        } catch (e) {
            setMsg(e.response?.data?.message || e.message || 'İşlem başarısız');
        }
    };

    const submit = async (e) => {
        e.preventDefault();
        setMsg('');
        try {
            await api.post(`/vendor/quote-requests/${id}/quotes`, {
                amount: parseFloat(String(amount).replace(',', '.')),
                delivery_days: deliveryDays ? parseInt(String(deliveryDays), 10) : null,
                note: note || null,
                included_notes: includedNotes || null,
                excluded_notes: excludedNotes || null,
            });
            setMsg('Teklif kaydedildi.');
            await load();
        } catch (e2) {
            setMsg(e2.response?.data?.message || e2.message || 'Teklif kaydedilemedi');
        }
    };

    if (!token()) return <Navigate to="/login" replace />;
    if (me && me.role !== 'vendor') return <Shell title="Yetki"><p className="text-red-600">Bu sayfa satıcılar içindir.</p></Shell>;
    if (loading) return <Shell title="Teklif"><p className="text-slate-500">Yükleniyor…</p></Shell>;
    if (!qr) return <Shell title="Teklif"><p className="text-slate-500">Talep bulunamadı.</p>{msg ? <p className="mt-2 text-sm text-red-600">{msg}</p> : null}</Shell>;

    return (
        <Shell title="Teklif talebi detayı">
            {msg ? <p className={`mb-4 text-sm ${msg.includes('kaydedildi') ? 'text-emerald-700' : 'text-red-600'}`}>{msg}</p> : null}
            <div className="mb-6 rounded-2xl border border-slate-200/80 bg-white/90 p-6 shadow-lg">
                <div className="text-lg font-bold text-slate-900">{qr.title}</div>
                <div className="mt-1 text-sm text-slate-600">
                    {qr.category?.name ? `Kategori: ${qr.category.name}` : null}
                    {qr.city ? ` · ${qr.city}` : null}
                </div>
                {qr.description ? <p className="mt-3 whitespace-pre-wrap text-sm text-slate-700">{qr.description}</p> : null}
                <div className="mt-4">
                    {qr.has_paid_meeting ? (
                        <span className="rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-800">Görüşme hakkı aktif</span>
                    ) : (
                        <div className="flex flex-wrap items-center gap-2">
                            <span className="rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-800">Teklif verebilmek için görüşme hakkı gerekli</span>
                            <button type="button" onClick={purchaseMeeting} className="rounded-full bg-slate-900 px-4 py-2 text-xs font-semibold text-white">
                                Görüşme hakkı satın al
                            </button>
                        </div>
                    )}
                </div>
            </div>

            <form onSubmit={submit} className="rounded-2xl border border-slate-200/80 bg-white/90 p-6 shadow-lg">
                <h2 className="mb-4 text-sm font-bold uppercase tracking-wider text-slate-500">Teklif gönder</h2>
                <div className="grid gap-3 md:grid-cols-2">
                    <div>
                        <label className="block text-xs font-medium text-slate-600">Tutar (₺)</label>
                        <input className="w-full rounded-xl border px-4 py-2" value={amount} onChange={(e) => setAmount(e.target.value)} required />
                    </div>
                    <div>
                        <label className="block text-xs font-medium text-slate-600">Teslim (gün)</label>
                        <input className="w-full rounded-xl border px-4 py-2" value={deliveryDays} onChange={(e) => setDeliveryDays(e.target.value)} placeholder="Opsiyonel" />
                    </div>
                </div>
                <div className="mt-3">
                    <label className="block text-xs font-medium text-slate-600">Not</label>
                    <textarea className="w-full rounded-xl border px-4 py-2" rows={3} value={note} onChange={(e) => setNote(e.target.value)} placeholder="Opsiyonel" />
                </div>
                <div className="mt-3 grid gap-3 md:grid-cols-2">
                    <div>
                        <label className="block text-xs font-medium text-slate-600">Dahil olanlar</label>
                        <textarea className="w-full rounded-xl border px-4 py-2" rows={3} value={includedNotes} onChange={(e) => setIncludedNotes(e.target.value)} placeholder="Opsiyonel" />
                    </div>
                    <div>
                        <label className="block text-xs font-medium text-slate-600">Hariç olanlar</label>
                        <textarea className="w-full rounded-xl border px-4 py-2" rows={3} value={excludedNotes} onChange={(e) => setExcludedNotes(e.target.value)} placeholder="Opsiyonel" />
                    </div>
                </div>
                <button type="submit" className="mt-4 w-full rounded-full bg-orange-500 py-3 font-semibold text-slate-900">
                    Teklifi kaydet
                </button>
            </form>
        </Shell>
    );
}

function flattenCategories(rows) {
    const out = [];
    (rows || []).forEach((p) => {
        out.push({ id: p.id, label: p.name });
        (p.children || []).forEach((c) => {
            out.push({ id: c.id, label: `${p.name} › ${c.name}` });
        });
    });
    return out;
}

function PriceEstimatePage() {
    const [q, setQ] = React.useState('');
    const [categoryId, setCategoryId] = React.useState('');
    const [catOpts, setCatOpts] = React.useState([]);
    const [hits, setHits] = React.useState([]);
    const [page, setPage] = React.useState(1);
    const [lastPage, setLastPage] = React.useState(1);
    const [searching, setSearching] = React.useState(false);
    const [cart, setCart] = React.useState({});
    const [bundle, setBundle] = React.useState(null);
    const [err, setErr] = React.useState('');

    React.useEffect(() => {
        api.get('/categories').then((r) => setCatOpts(flattenCategories(unwrapPayload(r)))).catch(() => setCatOpts([]));
    }, []);

    const runSearch = async (reset) => {
        setErr('');
        setSearching(true);
        const nextPage = reset ? 1 : page + 1;
        try {
            const params = new URLSearchParams();
            if (q.trim()) params.set('q', q.trim());
            if (categoryId) params.set('category_id', categoryId);
            params.set('page', String(nextPage));
            const r = await api.get(`/products?${params.toString()}`);
            const payload = unwrapPayload(r);
            const list = Array.isArray(payload) ? payload : [];
            const meta = r.data?.meta || {};
            setLastPage(meta.last_page || 1);
            setPage(nextPage);
            setHits((prev) => (reset ? list : [...prev, ...list]));
        } catch (ex) {
            setErr(ex.response?.data?.message || ex.message || 'Arama başarısız');
            if (reset) setHits([]);
        } finally {
            setSearching(false);
        }
    };

    const addLine = (product) => {
        setCart((prev) => {
            const id = product.id;
            const cur = prev[id] || { qty: 0, name: product.name, sub: [product.vendor?.name, product.category?.name].filter(Boolean).join(' · ') };
            return { ...prev, [id]: { ...cur, qty: cur.qty + 1 } };
        });
    };

    const setQty = (id, qty) => {
        const v = Math.min(99999, Math.max(1, parseInt(String(qty), 10) || 1));
        setCart((prev) => (prev[id] ? { ...prev, [id]: { ...prev[id], qty: v } } : prev));
    };

    const removeLine = (id) => {
        setCart((prev) => {
            const next = { ...prev };
            delete next[id];
            return next;
        });
    };

    const compute = async () => {
        setErr('');
        setBundle(null);
        const lines = Object.keys(cart).map((k) => ({ product_id: parseInt(k, 10), quantity: cart[k].qty }));
        if (!lines.length) {
            setErr('Önce ürün ekleyin.');
            return;
        }
        try {
            const r = await api.post('/price-estimate/bundle', { lines });
            setBundle(unwrapPayload(r));
        } catch (ex) {
            setErr(ex.response?.data?.message || ex.message || 'Hesaplanamadı');
        }
    };

    const confTr = (c) => ({ high: 'Yüksek', medium: 'Orta', low: 'Düşük', none: '—' }[c] || c);

    if (!token()) return <Navigate to="/login" replace />;
    return (
        <Shell title="İş hesaplama">
            <div className="mx-auto grid max-w-4xl gap-6 md:grid-cols-2">
                <div className="rounded-2xl border border-slate-200/80 bg-white/90 p-6 shadow-lg">
                    <h2 className="mb-3 text-sm font-semibold uppercase tracking-wide text-slate-500">Ürün ara &amp; ekle</h2>
                    <div className="space-y-3">
                        <input className="w-full rounded-xl border border-slate-200 px-4 py-2" value={q} onChange={(e) => setQ(e.target.value)} placeholder="Kelime (kartvizit, broşür…)" />
                        <select className="w-full rounded-xl border border-slate-200 px-4 py-2" value={categoryId} onChange={(e) => setCategoryId(e.target.value)}>
                            <option value="">Tüm kategoriler</option>
                            {catOpts.map((o) => (
                                <option key={o.id} value={o.id}>{o.label}</option>
                            ))}
                        </select>
                        <div className="flex flex-wrap gap-2">
                            <button type="button" onClick={() => { setHits([]); runSearch(true); }} disabled={searching} className="rounded-full bg-orange-500 px-5 py-2 text-sm font-semibold text-slate-900">
                                {searching ? '…' : 'Ara'}
                            </button>
                            {page < lastPage ? (
                                <button type="button" onClick={() => runSearch(false)} disabled={searching} className="rounded-full border border-slate-200 px-4 py-2 text-sm">
                                    Daha fazla
                                </button>
                            ) : null}
                        </div>
                    </div>
                    <ul className="mt-4 max-h-[420px] space-y-2 overflow-y-auto rounded-xl border border-slate-100 bg-slate-50/50 p-2">
                        {hits.map((p) => (
                            <li key={p.id} className="flex items-start justify-between gap-2 rounded-lg bg-white px-3 py-2 text-sm shadow-sm">
                                <div>
                                    <div className="font-medium text-slate-900">{p.name}</div>
                                    <div className="text-xs text-slate-500">{p.category?.name}</div>
                                </div>
                                <button type="button" onClick={() => addLine(p)} className="shrink-0 rounded-full border border-amber-200 bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-900">Ekle</button>
                            </li>
                        ))}
                        {!hits.length && !searching ? <li className="px-2 py-6 text-center text-sm text-slate-500">Arama yapın.</li> : null}
                    </ul>
                </div>
                <div className="space-y-4">
                    <div className="rounded-2xl border border-slate-200/80 bg-white/90 p-6 shadow-lg">
                        <h2 className="mb-2 text-sm font-semibold uppercase tracking-wide text-slate-500">İş kalemleri</h2>
                        {!Object.keys(cart).length ? <p className="text-sm text-slate-500">Soldan ürün ekleyin; adetleri düzenleyin.</p> : (
                            <ul className="space-y-2 text-sm">
                                {Object.entries(cart).map(([id, row]) => (
                                    <li key={id} className="flex flex-wrap items-center gap-2 rounded-xl border border-slate-100 bg-slate-50/80 px-3 py-2">
                                        <div className="min-w-0 flex-1">
                                            <div className="font-medium text-slate-900">{row.name}</div>
                                            {row.sub ? <div className="text-xs text-slate-500">{row.sub}</div> : null}
                                        </div>
                                        <label className="flex items-center gap-1 text-xs text-slate-600">
                                            Adet
                                            <input type="number" min={1} max={99999} className="w-20 rounded-lg border px-2 py-1" value={row.qty} onChange={(e) => setQty(id, e.target.value)} />
                                        </label>
                                        <button type="button" onClick={() => removeLine(id)} className="text-xs text-red-600">Kaldır</button>
                                    </li>
                                ))}
                            </ul>
                        )}
                        <button type="button" onClick={compute} disabled={!Object.keys(cart).length} className="mt-4 w-full rounded-full bg-slate-900 py-2.5 text-sm font-semibold text-white disabled:opacity-40">
                            Toplam tahmini hesapla
                        </button>
                    </div>
                    {err ? <p className="text-sm text-red-600">{err}</p> : null}
                    {bundle ? (
                        <div className="rounded-2xl border border-amber-200 bg-amber-50/80 p-6 shadow-inner">
                            {bundle.has_quote_only_lines ? (
                                <p className="mb-3 text-sm text-slate-700">Bazı kalemler teklif esasındadır; toplama dahil edilmezler.</p>
                            ) : null}
                            <p className="text-sm text-slate-500">Tahmini toplam aralık</p>
                            <p className="text-2xl font-bold text-slate-900">
                                ₺{Number(bundle.totals.estimated_min).toFixed(2)} — ₺{Number(bundle.totals.estimated_max).toFixed(2)}
                            </p>
                            <p className="mt-1 text-xs text-slate-600">Genel güven: {confTr(bundle.overall_confidence)}</p>
                            <p className="mt-2 text-xs text-slate-600">{bundle.disclaimer}</p>
                            <ul className="mt-4 space-y-2 border-t border-amber-200/60 pt-3 text-xs text-slate-700">
                                {(bundle.lines || []).map((ln) => (
                                    <li key={ln.product_id}>
                                        <span className="font-medium">{ln.name}</span>
                                        {' '}
                                        ×
                                        {ln.quantity}
                                        {ln.is_quote_only ? ' (teklif)' : ` — ₺${Number(ln.estimated_min).toFixed(2)}–₺${Number(ln.estimated_max).toFixed(2)} (${confTr(ln.confidence)})`}
                                    </li>
                                ))}
                            </ul>
                        </div>
                    ) : null}
                </div>
            </div>
        </Shell>
    );
}

function SupportListPage() {
    const [rows, setRows] = React.useState([]);
    const [loading, setLoading] = React.useState(true);
    React.useEffect(() => {
        if (!token()) return;
        api.get('/support-tickets').then((r) => setRows(Array.isArray(unwrapPayload(r)) ? unwrapPayload(r) : [])).finally(() => setLoading(false));
    }, []);
    if (!token()) return <Navigate to="/login" replace />;
    return (
        <Shell title="Destek talepleri">
            <Link to="/support/new" className="mb-6 inline-block rounded-full bg-orange-500 px-5 py-2.5 text-sm font-semibold text-slate-900 shadow-md hover:opacity-95">
                + Yeni talep
            </Link>
            {loading ? <p className="text-slate-500">Yükleniyor…</p> : (
                <ul className="space-y-3">
                    {rows.map((t) => (
                        <li key={t.id}>
                            <Link to={`/support/${t.id}`} className="block rounded-2xl border border-slate-200/80 bg-white/90 px-5 py-4 shadow-sm transition hover:shadow-md">
                                <span className="font-semibold text-slate-900">{t.subject}</span>
                                <span className="ml-2 text-xs text-slate-500">{t.status}</span>
                            </Link>
                        </li>
                    ))}
                </ul>
            )}
        </Shell>
    );
}

function SupportNewPage() {
    const [subject, setSubject] = React.useState('');
    const [body, setBody] = React.useState('');
    const [err, setErr] = React.useState('');
    const submit = async (e) => {
        e.preventDefault();
        setErr('');
        try {
            await api.post('/support-tickets', { subject, body });
            window.location.href = '/app/support';
        } catch (ex) {
            setErr(ex.response?.data?.message || ex.message);
        }
    };
    if (!token()) return <Navigate to="/login" replace />;
    return (
        <Shell title="Yeni destek talebi">
            <form onSubmit={submit} className="mx-auto max-w-lg space-y-4 rounded-2xl border border-slate-200/80 bg-white/90 p-8 shadow-lg">
                {err ? <p className="text-sm text-red-600">{err}</p> : null}
                <input className="w-full rounded-xl border px-4 py-3" placeholder="Konu" value={subject} onChange={(e) => setSubject(e.target.value)} required />
                <textarea className="w-full rounded-xl border px-4 py-3" rows={5} placeholder="Mesajınız" value={body} onChange={(e) => setBody(e.target.value)} required />
                <button type="submit" className="w-full rounded-full bg-orange-500 py-3 font-semibold text-slate-900">Gönder</button>
            </form>
        </Shell>
    );
}

function SupportDetailPage() {
    const { id } = useParams();
    const [t, setT] = React.useState(null);
    const [reply, setReply] = React.useState('');
    const load = () => api.get(`/support-tickets/${id}`).then((r) => setT(unwrapPayload(r))).catch(() => setT(null));
    React.useEffect(() => {
        if (id) load();
    }, [id]);
    const send = async (e) => {
        e.preventDefault();
        await api.post(`/support-tickets/${id}/reply`, { body: reply });
        setReply('');
        load();
    };
    if (!token()) return <Navigate to="/login" replace />;
    if (!t) return <Shell title="Destek"><p>Yükleniyor…</p></Shell>;
    const msgs = Array.isArray(t.messages) ? t.messages : [];
    return (
        <Shell title={t.subject}>
            <div className="mb-6 space-y-3">
                {msgs.map((m) => (
                    <div key={m.id} className="rounded-xl border border-slate-200 bg-white/90 p-4 text-sm shadow-sm">
                        <p className="text-xs text-slate-500">{m.user?.name}</p>
                        <p className="mt-1 whitespace-pre-wrap text-slate-800">{m.body}</p>
                    </div>
                ))}
            </div>
            {t.status !== 'closed' ? (
                <form onSubmit={send} className="flex flex-wrap gap-2">
                    <input className="min-w-[240px] flex-1 rounded-full border px-4 py-2" value={reply} onChange={(e) => setReply(e.target.value)} placeholder="Yanıtınız" required />
                    <button type="submit" className="rounded-full bg-orange-500 px-6 py-2 font-semibold text-slate-900">Gönder</button>
                </form>
            ) : null}
        </Shell>
    );
}

function VendorPayoutsPage() {
    const [rows, setRows] = React.useState([]);
    const [amount, setAmount] = React.useState('');
    const [msg, setMsg] = React.useState('');
    const load = () => api.get('/vendor/payout-requests').then((r) => setRows(Array.isArray(unwrapPayload(r)) ? unwrapPayload(r) : []));
    React.useEffect(() => {
        if (!token()) return;
        load();
    }, []);
    const submit = async (e) => {
        e.preventDefault();
        setMsg('');
        try {
            await api.post('/vendor/payout-requests', { amount: parseFloat(amount.replace(',', '.')) });
            setAmount('');
            load();
        } catch (ex) {
            setMsg(ex.response?.data?.message || ex.message);
        }
    };
    if (!token()) return <Navigate to="/login" replace />;
    return (
        <Shell title="Ödeme talepleri">
            <form onSubmit={submit} className="mb-8 flex max-w-md flex-wrap items-end gap-3 rounded-2xl border border-slate-200/80 bg-white/90 p-6 shadow-lg">
                {msg ? <p className="w-full text-sm text-red-600">{msg}</p> : null}
                <div>
                    <label className="block text-xs font-medium text-slate-600">Tutar (₺)</label>
                    <input className="rounded-xl border px-3 py-2" value={amount} onChange={(e) => setAmount(e.target.value)} required />
                </div>
                <button type="submit" className="rounded-full bg-orange-500 px-5 py-2 font-semibold text-slate-900">Talep oluştur</button>
            </form>
            <ul className="space-y-2">
                {rows.map((r) => (
                    <li key={r.id} className="rounded-xl border border-slate-200 bg-white/90 px-4 py-3 text-sm shadow-sm">
                        ₺{Number(r.amount).toFixed(2)} · {r.status}
                    </li>
                ))}
            </ul>
        </Shell>
    );
}

function AdminSupportListPage() {
    const [rows, setRows] = React.useState([]);
    React.useEffect(() => {
        if (!token()) return;
        api.get('/admin/support-tickets').then((r) => {
            const page = unwrapPage(r);
            const list = Array.isArray(page?.data) ? page.data : (Array.isArray(page) ? page : []);
            setRows(list);
        });
    }, []);
    if (!token()) return <Navigate to="/login" replace />;
    return (
        <Shell title="Destek (yönetici)">
            <ul className="space-y-2">
                {rows.map((t) => (
                    <li key={t.id} className="rounded-xl border border-slate-200 bg-white/90 px-4 py-3 text-sm">
                        <Link className="font-semibold text-orange-600 hover:underline" to={`/admin/support/${t.id}`}>{t.subject}</Link>
                        <span className="ml-2 text-slate-500">{t.status}</span>
                    </li>
                ))}
            </ul>
        </Shell>
    );
}

function AdminSupportDetailPage() {
    const { id } = useParams();
    const [t, setT] = React.useState(null);
    const [body, setBody] = React.useState('');
    const [status, setStatus] = React.useState('open');
    const [msg, setMsg] = React.useState('');

    const load = () => {
        if (!id) return;
        setMsg('');
        api.get(`/admin/support-tickets/${id}`)
            .then((r) => {
                const payload = unwrapPayload(r);
                setT(payload);
                if (payload?.status) setStatus(payload.status);
            })
            .catch((e) => {
                setT(null);
                setMsg(e.response?.data?.message || e.message || 'Yüklenemedi');
            });
    };

    React.useEffect(() => {
        if (!token()) return;
        load();
    }, [id]);

    const assign = async () => {
        setMsg('');
        try {
            await api.post(`/admin/support-tickets/${id}/assign`);
            load();
        } catch (e) {
            setMsg(e.response?.data?.message || e.message || 'Atanamadı');
        }
    };

    const updateStatus = async () => {
        setMsg('');
        try {
            await api.patch(`/admin/support-tickets/${id}/status`, { status });
            load();
        } catch (e) {
            setMsg(e.response?.data?.message || e.message || 'Güncellenemedi');
        }
    };

    const reply = async (e) => {
        e.preventDefault();
        setMsg('');
        try {
            await api.post(`/admin/support-tickets/${id}/reply`, { body });
            setBody('');
            load();
        } catch (e) {
            setMsg(e.response?.data?.message || e.message || 'Gönderilemedi');
        }
    };

    if (!token()) return <Navigate to="/login" replace />;
    if (!t) return <Shell title="Destek"><p className="text-sm text-red-600">{msg || 'Talep bulunamadı.'}</p></Shell>;

    const messages = Array.isArray(t.messages) ? t.messages : [];

    return (
        <Shell title="Destek talebi">
            {msg ? <p className="mb-4 text-sm text-red-600">{msg}</p> : null}
            <div className="mb-6 rounded-2xl border border-slate-200/80 bg-white/90 p-6 shadow-lg">
                <div className="text-lg font-bold text-slate-900">{t.subject}</div>
                <div className="mt-1 text-sm text-slate-600">
                    {t.user?.email ? `Müşteri: ${t.user.email}` : null}
                    {t.status ? ` · Durum: ${t.status}` : null}
                </div>
                <div className="mt-4 flex flex-wrap items-center gap-2">
                    <button type="button" onClick={assign} className="rounded-full border border-slate-200 bg-white/80 px-4 py-2 text-sm">
                        Bana ata
                    </button>
                    <select className="rounded-full border border-slate-200 bg-white/80 px-4 py-2 text-sm" value={status} onChange={(e) => setStatus(e.target.value)}>
                        <option value="open">open</option>
                        <option value="pending">pending</option>
                        <option value="closed">closed</option>
                    </select>
                    <button type="button" onClick={updateStatus} className="rounded-full bg-slate-900 px-4 py-2 text-sm font-semibold text-white">
                        Durumu güncelle
                    </button>
                </div>
            </div>

            <div className="mb-6 space-y-3">
                {messages.map((m) => (
                    <div key={m.id} className="rounded-xl border border-slate-200 bg-white/90 p-4 text-sm shadow-sm">
                        <p className="text-xs text-slate-500">{m.user?.name}</p>
                        <p className="mt-1 whitespace-pre-wrap text-slate-800">{m.body}</p>
                    </div>
                ))}
            </div>

            {t.status !== 'closed' ? (
                <form onSubmit={reply} className="flex flex-wrap gap-2">
                    <input className="min-w-[260px] flex-1 rounded-full border px-4 py-2 text-sm" value={body} onChange={(e) => setBody(e.target.value)} placeholder="Yanıt yazın…" required />
                    <button type="submit" className="rounded-full bg-orange-500 px-6 py-2 font-semibold text-slate-900">Yanıtla</button>
                </form>
            ) : (
                <p className="text-sm text-slate-500">Talep kapalı.</p>
            )}
        </Shell>
    );
}

function AdminHome() {
    const [data, setData] = React.useState(null);
    React.useEffect(() => {
        if (!token()) return;
        api.get('/admin/dashboard').then((r) => setData(unwrapPayload(r))).catch(() => setData(null));
    }, []);
    if (!token()) {
        return <Navigate to="/login" replace />;
    }
    return (
        <Shell title="Yönetim">
            <div className="rounded-2xl border border-slate-200/80 bg-slate-900 p-6 text-emerald-200 shadow-xl">
                <pre className="overflow-auto text-xs">{JSON.stringify(data, null, 2)}</pre>
            </div>
        </Shell>
    );
}

function AdminPayoutRequestsPage() {
    const [rows, setRows] = React.useState([]);
    const [loading, setLoading] = React.useState(true);
    const [err, setErr] = React.useState('');
    const [filter, setFilter] = React.useState('pending');
    const [note, setNote] = React.useState({});

    const load = () => {
        if (!token()) return;
        setErr('');
        setLoading(true);
        const qs = filter ? `?status=${encodeURIComponent(filter)}` : '';
        api.get(`/admin/payout-requests${qs}`)
            .then((r) => {
                const payload = unwrapPayload(r);
                const page = payload?.data ? payload : payload; // keep
                const list = Array.isArray(page?.data) ? page.data : (Array.isArray(page) ? page : []);
                setRows(list);
            })
            .catch((e) => setErr(e.response?.data?.message || e.message || 'Yüklenemedi'))
            .finally(() => setLoading(false));
    };

    React.useEffect(() => {
        load();
    }, [filter]);

    const approve = async (id) => {
        setErr('');
        try {
            await api.post(`/admin/payout-requests/${id}/approve`, { admin_note: note[id] || null });
            load();
        } catch (e) {
            setErr(e.response?.data?.message || e.message || 'Onaylanamadı');
        }
    };

    const reject = async (id) => {
        setErr('');
        try {
            await api.post(`/admin/payout-requests/${id}/reject`, { admin_note: note[id] || null });
            load();
        } catch (e) {
            setErr(e.response?.data?.message || e.message || 'Reddedilemedi');
        }
    };

    if (!token()) return <Navigate to="/login" replace />;

    return (
        <Shell title="Ödeme talepleri (admin)">
            <div className="mb-4 flex flex-wrap items-center gap-2">
                <label className="text-xs font-medium text-slate-600">Filtre</label>
                <select className="rounded-full border border-slate-200 bg-white/80 px-4 py-2 text-sm" value={filter} onChange={(e) => setFilter(e.target.value)}>
                    <option value="">Tümü</option>
                    <option value="pending">Bekleyen</option>
                    <option value="approved">Onaylanan</option>
                    <option value="rejected">Reddedilen</option>
                </select>
                <button type="button" onClick={load} className="rounded-full border border-slate-200 bg-white/80 px-4 py-2 text-sm">Yenile</button>
            </div>
            {err ? <p className="mb-4 text-sm text-red-600">{err}</p> : null}
            {loading ? <p className="text-slate-500">Yükleniyor…</p> : (
                <ul className="space-y-3">
                    {rows.map((p) => (
                        <li key={p.id} className="rounded-2xl border border-slate-200/80 bg-white/90 p-5 shadow-sm">
                            <div className="flex flex-wrap items-start justify-between gap-3">
                                <div>
                                    <div className="font-semibold text-slate-900">Talep #{p.id}</div>
                                    <div className="text-xs text-slate-500">
                                        {p.vendor?.name ? `Satıcı: ${p.vendor.name}` : (p.vendor_id ? `Satıcı #${p.vendor_id}` : '')}
                                        {p.status ? ` · ${p.status}` : ''}
                                    </div>
                                    <div className="mt-2 text-sm font-bold text-slate-800">₺{Number(p.amount).toFixed(2)}</div>
                                </div>
                                {p.status === 'pending' ? (
                                    <div className="flex flex-col gap-2">
                                        <input
                                            className="w-64 rounded-xl border px-3 py-2 text-sm"
                                            placeholder="Admin notu (opsiyonel)"
                                            value={note[p.id] || ''}
                                            onChange={(e) => setNote((prev) => ({ ...prev, [p.id]: e.target.value }))}
                                        />
                                        <div className="flex gap-2">
                                            <button type="button" onClick={() => approve(p.id)} className="rounded-full bg-emerald-500 px-4 py-2 text-xs font-semibold text-white">Onayla</button>
                                            <button type="button" onClick={() => reject(p.id)} className="rounded-full bg-rose-600 px-4 py-2 text-xs font-semibold text-white">Reddet</button>
                                        </div>
                                    </div>
                                ) : (
                                    <div className="text-xs text-slate-600">
                                        {p.admin_note ? <div className="max-w-sm whitespace-pre-wrap rounded-xl bg-slate-50 p-3">{p.admin_note}</div> : null}
                                    </div>
                                )}
                            </div>
                        </li>
                    ))}
                    {!rows.length ? <li className="text-sm text-slate-500">Kayıt yok.</li> : null}
                </ul>
            )}
        </Shell>
    );
}

function VendorDashboardPage() {
    const [data, setData] = React.useState(null);
    const [err, setErr] = React.useState('');
    React.useEffect(() => {
        if (!token()) return;
        api.get('/vendor/dashboard')
            .then((r) => setData(unwrapPayload(r)))
            .catch((e) => setErr(e.response?.data?.message || e.message || 'Yüklenemedi'));
    }, []);
    if (!token()) return <Navigate to="/login" replace />;
    if (err) return <Shell title="Satıcı paneli"><p className="text-red-600">{err}</p></Shell>;
    if (!data) return <Shell title="Satıcı paneli"><p className="text-slate-500">Yükleniyor…</p></Shell>;

    return (
        <Shell title="Satıcı paneli">
            <div className="grid gap-4 md:grid-cols-2">
                <div className="rounded-2xl border border-slate-200/80 bg-white/90 p-6 shadow-lg">
                    <p className="text-xs font-semibold uppercase tracking-wide text-slate-500">Mağaza</p>
                    <p className="mt-1 text-lg font-bold text-slate-900">{data.vendor?.name}</p>
                    <p className="mt-2 text-sm text-slate-700">Bakiye: <strong>₺{Number(data.vendor?.balance || 0).toFixed(2)}</strong></p>
                </div>
                <div className="rounded-2xl border border-slate-200/80 bg-white/90 p-6 shadow-lg">
                    <p className="text-xs font-semibold uppercase tracking-wide text-slate-500">Özet</p>
                    <ul className="mt-3 space-y-2 text-sm text-slate-700">
                        <li>Ürünler: <strong>{data.counts?.products ?? 0}</strong></li>
                        <li>Sipariş (toplam): <strong>{data.counts?.orders_total ?? 0}</strong></li>
                        <li>Aktif sipariş: <strong>{data.counts?.orders_active ?? 0}</strong></li>
                        <li>Açık teklif talebi: <strong>{data.counts?.open_quote_requests ?? 0}</strong></li>
                    </ul>
                </div>
            </div>
        </Shell>
    );
}

function VendorProductsPage() {
    const [rows, setRows] = React.useState([]);
    const [loading, setLoading] = React.useState(true);
    const [err, setErr] = React.useState('');
    const [cats, setCats] = React.useState([]);
    const [editing, setEditing] = React.useState(null);
    const [form, setForm] = React.useState({
        category_id: '',
        name: '',
        sku: '',
        price: '',
        stock: '0',
        is_active: true,
        is_featured: false,
        product_type: 'physical',
        digital_link: '',
        short_description: '',
        description: '',
    });

    const load = () => {
        setErr('');
        setLoading(true);
        api.get('/vendor/products')
            .then((r) => {
                const page = unwrapPage(r);
                const list = Array.isArray(page?.data) ? page.data : (Array.isArray(page) ? page : []);
                setRows(list);
            })
            .catch((e) => setErr(e.response?.data?.message || e.message || 'Yüklenemedi'))
            .finally(() => setLoading(false));
    };

    React.useEffect(() => {
        if (!token()) return;
        api.get('/categories').then((r) => setCats(Array.isArray(unwrapPayload(r)) ? unwrapPayload(r) : [])).catch(() => setCats([]));
        load();
    }, []);

    const resetForm = () => {
        setEditing(null);
        setForm({
            category_id: '',
            name: '',
            sku: '',
            price: '',
            stock: '0',
            is_active: true,
            is_featured: false,
            product_type: 'physical',
            digital_link: '',
            short_description: '',
            description: '',
        });
    };

    const edit = (p) => {
        setEditing(p);
        setForm({
            category_id: String(p.category_id || ''),
            name: p.name || '',
            sku: p.sku || '',
            price: String(p.price ?? ''),
            stock: String(p.stock ?? 0),
            is_active: !!p.is_active,
            is_featured: !!p.is_featured,
            product_type: p.product_type || 'physical',
            digital_link: p.digital_link || '',
            short_description: p.short_description || '',
            description: p.description || '',
        });
    };

    const save = async (e) => {
        e.preventDefault();
        setErr('');
        const payload = {
            category_id: parseInt(form.category_id, 10),
            name: form.name,
            sku: form.sku || null,
            price: parseFloat(String(form.price).replace(',', '.')),
            stock: parseInt(String(form.stock), 10) || 0,
            is_active: !!form.is_active,
            is_featured: !!form.is_featured,
            product_type: form.product_type || 'physical',
            digital_link: form.digital_link || null,
            short_description: form.short_description || null,
            description: form.description || null,
        };
        try {
            if (editing) {
                await api.put(`/vendor/products/${editing.id}`, payload);
            } else {
                await api.post('/vendor/products', payload);
            }
            resetForm();
            load();
        } catch (ex) {
            setErr(ex.response?.data?.message || ex.message || 'Kaydedilemedi');
        }
    };

    const remove = async (p) => {
        setErr('');
        try {
            await api.delete(`/vendor/products/${p.id}`);
            load();
        } catch (ex) {
            setErr(ex.response?.data?.message || ex.message || 'Silinemedi');
        }
    };

    if (!token()) return <Navigate to="/login" replace />;

    return (
        <Shell title="Ürünler (satıcı)">
            {err ? <p className="mb-4 text-sm text-red-600">{err}</p> : null}
            <div className="grid gap-6 md:grid-cols-2">
                <div className="rounded-2xl border border-slate-200/80 bg-white/90 p-6 shadow-lg">
                    <h2 className="mb-4 text-sm font-bold uppercase tracking-wider text-slate-500">{editing ? 'Ürün düzenle' : 'Yeni ürün'}</h2>
                    <form onSubmit={save} className="space-y-3">
                        <div>
                            <label className="block text-xs font-medium text-slate-600">Kategori</label>
                            <select className="mt-1 w-full rounded-xl border px-4 py-2" value={form.category_id} onChange={(e) => setForm((p) => ({ ...p, category_id: e.target.value }))} required>
                                <option value="">Seçin</option>
                                {cats.map((c) => <option key={c.id} value={c.id}>{c.name}</option>)}
                            </select>
                        </div>
                        <div>
                            <label className="block text-xs font-medium text-slate-600">Ad</label>
                            <input className="mt-1 w-full rounded-xl border px-4 py-2" value={form.name} onChange={(e) => setForm((p) => ({ ...p, name: e.target.value }))} required />
                        </div>
                        <div className="grid gap-3 md:grid-cols-2">
                            <div>
                                <label className="block text-xs font-medium text-slate-600">Fiyat</label>
                                <input className="mt-1 w-full rounded-xl border px-4 py-2" value={form.price} onChange={(e) => setForm((p) => ({ ...p, price: e.target.value }))} required />
                            </div>
                            <div>
                                <label className="block text-xs font-medium text-slate-600">Stok</label>
                                <input className="mt-1 w-full rounded-xl border px-4 py-2" value={form.stock} onChange={(e) => setForm((p) => ({ ...p, stock: e.target.value }))} />
                            </div>
                        </div>
                        <div className="grid gap-3 md:grid-cols-2">
                            <div>
                                <label className="block text-xs font-medium text-slate-600">SKU</label>
                                <input className="mt-1 w-full rounded-xl border px-4 py-2" value={form.sku} onChange={(e) => setForm((p) => ({ ...p, sku: e.target.value }))} />
                            </div>
                            <div>
                                <label className="block text-xs font-medium text-slate-600">Tür</label>
                                <select className="mt-1 w-full rounded-xl border px-4 py-2" value={form.product_type} onChange={(e) => setForm((p) => ({ ...p, product_type: e.target.value }))}>
                                    <option value="physical">Fiziksel</option>
                                    <option value="digital">Dijital</option>
                                </select>
                            </div>
                        </div>
                        {form.product_type === 'digital' ? (
                            <div>
                                <label className="block text-xs font-medium text-slate-600">Dijital link</label>
                                <input className="mt-1 w-full rounded-xl border px-4 py-2" value={form.digital_link} onChange={(e) => setForm((p) => ({ ...p, digital_link: e.target.value }))} />
                            </div>
                        ) : null}
                        <div className="grid gap-3 md:grid-cols-2">
                            <label className="flex items-center gap-2 text-sm text-slate-700">
                                <input type="checkbox" checked={form.is_active} onChange={(e) => setForm((p) => ({ ...p, is_active: e.target.checked }))} />
                                Aktif
                            </label>
                            <label className="flex items-center gap-2 text-sm text-slate-700">
                                <input type="checkbox" checked={form.is_featured} onChange={(e) => setForm((p) => ({ ...p, is_featured: e.target.checked }))} />
                                Öne çıkan
                            </label>
                        </div>
                        <div>
                            <label className="block text-xs font-medium text-slate-600">Kısa açıklama</label>
                            <input className="mt-1 w-full rounded-xl border px-4 py-2" value={form.short_description} onChange={(e) => setForm((p) => ({ ...p, short_description: e.target.value }))} />
                        </div>
                        <div>
                            <label className="block text-xs font-medium text-slate-600">Açıklama</label>
                            <textarea className="mt-1 w-full rounded-xl border px-4 py-2" rows={4} value={form.description} onChange={(e) => setForm((p) => ({ ...p, description: e.target.value }))} />
                        </div>
                        <div className="flex gap-2">
                            <button type="submit" className="flex-1 rounded-full bg-orange-500 py-2.5 text-sm font-semibold text-slate-900">
                                Kaydet
                            </button>
                            {editing ? (
                                <button type="button" onClick={resetForm} className="rounded-full border border-slate-200 bg-white/80 px-4 py-2.5 text-sm">
                                    İptal
                                </button>
                            ) : null}
                        </div>
                    </form>
                </div>
                <div className="rounded-2xl border border-slate-200/80 bg-white/90 p-6 shadow-lg">
                    <div className="mb-4 flex items-center justify-between">
                        <h2 className="text-sm font-bold uppercase tracking-wider text-slate-500">Mevcut ürünler</h2>
                        <button type="button" onClick={load} className="rounded-full border border-slate-200 bg-white/80 px-4 py-2 text-sm">Yenile</button>
                    </div>
                    {loading ? <p className="text-slate-500">Yükleniyor…</p> : (
                        <ul className="space-y-2">
                            {rows.map((p) => (
                                <li key={p.id} className="flex flex-wrap items-center justify-between gap-2 rounded-xl border border-slate-200 bg-white/80 px-4 py-3 text-sm">
                                    <div className="min-w-0">
                                        <div className="truncate font-semibold text-slate-900">{p.name}</div>
                                        <div className="text-xs text-slate-500">{p.category?.name} · ₺{Number(p.price).toFixed(2)} · stok {p.stock}</div>
                                    </div>
                                    <div className="flex gap-2">
                                        <button type="button" onClick={() => edit(p)} className="rounded-full border border-slate-200 px-3 py-1 text-xs">Düzenle</button>
                                        <button type="button" onClick={() => remove(p)} className="rounded-full bg-rose-600 px-3 py-1 text-xs font-semibold text-white">Sil</button>
                                    </div>
                                </li>
                            ))}
                            {!rows.length ? <li className="text-sm text-slate-500">Ürün yok.</li> : null}
                        </ul>
                    )}
                </div>
            </div>
        </Shell>
    );
}

function CustomerFavoritesPage() {
    const [rows, setRows] = React.useState([]);
    const [loading, setLoading] = React.useState(true);
    const [err, setErr] = React.useState('');

    const load = () => {
        setErr('');
        setLoading(true);
        api.get('/favorites')
            .then((r) => {
                const page = r.data;
                const list = Array.isArray(page?.data) ? page.data : [];
                setRows(list);
            })
            .catch((e) => setErr(e.response?.data?.message || e.message || 'Yüklenemedi'))
            .finally(() => setLoading(false));
    };

    React.useEffect(() => {
        if (!token()) return;
        load();
    }, []);

    const toggle = async (id) => {
        try {
            await api.post(`/favorites/toggle/${id}`);
            load();
        } catch (e) {
            setErr(e.response?.data?.message || e.message || 'Güncellenemedi');
        }
    };

    if (!token()) return <Navigate to="/login" replace />;

    return (
        <Shell title="Favorilerim">
            {err ? <p className="mb-4 text-sm text-red-600">{err}</p> : null}
            {loading ? <p className="text-slate-500">Yükleniyor…</p> : (
                <ul className="space-y-3">
                    {rows.map((p) => (
                        <li key={p.id} className="rounded-2xl border border-slate-200/80 bg-white/90 px-5 py-4 shadow-sm">
                            <div className="flex flex-wrap items-center justify-between gap-3">
                                <div className="min-w-0">
                                    <div className="truncate font-semibold text-slate-900">{p.name}</div>
                                    <div className="mt-1 text-xs text-slate-500">{p.category?.name} · {p.vendor?.name}</div>
                                </div>
                                <div className="flex items-center gap-2">
                                    <span className="text-sm font-bold text-slate-800">₺{Number(p.price).toFixed(2)}</span>
                                    <button type="button" onClick={() => toggle(p.id)} className="rounded-full bg-rose-600 px-4 py-2 text-xs font-semibold text-white">
                                        Çıkar
                                    </button>
                                </div>
                            </div>
                        </li>
                    ))}
                    {!rows.length ? <li className="text-sm text-slate-500">Favori ürün yok.</li> : null}
                </ul>
            )}
        </Shell>
    );
}

function CustomerAddressesPage() {
    const [rows, setRows] = React.useState([]);
    const [err, setErr] = React.useState('');
    const [loading, setLoading] = React.useState(true);
    const [districts, setDistricts] = React.useState([]);
    const [neighborhoods, setNeighborhoods] = React.useState([]);
    const [editing, setEditing] = React.useState(null);
    const [form, setForm] = React.useState({
        label: 'Adres',
        full_name: '',
        phone: '',
        turkiye_district_id: '',
        turkiye_neighborhood_id: '',
        cadde: '',
        sokak: '',
        bina_no: '',
        ic_kapi_no: '',
        line1: '',
        line2: '',
        is_default: false,
        is_billing_default: false,
    });

    const load = () => {
        setErr('');
        setLoading(true);
        api.get('/addresses')
            .then((r) => setRows(Array.isArray(unwrapPayload(r)) ? unwrapPayload(r) : []))
            .catch((e) => setErr(e.response?.data?.message || e.message || 'Yüklenemedi'))
            .finally(() => setLoading(false));
    };

    React.useEffect(() => {
        if (!token()) return;
        // District list comes from geography endpoint.
        api.get('/geography/provinces')
            .then((r) => {
                const provinces = unwrapPayload(r);
                const first = Array.isArray(provinces) ? provinces[0] : null;
                if (first?.id) {
                    return api.get(`/geography/provinces/${first.id}/districts`);
                }
                return null;
            })
            .then((r) => {
                if (r) setDistricts(Array.isArray(unwrapPayload(r)) ? unwrapPayload(r) : []);
            })
            .catch(() => {});
        load();
    }, []);

    React.useEffect(() => {
        const did = form.turkiye_district_id;
        if (!did) return;
        api.get(`/geography/districts/${did}/neighborhoods`)
            .then((r) => setNeighborhoods(Array.isArray(unwrapPayload(r)) ? unwrapPayload(r) : []))
            .catch(() => setNeighborhoods([]));
    }, [form.turkiye_district_id]);

    const reset = () => {
        setEditing(null);
        setForm({
            label: 'Adres',
            full_name: '',
            phone: '',
            turkiye_district_id: '',
            turkiye_neighborhood_id: '',
            cadde: '',
            sokak: '',
            bina_no: '',
            ic_kapi_no: '',
            line1: '',
            line2: '',
            is_default: false,
            is_billing_default: false,
        });
    };

    const edit = (a) => {
        setEditing(a);
        setForm({
            label: a.label || 'Adres',
            full_name: a.full_name || '',
            phone: a.phone || '',
            turkiye_district_id: a.turkiye_district_id ? String(a.turkiye_district_id) : '',
            turkiye_neighborhood_id: a.turkiye_neighborhood_id ? String(a.turkiye_neighborhood_id) : '',
            cadde: a.cadde || '',
            sokak: a.sokak || '',
            bina_no: a.bina_no || '',
            ic_kapi_no: a.ic_kapi_no || '',
            line1: a.line1 || '',
            line2: a.line2 || '',
            is_default: !!a.is_default,
            is_billing_default: !!a.is_billing_default,
        });
    };

    const save = async (e) => {
        e.preventDefault();
        setErr('');
        const payload = {
            ...form,
            turkiye_district_id: parseInt(form.turkiye_district_id, 10),
            turkiye_neighborhood_id: parseInt(form.turkiye_neighborhood_id, 10),
        };
        try {
            if (editing) {
                await api.put(`/addresses/${editing.id}`, payload);
            } else {
                await api.post('/addresses', payload);
            }
            reset();
            load();
        } catch (ex) {
            setErr(ex.response?.data?.message || ex.message || 'Kaydedilemedi');
        }
    };

    const remove = async (a) => {
        setErr('');
        try {
            await api.delete(`/addresses/${a.id}`);
            load();
        } catch (ex) {
            setErr(ex.response?.data?.message || ex.message || 'Silinemedi');
        }
    };

    if (!token()) return <Navigate to="/login" replace />;

    return (
        <Shell title="Adresler">
            {err ? <p className="mb-4 text-sm text-red-600">{err}</p> : null}
            <div className="grid gap-6 md:grid-cols-2">
                <div className="rounded-2xl border border-slate-200/80 bg-white/90 p-6 shadow-lg">
                    <h2 className="mb-4 text-sm font-bold uppercase tracking-wider text-slate-500">{editing ? 'Adresi düzenle' : 'Yeni adres'}</h2>
                    <form onSubmit={save} className="space-y-3">
                        <input className="w-full rounded-xl border px-4 py-2" value={form.label} onChange={(e) => setForm((p) => ({ ...p, label: e.target.value }))} placeholder="Etiket (Ev/İş…)" />
                        <input className="w-full rounded-xl border px-4 py-2" value={form.full_name} onChange={(e) => setForm((p) => ({ ...p, full_name: e.target.value }))} placeholder="Ad Soyad" required />
                        <input className="w-full rounded-xl border px-4 py-2" value={form.phone} onChange={(e) => setForm((p) => ({ ...p, phone: e.target.value }))} placeholder="Telefon" required />
                        <select className="w-full rounded-xl border px-4 py-2" value={form.turkiye_district_id} onChange={(e) => setForm((p) => ({ ...p, turkiye_district_id: e.target.value, turkiye_neighborhood_id: '' }))} required>
                            <option value="">İlçe seç</option>
                            {districts.map((d) => <option key={d.id} value={d.id}>{d.name}</option>)}
                        </select>
                        <select className="w-full rounded-xl border px-4 py-2" value={form.turkiye_neighborhood_id} onChange={(e) => setForm((p) => ({ ...p, turkiye_neighborhood_id: e.target.value }))} required>
                            <option value="">Mahalle seç</option>
                            {neighborhoods.map((n) => <option key={n.id} value={n.id}>{n.name}</option>)}
                        </select>
                        <div className="grid gap-3 md:grid-cols-2">
                            <input className="w-full rounded-xl border px-4 py-2" value={form.cadde} onChange={(e) => setForm((p) => ({ ...p, cadde: e.target.value }))} placeholder="Cadde" />
                            <input className="w-full rounded-xl border px-4 py-2" value={form.sokak} onChange={(e) => setForm((p) => ({ ...p, sokak: e.target.value }))} placeholder="Sokak" />
                        </div>
                        <div className="grid gap-3 md:grid-cols-2">
                            <input className="w-full rounded-xl border px-4 py-2" value={form.bina_no} onChange={(e) => setForm((p) => ({ ...p, bina_no: e.target.value }))} placeholder="Bina no" />
                            <input className="w-full rounded-xl border px-4 py-2" value={form.ic_kapi_no} onChange={(e) => setForm((p) => ({ ...p, ic_kapi_no: e.target.value }))} placeholder="İç kapı no" />
                        </div>
                        <input className="w-full rounded-xl border px-4 py-2" value={form.line1} onChange={(e) => setForm((p) => ({ ...p, line1: e.target.value }))} placeholder="Adres notu" />
                        <input className="w-full rounded-xl border px-4 py-2" value={form.line2} onChange={(e) => setForm((p) => ({ ...p, line2: e.target.value }))} placeholder="Ek bilgi" />
                        <div className="grid gap-3 md:grid-cols-2">
                            <label className="flex items-center gap-2 text-sm text-slate-700">
                                <input type="checkbox" checked={form.is_default} onChange={(e) => setForm((p) => ({ ...p, is_default: e.target.checked }))} />
                                Varsayılan
                            </label>
                            <label className="flex items-center gap-2 text-sm text-slate-700">
                                <input type="checkbox" checked={form.is_billing_default} onChange={(e) => setForm((p) => ({ ...p, is_billing_default: e.target.checked }))} />
                                Fatura varsayılan
                            </label>
                        </div>
                        <div className="flex gap-2">
                            <button type="submit" className="flex-1 rounded-full bg-orange-500 py-2.5 text-sm font-semibold text-slate-900">Kaydet</button>
                            {editing ? <button type="button" onClick={reset} className="rounded-full border border-slate-200 bg-white/80 px-4 py-2.5 text-sm">İptal</button> : null}
                        </div>
                    </form>
                </div>
                <div className="rounded-2xl border border-slate-200/80 bg-white/90 p-6 shadow-lg">
                    <div className="mb-4 flex items-center justify-between">
                        <h2 className="text-sm font-bold uppercase tracking-wider text-slate-500">Kayıtlı adresler</h2>
                        <button type="button" onClick={load} className="rounded-full border border-slate-200 bg-white/80 px-4 py-2 text-sm">Yenile</button>
                    </div>
                    {loading ? <p className="text-slate-500">Yükleniyor…</p> : (
                        <ul className="space-y-2">
                            {rows.map((a) => (
                                <li key={a.id} className="rounded-xl border border-slate-200 bg-white/80 p-4 text-sm">
                                    <div className="flex flex-wrap items-start justify-between gap-2">
                                        <div className="min-w-0">
                                            <div className="font-semibold text-slate-900">{a.label || 'Adres'}</div>
                                            <div className="mt-1 text-xs text-slate-500">{a.city} / {a.district} · {a.neighborhood}</div>
                                        </div>
                                        <div className="flex gap-2">
                                            <button type="button" onClick={() => edit(a)} className="rounded-full border border-slate-200 px-3 py-1 text-xs">Düzenle</button>
                                            <button type="button" onClick={() => remove(a)} className="rounded-full bg-rose-600 px-3 py-1 text-xs font-semibold text-white">Sil</button>
                                        </div>
                                    </div>
                                </li>
                            ))}
                            {!rows.length ? <li className="text-sm text-slate-500">Adres yok.</li> : null}
                        </ul>
                    )}
                </div>
            </div>
        </Shell>
    );
}

function CartPage() {
    const [data, setData] = React.useState(null);
    const [q, setQ] = React.useState('');
    const [hits, setHits] = React.useState([]);
    const [err, setErr] = React.useState('');

    const load = () => {
        setErr('');
        api.get('/cart').then((r) => setData(r.data)).catch((e) => setErr(e.response?.data?.message || e.message || 'Yüklenemedi'));
    };

    React.useEffect(() => {
        if (!token()) return;
        load();
    }, []);

    const search = async () => {
        setErr('');
        try {
            const r = await api.get(`/products?q=${encodeURIComponent(q)}`);
            const page = unwrapPayload(r);
            const list = Array.isArray(page?.data) ? page.data : [];
            setHits(list);
        } catch (e) {
            setErr(e.response?.data?.message || e.message || 'Arama başarısız');
        }
    };

    const add = async (pid) => {
        setErr('');
        try {
            await api.post('/cart/items', { product_id: pid, quantity: 1 });
            load();
        } catch (e) {
            setErr(e.response?.data?.message || e.message || 'Eklenemedi');
        }
    };

    const updateQty = async (item, qty) => {
        setErr('');
        try {
            await api.put(`/cart/items/${item.id}`, { quantity: qty });
            load();
        } catch (e) {
            setErr(e.response?.data?.message || e.message || 'Güncellenemedi');
        }
    };

    const remove = async (item) => {
        setErr('');
        try {
            await api.delete(`/cart/items/${item.id}`);
            load();
        } catch (e) {
            setErr(e.response?.data?.message || e.message || 'Silinemedi');
        }
    };

    if (!token()) return <Navigate to="/login" replace />;

    const items = Array.isArray(data?.items) ? data.items : [];

    return (
        <Shell title="Sepet">
            {err ? <p className="mb-4 text-sm text-red-600">{err}</p> : null}
            <div className="mb-6 rounded-2xl border border-slate-200/80 bg-white/90 p-6 shadow-lg">
                <h2 className="mb-3 text-sm font-bold uppercase tracking-wider text-slate-500">Ürün ekle</h2>
                <div className="flex flex-wrap gap-2">
                    <input className="min-w-[240px] flex-1 rounded-full border px-4 py-2 text-sm" value={q} onChange={(e) => setQ(e.target.value)} placeholder="Ürün ara…" />
                    <button type="button" onClick={search} className="rounded-full bg-orange-500 px-5 py-2 text-sm font-semibold text-slate-900">Ara</button>
                </div>
                {hits.length ? (
                    <ul className="mt-3 space-y-2">
                        {hits.slice(0, 8).map((p) => (
                            <li key={p.id} className="flex items-center justify-between rounded-xl border border-slate-200 bg-white/80 px-4 py-2 text-sm">
                                <span className="truncate">{p.name}</span>
                                <button type="button" onClick={() => add(p.id)} className="rounded-full border border-slate-200 px-3 py-1 text-xs">Ekle</button>
                            </li>
                        ))}
                    </ul>
                ) : null}
            </div>

            <div className="rounded-2xl border border-slate-200/80 bg-white/90 p-6 shadow-lg">
                <h2 className="mb-4 text-sm font-bold uppercase tracking-wider text-slate-500">Sepet kalemleri</h2>
                {!items.length ? <p className="text-sm text-slate-500">Sepet boş.</p> : (
                    <ul className="space-y-2">
                        {items.map((it) => (
                            <li key={it.id} className="flex flex-wrap items-center justify-between gap-2 rounded-xl border border-slate-200 bg-white/80 px-4 py-3 text-sm">
                                <div className="min-w-0">
                                    <div className="truncate font-semibold text-slate-900">{it.product?.name}</div>
                                    <div className="text-xs text-slate-500">₺{Number(it.product?.price || 0).toFixed(2)}</div>
                                </div>
                                <div className="flex items-center gap-2">
                                    <input type="number" min={1} max={999} className="w-20 rounded-xl border px-3 py-2 text-sm" value={it.quantity} onChange={(e) => updateQty(it, parseInt(e.target.value, 10) || 1)} />
                                    <button type="button" onClick={() => remove(it)} className="rounded-full bg-rose-600 px-3 py-1 text-xs font-semibold text-white">Sil</button>
                                </div>
                            </li>
                        ))}
                    </ul>
                )}
                <div className="mt-4 flex items-center justify-between">
                    <span className="text-sm text-slate-600">Toplam</span>
                    <strong className="text-lg text-slate-900">₺{Number(data?.total || 0).toFixed(2)}</strong>
                </div>
                <Link to="/checkout" className="mt-4 block w-full rounded-full bg-slate-900 py-3 text-center text-sm font-semibold text-white">
                    Ödemeye geç
                </Link>
            </div>
        </Shell>
    );
}

function CheckoutPage() {
    const [addresses, setAddresses] = React.useState([]);
    const [addressId, setAddressId] = React.useState('');
    const [msg, setMsg] = React.useState('');

    React.useEffect(() => {
        if (!token()) return;
        api.get('/addresses').then((r) => setAddresses(Array.isArray(unwrapPayload(r)) ? unwrapPayload(r) : [])).catch(() => setAddresses([]));
    }, []);

    const submit = async (e) => {
        e.preventDefault();
        setMsg('');
        try {
            const r = await api.post('/checkout', { address_id: parseInt(addressId, 10) });
            setMsg(r.data?.message || 'Sipariş oluşturuldu.');
        } catch (ex) {
            setMsg(ex.response?.data?.message || ex.message || 'Başarısız');
        }
    };

    if (!token()) return <Navigate to="/login" replace />;

    return (
        <Shell title="Checkout">
            <form onSubmit={submit} className="mx-auto max-w-xl space-y-4 rounded-2xl border border-slate-200/80 bg-white/90 p-8 shadow-lg">
                {msg ? <p className="text-sm text-slate-700">{msg}</p> : null}
                <select className="w-full rounded-xl border px-4 py-3" value={addressId} onChange={(e) => setAddressId(e.target.value)} required>
                    <option value="">Adres seç</option>
                    {addresses.map((a) => (
                        <option key={a.id} value={a.id}>{a.label} — {a.city}/{a.district}</option>
                    ))}
                </select>
                <button type="submit" className="w-full rounded-full bg-orange-500 py-3 font-semibold text-slate-900">
                    Siparişi oluştur
                </button>
            </form>
        </Shell>
    );
}

function App() {
    return (
        <BrowserRouter basename="/app">
            <Routes>
                <Route path="/login" element={<LoginPage />} />
                <Route path="/" element={<DashboardHome />} />
                <Route path="/customer/dashboard" element={<CustomerDashboardPage />} />
                <Route path="/customer/favorites" element={<CustomerFavoritesPage />} />
                <Route path="/customer/addresses" element={<CustomerAddressesPage />} />
                <Route path="/customer/orders" element={<OrdersList vendor={false} />} />
                <Route path="/customer/price-estimate" element={<PriceEstimatePage />} />
                <Route path="/customer/quote-requests" element={<CustomerQuoteRequestsPage />} />
                <Route path="/customer/quote-requests/new" element={<CustomerQuoteRequestNewPage />} />
                <Route path="/customer/quote-requests/:id" element={<CustomerQuoteRequestDetailPage />} />
                <Route path="/vendor/orders" element={<OrdersList vendor />} />
                <Route path="/vendor/orders/:id" element={<VendorOrderDetailPage />} />
                <Route path="/vendor/dashboard" element={<VendorDashboardPage />} />
                <Route path="/vendor/products" element={<VendorProductsPage />} />
                <Route path="/vendor/quote-requests" element={<VendorQuoteRequestsPage />} />
                <Route path="/vendor/quote-requests/:id" element={<VendorQuoteRequestDetailPage />} />
                <Route path="/orders/:id" element={<OrderDetailPage />} />
                <Route path="/support" element={<SupportListPage />} />
                <Route path="/support/new" element={<SupportNewPage />} />
                <Route path="/support/:id" element={<SupportDetailPage />} />
                <Route path="/vendor/payouts" element={<VendorPayoutsPage />} />
                <Route path="/admin/support" element={<AdminSupportListPage />} />
                <Route path="/admin/support/:id" element={<AdminSupportDetailPage />} />
                <Route path="/admin/payout-requests" element={<AdminPayoutRequestsPage />} />
                <Route path="/notifications" element={<NotificationsPage />} />
                <Route path="/messages" element={<MessagesPage />} />
                <Route path="/messages/:id" element={<ConversationDetailPage />} />
                <Route path="/cart" element={<CartPage />} />
                <Route path="/checkout" element={<CheckoutPage />} />
                <Route path="/admin" element={<AdminHome />} />
                <Route path="*" element={<Navigate to="/" replace />} />
            </Routes>
        </BrowserRouter>
    );
}

const el = document.getElementById('spa-root');
if (el) {
    createRoot(el).render(<App />);
}
