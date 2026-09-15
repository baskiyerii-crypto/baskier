import AsyncStorage from '@react-native-async-storage/async-storage';
import { createContext, useCallback, useContext, useEffect, useMemo, useState } from 'react';
import { api, setToken as persistToken, unwrapData } from '../api';

const AuthContext = createContext(null);

export function AuthProvider({ children }) {
    const [user, setUser] = useState(null);
    const [token, setTokenState] = useState(null);
    const [ready, setReady] = useState(false);

    const loadSession = useCallback(async () => {
        const t = await AsyncStorage.getItem('token');
        setTokenState(t);
        if (!t) {
            setUser(null);
            return;
        }
        try {
            const u = unwrapData(await api('/auth/user'));
            setUser(u);
        } catch {
            await persistToken(null);
            setTokenState(null);
            setUser(null);
        }
    }, []);

    useEffect(() => {
        (async () => {
            await loadSession();
            setReady(true);
        })();
    }, [loadSession]);

    const setToken = useCallback(async (t) => {
        await persistToken(t);
        setTokenState(t);
        if (!t) setUser(null);
    }, []);

    const login = useCallback(async (email, password) => {
        const data = unwrapData(await api('/auth/login', {
            method: 'POST',
            body: JSON.stringify({ email, password, device_name: 'expo-app' }),
        }));
        await persistToken(data.token);
        setTokenState(data.token);
        setUser(data.user);
        return data.user;
    }, []);

    const register = useCallback(async (payload) => {
        const data = unwrapData(await api('/auth/register', {
            method: 'POST',
            body: JSON.stringify({ ...payload, device_name: 'expo-app' }),
        }));
        await persistToken(data.token);
        setTokenState(data.token);
        setUser(data.user);
        return data.user;
    }, []);

    const logout = useCallback(async () => {
        try {
            await api('/auth/logout', { method: 'POST' });
        } catch {
            /* ignore */
        }
        await persistToken(null);
        setTokenState(null);
        setUser(null);
    }, []);

    const refreshUser = useCallback(async () => {
        try {
            const u = unwrapData(await api('/auth/user'));
            setUser(u);
            return u;
        } catch {
            await logout();
            return null;
        }
    }, [logout]);

    const value = useMemo(
        () => ({
            user,
            token,
            ready,
            login,
            register,
            logout,
            refreshUser,
            setUser,
        }),
        [user, token, ready, login, register, logout, refreshUser],
    );

    return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>;
}

export function useAuth() {
    const ctx = useContext(AuthContext);
    if (!ctx) throw new Error('useAuth outside AuthProvider');
    return ctx;
}
