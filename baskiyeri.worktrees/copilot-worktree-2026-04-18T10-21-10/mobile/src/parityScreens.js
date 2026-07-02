/**
 * Web ile aynı içerik/kapsam — API üzerinden.
 */
import { Ionicons } from '@expo/vector-icons';
import { useFocusEffect, useNavigation, useRoute } from '@react-navigation/native';
import { useCallback, useEffect, useState } from 'react';
import {
    ActivityIndicator,
    Dimensions,
    FlatList,
    Image,
    ImageBackground,
    Modal,
    Platform,
    Pressable,
    RefreshControl,
    ScrollView,
    StyleSheet,
    Text,
    TextInput,
    View,
} from 'react-native';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import { API_URL, api } from './api';

const SITE_ORIGIN = API_URL.replace(/\/api\/v1\/?$/, '');

function productImageUrl(p) {
    if (p?.main_image) return `${SITE_ORIGIN}/storage/${p.main_image}`;
    return `https://picsum.photos/600/450?random=p${p?.id || 0}`;
}
import { useAuth } from './context/AuthContext';

const { width: SCREEN_W } = Dimensions.get('window');
const HERO_H = Math.min(380, Math.round(SCREEN_W * 1.05));
const HERO_SLIDES = [
    'https://picsum.photos/1200/900?random=slider1',
    'https://picsum.photos/1200/900?random=slider2',
    'https://picsum.photos/1200/900?random=slider3',
];
const PROMO_IMGS = {
    a: 'https://picsum.photos/800/500?random=kart1',
    b: 'https://picsum.photos/400/500?random=kart2',
    c: 'https://picsum.photos/400/500?random=kart3',
    teklif: 'https://picsum.photos/800/500?random=teklif',
};

const S = StyleSheet.create({
    flex: { flex: 1, backgroundColor: '#f9fafb' },
    topBar: {
        flexDirection: 'row',
        alignItems: 'center',
        paddingHorizontal: 16,
        paddingVertical: 14,
        backgroundColor: '#fff',
        borderBottomWidth: 1,
        borderBottomColor: '#e5e7eb',
    },
    brand: { fontSize: 18, fontWeight: '700', color: '#111827' },
    listPad: { padding: 16, paddingBottom: 48 },
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
    muted: { textAlign: 'center', color: '#9ca3af', marginTop: 24, paddingHorizontal: 16 },
    btn: { backgroundColor: '#f97316', borderRadius: 999, paddingVertical: 14, alignItems: 'center', marginTop: 8 },
    btnText: { fontWeight: '700', fontSize: 16, color: '#111827' },
    btnSm: { alignSelf: 'flex-start', backgroundColor: '#ffedd5', paddingHorizontal: 14, paddingVertical: 8, borderRadius: 999, marginTop: 10 },
    btnSmText: { fontWeight: '700', fontSize: 14, color: '#9a3412' },
    btnOutline: { borderWidth: 1, borderColor: '#e5e7eb', borderRadius: 999, paddingVertical: 12, alignItems: 'center', marginTop: 8 },
    sectionTitle: { fontSize: 13, fontWeight: '700', color: '#6b7280', textTransform: 'uppercase', letterSpacing: 0.5, marginBottom: 10, marginTop: 8 },
    linkRow: { paddingVertical: 14, borderBottomWidth: 1, borderBottomColor: '#f3f4f6' },
    linkText: { fontSize: 16, color: '#111827', fontWeight: '500' },
    hint: { fontSize: 13, color: '#6b7280', lineHeight: 20 },
    input: {
        borderWidth: 1,
        borderColor: '#e5e7eb',
        borderRadius: 12,
        padding: 12,
        marginBottom: 10,
        fontSize: 15,
        backgroundColor: '#fafafa',
    },
    label: { fontSize: 12, fontWeight: '600', color: '#374151', marginBottom: 4 },
    err: { color: '#b91c1c', marginBottom: 8 },
    horiz: { flexDirection: 'row', flexWrap: 'wrap', gap: 8 },
    chip: { paddingHorizontal: 12, paddingVertical: 8, borderRadius: 999, backgroundColor: '#fff', borderWidth: 1, borderColor: '#e5e7eb' },
    chipOn: { backgroundColor: '#ffedd5', borderColor: '#f97316' },
    homeHeader: {
        flexDirection: 'row',
        justifyContent: 'space-between',
        alignItems: 'center',
        paddingHorizontal: 16,
        paddingBottom: 10,
        backgroundColor: '#fff',
        borderBottomWidth: 1,
        borderBottomColor: '#f3f4f6',
    },
    homeLogo: { fontSize: 21, fontWeight: '800', color: '#111827', letterSpacing: -0.5 },
    homeHeaderIcons: { flexDirection: 'row', alignItems: 'center', gap: 18 },
    badge: {
        position: 'absolute',
        right: -6,
        top: -4,
        backgroundColor: '#ea580c',
        borderRadius: 8,
        minWidth: 16,
        height: 16,
        alignItems: 'center',
        justifyContent: 'center',
        paddingHorizontal: 4,
    },
    badgeText: { color: '#fff', fontSize: 10, fontWeight: '800' },
    heroCarousel: { height: HERO_H, width: SCREEN_W },
    heroSlide: { width: SCREEN_W, height: HERO_H, justifyContent: 'flex-end' },
    heroGrad: { ...StyleSheet.absoluteFillObject, backgroundColor: 'rgba(0,0,0,0.42)' },
    heroInner: { paddingHorizontal: 20, paddingBottom: 20, paddingTop: 8 },
    heroKicker: { color: 'rgba(255,255,255,0.95)', fontSize: 11, letterSpacing: 2.5, textAlign: 'center', marginBottom: 8, fontWeight: '600' },
    heroH1: {
        color: '#fff',
        fontSize: 26,
        fontWeight: '700',
        textAlign: 'center',
        lineHeight: 32,
        marginBottom: 14,
        ...(Platform.OS === 'ios' ? { fontFamily: 'Georgia' } : {}),
    },
    heroHint: { color: 'rgba(255,255,255,0.85)', fontSize: 12, textAlign: 'center', marginTop: 10, lineHeight: 18 },
    searchPill: {
        flexDirection: 'row',
        alignItems: 'center',
        backgroundColor: '#fff',
        borderRadius: 999,
        overflow: 'hidden',
        width: '100%',
        maxWidth: 560,
        alignSelf: 'center',
        shadowColor: '#000',
        shadowOffset: { width: 0, height: 4 },
        shadowOpacity: 0.12,
        shadowRadius: 12,
        elevation: 6,
    },
    searchCatBtn: { paddingHorizontal: 10, paddingVertical: 12, borderRightWidth: 1, borderRightColor: '#e5e7eb', maxWidth: 128 },
    searchCatTxt: { fontSize: 11, color: '#6b7280', fontWeight: '600' },
    searchHeroInput: { flex: 1, paddingVertical: 12, paddingHorizontal: 12, fontSize: 15, color: '#111827' },
    searchHeroGo: { paddingHorizontal: 14, paddingVertical: 12 },
    promoCard: { height: 192, borderRadius: 16, overflow: 'hidden', marginBottom: 12, backgroundColor: '#eee' },
    promoImg: { width: '100%', height: '100%' },
    promoOverlay: {
        ...StyleSheet.absoluteFillObject,
        padding: 16,
        justifyContent: 'space-between',
        backgroundColor: 'rgba(0,0,0,0.25)',
    },
    promoTag: { color: 'rgba(255,255,255,0.95)', fontSize: 11, fontWeight: '700', letterSpacing: 1.2 },
    promoTitle: { color: '#fff', fontSize: 20, fontWeight: '800' },
    promoBtn: {
        alignSelf: 'flex-start',
        backgroundColor: 'rgba(255,255,255,0.95)',
        paddingHorizontal: 14,
        paddingVertical: 8,
        borderRadius: 999,
        marginTop: 8,
    },
    promoBtnText: { fontSize: 13, fontWeight: '700', color: '#111827' },
    teklifWrap: { borderRadius: 16, overflow: 'hidden', backgroundColor: '#fff', borderWidth: 1, borderColor: '#f3f4f6', marginBottom: 20 },
    teklifImg: { width: '100%', height: 160 },
    teklifBody: { padding: 18 },
    teklifTag: { fontSize: 11, fontWeight: '800', color: '#ea580c', letterSpacing: 1.5, marginBottom: 8 },
    teklifH: { fontSize: 22, fontWeight: '800', color: '#111827', marginBottom: 8, lineHeight: 28 },
    teklifSub: { fontSize: 14, color: '#6b7280', marginBottom: 14, lineHeight: 20 },
    teklifBtn: { backgroundColor: '#fbbf24', paddingVertical: 14, borderRadius: 999, alignItems: 'center' },
    teklifBtnTxt: { fontWeight: '800', fontSize: 16, color: '#111827' },
    grid2: { flexDirection: 'row', flexWrap: 'wrap', gap: 10, justifyContent: 'space-between' },
    prodTile: { width: (SCREEN_W - 16 * 2 - 10) / 2, borderRadius: 14, overflow: 'hidden', backgroundColor: '#fff', borderWidth: 1, borderColor: '#f3f4f6', marginBottom: 10 },
    prodTileImg: { width: '100%', height: 120, backgroundColor: '#f3f4f6' },
    prodTileBody: { padding: 10 },
    sectionHead: { marginTop: 8, marginBottom: 12 },
    sectionLink: { color: '#ea580c', fontWeight: '700', fontSize: 14, marginTop: 4 },
    modalBox: { flex: 1, backgroundColor: 'rgba(0,0,0,0.5)', justifyContent: 'flex-end' },
    modalInner: { backgroundColor: '#fff', borderTopLeftRadius: 16, borderTopRightRadius: 16, padding: 16, maxHeight: '70%' },
    siteFooter: { backgroundColor: '#111827', padding: 20, paddingBottom: 28, marginHorizontal: -16, marginTop: 8 },
    siteFooterTitle: { color: '#fff', fontWeight: '800', fontSize: 15, marginBottom: 10 },
    siteFooterLink: { color: '#fdba74', paddingVertical: 8, fontSize: 15, fontWeight: '600' },
    siteFooterHint: { color: '#9ca3af', fontSize: 11, marginTop: 14, lineHeight: 17 },
    flGrid: { flexDirection: 'row', flexWrap: 'wrap', justifyContent: 'space-between' },
    flCard: { width: (SCREEN_W - 32 - 10) / 2, marginBottom: 14 },
    flImg: { width: '100%', height: 118, borderRadius: 12, backgroundColor: '#e5e7eb' },
    flTeklifBtn: { backgroundColor: '#fbbf24', borderRadius: 999, paddingVertical: 10, alignItems: 'center', marginTop: 8 },
    flTeklifTxt: { fontWeight: '800', fontSize: 14, color: '#111827' },
    dashStatGrid: { flexDirection: 'row', flexWrap: 'wrap', gap: 10, marginTop: 16 },
    dashStatCell: {
        width: '30%',
        minWidth: 100,
        backgroundColor: '#fff',
        borderRadius: 14,
        padding: 14,
        borderWidth: 1,
        borderColor: '#f3f4f6',
        alignItems: 'center',
    },
    dashStatNum: { fontSize: 22, fontWeight: '800', color: '#111827' },
    dashStatLbl: { fontSize: 12, color: '#6b7280', marginTop: 4, textAlign: 'center' },
    dashRow: {
        flexDirection: 'row',
        justifyContent: 'space-between',
        alignItems: 'center',
        paddingVertical: 14,
        borderBottomWidth: 1,
        borderBottomColor: '#f3f4f6',
    },
});

function SiteFooter({ navigation }) {
    const link = (path, label) => (
        <Pressable onPress={() => navigation.navigate('WebMirror', { path })}>
            <Text style={S.siteFooterLink}>{label}</Text>
        </Pressable>
    );
    return (
        <View style={S.siteFooter}>
            <Text style={S.siteFooterTitle}>Web sitesi — aynı sayfalar</Text>
            {link('/', 'Ana sayfa')}
            {link('/urunler', 'Tüm ürünler')}
            {link('/saticilar', 'Satıcılar')}
            {link('/is-ilanlari', 'İş ilanları')}
            {link('/teklif-talebi', 'Teklif talebi formu')}
            {link('/gizlilik', 'Gizlilik politikası')}
            {link('/kullanim-kosullari', 'Kullanım koşulları')}
            {link('/hakkimizda', 'Hakkımızda')}
            <Text style={S.siteFooterHint}>
                Bu bağlantılar web’deki Blade sayfalarını aynen gösterir. Giriş gerektiren işlemler için uygulama içi hesabınızı veya web’de oturum açmayı kullanın.
            </Text>
        </View>
    );
}

export function HomeScreen() {
    const navigation = useNavigation();
    const insets = useSafeAreaInsets();
    const { token } = useAuth();
    const [data, setData] = useState(null);
    const [loading, setLoading] = useState(true);
    const [refreshing, setRefreshing] = useState(false);
    const [heroQ, setHeroQ] = useState('');
    const [catModal, setCatModal] = useState(false);
    const [selCatSlug, setSelCatSlug] = useState('');
    const [selCatName, setSelCatName] = useState('');
    const [cartCount, setCartCount] = useState(0);
    const [slideIx, setSlideIx] = useState(0);

    const load = useCallback(async () => {
        try {
            const h = await api('/home');
            setData(h);
        } catch {
            setData(null);
        } finally {
            setLoading(false);
            setRefreshing(false);
        }
    }, []);

    const refreshCart = useCallback(async () => {
        if (!token) {
            setCartCount(0);
            return;
        }
        try {
            const c = await api('/cart');
            const n = (c.items || []).reduce((s, row) => s + (row.quantity || 0), 0);
            setCartCount(n);
        } catch {
            setCartCount(0);
        }
    }, [token]);

    useEffect(() => {
        load();
    }, [load]);

    useEffect(() => {
        refreshCart();
    }, [refreshCart, token]);

    useFocusEffect(
        useCallback(() => {
            refreshCart();
        }, [refreshCart]),
    );

    const runHeroSearch = () => {
        navigation.navigate('ProductList', {
            q: heroQ.trim() || undefined,
            category: selCatSlug || undefined,
        });
    };

    if (loading) {
        return (
            <View style={[S.flex, { justifyContent: 'center', alignItems: 'center' }]}>
                <ActivityIndicator size="large" color="#f97316" />
            </View>
        );
    }

    const fp = data?.featured_products || [];
    const dp = data?.digital_products || [];
    const fj = data?.freelancer_jobs || [];
    const fc = data?.featured_categories || [];
    const fcat = data?.freelancer_categories || [];
    const rootCategories = (data?.categories && data.categories.length ? data.categories : fc) || [];

    return (
        <View style={S.flex}>
            <View style={[S.homeHeader, { paddingTop: Math.max(insets.top, 10) }]}>
                <View style={{ flexDirection: 'row', alignItems: 'center', gap: 8 }}>
                    <Text style={S.homeLogo}>BaskıYeri+</Text>
                    <Pressable
                        onPress={() => navigation.navigate('WebMirror', { path: '/' })}
                        style={{ backgroundColor: '#ffedd5', paddingHorizontal: 10, paddingVertical: 6, borderRadius: 999 }}
                    >
                        <Text style={{ fontSize: 11, fontWeight: '800', color: '#9a3412' }}>Web</Text>
                    </Pressable>
                </View>
                <View style={S.homeHeaderIcons}>
                    <Pressable
                        hitSlop={10}
                        onPress={() => (token ? navigation.navigate('Favorites') : navigation.navigate('Login'))}
                    >
                        <Ionicons name="heart-outline" size={24} color="#111827" />
                    </Pressable>
                    <Pressable hitSlop={10} onPress={() => navigation.navigate('Tabs', { screen: 'Sepet' })} style={{ position: 'relative' }}>
                        <Ionicons name="bag-outline" size={24} color="#111827" />
                        {cartCount > 0 ? (
                            <View style={S.badge}>
                                <Text style={S.badgeText}>{cartCount > 99 ? '99+' : cartCount}</Text>
                            </View>
                        ) : null}
                    </Pressable>
                    <Pressable hitSlop={10} onPress={() => navigation.navigate('Tabs', { screen: 'Hesabım' })}>
                        <Ionicons name="person-outline" size={24} color="#111827" />
                    </Pressable>
                </View>
            </View>

            <ScrollView refreshControl={<RefreshControl refreshing={refreshing} onRefresh={() => { setRefreshing(true); load(); }} />}>
                <View style={{ position: 'relative', height: HERO_H }}>
                    <ScrollView
                        horizontal
                        pagingEnabled
                        showsHorizontalScrollIndicator={false}
                        onMomentumScrollEnd={(e) => {
                            const x = e.nativeEvent.contentOffset.x;
                            setSlideIx(Math.round(x / SCREEN_W));
                        }}
                    >
                        {HERO_SLIDES.map((uri, i) => (
                            <View key={i} style={{ width: SCREEN_W, height: HERO_H }}>
                                <ImageBackground source={{ uri }} style={{ width: SCREEN_W, height: HERO_H }} resizeMode="cover">
                                    <View style={S.heroGrad} />
                                </ImageBackground>
                            </View>
                        ))}
                    </ScrollView>
                    <View style={[StyleSheet.absoluteFillObject, { justifyContent: 'flex-end', zIndex: 2 }]} pointerEvents="box-none">
                        <View style={S.heroInner}>
                            <Text style={S.heroKicker}>İHTİYACIN OLAN HER ŞEY</Text>
                            <Text style={S.heroH1}>En İyi Baskı{'\n'}Kategorilerimizi Keşfet</Text>
                            <View style={S.searchPill}>
                                <Pressable style={S.searchCatBtn} onPress={() => setCatModal(true)}>
                                    <Text style={S.searchCatTxt} numberOfLines={1}>
                                        {selCatName || 'Tüm kategoriler'}
                                    </Text>
                                </Pressable>
                                <TextInput
                                    style={S.searchHeroInput}
                                    placeholder="Ürün veya kategori ara…"
                                    placeholderTextColor="#9ca3af"
                                    value={heroQ}
                                    onChangeText={setHeroQ}
                                    onSubmitEditing={runHeroSearch}
                                    returnKeyType="search"
                                />
                                <Pressable style={S.searchHeroGo} onPress={runHeroSearch}>
                                    <Ionicons name="search" size={22} color="#111827" />
                                </Pressable>
                            </View>
                            <Text style={S.heroHint}>Kartvizit, broşür, davetiye, tabela, branda ve tüm baskı işleriniz için tek adres.</Text>
                            <View style={{ flexDirection: 'row', justifyContent: 'center', gap: 6, marginTop: 10 }}>
                                {HERO_SLIDES.map((_, i) => (
                                    <View
                                        key={i}
                                        style={{
                                            width: 8,
                                            height: 8,
                                            borderRadius: 4,
                                            backgroundColor: i === slideIx ? '#fff' : 'rgba(255,255,255,0.4)',
                                        }}
                                    />
                                ))}
                            </View>
                        </View>
                    </View>
                </View>

                <View style={S.listPad}>
                    <Pressable
                        style={S.promoCard}
                        onPress={() => navigation.navigate('ProductList')}
                    >
                        <Image source={{ uri: PROMO_IMGS.a }} style={S.promoImg} resizeMode="cover" />
                        <View style={S.promoOverlay}>
                            <Text style={S.promoTag}>ÖZEL KOLEKSİYON</Text>
                            <View>
                                <Text style={S.promoTitle}>Kartvizit & Broşür</Text>
                                <View style={S.promoBtn}>
                                    <Text style={S.promoBtnText}>Hemen incele</Text>
                                </View>
                            </View>
                        </View>
                    </Pressable>

                    <View style={{ flexDirection: 'row', gap: 10 }}>
                        <Pressable style={[S.promoCard, { flex: 1, height: 176, marginBottom: 0 }]} onPress={() => navigation.navigate('ProductList')}>
                            <Image source={{ uri: PROMO_IMGS.b }} style={S.promoImg} resizeMode="cover" />
                            <View style={S.promoOverlay}>
                                <Text style={S.promoTag}>KAMPANYA</Text>
                                <View>
                                    <Text style={[S.promoTitle, { fontSize: 28 }]}>%34</Text>
                                    <Text style={{ color: '#fff', fontWeight: '600', marginBottom: 6 }}>Baskı indirimi</Text>
                                    <View style={[S.promoBtn, { backgroundColor: 'rgba(255,255,255,0.2)' }]}>
                                        <Text style={[S.promoBtnText, { color: '#fff' }]}>İncele</Text>
                                    </View>
                                </View>
                            </View>
                        </Pressable>
                        <Pressable style={[S.promoCard, { flex: 1, height: 176, marginBottom: 0 }]} onPress={() => navigation.navigate('ProductList')}>
                            <Image source={{ uri: PROMO_IMGS.c }} style={S.promoImg} resizeMode="cover" />
                            <View style={S.promoOverlay}>
                                <Text style={S.promoTag}>TASARIM</Text>
                                <View>
                                    <Text style={[S.promoTitle, { fontSize: 17 }]}>Davetiye & Katalog</Text>
                                    <View style={S.promoBtn}>
                                        <Text style={S.promoBtnText}>İncele</Text>
                                    </View>
                                </View>
                            </View>
                        </Pressable>
                    </View>

                    <Pressable
                        style={S.teklifWrap}
                        onPress={() => (token ? navigation.navigate('CreateQuoteRequest') : navigation.navigate('Login'))}
                    >
                        <Image source={{ uri: PROMO_IMGS.teklif }} style={S.teklifImg} resizeMode="cover" />
                        <View style={S.teklifBody}>
                            <Text style={S.teklifTag}>TABELA · FOLYO · BRANDA</Text>
                            <Text style={S.teklifH}>İhtiyacınız olan işe teklif alın</Text>
                            <Text style={S.teklifSub}>Ne istediğinizi yazın; satıcılar size teklif versin. (Giriş sonrası oluşturma)</Text>
                            <View style={S.teklifBtn}>
                                <Text style={S.teklifBtnTxt}>Teklif al →</Text>
                            </View>
                        </View>
                    </Pressable>

                    <View style={[S.horiz, { marginBottom: 12 }]}>
                        <Pressable style={[S.chip, S.chipOn]} onPress={() => navigation.navigate('Vendors')}>
                            <Text>Satıcılar</Text>
                        </Pressable>
                        <Pressable style={[S.chip, S.chipOn]} onPress={() => navigation.navigate('FreelancerJobs')}>
                            <Text>İş ilanları</Text>
                        </Pressable>
                        <Pressable style={S.chip} onPress={() => navigation.navigate('Tabs', { screen: 'Kategoriler' })}>
                            <Text>Kategoriler</Text>
                        </Pressable>
                    </View>

                    <View style={{ flexDirection: 'row', justifyContent: 'space-between', alignItems: 'flex-end' }}>
                        <View style={S.sectionHead}>
                            <Text style={S.sectionTitle}>Öne çıkan ürünler</Text>
                        </View>
                        <Pressable onPress={() => navigation.navigate('ProductList')}>
                            <Text style={S.sectionLink}>Tümünü gör →</Text>
                        </Pressable>
                    </View>
                    <View style={S.grid2}>
                        {fp.map((item) => (
                            <Pressable key={item.id} style={S.prodTile} onPress={() => navigation.navigate('ProductDetail', { slug: item.slug })}>
                                <Image source={{ uri: productImageUrl(item) }} style={S.prodTileImg} resizeMode="cover" />
                                <View style={S.prodTileBody}>
                                    <Text style={{ fontSize: 13, fontWeight: '700', color: '#111827' }} numberOfLines={2}>
                                        {item.name}
                                    </Text>
                                    <Text style={{ fontSize: 14, fontWeight: '800', color: '#ea580c', marginTop: 4 }}>
                                        ₺{Number(item.price).toFixed(2)}
                                    </Text>
                                </View>
                            </Pressable>
                        ))}
                    </View>
                    {fp.length === 0 ? <Text style={S.muted}>Öne çıkan ürün yok.</Text> : null}

                    <View style={{ flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', marginTop: 8 }}>
                        <Text style={S.sectionTitle}>Dijital ürünler</Text>
                        <Pressable onPress={() => navigation.navigate('ProductList', { type: 'digital' })}>
                            <Text style={S.sectionLink}>Tümünü gör →</Text>
                        </Pressable>
                    </View>
                    <Text style={[S.hint, { marginBottom: 12 }]}>Şablonlar, grafik paketleri, dijital dosyalar.</Text>
                    <View style={S.grid2}>
                        {dp.map((item) => (
                            <Pressable key={item.id} style={S.prodTile} onPress={() => navigation.navigate('ProductDetail', { slug: item.slug })}>
                                <Image source={{ uri: productImageUrl(item) }} style={S.prodTileImg} resizeMode="cover" />
                                <View style={S.prodTileBody}>
                                    <Text style={{ fontSize: 10, fontWeight: '800', color: '#92400e', marginBottom: 4 }}>DİJİTAL</Text>
                                    <Text style={{ fontSize: 13, fontWeight: '700', color: '#111827' }} numberOfLines={2}>
                                        {item.name}
                                    </Text>
                                    <Text style={{ fontSize: 13, color: '#6b7280', marginTop: 2 }} numberOfLines={1}>
                                        {item.vendor?.name}
                                    </Text>
                                    <Text style={{ fontSize: 14, fontWeight: '800', color: '#ea580c', marginTop: 4 }}>
                                        ₺{Number(item.price).toFixed(2)}
                                    </Text>
                                </View>
                            </Pressable>
                        ))}
                    </View>

                    <Text style={[S.sectionTitle, { marginTop: 16 }]}>İş yapanlar & Freelancer</Text>
                    <Text style={[S.hint, { marginBottom: 12 }]}>Tasarım, baskı, web, tabela — ihtiyacınız olan işi seçin, teklif alın.</Text>
                    <View style={S.flGrid}>
                        {fcat.map((c) => (
                            <View key={c.key} style={S.flCard}>
                                <Pressable onPress={() => navigation.navigate('FreelancerJobs', { category: c.key })}>
                                    <Image
                                        source={{ uri: `https://picsum.photos/600/450?random=${c.seed}` }}
                                        style={S.flImg}
                                        resizeMode="cover"
                                    />
                                    <Text style={{ fontWeight: '700', marginTop: 8, fontSize: 14, color: '#111827' }}>{c.label}</Text>
                                    <Text style={{ color: '#6b7280', fontSize: 12, marginTop: 2 }}>{c.count} açık ilan</Text>
                                </Pressable>
                                <Pressable
                                    style={S.flTeklifBtn}
                                    onPress={() => (token ? navigation.navigate('CreateQuoteRequest') : navigation.navigate('Login'))}
                                >
                                    <Text style={S.flTeklifTxt}>Teklif al</Text>
                                </Pressable>
                            </View>
                        ))}
                    </View>

                    <View style={{ flexDirection: 'row', justifyContent: 'space-between', marginTop: 8 }}>
                        <Text style={S.sectionTitle}>Son eklenen ilanlar</Text>
                        <Pressable onPress={() => navigation.navigate('FreelancerJobs')}>
                            <Text style={S.sectionLink}>Tüm ilanlar →</Text>
                        </Pressable>
                    </View>
                    {fj.map((job) => (
                        <Pressable key={job.id} style={S.card} onPress={() => navigation.navigate('FreelancerJobDetail', { jobId: job.id })}>
                            <Text style={S.pName}>{job.title}</Text>
                            <Text style={S.pMeta}>{job.category} · {job.user?.name}</Text>
                        </Pressable>
                    ))}

                    <Text style={[S.sectionTitle, { marginTop: 20 }]}>Kategoriler</Text>
                    <Text style={[S.hint, { marginBottom: 12 }]}>Baskı ve reklam ürünlerinde aradığınız kategoriye göz atın.</Text>
                    <View style={S.grid2}>
                        {(data?.categories || []).map((cat) => (
                            <Pressable
                                key={cat.id}
                                style={S.prodTile}
                                onPress={() => navigation.navigate('ProductList', { category: cat.slug })}
                            >
                                <Image
                                    source={{ uri: `https://picsum.photos/400/300?random=kategori${cat.id}` }}
                                    style={S.prodTileImg}
                                    resizeMode="cover"
                                />
                                <View style={[S.prodTileBody, { alignItems: 'center' }]}>
                                    <Text style={{ fontSize: 14, fontWeight: '700', color: '#111827', textAlign: 'center' }}>{cat.name}</Text>
                                </View>
                            </Pressable>
                        ))}
                    </View>

                    <View style={{ flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', marginTop: 16 }}>
                        <Text style={S.sectionTitle}>Yeni koleksiyon ürünleri</Text>
                        <Pressable onPress={() => navigation.navigate('ProductList')}>
                            <Text style={S.sectionLink}>Tüm ürünleri gör →</Text>
                        </Pressable>
                    </View>
                    <Text style={[S.hint, { marginBottom: 12 }]}>En güncel baskı ve reklam ürünleri (web ana sayfa ile aynı liste).</Text>
                    <View style={S.grid2}>
                        {fp.map((item) => (
                            <Pressable key={`yc-${item.id}`} style={S.prodTile} onPress={() => navigation.navigate('ProductDetail', { slug: item.slug })}>
                                <Image source={{ uri: productImageUrl(item) }} style={S.prodTileImg} resizeMode="cover" />
                                <View style={S.prodTileBody}>
                                    <Text style={{ fontSize: 12, color: '#6b7280' }} numberOfLines={1}>
                                        {item.vendor?.name || 'Satıcı'}
                                    </Text>
                                    <Text style={{ fontSize: 13, fontWeight: '700', color: '#111827', marginTop: 4 }} numberOfLines={2}>
                                        {item.name}
                                    </Text>
                                    <View style={{ flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', marginTop: 8 }}>
                                        <Text style={{ fontSize: 15, fontWeight: '800', color: '#ea580c' }}>₺{Number(item.price).toFixed(2)}</Text>
                                        <Text style={{ fontSize: 11, color: '#9ca3af' }}>Sepete ekle</Text>
                                    </View>
                                </View>
                            </Pressable>
                        ))}
                    </View>

                    <SiteFooter navigation={navigation} />
                </View>
            </ScrollView>

            <Modal visible={catModal} transparent animationType="slide" onRequestClose={() => setCatModal(false)}>
                <View style={S.modalBox}>
                    <Pressable style={StyleSheet.absoluteFillObject} onPress={() => setCatModal(false)} />
                    <View style={S.modalInner}>
                        <Text style={{ fontWeight: '800', fontSize: 18, marginBottom: 12 }}>Kategori seç</Text>
                        <FlatList
                            data={[{ id: 'all', name: 'Tüm kategoriler', slug: '' }, ...rootCategories]}
                            keyExtractor={(it) => String(it.id)}
                            renderItem={({ item }) => (
                                <Pressable
                                    style={{ paddingVertical: 14, borderBottomWidth: 1, borderBottomColor: '#f3f4f6' }}
                                    onPress={() => {
                                        setSelCatSlug(item.slug || '');
                                        setSelCatName(item.slug ? item.name : '');
                                        setCatModal(false);
                                    }}
                                >
                                    <Text style={{ fontSize: 16 }}>{item.name}</Text>
                                </Pressable>
                            )}
                        />
                    </View>
                </View>
            </Modal>
        </View>
    );
}

export function ProductDetailScreen() {
    const navigation = useNavigation();
    const route = useRoute();
    const { slug } = route.params || {};
    const { user, token } = useAuth();
    const [p, setP] = useState(null);
    const [loading, setLoading] = useState(true);
    const [adding, setAdding] = useState(false);
    const [favBusy, setFavBusy] = useState(false);
    const [msg, setMsg] = useState('');

    useEffect(() => {
        if (!slug) return;
        (async () => {
            try {
                const data = await api(`/products/${encodeURIComponent(slug)}`);
                setP(data);
            } catch {
                setP(null);
            } finally {
                setLoading(false);
            }
        })();
    }, [slug]);

    const addCart = async () => {
        if (!token || !user) {
            navigation.navigate('Login');
            return;
        }
        setAdding(true);
        setMsg('');
        try {
            await api('/cart/items', {
                method: 'POST',
                body: JSON.stringify({ product_id: p.id, quantity: 1 }),
            });
            setMsg('Sepete eklendi');
        } catch (e) {
            setMsg(e.message || 'Hata');
        } finally {
            setAdding(false);
        }
    };

    const toggleFav = async () => {
        if (!token || !user) {
            navigation.navigate('Login');
            return;
        }
        setFavBusy(true);
        try {
            await api(`/favorites/toggle/${p.id}`, { method: 'POST' });
            setMsg('Favoriler güncellendi');
            setTimeout(() => setMsg(''), 2000);
        } catch (e) {
            setMsg(e.message || 'Hata');
        } finally {
            setFavBusy(false);
        }
    };

    if (loading) {
        return (
            <View style={[S.flex, { justifyContent: 'center', alignItems: 'center' }]}>
                <ActivityIndicator size="large" color="#f97316" />
            </View>
        );
    }
    if (!p) {
        return (
            <View style={S.listPad}>
                <Text style={S.muted}>Ürün bulunamadı.</Text>
            </View>
        );
    }

    return (
        <ScrollView style={S.flex}>
            <View style={S.topBar}>
                <Pressable onPress={() => navigation.goBack()}>
                    <Text style={{ color: '#ea580c', fontWeight: '600' }}>← Geri</Text>
                </Pressable>
            </View>
            <View style={S.listPad}>
                {msg ? <Text style={{ color: '#15803d', marginBottom: 8 }}>{msg}</Text> : null}
                <Text style={{ fontSize: 22, fontWeight: '800', color: '#111827' }}>{p.name}</Text>
                <Text style={[S.pPrice, { fontSize: 22 }]}>₺{Number(p.price).toFixed(2)}</Text>
                <Text style={S.pMeta}>{p.vendor?.name}</Text>
                {p.category ? <Text style={S.pMeta}>Kategori: {p.category.name}</Text> : null}
                {p.description ? <Text style={[S.hint, { marginTop: 12 }]}>{p.description}</Text> : null}
                <Pressable style={S.btn} onPress={addCart} disabled={adding}>
                    {adding ? <ActivityIndicator color="#111" /> : <Text style={S.btnText}>{token ? 'Sepete ekle' : 'Sepete eklemek için giriş'}</Text>}
                </Pressable>
                {token ? (
                    <Pressable style={S.btnOutline} onPress={toggleFav} disabled={favBusy}>
                        <Text style={{ fontWeight: '600' }}>Favorilere ekle / çıkar</Text>
                    </Pressable>
                ) : null}
                {p.vendor?.slug ? (
                    <Pressable style={{ marginTop: 16 }} onPress={() => navigation.navigate('VendorDetail', { slug: p.vendor.slug })}>
                        <Text style={{ color: '#ea580c', fontWeight: '600' }}>Satıcı mağazası →</Text>
                    </Pressable>
                ) : null}
            </View>
        </ScrollView>
    );
}

export function VendorsListScreen() {
    const navigation = useNavigation();
    const [rows, setRows] = useState([]);
    const [loading, setLoading] = useState(true);
    const [refreshing, setRefreshing] = useState(false);

    const load = useCallback(async () => {
        try {
            const res = await api('/vendors');
            setRows(res.data || []);
        } catch {
            setRows([]);
        } finally {
            setLoading(false);
            setRefreshing(false);
        }
    }, []);

    useEffect(() => {
        load();
    }, [load]);

    return (
        <View style={S.flex}>
            <View style={S.topBar}>
                <Pressable onPress={() => navigation.goBack()}>
                    <Text style={{ color: '#ea580c', fontWeight: '600' }}>← Geri</Text>
                </Pressable>
                <Text style={S.brand}>Satıcılar</Text>
                <View style={{ width: 48 }} />
            </View>
            {loading ? (
                <ActivityIndicator style={{ marginTop: 24 }} color="#f97316" />
            ) : (
                <FlatList
                    data={rows}
                    keyExtractor={(item) => String(item.id)}
                    contentContainerStyle={S.listPad}
                    refreshControl={<RefreshControl refreshing={refreshing} onRefresh={() => { setRefreshing(true); load(); }} />}
                    renderItem={({ item }) => (
                        <Pressable style={S.card} onPress={() => navigation.navigate('VendorDetail', { slug: item.slug })}>
                            <Text style={S.pName}>{item.name}</Text>
                            <Text style={S.pMeta}>{item.city || '—'}</Text>
                        </Pressable>
                    )}
                    ListEmptyComponent={<Text style={S.muted}>Satıcı yok.</Text>}
                />
            )}
        </View>
    );
}

export function VendorDetailScreen() {
    const navigation = useNavigation();
    const route = useRoute();
    const { slug } = route.params || {};
    const [v, setV] = useState(null);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        if (!slug) return;
        (async () => {
            try {
                const data = await api(`/vendors/${encodeURIComponent(slug)}`);
                setV(data);
            } catch {
                setV(null);
            } finally {
                setLoading(false);
            }
        })();
    }, [slug]);

    if (loading) {
        return (
            <View style={[S.flex, { justifyContent: 'center', alignItems: 'center' }]}>
                <ActivityIndicator size="large" color="#f97316" />
            </View>
        );
    }
    if (!v) {
        return (
            <View style={S.listPad}>
                <Text style={S.muted}>Mağaza bulunamadı.</Text>
            </View>
        );
    }

    const products = v.products || [];

    return (
        <View style={S.flex}>
            <View style={S.topBar}>
                <Pressable onPress={() => navigation.goBack()}>
                    <Text style={{ color: '#ea580c', fontWeight: '600' }}>← Geri</Text>
                </Pressable>
            </View>
            <FlatList
                ListHeaderComponent={
                    <View style={S.listPad}>
                        <Text style={{ fontSize: 22, fontWeight: '800' }}>{v.name}</Text>
                        <Text style={S.hint}>{v.description || ''}</Text>
                        <Text style={[S.sectionTitle, { marginTop: 16 }]}>Ürünler</Text>
                    </View>
                }
                data={products}
                keyExtractor={(item) => String(item.id)}
                contentContainerStyle={{ paddingHorizontal: 16, paddingBottom: 32 }}
                renderItem={({ item }) => (
                    <Pressable style={S.card} onPress={() => navigation.navigate('ProductDetail', { slug: item.slug })}>
                        <Text style={S.pName}>{item.name}</Text>
                        <Text style={S.pPrice}>₺{Number(item.price).toFixed(2)}</Text>
                    </Pressable>
                )}
                ListEmptyComponent={<Text style={S.muted}>Ürün yok.</Text>}
            />
        </View>
    );
}

export function FreelancerJobsScreen() {
    const navigation = useNavigation();
    const route = useRoute();
    const initialCat = route.params?.category;
    const [jobs, setJobs] = useState([]);
    const [loading, setLoading] = useState(true);
    const [refreshing, setRefreshing] = useState(false);
    const [cat, setCat] = useState(initialCat || '');

    useEffect(() => {
        if (route.params?.category) setCat(route.params.category);
    }, [route.params?.category]);

    const load = useCallback(async () => {
        try {
            const q = cat ? `?category=${encodeURIComponent(cat)}` : '';
            const res = await api(`/freelancer-jobs${q}`);
            setJobs(res.data || []);
        } catch {
            setJobs([]);
        } finally {
            setLoading(false);
            setRefreshing(false);
        }
    }, [cat]);

    useEffect(() => {
        load();
    }, [load]);

    const cats = [
        { key: '', label: 'Tümü' },
        { key: 'logo', label: 'Logo' },
        { key: 'brochure', label: 'Broşür' },
        { key: 'digital', label: 'Dijital' },
        { key: 'wordpress', label: 'WordPress' },
        { key: 'other', label: 'Diğer' },
    ];

    return (
        <View style={S.flex}>
            <View style={S.topBar}>
                <Pressable onPress={() => navigation.goBack()}>
                    <Text style={{ color: '#ea580c', fontWeight: '600' }}>← Geri</Text>
                </Pressable>
                <Text style={S.brand}>İş ilanları</Text>
                <Pressable onPress={() => navigation.navigate('CreateFreelancerJob')}>
                    <Text style={{ color: '#ea580c', fontWeight: '600' }}>+ İlan</Text>
                </Pressable>
            </View>
            <ScrollView horizontal showsHorizontalScrollIndicator={false} style={{ backgroundColor: '#fff', maxHeight: 48, borderBottomWidth: 1, borderBottomColor: '#e5e7eb' }} contentContainerStyle={{ paddingHorizontal: 12, paddingVertical: 10, alignItems: 'center' }}>
                {cats.map((c) => (
                    <Pressable key={c.key || 'all'} style={[S.chip, cat === c.key && { backgroundColor: '#ffedd5', borderColor: '#f97316' }]} onPress={() => setCat(c.key)}>
                        <Text style={{ fontSize: 13 }}>{c.label}</Text>
                    </Pressable>
                ))}
            </ScrollView>
            {loading ? (
                <ActivityIndicator style={{ marginTop: 24 }} color="#f97316" />
            ) : (
                <FlatList
                    data={jobs}
                    keyExtractor={(item) => String(item.id)}
                    contentContainerStyle={S.listPad}
                    refreshControl={<RefreshControl refreshing={refreshing} onRefresh={() => { setRefreshing(true); load(); }} />}
                    renderItem={({ item }) => (
                        <Pressable style={S.card} onPress={() => navigation.navigate('FreelancerJobDetail', { jobId: item.id })}>
                            <Text style={S.pName}>{item.title}</Text>
                            <Text style={S.pMeta}>
                                {item.category} · {item.user?.name}
                            </Text>
                        </Pressable>
                    )}
                    ListEmptyComponent={<Text style={S.muted}>İlan yok.</Text>}
                />
            )}
        </View>
    );
}

export function FreelancerJobDetailScreen() {
    const navigation = useNavigation();
    const route = useRoute();
    const { jobId } = route.params || {};
    const { user, token } = useAuth();
    const [job, setJob] = useState(null);
    const [loading, setLoading] = useState(true);
    const [amount, setAmount] = useState('');
    const [proposal, setProposal] = useState('');
    const [busy, setBusy] = useState(false);
    const [msg, setMsg] = useState('');

    const load = useCallback(async () => {
        if (!jobId) return;
        try {
            const data = await api(`/freelancer-jobs/${jobId}`);
            setJob(data);
        } catch {
            setJob(null);
        } finally {
            setLoading(false);
        }
    }, [jobId]);

    useEffect(() => {
        load();
    }, [load]);

    const submitBid = async () => {
        if (!token) {
            navigation.navigate('Login');
            return;
        }
        setBusy(true);
        setMsg('');
        try {
            await api(`/freelancer-jobs/${jobId}/bids`, {
                method: 'POST',
                body: JSON.stringify({
                    amount: parseFloat(String(amount).replace(',', '.')) || 0,
                    proposal,
                }),
            });
            setMsg('Teklif gönderildi');
            load();
        } catch (e) {
            setMsg(e.message || 'Hata');
        } finally {
            setBusy(false);
        }
    };

    const selectBid = async (bidId) => {
        setBusy(true);
        try {
            await api(`/freelancer-jobs/${jobId}/bids/${bidId}/select`, { method: 'POST' });
            setMsg('Teklif seçildi');
            load();
        } catch (e) {
            setMsg(e.message || 'Hata');
        } finally {
            setBusy(false);
        }
    };

    if (loading) {
        return (
            <View style={[S.flex, { justifyContent: 'center', alignItems: 'center' }]}>
                <ActivityIndicator size="large" color="#f97316" />
            </View>
        );
    }
    if (!job) {
        return (
            <View style={S.listPad}>
                <Text style={S.muted}>İlan bulunamadı.</Text>
            </View>
        );
    }

    const isOwner = user?.id === job.user_id;
    const bids = job.bids || [];

    return (
        <ScrollView style={S.flex}>
            <View style={S.topBar}>
                <Pressable onPress={() => navigation.goBack()}>
                    <Text style={{ color: '#ea580c', fontWeight: '600' }}>← Geri</Text>
                </Pressable>
            </View>
            <View style={S.listPad}>
                {msg ? <Text style={{ marginBottom: 8, color: '#15803d' }}>{msg}</Text> : null}
                <Text style={{ fontSize: 20, fontWeight: '800' }}>{job.title}</Text>
                <Text style={S.pMeta}>{job.category}</Text>
                <Text style={[S.hint, { marginTop: 12 }]}>{job.description || '—'}</Text>
                <Text style={S.pMeta}>İlan sahibi: {job.user?.name}</Text>

                {isOwner && job.status === 'open' ? (
                    <>
                        <Text style={[S.sectionTitle, { marginTop: 20 }]}>Gelen teklifler</Text>
                        {bids.map((b) => (
                            <View key={b.id} style={S.card}>
                                <Text style={S.pName}>
                                    {b.user?.name} — ₺{Number(b.amount).toFixed(2)}
                                </Text>
                                <Text style={S.pMeta}>{b.proposal || '—'}</Text>
                                {b.status === 'pending' ? (
                                    <Pressable style={S.btnSm} onPress={() => selectBid(b.id)} disabled={busy}>
                                        <Text style={S.btnSmText}>Bu teklifi seç</Text>
                                    </Pressable>
                                ) : (
                                    <Text style={S.pMeta}>{b.status}</Text>
                                )}
                            </View>
                        ))}
                        {bids.length === 0 ? <Text style={S.hint}>Henüz teklif yok.</Text> : null}
                    </>
                ) : null}

                {!isOwner && job.status === 'open' && token ? (
                    <View style={{ marginTop: 20 }}>
                        <Text style={S.sectionTitle}>Teklif ver</Text>
                        <TextInput style={S.input} placeholder="Tutar (₺)" keyboardType="decimal-pad" value={amount} onChangeText={setAmount} />
                        <TextInput style={[S.input, { minHeight: 80 }]} placeholder="Öneri metni" multiline value={proposal} onChangeText={setProposal} />
                        <Pressable style={S.btn} onPress={submitBid} disabled={busy}>
                            {busy ? <ActivityIndicator color="#111" /> : <Text style={S.btnText}>Teklif gönder</Text>}
                        </Pressable>
                    </View>
                ) : null}

                {!isOwner && !token && job.status === 'open' ? (
                    <Pressable style={[S.btn, { marginTop: 20 }]} onPress={() => navigation.navigate('Login')}>
                        <Text style={S.btnText}>Teklif vermek için giriş</Text>
                    </Pressable>
                ) : null}
            </View>
        </ScrollView>
    );
}

export function StaticPageScreen() {
    const navigation = useNavigation();
    const route = useRoute();
    const { slug } = route.params || {};
    const [page, setPage] = useState(null);

    useEffect(() => {
        if (!slug) return;
        (async () => {
            try {
                const data = await api(`/pages/${slug}`);
                setPage(data);
            } catch {
                setPage(null);
            }
        })();
    }, [slug]);

    return (
        <ScrollView style={S.flex}>
            <View style={S.topBar}>
                <Pressable onPress={() => navigation.goBack()}>
                    <Text style={{ color: '#ea580c', fontWeight: '600' }}>← Geri</Text>
                </Pressable>
            </View>
            <View style={S.listPad}>
                {page ? (
                    <>
                        <Text style={{ fontSize: 22, fontWeight: '800', marginBottom: 16 }}>{page.title}</Text>
                        {(page.paragraphs || []).map((para, i) => (
                            <Text key={i} style={[S.hint, { marginBottom: 12 }]}>
                                {para}
                            </Text>
                        ))}
                    </>
                ) : (
                    <Text style={S.muted}>Yüklenemedi.</Text>
                )}
            </View>
        </ScrollView>
    );
}

export function FavoritesScreen() {
    const navigation = useNavigation();
    const { token } = useAuth();
    const [rows, setRows] = useState([]);
    const [loading, setLoading] = useState(true);

    const load = useCallback(async () => {
        try {
            const res = await api('/favorites');
            setRows(res.data || []);
        } catch {
            setRows([]);
        } finally {
            setLoading(false);
        }
    }, []);

    useEffect(() => {
        if (token) load();
        else setLoading(false);
    }, [load, token]);

    if (!token) {
        return (
            <View style={S.flex}>
                <View style={S.topBar}>
                    <Pressable onPress={() => navigation.goBack()}>
                        <Text style={{ color: '#ea580c', fontWeight: '600' }}>← Geri</Text>
                    </Pressable>
                    <Text style={S.brand}>Favorilerim</Text>
                </View>
                <View style={S.listPad}>
                    <Text style={S.hint}>Favoriler için giriş yapın.</Text>
                    <Pressable style={S.btn} onPress={() => navigation.navigate('Login')}>
                        <Text style={S.btnText}>Giriş yap</Text>
                    </Pressable>
                </View>
            </View>
        );
    }

    return (
        <View style={S.flex}>
            <View style={S.topBar}>
                <Pressable onPress={() => navigation.goBack()}>
                    <Text style={{ color: '#ea580c', fontWeight: '600' }}>← Geri</Text>
                </Pressable>
                <Text style={S.brand}>Favorilerim</Text>
                <View style={{ width: 48 }} />
            </View>
            {loading ? (
                <ActivityIndicator style={{ marginTop: 24 }} color="#f97316" />
            ) : (
                <FlatList
                    data={rows}
                    keyExtractor={(item) => String(item.id)}
                    contentContainerStyle={S.listPad}
                    renderItem={({ item }) => (
                        <Pressable style={S.card} onPress={() => navigation.navigate('ProductDetail', { slug: item.slug })}>
                            <Text style={S.pName}>{item.name}</Text>
                            <Text style={S.pPrice}>₺{Number(item.price).toFixed(2)}</Text>
                        </Pressable>
                    )}
                    ListEmptyComponent={<Text style={S.muted}>Favori yok.</Text>}
                />
            )}
        </View>
    );
}

export function AddressesScreen() {
    const navigation = useNavigation();
    const { token } = useAuth();
    const [list, setList] = useState([]);
    const [loading, setLoading] = useState(true);

    const load = useCallback(async () => {
        try {
            const data = await api('/addresses');
            setList(Array.isArray(data) ? data : []);
        } catch {
            setList([]);
        } finally {
            setLoading(false);
        }
    }, []);

    useEffect(() => {
        if (token) load();
        else setLoading(false);
    }, [load, token]);

    if (!token) {
        return (
            <View style={S.flex}>
                <View style={S.topBar}>
                    <Pressable onPress={() => navigation.goBack()}>
                        <Text style={{ color: '#ea580c', fontWeight: '600' }}>← Geri</Text>
                    </Pressable>
                    <Text style={S.brand}>Adreslerim</Text>
                </View>
                <View style={S.listPad}>
                    <Pressable style={S.btn} onPress={() => navigation.navigate('Login')}>
                        <Text style={S.btnText}>Giriş yap</Text>
                    </Pressable>
                </View>
            </View>
        );
    }

    return (
        <View style={S.flex}>
            <View style={S.topBar}>
                <Pressable onPress={() => navigation.goBack()}>
                    <Text style={{ color: '#ea580c', fontWeight: '600' }}>← Geri</Text>
                </Pressable>
                <Text style={S.brand}>Adreslerim</Text>
                <View style={{ width: 48 }} />
            </View>
            {loading ? (
                <ActivityIndicator style={{ marginTop: 24 }} color="#f97316" />
            ) : (
                <FlatList
                    data={list}
                    keyExtractor={(item) => String(item.id)}
                    contentContainerStyle={S.listPad}
                    ListHeaderComponent={<Text style={[S.hint, { marginBottom: 12 }]}>Tam adres yönetimi web hesabınızda da mevcuttur.</Text>}
                    renderItem={({ item }) => (
                        <View style={S.card}>
                            <Text style={S.pName}>{item.label} — {item.full_name}</Text>
                            <Text style={S.pMeta}>{item.city} {item.line1}</Text>
                        </View>
                    )}
                    ListEmptyComponent={<Text style={S.muted}>Kayıtlı adres yok.</Text>}
                />
            )}
        </View>
    );
}

export function QuoteRequestsScreen() {
    const navigation = useNavigation();
    const { token } = useAuth();
    const [rows, setRows] = useState([]);
    const [loading, setLoading] = useState(true);

    const load = useCallback(async () => {
        try {
            const res = await api('/quote-requests');
            setRows(res.data || []);
        } catch {
            setRows([]);
        } finally {
            setLoading(false);
        }
    }, []);

    useEffect(() => {
        if (token) load();
        else setLoading(false);
    }, [load, token]);

    if (!token) {
        return (
            <View style={S.flex}>
                <View style={S.topBar}>
                    <Pressable onPress={() => navigation.goBack()}>
                        <Text style={{ color: '#ea580c', fontWeight: '600' }}>← Geri</Text>
                    </Pressable>
                    <Text style={S.brand}>Teklif taleplerim</Text>
                </View>
                <View style={S.listPad}>
                    <Pressable style={S.btn} onPress={() => navigation.navigate('Login')}>
                        <Text style={S.btnText}>Giriş yap</Text>
                    </Pressable>
                </View>
            </View>
        );
    }

    return (
        <View style={S.flex}>
            <View style={S.topBar}>
                <Pressable onPress={() => navigation.goBack()}>
                    <Text style={{ color: '#ea580c', fontWeight: '600' }}>← Geri</Text>
                </Pressable>
                <Text style={S.brand}>Teklif taleplerim</Text>
                <Pressable onPress={() => navigation.navigate('CreateQuoteRequest')}>
                    <Text style={{ color: '#ea580c', fontWeight: '600' }}>+</Text>
                </Pressable>
            </View>
            {loading ? (
                <ActivityIndicator style={{ marginTop: 24 }} color="#f97316" />
            ) : (
                <FlatList
                    data={rows}
                    keyExtractor={(item) => String(item.id)}
                    contentContainerStyle={S.listPad}
                    renderItem={({ item }) => (
                        <Pressable style={S.card} onPress={() => navigation.navigate('QuoteRequestDetail', { id: item.id })}>
                            <Text style={S.pName}>{item.title}</Text>
                            <Text style={S.pMeta}>{item.status}</Text>
                        </Pressable>
                    )}
                    ListEmptyComponent={<Text style={S.muted}>Talep yok.</Text>}
                />
            )}
        </View>
    );
}

export function QuoteRequestDetailScreen() {
    const navigation = useNavigation();
    const route = useRoute();
    const { id } = route.params || {};
    const [qr, setQr] = useState(null);
    const [loading, setLoading] = useState(true);
    const [busy, setBusy] = useState(false);
    const [msg, setMsg] = useState('');

    const load = useCallback(async () => {
        if (!id) return;
        try {
            const data = await api(`/quote-requests/${id}`);
            setQr(data);
        } catch {
            setQr(null);
        } finally {
            setLoading(false);
        }
    }, [id]);

    useEffect(() => {
        load();
    }, [load]);

    const selectQuote = async (quoteId) => {
        setBusy(true);
        setMsg('');
        try {
            await api(`/quote-requests/${id}/quotes/${quoteId}/select`, { method: 'POST' });
            setMsg('Teklif seçildi');
            load();
        } catch (e) {
            setMsg(e.message || 'Hata');
        } finally {
            setBusy(false);
        }
    };

    if (loading || !qr) {
        return (
            <View style={[S.flex, { justifyContent: 'center', alignItems: 'center' }]}>
                <ActivityIndicator size="large" color="#f97316" />
            </View>
        );
    }

    const quotes = qr.quotes || [];

    return (
        <ScrollView style={S.flex}>
            <View style={S.topBar}>
                <Pressable onPress={() => navigation.goBack()}>
                    <Text style={{ color: '#ea580c', fontWeight: '600' }}>← Geri</Text>
                </Pressable>
            </View>
            <View style={S.listPad}>
                {msg ? <Text style={{ color: '#15803d', marginBottom: 8 }}>{msg}</Text> : null}
                <Text style={{ fontSize: 20, fontWeight: '800' }}>{qr.title}</Text>
                <Text style={S.pMeta}>{qr.status}</Text>
                <Text style={[S.hint, { marginTop: 12 }]}>{qr.description || '—'}</Text>
                <Text style={[S.sectionTitle, { marginTop: 16 }]}>Gelen teklifler</Text>
                {quotes.map((q) => (
                    <View key={q.id} style={S.card}>
                        <Text style={S.pName}>{q.vendor?.name || 'Satıcı'}</Text>
                        <Text style={S.pPrice}>₺{Number(q.amount).toFixed(2)}</Text>
                        <Text style={S.pMeta}>{q.status}</Text>
                        {q.status === 'pending' && qr.status === 'open' ? (
                            <Pressable style={S.btnSm} onPress={() => selectQuote(q.id)} disabled={busy}>
                                <Text style={S.btnSmText}>Seç</Text>
                            </Pressable>
                        ) : null}
                    </View>
                ))}
            </View>
        </ScrollView>
    );
}

export function MyFreelancerJobsScreen() {
    const navigation = useNavigation();
    const { token } = useAuth();
    const [rows, setRows] = useState([]);
    const [loading, setLoading] = useState(true);

    const load = useCallback(async () => {
        try {
            const res = await api('/freelancer-jobs/mine');
            setRows(res.data || []);
        } catch {
            setRows([]);
        } finally {
            setLoading(false);
        }
    }, []);

    useEffect(() => {
        if (token) load();
        else setLoading(false);
    }, [load, token]);

    if (!token) {
        return (
            <View style={S.flex}>
                <View style={S.topBar}>
                    <Pressable onPress={() => navigation.goBack()}>
                        <Text style={{ color: '#ea580c', fontWeight: '600' }}>← Geri</Text>
                    </Pressable>
                    <Text style={S.brand}>İlanlarım</Text>
                </View>
                <View style={S.listPad}>
                    <Pressable style={S.btn} onPress={() => navigation.navigate('Login')}>
                        <Text style={S.btnText}>Giriş yap</Text>
                    </Pressable>
                </View>
            </View>
        );
    }

    return (
        <View style={S.flex}>
            <View style={S.topBar}>
                <Pressable onPress={() => navigation.goBack()}>
                    <Text style={{ color: '#ea580c', fontWeight: '600' }}>← Geri</Text>
                </Pressable>
                <Text style={S.brand}>İlanlarım</Text>
                <Pressable onPress={() => navigation.navigate('CreateFreelancerJob')}>
                    <Text style={{ color: '#ea580c', fontWeight: '600' }}>+</Text>
                </Pressable>
            </View>
            {loading ? (
                <ActivityIndicator style={{ marginTop: 24 }} color="#f97316" />
            ) : (
                <FlatList
                    data={rows}
                    keyExtractor={(item) => String(item.id)}
                    contentContainerStyle={S.listPad}
                    renderItem={({ item }) => (
                        <Pressable style={S.card} onPress={() => navigation.navigate('FreelancerJobDetail', { jobId: item.id })}>
                            <Text style={S.pName}>{item.title}</Text>
                            <Text style={S.pMeta}>{item.status}</Text>
                        </Pressable>
                    )}
                    ListEmptyComponent={<Text style={S.muted}>İlan yok.</Text>}
                />
            )}
        </View>
    );
}

export function OrderDetailScreen() {
    const navigation = useNavigation();
    const route = useRoute();
    const { orderId } = route.params || {};
    const [o, setO] = useState(null);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        if (!orderId) return;
        (async () => {
            try {
                const data = await api(`/orders/${orderId}`);
                setO(data);
            } catch {
                setO(null);
            } finally {
                setLoading(false);
            }
        })();
    }, [orderId]);

    if (loading) {
        return (
            <View style={[S.flex, { justifyContent: 'center', alignItems: 'center' }]}>
                <ActivityIndicator size="large" color="#f97316" />
            </View>
        );
    }
    if (!o) {
        return (
            <View style={S.listPad}>
                <Text style={S.muted}>Sipariş bulunamadı.</Text>
            </View>
        );
    }

    const items = o.items || [];

    return (
        <ScrollView style={S.flex}>
            <View style={S.topBar}>
                <Pressable onPress={() => navigation.goBack()}>
                    <Text style={{ color: '#ea580c', fontWeight: '600' }}>← Geri</Text>
                </Pressable>
            </View>
            <View style={S.listPad}>
                <Text style={{ fontSize: 20, fontWeight: '800' }}>#{o.order_number}</Text>
                <Text style={S.pMeta}>{o.status}</Text>
                <Text style={S.pPrice}>₺{Number(o.subtotal).toFixed(2)}</Text>
                <Text style={[S.sectionTitle, { marginTop: 16 }]}>Kalemler</Text>
                {items.map((line) => (
                    <View key={line.id} style={S.card}>
                        <Text style={S.pName}>{line.name}</Text>
                        <Text style={S.pMeta}>
                            {line.quantity} × ₺{Number(line.price).toFixed(2)}
                        </Text>
                    </View>
                ))}
            </View>
        </ScrollView>
    );
}

function flattenCatIds(nodes, out = []) {
    if (!Array.isArray(nodes)) return out;
    for (const c of nodes) {
        out.push({ id: c.id, name: c.name });
        if (c.children?.length) flattenCatIds(c.children, out);
    }
    return out;
}

export function CreateQuoteRequestScreen() {
    const navigation = useNavigation();
    const { token } = useAuth();
    const [categories, setCategories] = useState([]);
    const [categoryId, setCategoryId] = useState('');
    const [title, setTitle] = useState('');
    const [description, setDescription] = useState('');
    const [city, setCity] = useState('');
    const [err, setErr] = useState('');
    const [loading, setLoading] = useState(false);

    useEffect(() => {
        (async () => {
            try {
                const rows = await api('/categories');
                setCategories(flattenCatIds(rows));
            } catch {
                setCategories([]);
            }
        })();
    }, []);

    const submit = async () => {
        if (!token) {
            navigation.navigate('Login');
            return;
        }
        setErr('');
        setLoading(true);
        try {
            await api('/quote-requests', {
                method: 'POST',
                body: JSON.stringify({
                    category_id: parseInt(categoryId, 10),
                    title,
                    description,
                    city: city || null,
                }),
            });
            navigation.goBack();
        } catch (e) {
            setErr(e.message || 'Hata');
        } finally {
            setLoading(false);
        }
    };

    return (
        <ScrollView style={S.flex}>
            <View style={S.topBar}>
                <Pressable onPress={() => navigation.goBack()}>
                    <Text style={{ color: '#ea580c', fontWeight: '600' }}>← Geri</Text>
                </Pressable>
                <Text style={S.brand}>Teklif talebi</Text>
            </View>
            <View style={S.listPad}>
                <Text style={S.hint}>Web’deki teklif talebi formu ile aynı alanlar.</Text>
                <Text style={S.label}>Kategori</Text>
                <ScrollView style={{ maxHeight: 120, marginBottom: 10 }}>
                    {categories.map((c) => (
                        <Pressable key={c.id} style={[S.chip, { marginBottom: 6 }, String(categoryId) === String(c.id) && { backgroundColor: '#ffedd5' }]} onPress={() => setCategoryId(String(c.id))}>
                            <Text>{c.name}</Text>
                        </Pressable>
                    ))}
                </ScrollView>
                <TextInput style={S.input} placeholder="Başlık" value={title} onChangeText={setTitle} />
                <TextInput style={[S.input, { minHeight: 100 }]} placeholder="Açıklama" multiline value={description} onChangeText={setDescription} />
                <TextInput style={S.input} placeholder="Şehir (isteğe bağlı)" value={city} onChangeText={setCity} />
                {err ? <Text style={S.err}>{err}</Text> : null}
                <Pressable style={S.btn} onPress={submit} disabled={loading || !categoryId || !title}>
                    {loading ? <ActivityIndicator color="#111" /> : <Text style={S.btnText}>Gönder</Text>}
                </Pressable>
            </View>
        </ScrollView>
    );
}

export function CreateFreelancerJobScreen() {
    const navigation = useNavigation();
    const { token } = useAuth();
    const [category, setCategory] = useState('logo');
    const [title, setTitle] = useState('');
    const [description, setDescription] = useState('');
    const [err, setErr] = useState('');
    const [loading, setLoading] = useState(false);

    const cats = [
        { key: 'logo', label: 'Logo' },
        { key: 'brochure', label: 'Broşür' },
        { key: 'digital', label: 'Dijital' },
        { key: 'wordpress', label: 'WordPress' },
        { key: 'other', label: 'Diğer' },
    ];

    const submit = async () => {
        if (!token) {
            navigation.navigate('Login');
            return;
        }
        setErr('');
        setLoading(true);
        try {
            await api('/freelancer-jobs', {
                method: 'POST',
                body: JSON.stringify({ category, title, description }),
            });
            navigation.navigate('FreelancerJobs');
        } catch (e) {
            setErr(e.message || 'Hata');
        } finally {
            setLoading(false);
        }
    };

    return (
        <ScrollView style={S.flex}>
            <View style={S.topBar}>
                <Pressable onPress={() => navigation.goBack()}>
                    <Text style={{ color: '#ea580c', fontWeight: '600' }}>← Geri</Text>
                </Pressable>
                <Text style={S.brand}>İlan ver</Text>
            </View>
            <View style={S.listPad}>
                <Text style={S.label}>Kategori</Text>
                <View style={S.horiz}>
                    {cats.map((c) => (
                        <Pressable key={c.key} style={[S.chip, category === c.key && { backgroundColor: '#ffedd5' }]} onPress={() => setCategory(c.key)}>
                            <Text>{c.label}</Text>
                        </Pressable>
                    ))}
                </View>
                <TextInput style={S.input} placeholder="Başlık" value={title} onChangeText={setTitle} />
                <TextInput style={[S.input, { minHeight: 120 }]} placeholder="Açıklama" multiline value={description} onChangeText={setDescription} />
                {err ? <Text style={S.err}>{err}</Text> : null}
                <Pressable style={S.btn} onPress={submit} disabled={loading || !title}>
                    {loading ? <ActivityIndicator color="#111" /> : <Text style={S.btnText}>Yayınla</Text>}
                </Pressable>
            </View>
        </ScrollView>
    );
}

export function CustomerDashboardScreen() {
    const navigation = useNavigation();
    const { token, user } = useAuth();
    const [d, setD] = useState(null);
    const [err, setErr] = useState('');
    const [loading, setLoading] = useState(true);

    const load = useCallback(async () => {
        if (!token || user?.role !== 'customer') {
            setLoading(false);
            setD(null);
            return;
        }
        setErr('');
        try {
            const data = await api('/customer/dashboard');
            setD(data);
        } catch (e) {
            setErr(e.message || 'Yüklenemedi');
            setD(null);
        } finally {
            setLoading(false);
        }
    }, [token, user?.role]);

    useFocusEffect(
        useCallback(() => {
            setLoading(true);
            load();
        }, [load]),
    );

    return (
        <View style={S.flex}>
            <View style={S.topBar}>
                <Pressable onPress={() => navigation.goBack()}>
                    <Text style={{ color: '#ea580c', fontWeight: '600' }}>← Geri</Text>
                </Pressable>
                <Text style={S.brand}>Hesabım özeti</Text>
                <View style={{ width: 48 }} />
            </View>
            {!token ? (
                <View style={S.listPad}>
                    <Text style={S.hint}>Özeti görmek için giriş yapın.</Text>
                    <Pressable style={S.btn} onPress={() => navigation.navigate('Login')}>
                        <Text style={S.btnText}>Giriş yap</Text>
                    </Pressable>
                </View>
            ) : user?.role !== 'customer' ? (
                <View style={S.listPad}>
                    <Text style={S.muted}>Bu özet yalnızca müşteri hesapları içindir.</Text>
                </View>
            ) : (
                <ScrollView contentContainerStyle={S.listPad}>
                    {loading ? <ActivityIndicator style={{ marginVertical: 24 }} color="#f97316" /> : null}
                    {err ? <Text style={S.err}>{err}</Text> : null}
                    {d ? (
                        <>
                            <Text style={{ fontSize: 18, fontWeight: '800', color: '#111827' }}>Merhaba, {d.user?.name}</Text>
                            <Text style={[S.hint, { marginTop: 6 }]}>{d.user?.email}</Text>
                            <View style={S.dashStatGrid}>
                                <View style={S.dashStatCell}>
                                    <Text style={S.dashStatNum}>{d.orders_count}</Text>
                                    <Text style={S.dashStatLbl}>Sipariş</Text>
                                </View>
                                <View style={S.dashStatCell}>
                                    <Text style={S.dashStatNum}>{d.cart_count}</Text>
                                    <Text style={S.dashStatLbl}>Sepet kalemi</Text>
                                </View>
                                <View style={S.dashStatCell}>
                                    <Text style={S.dashStatNum}>{d.favorites_count}</Text>
                                    <Text style={S.dashStatLbl}>Favori</Text>
                                </View>
                            </View>
                            <Text style={[S.sectionTitle, { marginTop: 20 }]}>Kısayollar</Text>
                            <Pressable style={S.dashRow} onPress={() => navigation.navigate('Tabs', { screen: 'Siparişler' })}>
                                <Text style={S.linkText}>Siparişlerim</Text>
                                <Text style={S.hint}>→</Text>
                            </Pressable>
                            <Pressable style={S.dashRow} onPress={() => navigation.navigate('Tabs', { screen: 'Sepet' })}>
                                <Text style={S.linkText}>Sepet</Text>
                                <Text style={S.hint}>→</Text>
                            </Pressable>
                            <Pressable style={S.dashRow} onPress={() => navigation.navigate('Favorites')}>
                                <Text style={S.linkText}>Favoriler</Text>
                                <Text style={S.hint}>→</Text>
                            </Pressable>
                            <Pressable style={S.dashRow} onPress={() => navigation.navigate('WebMirror', { path: '/hesabim' })}>
                                <Text style={S.linkText}>Web’de tam panel (Blade)</Text>
                                <Text style={S.hint}>→</Text>
                            </Pressable>
                        </>
                    ) : !loading && !err ? (
                        <Text style={S.muted}>Veri yok.</Text>
                    ) : null}
                </ScrollView>
            )}
        </View>
    );
}
