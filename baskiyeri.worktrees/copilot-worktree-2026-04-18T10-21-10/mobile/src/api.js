import AsyncStorage from '@react-native-async-storage/async-storage';
import Constants from 'expo-constants';

const API_URL =
    Constants.expoConfig?.extra?.apiUrl ||
    process.env.EXPO_PUBLIC_API_URL ||
    'http://127.0.0.1:8000/api/v1';

export { API_URL };

export async function api(path, options = {}) {
    const token = await AsyncStorage.getItem('token');
    const headers = {
        Accept: 'application/json',
        'Content-Type': 'application/json',
        ...options.headers,
    };
    if (token) {
        headers.Authorization = `Bearer ${token}`;
    }
    const res = await fetch(`${API_URL}${path}`, { ...options, headers });
    const text = await res.text();
    let data = {};
    try {
        data = text ? JSON.parse(text) : {};
    } catch {
        data = { message: text };
    }
    if (!res.ok) {
        const msg = data.message || data.error || `HTTP ${res.status}`;
        throw new Error(typeof msg === 'string' ? msg : JSON.stringify(msg));
    }
    return data;
}

export async function setToken(token) {
    if (token) {
        await AsyncStorage.setItem('token', token);
    } else {
        await AsyncStorage.removeItem('token');
    }
}
