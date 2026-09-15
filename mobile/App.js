import { Ionicons } from '@expo/vector-icons';
import { createBottomTabNavigator } from '@react-navigation/bottom-tabs';
import { NavigationContainer, useNavigation, useRoute } from '@react-navigation/native';
import { createNativeStackNavigator } from '@react-navigation/native-stack';
import { StatusBar } from 'expo-status-bar';
import { useCallback, useEffect, useState } from 'react';
import {
    ActivityIndicator,
    FlatList,
    Pressable,
    RefreshControl,
    ScrollView,
    StyleSheet,
    Text,
    TextInput,
    View,
} from 'react-native';
import { SafeAreaProvider } from 'react-native-safe-area-context';
import { api, listFromApi, unwrapData } from './src/api';
import { AuthProvider, useAuth } from './src/context/AuthContext';
import WebMirrorScreen from './src/WebMirrorScreen';
import {
    AddressesScreen,
    AdminPayoutRequestsScreen,
    AdminSupportTicketDetailScreen,
    AdminSupportTicketsScreen,
    ConversationsScreen,
    CreateFreelancerJobScreen,
    CreateQuoteRequestScreen,
    CreateSupportTicketScreen,
    CustomerDashboardScreen,
    CustomerPriceEstimateScreen,
    FavoritesScreen,
    FreelancerJobDetailScreen,
    FreelancerJobsScreen,
    MyFreelancerJobsScreen,
    NotificationsScreen,
    OrderDetailScreen,
    ProductDetailScreen,
    QuoteRequestDetailScreen,
    QuoteRequestsScreen,
    StaticPageScreen,
    SupportTicketDetailScreen,
    SupportTicketsScreen,
    VendorDetailScreen,
    VendorOrderDetailScreen,
    VendorPayoutRequestsScreen,
    VendorsListScreen,
} from './src/parityScreens';

const RootStack = createNativeStackNavigator();
const VendorStack = createNativeStackNavigator();
const AdminStack = createNativeStackNavigator();
const Tab = createBottomTabNavigator();

function LoginScreen({ navigation }) {
    const { login } = useAuth();
    const [email, setEmail] = useState('');
    const [password, setPassword] = useState('');
    const [err, setErr] = useState('');
    const [loading, setLoading] = useState(false);

    const submit = async () => {
        setErr('');
        setLoading(true);
        try {
            const u = await login(email.trim(), password);
            if (u?.role === 'customer' && navigation.canGoBack()) navigation.goBack();
        } catch (e) {
            setErr(e.message || 'Giriş başarısız');
        } finally {
            setLoading(false);
        }
    };

    return (
        <View style={styles.pad}>
            <Pressable style={styles.closeBtn} onPress={() => navigation.goBack()} hitSlop={12}>
                <Text style={styles.closeBtnText}>✕ Kapat</Text>
            </Pressable>
            <Text style={styles.heroTitle}>BaskıYeri</Text>
            <Text style={styles.heroSub}>Matbaa ve hizmet pazaryeri</Text>
            <TextInput
                style={styles.input}
                placeholder="E-posta"
                autoCapitalize="none"
                keyboardType="email-address"
                value={email}
                onChangeText={setEmail}
            />
            <TextInput
                style={styles.input}
                placeholder="Şifre"
                secureTextEntry
                value={password}
                onChangeText={setPassword}
            />
            {err ? <Text style={styles.err}>{err}</Text> : null}
            <Pressable style={styles.btn} onPress={submit} disabled={loading}>
                {loading ? <ActivityIndicator color="#111" /> : <Text style={styles.btnText}>Giriş yap</Text>}
            </Pressable>
            <Pressable onPress={() => navigation.navigate('Register')}>
                <Text style={styles.link}>Hesap oluştur</Text>
            </Pressable>
        </View>
    );
}

function RegisterScreen({ navigation }) {
    const { register } = useAuth();
    const [name, setName] = useState('');
    const [email, setEmail] = useState('');
    const [password, setPassword] = useState('');
    const [passwordConfirmation, setPasswordConfirmation] = useState('');
    const [role, setRole] = useState('customer');
    const [businessTypes, setBusinessTypes] = useState([]);
    const [selectedTypeIds, setSelectedTypeIds] = useState([]);
    const [err, setErr] = useState('');
    const [loading, setLoading] = useState(false);

    useEffect(() => {
        let cancelled = false;
        (async () => {
            try {
                const rows = unwrapData(await api('/business-types'));
                if (!cancelled && Array.isArray(rows)) setBusinessTypes(rows);
            } catch {
                /* public endpoint; ignore */
            }
        })();
        return () => {
            cancelled = true;
        };
    }, []);

    const toggleType = (id) => {
        setSelectedTypeIds((prev) =>
            prev.includes(id) ? prev.filter((x) => x !== id) : [...prev, id],
        );
    };

    const submit = async () => {
        setErr('');
        if (role === 'vendor' && businessTypes.length > 0 && selectedTypeIds.length === 0) {
            setErr('En az bir iş kolu seçin.');
            return;
        }
        setLoading(true);
        try {
            const payload = {
                name,
                email: email.trim(),
                password,
                password_confirmation: passwordConfirmation,
                role,
            };
            if (role === 'vendor' && selectedTypeIds.length > 0) {
                payload.business_type_ids = selectedTypeIds;
            }
            const u = await register(payload);
            if (u?.role === 'customer' && navigation.canGoBack()) navigation.goBack();
        } catch (e) {
            setErr(e.message || 'Kayıt başarısız');
        } finally {
            setLoading(false);
        }
    };

    return (
        <ScrollView style={styles.padScroll} contentContainerStyle={styles.padContent}>
            <Pressable style={styles.closeBtn} onPress={() => navigation.goBack()} hitSlop={12}>
                <Text style={styles.closeBtnText}>✕ Kapat</Text>
            </Pressable>
            <Text style={styles.title}>Kayıt ol</Text>
            <TextInput style={styles.input} placeholder="Ad Soyad" value={name} onChangeText={setName} />
            <TextInput
                style={styles.input}
                placeholder="E-posta"
                autoCapitalize="none"
                keyboardType="email-address"
                value={email}
                onChangeText={setEmail}
            />
            <TextInput
                style={styles.input}
                placeholder="Şifre"
                secureTextEntry
                value={password}
                onChangeText={setPassword}
            />
            <TextInput
                style={styles.input}
                placeholder="Şifre tekrar"
                secureTextEntry
                value={passwordConfirmation}
                onChangeText={setPasswordConfirmation}
            />
            <Text style={styles.label}>Hesap türü</Text>
            <View style={styles.row}>
                <Pressable onPress={() => setRole('customer')} style={[styles.chip, role === 'customer' && styles.chipOn]}>
                    <Text>Müşteri</Text>
                </Pressable>
                <Pressable onPress={() => setRole('vendor')} style={[styles.chip, role === 'vendor' && styles.chipOn]}>
                    <Text>Satıcı</Text>
                </Pressable>
            </View>
            {role === 'vendor' && businessTypes.length > 0 ? (
                <>
                    <Text style={styles.label}>İş kolu (en az bir)</Text>
                    <View style={styles.wrap}>
                        {businessTypes.map((bt) => (
                            <Pressable
                                key={bt.id}
                                onPress={() => toggleType(bt.id)}
                                style={[styles.chip, selectedTypeIds.includes(bt.id) && styles.chipOn]}
                            >
                                <Text style={styles.chipTextSmall}>{bt.name}</Text>
                            </Pressable>
                        ))}
                    </View>
                </>
            ) : null}
            {err ? <Text style={styles.err}>{err}</Text> : null}
            <Pressable style={styles.btn} onPress={submit} disabled={loading}>
                {loading ? <ActivityIndicator color="#111" /> : <Text style={styles.btnText}>Kayıt ol</Text>}
            </Pressable>
        </ScrollView>
    );
}

function walkCategories(nodes, depth = 0) {
    let rows = [];
    if (!Array.isArray(nodes)) return rows;
    for (const c of nodes) {
        rows.push({ id: c.id, slug: c.slug, name: c.name, depth });
        if (c.children?.length) {
            rows = rows.concat(walkCategories(c.children, depth + 1));
        }
    }
    return rows;
}

function CategoriesScreen() {
    const navigation = useNavigation();
    const { user, token } = useAuth();
    const [categories, setCategories] = useState([]);
    const [selectedSlug, setSelectedSlug] = useState(null);
    const [products, setProducts] = useState([]);
    const [refreshing, setRefreshing] = useState(false);
    const [loading, setLoading] = useState(true);
    const [adding, setAdding] = useState(null);
    const [msg, setMsg] = useState('');

    useEffect(() => {
        (async () => {
            try {
                const rows = unwrapData(await api('/categories'));
                setCategories(Array.isArray(rows) ? rows : []);
            } catch {
                setCategories([]);
            }
        })();
    }, []);

    const flatCats = walkCategories(categories);

    const loadProducts = useCallback(async () => {
        try {
            const qs = new URLSearchParams();
            if (selectedSlug) qs.set('category', selectedSlug);
            const query = qs.toString();
            const res = await api('/products' + (query ? `?${query}` : ''));
            setProducts(listFromApi(res));
        } catch {
            setProducts([]);
        } finally {
            setLoading(false);
            setRefreshing(false);
        }
    }, [selectedSlug]);

    useEffect(() => {
        setLoading(true);
        loadProducts();
    }, [loadProducts]);

    const addToCart = async (productId) => {
        if (!token || !user) {
            navigation.navigate('Login');
            return;
        }
        setMsg('');
        setAdding(productId);
        try {
            await api('/cart/items', {
                method: 'POST',
                body: JSON.stringify({ product_id: productId, quantity: 1 }),
            });
            setMsg('Sepete eklendi');
            setTimeout(() => setMsg(''), 2000);
        } catch (e) {
            setMsg(e.message || 'Eklenemedi');
        } finally {
            setAdding(null);
        }
    };

    return (
        <View style={styles.flex}>
            <View style={styles.topBar}>
                <Text style={styles.brand}>Kategoriler</Text>
            </View>
            {msg ? <Text style={styles.toast}>{msg}</Text> : null}
            <ScrollView horizontal showsHorizontalScrollIndicator={false} style={styles.catStrip} contentContainerStyle={styles.catStripInner}>
                <Pressable
                    style={[styles.catChip, !selectedSlug && styles.catChipOn]}
                    onPress={() => setSelectedSlug(null)}
                >
                    <Text style={[styles.catChipText, !selectedSlug && styles.catChipTextOn]}>Tümü</Text>
                </Pressable>
                {flatCats.map((c) => (
                    <Pressable
                        key={c.id}
                        style={[styles.catChip, selectedSlug === c.slug && styles.catChipOn]}
                        onPress={() => setSelectedSlug(c.slug)}
                    >
                        <Text style={[styles.catChipText, selectedSlug === c.slug && styles.catChipTextOn]}>
                            {'—'.repeat(Math.max(0, c.depth))}
                            {c.depth ? ' ' : ''}
                            {c.name}
                        </Text>
                    </Pressable>
                ))}
            </ScrollView>
            {loading ? (
                <ActivityIndicator style={{ marginTop: 24 }} color="#f97316" />
            ) : (
                <FlatList
                    data={products}
                    keyExtractor={(item) => String(item.id)}
                    contentContainerStyle={styles.listPad}
                    refreshControl={<RefreshControl refreshing={refreshing} onRefresh={() => { setRefreshing(true); loadProducts(); }} />}
                    renderItem={({ item }) => (
                        <View style={styles.card}>
                            <Pressable onPress={() => navigation.navigate('ProductDetail', { slug: item.slug })}>
                                <Text style={styles.pName}>{item.name}</Text>
                                <Text style={styles.pPrice}>₺{Number(item.price).toFixed(2)}</Text>
                                <Text style={styles.pMeta}>{item.vendor?.name || 'Satıcı'}</Text>
                            </Pressable>
                            <Pressable
                                style={styles.btnSm}
                                onPress={() => addToCart(item.id)}
                                disabled={adding === item.id}
                            >
                                {adding === item.id ? (
                                    <ActivityIndicator color="#111" size="small" />
                                ) : (
                                    <Text style={styles.btnSmText}>{token && user ? 'Sepete ekle' : 'Sepete eklemek için giriş'}</Text>
                                )}
                            </Pressable>
                        </View>
                    )}
                    ListEmptyComponent={<Text style={styles.muted}>Bu kategoride ürün yok.</Text>}
                />
            )}
        </View>
    );
}

function CustomerShopScreen() {
    const navigation = useNavigation();
    const route = useRoute();
    const { user, token } = useAuth();
    const [q, setQ] = useState('');
    const [categorySlug, setCategorySlug] = useState('');
    const [products, setProducts] = useState([]);
    const [refreshing, setRefreshing] = useState(false);
    const [loading, setLoading] = useState(true);
    const [adding, setAdding] = useState(null);
    const [msg, setMsg] = useState('');

    useEffect(() => {
        const p = route.params || {};
        if (p.q !== undefined) setQ(typeof p.q === 'string' ? p.q : '');
        if (p.category !== undefined) setCategorySlug(p.category ? String(p.category) : '');
    }, [route.params]);

    const productType = route.params?.type;

    const load = useCallback(async () => {
        try {
            const params = new URLSearchParams();
            if (q.trim()) params.set('q', q.trim());
            if (categorySlug) params.set('category', categorySlug);
            if (productType === 'digital') params.set('type', 'digital');
            const query = params.toString();
            const res = await api('/products' + (query ? `?${query}` : ''));
            setProducts(listFromApi(res));
        } catch {
            setProducts([]);
        } finally {
            setLoading(false);
            setRefreshing(false);
        }
    }, [q, categorySlug, productType]);

    useEffect(() => {
        load();
    }, [load]);

    const addToCart = async (productId) => {
        if (!token || !user) {
            navigation.navigate('Login');
            return;
        }
        setMsg('');
        setAdding(productId);
        try {
            await api('/cart/items', {
                method: 'POST',
                body: JSON.stringify({ product_id: productId, quantity: 1 }),
            });
            setMsg('Sepete eklendi');
            setTimeout(() => setMsg(''), 2000);
        } catch (e) {
            setMsg(e.message || 'Eklenemedi');
        } finally {
            setAdding(null);
        }
    };

    return (
        <View style={styles.flex}>
            <View style={styles.topBar}>
                <Text style={styles.brand}>Ürünler</Text>
            </View>
            {(categorySlug || productType === 'digital') ? (
                <View style={{ paddingHorizontal: 16, paddingBottom: 8, flexDirection: 'row', flexWrap: 'wrap', gap: 8 }}>
                    {categorySlug ? (
                        <Pressable style={styles.chip} onPress={() => { setCategorySlug(''); navigation.setParams?.({ category: undefined }); }}>
                            <Text style={{ fontSize: 12 }}>Kategori: {categorySlug} ✕</Text>
                        </Pressable>
                    ) : null}
                    {productType === 'digital' ? (
                        <Pressable style={styles.chip} onPress={() => navigation.navigate('ProductList', {})}>
                            <Text style={{ fontSize: 12 }}>Sadece dijital ✕</Text>
                        </Pressable>
                    ) : null}
                </View>
            ) : null}
            <View style={styles.searchRow}>
                <TextInput
                    style={styles.searchInput}
                    placeholder="Ürün veya kategori ara…"
                    value={q}
                    onChangeText={setQ}
                    onSubmitEditing={load}
                    returnKeyType="search"
                />
                <Pressable style={styles.searchBtn} onPress={load}>
                    <Text style={styles.searchBtnText}>Ara</Text>
                </Pressable>
            </View>
            {msg ? <Text style={styles.toast}>{msg}</Text> : null}
            {loading ? (
                <ActivityIndicator style={{ marginTop: 24 }} color="#f97316" />
            ) : (
                <FlatList
                    data={products}
                    keyExtractor={(item) => String(item.id)}
                    contentContainerStyle={styles.listPad}
                    refreshControl={<RefreshControl refreshing={refreshing} onRefresh={() => { setRefreshing(true); load(); }} />}
                    renderItem={({ item }) => (
                        <View style={styles.card}>
                            <Pressable onPress={() => navigation.navigate('ProductDetail', { slug: item.slug })}>
                                <Text style={styles.pName}>{item.name}</Text>
                                <Text style={styles.pPrice}>₺{Number(item.price).toFixed(2)}</Text>
                                <Text style={styles.pMeta}>{item.vendor?.name || 'Satıcı'}</Text>
                            </Pressable>
                            <Pressable
                                style={styles.btnSm}
                                onPress={() => addToCart(item.id)}
                                disabled={adding === item.id}
                            >
                                {adding === item.id ? (
                                    <ActivityIndicator color="#111" size="small" />
                                ) : (
                                    <Text style={styles.btnSmText}>{token && user ? 'Sepete ekle' : 'Sepete eklemek için giriş'}</Text>
                                )}
                            </Pressable>
                        </View>
                    )}
                    ListEmptyComponent={<Text style={styles.muted}>Ürün bulunamadı.</Text>}
                />
            )}
        </View>
    );
}

function CustomerCartScreen() {
    const navigation = useNavigation();
    const { user, token } = useAuth();
    const [cart, setCart] = useState({ items: [], total: '0' });
    const [addresses, setAddresses] = useState([]);
    const [addressId, setAddressId] = useState(null);
    const [loading, setLoading] = useState(true);
    const [checkoutLoading, setCheckoutLoading] = useState(false);
    const [err, setErr] = useState('');

    const load = useCallback(async () => {
        setErr('');
        try {
            const [cRaw, aRaw] = await Promise.all([api('/cart'), api('/addresses')]);
            const c = unwrapData(cRaw) || {};
            const a = unwrapData(aRaw);
            setCart({ items: c.items || [], total: c.total || '0' });
            const list = Array.isArray(a) ? a : [];
            setAddresses(list);
            setAddressId((prev) => (prev != null ? prev : list[0]?.id ?? null));
        } catch (e) {
            setErr(e.message || 'Yüklenemedi');
        } finally {
            setLoading(false);
        }
    }, []);

    useEffect(() => {
        if (!token || !user) {
            setLoading(false);
            return;
        }
        setLoading(true);
        load();
    }, [token, user, load]);

    if (!token || !user) {
        return (
            <View style={styles.flex}>
                <View style={styles.topBar}>
                    <Text style={styles.brand}>Sepetim</Text>
                </View>
                <View style={[styles.listPad, styles.guestBox]}>
                    <Text style={styles.pName}>Sepet için giriş gerekli</Text>
                    <Text style={styles.hint}>
                        Ürünleri giriş yapmadan gezebilirsiniz. Sepete ürün eklemek ve sipariş vermek için hesabınıza girin.
                    </Text>
                    <Pressable style={styles.btn} onPress={() => navigation.navigate('Login')}>
                        <Text style={styles.btnText}>Giriş yap</Text>
                    </Pressable>
                    <Pressable style={styles.btnOutline} onPress={() => navigation.navigate('Register')}>
                        <Text style={styles.btnOutlineText}>Hesap oluştur</Text>
                    </Pressable>
                </View>
            </View>
        );
    }

    const checkout = async () => {
        if (!addressId) {
            setErr('Teslimat adresi seçin veya web üzerinden adres ekleyin.');
            return;
        }
        setCheckoutLoading(true);
        setErr('');
        try {
            await api('/checkout', {
                method: 'POST',
                body: JSON.stringify({ address_id: addressId }),
            });
            await load();
        } catch (e) {
            setErr(e.message || 'Ödeme tamamlanamadı');
        } finally {
            setCheckoutLoading(false);
        }
    };

    return (
        <View style={styles.flex}>
            <View style={styles.topBar}>
                <Text style={styles.brand}>Sepetim</Text>
            </View>
            {loading ? (
                <ActivityIndicator style={{ marginTop: 24 }} color="#f97316" />
            ) : (
                <ScrollView contentContainerStyle={styles.listPad}>
                    {(cart.items || []).length === 0 ? (
                        <Text style={styles.muted}>Sepetiniz boş.</Text>
                    ) : (
                        (cart.items || []).map((row) => (
                            <View key={row.id} style={styles.card}>
                                <Text style={styles.pName}>{row.product?.name}</Text>
                                <Text style={styles.pMeta}>
                                    {row.quantity} × ₺{Number(row.product?.price || 0).toFixed(2)}
                                </Text>
                            </View>
                        ))
                    )}
                    <Text style={styles.totalLine}>Toplam: ₺{Number(cart.total).toFixed(2)}</Text>
                    {addresses.length > 0 ? (
                        <>
                            <Text style={styles.label}>Teslimat adresi</Text>
                            {addresses.map((ad) => (
                                <Pressable
                                    key={ad.id}
                                    style={[styles.addrRow, addressId === ad.id && styles.addrRowOn]}
                                    onPress={() => setAddressId(ad.id)}
                                >
                                    <Text style={styles.addrText}>
                                        {ad.label} — {[ad.neighborhood, ad.cadde, ad.sokak, ad.district, ad.city].filter(Boolean).join(' / ')}
                                        {ad.line1 ? ` · ${ad.line1}` : ''}
                                    </Text>
                                </Pressable>
                            ))}
                        </>
                    ) : (
                        <Text style={styles.hint}>Kayıtlı adres yok. Önce web üzerinden adres ekleyin.</Text>
                    )}
                    {err ? <Text style={styles.err}>{err}</Text> : null}
                    <Pressable
                        style={[styles.btn, (cart.items || []).length === 0 && styles.btnDisabled]}
                        onPress={checkout}
                        disabled={checkoutLoading || (cart.items || []).length === 0}
                    >
                        {checkoutLoading ? (
                            <ActivityIndicator color="#111" />
                        ) : (
                            <Text style={styles.btnText}>Siparişi onayla (demo ödeme)</Text>
                        )}
                    </Pressable>
                </ScrollView>
            )}
        </View>
    );
}

function CustomerOrdersScreen() {
    const navigation = useNavigation();
    const { user, token } = useAuth();
    const [orders, setOrders] = useState([]);
    const [refreshing, setRefreshing] = useState(false);
    const [loading, setLoading] = useState(true);

    const load = useCallback(async () => {
        try {
            const res = await api('/orders');
            setOrders(listFromApi(res));
        } catch {
            setOrders([]);
        } finally {
            setLoading(false);
            setRefreshing(false);
        }
    }, []);

    useEffect(() => {
        if (!token || !user) {
            setLoading(false);
            return;
        }
        load();
    }, [token, user, load]);

    if (!token || !user) {
        return (
            <View style={styles.flex}>
                <View style={styles.topBar}>
                    <Text style={styles.brand}>Siparişlerim</Text>
                </View>
                <View style={[styles.listPad, styles.guestBox]}>
                    <Text style={styles.pName}>Siparişlerinizi görmek için giriş yapın</Text>
                    <Text style={styles.hint}>Misafir olarak ürün ve kategorilere göz atabilirsiniz.</Text>
                    <Pressable style={styles.btn} onPress={() => navigation.navigate('Login')}>
                        <Text style={styles.btnText}>Giriş yap</Text>
                    </Pressable>
                </View>
            </View>
        );
    }

    return (
        <View style={styles.flex}>
            <View style={styles.topBar}>
                <Text style={styles.brand}>Siparişlerim</Text>
            </View>
            {loading ? (
                <ActivityIndicator style={{ marginTop: 24 }} color="#f97316" />
            ) : (
                <FlatList
                    data={orders}
                    keyExtractor={(item) => String(item.id)}
                    contentContainerStyle={styles.listPad}
                    refreshControl={<RefreshControl refreshing={refreshing} onRefresh={() => { setRefreshing(true); load(); }} />}
                    renderItem={({ item }) => (
                        <Pressable style={styles.card} onPress={() => navigation.navigate('OrderDetail', { orderId: item.id })}>
                            <Text style={styles.pName}>#{item.order_number}</Text>
                            <Text style={styles.pMeta}>{item.vendor?.name || '—'}</Text>
                            <Text style={styles.pPrice}>₺{Number(item.subtotal).toFixed(2)} · {item.status}</Text>
                            <Text style={[styles.hint, { marginTop: 6 }]}>Detay için dokunun</Text>
                        </Pressable>
                    )}
                    ListEmptyComponent={<Text style={styles.muted}>Henüz sipariş yok.</Text>}
                />
            )}
        </View>
    );
}

function GuestAccountScreen() {
    const navigation = useNavigation();
    return (
        <View style={styles.flex}>
            <View style={styles.topBar}>
                <Text style={styles.brand}>Hesabım</Text>
            </View>
            <ScrollView contentContainerStyle={[styles.listPad, styles.guestBox]}>
                <Text style={styles.heroTitle}>BaskıYeri</Text>
                <Text style={styles.hint}>
                    Web sitesindeki ürünler, kategoriler, satıcılar ve iş ilanları mobilde de aynı API ile listelenir. Sepet ve sipariş için
                    giriş gerekir.
                </Text>
                <Pressable style={styles.btn} onPress={() => navigation.navigate('Login')}>
                    <Text style={styles.btnText}>Giriş yap</Text>
                </Pressable>
                <Pressable style={styles.btnOutline} onPress={() => navigation.navigate('Register')}>
                    <Text style={styles.btnOutlineText}>Hesap oluştur</Text>
                </Pressable>
                <Text style={styles.menuSection}>Web ile aynı içerikler</Text>
                <Pressable style={styles.menuRow} onPress={() => navigation.navigate('ProductList')}>
                    <Text style={styles.menuRowText}>Tüm ürünler (ara)</Text>
                </Pressable>
                <Pressable style={styles.menuRow} onPress={() => navigation.navigate('Tabs', { screen: 'Kategoriler' })}>
                    <Text style={styles.menuRowText}>Kategoriler</Text>
                </Pressable>
                <Pressable style={styles.menuRow} onPress={() => navigation.navigate('Vendors')}>
                    <Text style={styles.menuRowText}>Satıcılar</Text>
                </Pressable>
                <Pressable style={styles.menuRow} onPress={() => navigation.navigate('FreelancerJobs')}>
                    <Text style={styles.menuRowText}>İş ilanları</Text>
                </Pressable>
                <Pressable style={styles.menuRow} onPress={() => navigation.navigate('StaticPage', { slug: 'privacy' })}>
                    <Text style={styles.menuRowText}>Gizlilik politikası</Text>
                </Pressable>
                <Pressable style={styles.menuRow} onPress={() => navigation.navigate('StaticPage', { slug: 'terms' })}>
                    <Text style={styles.menuRowText}>Kullanım koşulları</Text>
                </Pressable>
                <Pressable style={styles.menuRow} onPress={() => navigation.navigate('StaticPage', { slug: 'about' })}>
                    <Text style={styles.menuRowText}>Hakkımızda</Text>
                </Pressable>
            </ScrollView>
        </View>
    );
}

function AccountScreen({ subtitle }) {
    const navigation = useNavigation();
    const { user, logout } = useAuth();
    const customerMenu = user?.role === 'customer' && !subtitle;

    return (
        <ScrollView style={styles.flex}>
            <View style={styles.topBar}>
                <Text style={styles.brand}>Hesabım</Text>
            </View>
            <View style={styles.listPad}>
                <View style={styles.card}>
                    <Text style={styles.pName}>{user?.name}</Text>
                    <Text style={styles.pMeta}>{user?.email}</Text>
                    {subtitle ? <Text style={styles.hint}>{subtitle}</Text> : null}
                    <View style={styles.badgeRow}>
                        <Text style={styles.badge}>
                            {user?.role === 'vendor' ? 'Satıcı' : user?.role === 'admin' ? 'Yönetici' : 'Müşteri'}
                        </Text>
                    </View>
                </View>
                {customerMenu ? (
                    <>
                        <Text style={styles.menuSection}>Özet</Text>
                        <Pressable style={styles.menuRow} onPress={() => navigation.navigate('CustomerDashboard')}>
                            <Text style={styles.menuRowText}>Hesabım özeti</Text>
                        </Pressable>
                        <Pressable style={styles.menuRow} onPress={() => navigation.navigate('WebMirror', { path: '/hesabim/fiyat-tahmini' })}>
                            <Text style={styles.menuRowText}>İş hesaplama (web)</Text>
                        </Pressable>
                        <Text style={styles.menuSection}>Web — tam sayfa (Blade)</Text>
                        <Pressable style={styles.menuRow} onPress={() => navigation.navigate('WebMirror', { path: '/hesabim' })}>
                            <Text style={styles.menuRowText}>Web müşteri paneli (/hesabim)</Text>
                        </Pressable>
                        <Pressable style={styles.menuRow} onPress={() => navigation.navigate('WebMirror', { path: '/sepet' })}>
                            <Text style={styles.menuRowText}>Web sepet</Text>
                        </Pressable>
                        <Pressable style={styles.menuRow} onPress={() => navigation.navigate('WebMirror', { path: '/' })}>
                            <Text style={styles.menuRowText}>Web ana sayfa</Text>
                        </Pressable>
                        <Text style={styles.menuSection}>Hesabım (uygulama)</Text>
                        <Pressable style={styles.menuRow} onPress={() => navigation.navigate('Favorites')}>
                            <Text style={styles.menuRowText}>Favorilerim</Text>
                        </Pressable>
                        <Pressable style={styles.menuRow} onPress={() => navigation.navigate('Addresses')}>
                            <Text style={styles.menuRowText}>Adreslerim</Text>
                        </Pressable>
                        <Pressable style={styles.menuRow} onPress={() => navigation.navigate('QuoteRequests')}>
                            <Text style={styles.menuRowText}>Teklif taleplerim</Text>
                        </Pressable>
                        <Pressable style={styles.menuRow} onPress={() => navigation.navigate('MyFreelancerJobs')}>
                            <Text style={styles.menuRowText}>İş ilanlarım</Text>
                        </Pressable>
                        <Pressable style={styles.menuRow} onPress={() => navigation.navigate('CreateQuoteRequest')}>
                            <Text style={styles.menuRowText}>+ Teklif talebi oluştur</Text>
                        </Pressable>
                        <Pressable style={styles.menuRow} onPress={() => navigation.navigate('CreateFreelancerJob')}>
                            <Text style={styles.menuRowText}>+ İş ilanı ver</Text>
                        </Pressable>
                        <Pressable style={styles.menuRow} onPress={() => navigation.navigate('Notifications')}>
                            <Text style={styles.menuRowText}>Bildirimler</Text>
                        </Pressable>
                        <Pressable style={styles.menuRow} onPress={() => navigation.navigate('Conversations')}>
                            <Text style={styles.menuRowText}>Mesajlar</Text>
                        </Pressable>
                        <Pressable style={styles.menuRow} onPress={() => navigation.navigate('SupportTickets')}>
                            <Text style={styles.menuRowText}>Destek taleplerim</Text>
                        </Pressable>
                        <Pressable style={styles.menuRow} onPress={() => navigation.navigate('CreateSupportTicket')}>
                            <Text style={styles.menuRowText}>Yeni destek talebi</Text>
                        </Pressable>
                        <Text style={styles.menuSection}>Keşfet</Text>
                        <Pressable style={styles.menuRow} onPress={() => navigation.navigate('ProductList')}>
                            <Text style={styles.menuRowText}>Tüm ürünler</Text>
                        </Pressable>
                        <Pressable style={styles.menuRow} onPress={() => navigation.navigate('Vendors')}>
                            <Text style={styles.menuRowText}>Satıcılar</Text>
                        </Pressable>
                        <Pressable style={styles.menuRow} onPress={() => navigation.navigate('FreelancerJobs')}>
                            <Text style={styles.menuRowText}>İş ilanları</Text>
                        </Pressable>
                        <Text style={styles.menuSection}>Yasal</Text>
                        <Pressable style={styles.menuRow} onPress={() => navigation.navigate('StaticPage', { slug: 'privacy' })}>
                            <Text style={styles.menuRowText}>Gizlilik politikası</Text>
                        </Pressable>
                        <Pressable style={styles.menuRow} onPress={() => navigation.navigate('StaticPage', { slug: 'terms' })}>
                            <Text style={styles.menuRowText}>Kullanım koşulları</Text>
                        </Pressable>
                        <Pressable style={styles.menuRow} onPress={() => navigation.navigate('StaticPage', { slug: 'about' })}>
                            <Text style={styles.menuRowText}>Hakkımızda</Text>
                        </Pressable>
                    </>
                ) : null}
                <Pressable style={[styles.btnOutline, { marginTop: 16 }]} onPress={logout}>
                    <Text style={styles.btnOutlineText}>Çıkış yap</Text>
                </Pressable>
            </View>
        </ScrollView>
    );
}

function CustomerHesapScreen() {
    const { user, token } = useAuth();
    if (!token || !user) {
        return <GuestAccountScreen />;
    }
    return <AccountScreen />;
}

function VendorDashboardScreen() {
    const navigation = useNavigation();
    const [data, setData] = useState(null);
    const [loading, setLoading] = useState(true);
    const [refreshing, setRefreshing] = useState(false);

    const load = useCallback(async () => {
        try {
            const d = await api('/vendor/dashboard');
            setData(d);
        } catch {
            setData(null);
        } finally {
            setLoading(false);
            setRefreshing(false);
        }
    }, []);

    useEffect(() => {
        load();
    }, [load]);

    return (
        <View style={styles.flex}>
            <View style={styles.topBar}>
                <Text style={styles.brand}>Mağaza özeti</Text>
            </View>
            {loading ? (
                <ActivityIndicator style={{ marginTop: 24 }} color="#f97316" />
            ) : (
                <ScrollView
                    contentContainerStyle={styles.listPad}
                    refreshControl={<RefreshControl refreshing={refreshing} onRefresh={() => { setRefreshing(true); load(); }} />}
                >
                    <View style={styles.card}>
                        <Text style={styles.pName}>{data?.vendor?.name}</Text>
                        <Text style={styles.pMeta}>
                            {data?.vendor?.is_active ? 'Yayında' : 'Onay bekliyor'}
                        </Text>
                    </View>
                    {data?.counts ? (
                        <View style={styles.statGrid}>
                            <View style={styles.statCell}>
                                <Text style={styles.statNum}>{data.counts.products}</Text>
                                <Text style={styles.statLbl}>Ürün</Text>
                            </View>
                            <View style={styles.statCell}>
                                <Text style={styles.statNum}>{data.counts.orders_active}</Text>
                                <Text style={styles.statLbl}>Aktif sipariş</Text>
                            </View>
                            <View style={styles.statCell}>
                                <Text style={styles.statNum}>{data.counts.open_quote_requests}</Text>
                                <Text style={styles.statLbl}>Açık talep</Text>
                            </View>
                        </View>
                    ) : null}
                    <Pressable
                        style={[styles.menuRow, { marginTop: 16 }]}
                        onPress={() => navigation.navigate('VendorPayoutRequests')}
                    >
                        <Text style={styles.menuRowText}>Ödeme talepleri (bakiye çekimi)</Text>
                    </Pressable>
                </ScrollView>
            )}
        </View>
    );
}

function VendorProductsScreen() {
    const [products, setProducts] = useState([]);
    const [cats, setCats] = useState([]);
    const [loading, setLoading] = useState(true);
    const [refreshing, setRefreshing] = useState(false);
    const [modalOpen, setModalOpen] = useState(false);
    const [editing, setEditing] = useState(null);
    const [err, setErr] = useState('');
    const [form, setForm] = useState({
        category_id: '',
        name: '',
        price: '',
        stock: '0',
        is_active: true,
    });

    const load = useCallback(async () => {
        try {
            const res = await api('/vendor/products');
            setProducts(listFromApi(res));
        } catch {
            setProducts([]);
        } finally {
            setLoading(false);
            setRefreshing(false);
        }
    }, []);

    useEffect(() => {
        (async () => {
            try {
                const c = unwrapData(await api('/categories'));
                if (Array.isArray(c)) setCats(c);
            } catch {
                setCats([]);
            }
        })();
        load();
    }, [load]);

    const openNew = () => {
        setEditing(null);
        setErr('');
        setForm({ category_id: cats[0]?.id ? String(cats[0].id) : '', name: '', price: '', stock: '0', is_active: true });
        setModalOpen(true);
    };

    const openEdit = (p) => {
        setEditing(p);
        setErr('');
        setForm({
            category_id: p.category_id ? String(p.category_id) : (cats[0]?.id ? String(cats[0].id) : ''),
            name: p.name || '',
            price: String(p.price ?? ''),
            stock: String(p.stock ?? 0),
            is_active: !!p.is_active,
        });
        setModalOpen(true);
    };

    const save = async () => {
        setErr('');
        const payload = {
            category_id: parseInt(form.category_id, 10),
            name: form.name,
            price: parseFloat(String(form.price).replace(',', '.')),
            stock: parseInt(String(form.stock), 10) || 0,
            is_active: !!form.is_active,
        };
        try {
            if (editing?.id) {
                await api(`/vendor/products/${editing.id}`, { method: 'PUT', body: JSON.stringify(payload) });
            } else {
                await api('/vendor/products', { method: 'POST', body: JSON.stringify(payload) });
            }
            setModalOpen(false);
            await load();
        } catch (e) {
            setErr(e.message || 'Kaydedilemedi');
        }
    };

    const remove = async (p) => {
        try {
            await api(`/vendor/products/${p.id}`, { method: 'DELETE' });
            await load();
        } catch {
            /* ignore */
        }
    };

    return (
        <View style={styles.flex}>
            <View style={styles.topBar}>
                <Text style={styles.brand}>Ürünlerim</Text>
                <Pressable onPress={openNew} hitSlop={10}>
                    <Text style={{ color: '#ea580c', fontWeight: '800' }}>+ Yeni</Text>
                </Pressable>
            </View>
            {loading ? (
                <ActivityIndicator style={{ marginTop: 24 }} color="#f97316" />
            ) : (
                <FlatList
                    data={products}
                    keyExtractor={(item) => String(item.id)}
                    contentContainerStyle={styles.listPad}
                    refreshControl={<RefreshControl refreshing={refreshing} onRefresh={() => { setRefreshing(true); load(); }} />}
                    renderItem={({ item }) => (
                        <View style={styles.card}>
                            <Text style={styles.pName}>{item.name}</Text>
                            <Text style={styles.pPrice}>₺{Number(item.price).toFixed(2)}</Text>
                            <Text style={styles.pMeta}>{item.is_active ? 'Aktif' : 'Pasif'}</Text>
                            <View style={[styles.row, { marginTop: 10 }]}>
                                <Pressable style={styles.chip} onPress={() => openEdit(item)}>
                                    <Text>Düzenle</Text>
                                </Pressable>
                                <Pressable style={[styles.chip, { backgroundColor: '#fee2e2', borderColor: '#fecaca' }]} onPress={() => remove(item)}>
                                    <Text style={{ fontWeight: '700', color: '#991b1b' }}>Sil</Text>
                                </Pressable>
                            </View>
                        </View>
                    )}
                    ListEmptyComponent={<Text style={styles.muted}>Ürün yok.</Text>}
                />
            )}

            <Modal visible={modalOpen} animationType="slide" onRequestClose={() => setModalOpen(false)}>
                <View style={[styles.flex, { backgroundColor: '#fff' }]}>
                    <View style={styles.topBar}>
                        <Pressable onPress={() => setModalOpen(false)} hitSlop={10}>
                            <Text style={{ color: '#ea580c', fontWeight: '700' }}>← Kapat</Text>
                        </Pressable>
                        <Text style={styles.brand}>{editing ? 'Ürün düzenle' : 'Yeni ürün'}</Text>
                        <View style={{ width: 60 }} />
                    </View>
                    <ScrollView contentContainerStyle={styles.listPad}>
                        {err ? <Text style={styles.err}>{err}</Text> : null}
                        <Text style={styles.label}>Kategori ID</Text>
                        <TextInput style={styles.input} value={form.category_id} onChangeText={(t) => setForm((p) => ({ ...p, category_id: t }))} placeholder="Kategori ID" />
                        <Text style={styles.hint}>Mobil için şimdilik kategori seçimi ID ile. (İstersen dropdown’a çevirebilirim.)</Text>
                        <Text style={styles.label}>Ad</Text>
                        <TextInput style={styles.input} value={form.name} onChangeText={(t) => setForm((p) => ({ ...p, name: t }))} placeholder="Ürün adı" />
                        <Text style={styles.label}>Fiyat</Text>
                        <TextInput style={styles.input} value={form.price} onChangeText={(t) => setForm((p) => ({ ...p, price: t }))} placeholder="0.00" keyboardType="decimal-pad" />
                        <Text style={styles.label}>Stok</Text>
                        <TextInput style={styles.input} value={form.stock} onChangeText={(t) => setForm((p) => ({ ...p, stock: t }))} placeholder="0" keyboardType="number-pad" />
                        <View style={[styles.row, { marginTop: 6 }]}>
                            <Pressable onPress={() => setForm((p) => ({ ...p, is_active: !p.is_active }))} style={[styles.chip, form.is_active && styles.chipOn]}>
                                <Text>Aktif</Text>
                            </Pressable>
                        </View>
                        <Pressable style={styles.btn} onPress={save}>
                            <Text style={styles.btnText}>Kaydet</Text>
                        </Pressable>
                    </ScrollView>
                </View>
            </Modal>
        </View>
    );
}

function VendorOrdersScreen() {
    const navigation = useNavigation();
    const [orders, setOrders] = useState([]);
    const [loading, setLoading] = useState(true);
    const [refreshing, setRefreshing] = useState(false);

    const load = useCallback(async () => {
        try {
            const res = await api('/vendor/orders');
            setOrders(listFromApi(res));
        } catch {
            setOrders([]);
        } finally {
            setLoading(false);
            setRefreshing(false);
        }
    }, []);

    useEffect(() => {
        load();
    }, [load]);

    return (
        <View style={styles.flex}>
            <View style={styles.topBar}>
                <Text style={styles.brand}>Gelen siparişler</Text>
            </View>
            {loading ? (
                <ActivityIndicator style={{ marginTop: 24 }} color="#f97316" />
            ) : (
                <FlatList
                    data={orders}
                    keyExtractor={(item) => String(item.id)}
                    contentContainerStyle={styles.listPad}
                    refreshControl={<RefreshControl refreshing={refreshing} onRefresh={() => { setRefreshing(true); load(); }} />}
                    renderItem={({ item }) => (
                        <Pressable style={styles.card} onPress={() => navigation.navigate('VendorOrderDetail', { orderId: item.id })}>
                            <Text style={styles.pName}>#{item.order_number}</Text>
                            <Text style={styles.pMeta}>{item.user?.name || 'Müşteri'}</Text>
                            <Text style={styles.pPrice}>₺{Number(item.subtotal).toFixed(2)} · {item.status}</Text>
                        </Pressable>
                    )}
                    ListEmptyComponent={<Text style={styles.muted}>Sipariş yok.</Text>}
                />
            )}
        </View>
    );
}

function VendorRootStack() {
    return (
        <VendorStack.Navigator screenOptions={{ headerShown: false }}>
            <VendorStack.Screen name="VendorTabs" component={VendorTabs} />
            <VendorStack.Screen name="VendorOrderDetail" component={VendorOrderDetailScreen} />
            <VendorStack.Screen name="VendorPayoutRequests" component={VendorPayoutRequestsScreen} />
        </VendorStack.Navigator>
    );
}

function AdminRootStack() {
    return (
        <AdminStack.Navigator screenOptions={{ headerShown: false }}>
            <AdminStack.Screen name="AdminTabs" component={AdminTabs} />
            <AdminStack.Screen name="AdminSupportTickets" component={AdminSupportTicketsScreen} />
            <AdminStack.Screen name="AdminSupportTicketDetail" component={AdminSupportTicketDetailScreen} />
            <AdminStack.Screen name="AdminPayoutRequests" component={AdminPayoutRequestsScreen} />
        </AdminStack.Navigator>
    );
}

function AdminDashboardScreen() {
    const navigation = useNavigation();
    const [data, setData] = useState(null);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        (async () => {
            try {
                const d = await api('/admin/dashboard');
                setData(d);
            } catch {
                setData(null);
            } finally {
                setLoading(false);
            }
        })();
    }, []);

    return (
        <View style={styles.flex}>
            <View style={styles.topBar}>
                <Text style={styles.brand}>Yönetim</Text>
            </View>
            {loading ? (
                <ActivityIndicator style={{ marginTop: 24 }} color="#f97316" />
            ) : (
                <ScrollView contentContainerStyle={styles.listPad}>
                    {data?.stats ? (
                        <View style={styles.statGrid}>
                            {Object.entries(data.stats).map(([k, v]) => (
                                <View key={k} style={styles.statCell}>
                                    <Text style={styles.statNum}>{v}</Text>
                                    <Text style={styles.statLbl}>{k}</Text>
                                </View>
                            ))}
                        </View>
                    ) : null}
                    <Text style={styles.hint}>{data?.hint || data?.message || ''}</Text>
                    <Text style={[styles.menuSection, { marginTop: 20 }]}>İşlemler</Text>
                    <Pressable style={styles.menuRow} onPress={() => navigation.navigate('AdminSupportTickets')}>
                        <Text style={styles.menuRowText}>Destek talepleri</Text>
                    </Pressable>
                    <Pressable style={styles.menuRow} onPress={() => navigation.navigate('AdminPayoutRequests')}>
                        <Text style={styles.menuRowText}>Satıcı ödeme talepleri</Text>
                    </Pressable>
                </ScrollView>
            )}
        </View>
    );
}

const tabIcon = (nameOutline, nameSolid) => {
    return ({ focused, color, size }) => (
        <Ionicons name={(focused ? nameSolid : nameOutline)} size={size} color={color} />
    );
};

function MarketplaceTabs() {
    return (
        <Tab.Navigator
            screenOptions={{
                tabBarActiveTintColor: '#ea580c',
                tabBarInactiveTintColor: '#9ca3af',
                headerShown: false,
            }}
        >
            <Tab.Screen
                name="Ana Sayfa"
                component={WebMirrorScreen}
                initialParams={{ path: '/', showHeader: false }}
                options={{ tabBarIcon: tabIcon('home-outline', 'home') }}
            />
            <Tab.Screen name="Ürünler" component={CustomerShopScreen} options={{ tabBarIcon: tabIcon('search-outline', 'search') }} />
            <Tab.Screen name="Kategoriler" component={CategoriesScreen} options={{ tabBarIcon: tabIcon('grid-outline', 'grid') }} />
            <Tab.Screen name="Sepet" component={CustomerCartScreen} options={{ tabBarIcon: tabIcon('cart-outline', 'cart') }} />
            <Tab.Screen name="Siparişler" component={CustomerOrdersScreen} options={{ tabBarIcon: tabIcon('receipt-outline', 'receipt') }} />
            <Tab.Screen name="Hesabım" component={CustomerHesapScreen} options={{ tabBarIcon: tabIcon('person-outline', 'person') }} />
        </Tab.Navigator>
    );
}

function MarketplaceStack() {
    return (
        <RootStack.Navigator screenOptions={{ headerShown: false }}>
            <RootStack.Screen name="Tabs" component={MarketplaceTabs} />
            <RootStack.Screen name="ProductList" component={CustomerShopScreen} />
            <RootStack.Screen name="ProductDetail" component={ProductDetailScreen} />
            <RootStack.Screen name="Vendors" component={VendorsListScreen} />
            <RootStack.Screen name="VendorDetail" component={VendorDetailScreen} />
            <RootStack.Screen name="FreelancerJobs" component={FreelancerJobsScreen} />
            <RootStack.Screen name="FreelancerJobDetail" component={FreelancerJobDetailScreen} />
            <RootStack.Screen name="StaticPage" component={StaticPageScreen} />
            <RootStack.Screen name="Favorites" component={FavoritesScreen} />
            <RootStack.Screen name="Addresses" component={AddressesScreen} />
            <RootStack.Screen name="QuoteRequests" component={QuoteRequestsScreen} />
            <RootStack.Screen name="QuoteRequestDetail" component={QuoteRequestDetailScreen} />
            <RootStack.Screen name="MyFreelancerJobs" component={MyFreelancerJobsScreen} />
            <RootStack.Screen name="OrderDetail" component={OrderDetailScreen} />
            <RootStack.Screen name="CreateQuoteRequest" component={CreateQuoteRequestScreen} />
            <RootStack.Screen name="CreateFreelancerJob" component={CreateFreelancerJobScreen} />
            <RootStack.Screen name="WebMirror" component={WebMirrorScreen} />
            <RootStack.Screen name="CustomerDashboard" component={CustomerDashboardScreen} />
            <RootStack.Screen name="CustomerPriceEstimate" component={CustomerPriceEstimateScreen} />
            <RootStack.Screen name="Notifications" component={NotificationsScreen} />
            <RootStack.Screen name="Conversations" component={ConversationsScreen} />
            <RootStack.Screen name="SupportTickets" component={SupportTicketsScreen} />
            <RootStack.Screen name="SupportTicketDetail" component={SupportTicketDetailScreen} />
            <RootStack.Screen name="CreateSupportTicket" component={CreateSupportTicketScreen} />
            <RootStack.Screen
                name="Login"
                component={LoginScreen}
                options={{ presentation: 'modal', animation: 'slide_from_bottom' }}
            />
            <RootStack.Screen
                name="Register"
                component={RegisterScreen}
                options={{ presentation: 'modal', animation: 'slide_from_bottom' }}
            />
        </RootStack.Navigator>
    );
}

function VendorTabs() {
    return (
        <Tab.Navigator
            screenOptions={{
                tabBarActiveTintColor: '#ea580c',
                tabBarInactiveTintColor: '#9ca3af',
                headerShown: false,
            }}
        >
            <Tab.Screen name="Özet" component={VendorDashboardScreen} options={{ tabBarIcon: tabIcon('stats-chart-outline', 'stats-chart') }} />
            <Tab.Screen name="Ürünler" component={VendorProductsScreen} options={{ tabBarIcon: tabIcon('cube-outline', 'cube') }} />
            <Tab.Screen name="Siparişler" component={VendorOrdersScreen} options={{ tabBarIcon: tabIcon('list-outline', 'list') }} />
            <Tab.Screen
                name="Hesabım"
                options={{ tabBarIcon: tabIcon('person-outline', 'person') }}
            >
                {() => <AccountScreen subtitle="Satıcı paneli — detaylı işlemler için web." />}
            </Tab.Screen>
        </Tab.Navigator>
    );
}

function AdminTabs() {
    return (
        <Tab.Navigator
            screenOptions={{
                tabBarActiveTintColor: '#ea580c',
                tabBarInactiveTintColor: '#9ca3af',
                headerShown: false,
            }}
        >
            <Tab.Screen name="Panel" component={AdminDashboardScreen} options={{ tabBarIcon: tabIcon('speedometer-outline', 'speedometer') }} />
            <Tab.Screen
                name="Hesabım"
                options={{ tabBarIcon: tabIcon('person-outline', 'person') }}
            >
                {() => <AccountScreen subtitle="Tam yönetim için web admin paneli." />}
            </Tab.Screen>
        </Tab.Navigator>
    );
}

function RootNavigator() {
    const { user, ready, token } = useAuth();

    if (!ready) {
        return (
            <View style={[styles.flex, styles.centered]}>
                <ActivityIndicator size="large" color="#f97316" />
            </View>
        );
    }

    if (token && user?.role === 'vendor') {
        return <VendorRootStack />;
    }
    if (token && user?.role === 'admin') {
        return <AdminRootStack />;
    }

    /* Misafir + müşteri: önce pazaryeri; giriş modal */
    return <MarketplaceStack />;
}

export default function App() {
    return (
        <SafeAreaProvider>
            <AuthProvider>
                <NavigationContainer>
                    <RootNavigator />
                    <StatusBar style="dark" />
                </NavigationContainer>
            </AuthProvider>
        </SafeAreaProvider>
    );
}

const styles = StyleSheet.create({
    flex: { flex: 1, backgroundColor: '#f9fafb' },
    centered: { justifyContent: 'center', alignItems: 'center' },
    pad: { flex: 1, padding: 24, justifyContent: 'center', backgroundColor: '#fff' },
    padScroll: { flex: 1, backgroundColor: '#fff' },
    padContent: { padding: 24, paddingBottom: 48 },
    heroTitle: { fontSize: 28, fontWeight: '800', color: '#111827', letterSpacing: -0.5 },
    heroSub: { fontSize: 15, color: '#6b7280', marginBottom: 28 },
    title: { fontSize: 22, fontWeight: '700', marginBottom: 16, color: '#111827' },
    label: { fontSize: 13, fontWeight: '600', color: '#374151', marginBottom: 8, marginTop: 4 },
    input: {
        borderWidth: 1,
        borderColor: '#e5e7eb',
        borderRadius: 12,
        padding: 14,
        marginBottom: 12,
        fontSize: 16,
        backgroundColor: '#fafafa',
    },
    btn: {
        backgroundColor: '#f97316',
        borderRadius: 999,
        paddingVertical: 14,
        alignItems: 'center',
        marginTop: 8,
    },
    btnDisabled: { opacity: 0.45 },
    btnText: { fontWeight: '700', fontSize: 16, color: '#111827' },
    btnSm: {
        alignSelf: 'flex-start',
        backgroundColor: '#ffedd5',
        paddingHorizontal: 14,
        paddingVertical: 8,
        borderRadius: 999,
        marginTop: 10,
    },
    btnSmText: { fontWeight: '700', fontSize: 14, color: '#9a3412' },
    btnOutline: {
        borderWidth: 1,
        borderColor: '#e5e7eb',
        borderRadius: 999,
        paddingVertical: 14,
        alignItems: 'center',
        marginTop: 16,
        backgroundColor: '#fff',
    },
    btnOutlineText: { fontWeight: '600', color: '#374151' },
    err: { color: '#b91c1c', marginBottom: 8 },
    link: { color: '#ea580c', marginTop: 20, textAlign: 'center', fontWeight: '600' },
    row: { flexDirection: 'row', gap: 8, marginBottom: 12 },
    wrap: { flexDirection: 'row', flexWrap: 'wrap', gap: 8, marginBottom: 12 },
    chip: { paddingHorizontal: 14, paddingVertical: 8, borderRadius: 999, borderWidth: 1, borderColor: '#e5e7eb', backgroundColor: '#fff' },
    chipOn: { backgroundColor: '#ffedd5', borderColor: '#f97316' },
    chipTextSmall: { fontSize: 13 },
    topBar: {
        flexDirection: 'row',
        justifyContent: 'space-between',
        alignItems: 'center',
        paddingHorizontal: 16,
        paddingVertical: 14,
        backgroundColor: '#fff',
        borderBottomWidth: 1,
        borderBottomColor: '#e5e7eb',
    },
    brand: { fontSize: 18, fontWeight: '700', color: '#111827' },
    listPad: { padding: 16, paddingBottom: 32 },
    card: {
        marginBottom: 10,
        padding: 16,
        backgroundColor: '#fff',
        borderRadius: 16,
        borderWidth: 1,
        borderColor: '#f3f4f6',
    },
    pName: { fontSize: 16, fontWeight: '600', color: '#111827' },
    pPrice: { fontSize: 15, color: '#ea580c', fontWeight: '700', marginTop: 4 },
    pMeta: { fontSize: 13, color: '#6b7280', marginTop: 4 },
    muted: { textAlign: 'center', color: '#9ca3af', marginTop: 32, paddingHorizontal: 24 },
    hint: { fontSize: 13, color: '#6b7280', marginTop: 12, lineHeight: 20 },
    toast: { textAlign: 'center', color: '#15803d', fontWeight: '600', paddingVertical: 8, backgroundColor: '#ecfdf5' },
    totalLine: { fontSize: 17, fontWeight: '700', marginVertical: 16, color: '#111827' },
    addrRow: {
        borderWidth: 1,
        borderColor: '#e5e7eb',
        borderRadius: 12,
        padding: 12,
        marginBottom: 8,
        backgroundColor: '#fff',
    },
    addrRowOn: { borderColor: '#f97316', backgroundColor: '#fff7ed' },
    addrText: { fontSize: 14, color: '#374151' },
    statGrid: { flexDirection: 'row', flexWrap: 'wrap', gap: 10 },
    statCell: {
        width: '30%',
        minWidth: 100,
        backgroundColor: '#fff',
        borderRadius: 14,
        padding: 14,
        borderWidth: 1,
        borderColor: '#f3f4f6',
    },
    statNum: { fontSize: 20, fontWeight: '800', color: '#111827' },
    statLbl: { fontSize: 11, color: '#6b7280', marginTop: 4, textTransform: 'capitalize' },
    badgeRow: { marginTop: 12 },
    badge: { alignSelf: 'flex-start', backgroundColor: '#e5e7eb', paddingHorizontal: 10, paddingVertical: 4, borderRadius: 8, fontSize: 12, fontWeight: '600', color: '#374151' },
    menuSection: {
        fontSize: 12,
        fontWeight: '700',
        color: '#9ca3af',
        textTransform: 'uppercase',
        letterSpacing: 0.6,
        marginTop: 20,
        marginBottom: 8,
    },
    menuRow: {
        backgroundColor: '#fff',
        borderRadius: 12,
        borderWidth: 1,
        borderColor: '#f3f4f6',
        paddingVertical: 14,
        paddingHorizontal: 16,
        marginBottom: 8,
    },
    menuRowText: { fontSize: 16, color: '#111827', fontWeight: '500' },
    closeBtn: { alignSelf: 'flex-end', marginBottom: 8 },
    closeBtnText: { fontSize: 15, color: '#6b7280', fontWeight: '600' },
    searchRow: {
        flexDirection: 'row',
        alignItems: 'center',
        paddingHorizontal: 12,
        paddingBottom: 10,
        gap: 8,
        backgroundColor: '#fff',
        borderBottomWidth: 1,
        borderBottomColor: '#e5e7eb',
    },
    searchInput: {
        flex: 1,
        borderWidth: 1,
        borderColor: '#e5e7eb',
        borderRadius: 12,
        paddingHorizontal: 14,
        paddingVertical: 10,
        fontSize: 16,
        backgroundColor: '#fafafa',
    },
    searchBtn: {
        backgroundColor: '#ffedd5',
        paddingHorizontal: 16,
        paddingVertical: 10,
        borderRadius: 12,
    },
    searchBtnText: { fontWeight: '700', color: '#9a3412' },
    catStrip: { maxHeight: 52, backgroundColor: '#fff', borderBottomWidth: 1, borderBottomColor: '#e5e7eb' },
    catStripInner: { paddingHorizontal: 12, paddingVertical: 10, alignItems: 'center', gap: 8 },
    catChip: {
        paddingHorizontal: 14,
        paddingVertical: 8,
        borderRadius: 999,
        borderWidth: 1,
        borderColor: '#e5e7eb',
        backgroundColor: '#fafafa',
        marginRight: 8,
    },
    catChipOn: { backgroundColor: '#ffedd5', borderColor: '#f97316' },
    catChipText: { fontSize: 13, color: '#374151', fontWeight: '500' },
    catChipTextOn: { color: '#9a3412', fontWeight: '700' },
    guestBox: { paddingTop: 8 },
});
