'use client';

import React, { createContext, useContext, useState, useEffect } from 'react';
import Cookies from 'js-cookie';
import api from '@/lib/api';
import { useRouter } from 'next/navigation';

interface User {
    id: number;
    name: string;
    email: string;
    role: 'admin' | 'socio' | 'cliente';
}

interface AuthContextType {
    user: User | null;
    loading: boolean;
    login: (token: string, userData: User) => void;
    logout: () => void;
}

const AuthContext = createContext<AuthContextType | undefined>(undefined);

export function AuthProvider({ children }: { children: React.ReactNode }) {
    const [user, setUser] = useState<User | null>(null);
    const [loading, setLoading] = useState(true);
    const router = useRouter();

    useEffect(() => {
        async function loadUserFromCookies() {
            const token = Cookies.get('menuvi_token');
            if (token) {
                try {
                    const { data } = await api.get('/auth/me');
                    setUser(data);
                } catch (error) {
                    Cookies.remove('menuvi_token');
                }
            }
            setLoading(false);
        }
        loadUserFromCookies();
    }, []);

    const login = (token: string, userData: User) => {
        Cookies.set('menuvi_token', token, { expires: 7 }); // Expires in 7 days
        setUser(userData);

        // Redirect based on role
        if (userData.role === 'admin') router.push('/admin');
        else if (userData.role === 'socio') router.push('/partner');
        else router.push('/');
    };

    const logout = async () => {
        try {
            await api.post('/auth/logout');
        } catch (e) {
            // Ignore logout errors
        } finally {
            Cookies.remove('menuvi_token');
            setUser(null);
            router.push('/login');
        }
    };

    return (
        <AuthContext.Provider value={{ user, loading, login, logout }}>
            {children}
        </AuthContext.Provider>
    );
}

export const useAuth = () => {
    const context = useContext(AuthContext);
    if (context === undefined) {
        throw new Error('useAuth must be used within an AuthProvider');
    }
    return context;
};
