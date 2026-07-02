/**
 * Web sitesinin aynısı — Blade/CSS ile birebir görünüm (WebView).
 * Telefonda localhost yerine bilgisayar IP’si kullanın (app.json webUrl / apiUrl).
 */
import { Ionicons } from '@expo/vector-icons';
import { useNavigation, useRoute } from '@react-navigation/native';
import { useState } from 'react';
import { ActivityIndicator, Pressable, StyleSheet, Text, View } from 'react-native';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import { WebView } from 'react-native-webview';
import { webUrl } from './webOrigin';

export default function WebMirrorScreen() {
    const navigation = useNavigation();
    const route = useRoute();
    const insets = useSafeAreaInsets();
    const path = route.params?.path ?? '/';
    /** Sekme ana sayfası: web’in kendi header/footer’ı görünsün, üstte RN çubuğu olmasın. */
    const showHeader = route.params?.showHeader !== false;
    const uri = webUrl(path);
    const [loading, setLoading] = useState(true);

    return (
        <View style={[styles.flex, !showHeader && { paddingTop: insets.top }]}>
            {showHeader ? (
                <View style={[styles.bar, { paddingTop: Math.max(insets.top, 8) }]}>
                    <Pressable onPress={() => navigation.goBack()} style={styles.back} hitSlop={12}>
                        <Ionicons name="arrow-back" size={22} color="#111827" />
                        <Text style={styles.backText}>Geri</Text>
                    </Pressable>
                    <Text style={styles.title} numberOfLines={1}>
                        Web görünümü
                    </Text>
                    <View style={{ width: 72 }} />
                </View>
            ) : null}
            {loading ? (
                <View style={styles.loading}>
                    <ActivityIndicator size="large" color="#f97316" />
                    <Text style={styles.loadingTxt}>Yükleniyor…</Text>
                </View>
            ) : null}
            <WebView
                source={{ uri }}
                style={styles.web}
                onLoadStart={() => setLoading(true)}
                onLoadEnd={() => setLoading(false)}
                onError={() => setLoading(false)}
                startInLoadingState
                javaScriptEnabled
                domStorageEnabled
                setSupportMultipleWindows={false}
            />
        </View>
    );
}

const styles = StyleSheet.create({
    flex: { flex: 1, backgroundColor: '#fff' },
    bar: {
        flexDirection: 'row',
        alignItems: 'center',
        justifyContent: 'space-between',
        paddingHorizontal: 12,
        paddingBottom: 10,
        borderBottomWidth: 1,
        borderBottomColor: '#e5e7eb',
        backgroundColor: '#fff',
    },
    back: { flexDirection: 'row', alignItems: 'center', gap: 4, width: 72 },
    backText: { fontSize: 16, fontWeight: '600', color: '#111827' },
    title: { flex: 1, textAlign: 'center', fontWeight: '700', color: '#374151', fontSize: 15 },
    web: { flex: 1 },
    loading: {
        ...StyleSheet.absoluteFillObject,
        justifyContent: 'center',
        alignItems: 'center',
        backgroundColor: 'rgba(255,255,255,0.85)',
        zIndex: 10,
    },
    loadingTxt: { marginTop: 8, color: '#6b7280' },
});
