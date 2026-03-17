'use client';

import { useState } from 'react';
import { useAuth } from '@/context/AuthContext';
import api from '@/lib/api';
import { motion } from 'framer-motion';
import { Mail, Lock, AlertCircle, ArrowRight, Store } from 'lucide-react';
import Link from 'next/link';
import Image from 'next/image';

export default function LoginPage() {
    const [email, setEmail] = useState('');
    const [password, setPassword] = useState('');
    const [error, setError] = useState('');
    const [isSubmitting, setIsSubmitting] = useState(false);
    const { login } = useAuth();

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault();
        setError('');
        setIsSubmitting(true);

        try {
            const { data } = await api.post('/auth/login', { email, password });

            // Load user details after login success
            const userRes = await api.get('/auth/me', {
                headers: { Authorization: `Bearer ${data.access_token}` }
            });

            login(data.access_token, userRes.data);
        } catch (err: any) {
            const message = err.response?.data?.message || err.response?.data?.error || 'Credenciales incorrectas. Intenta de nuevo.';
            setError(message);
        } finally {
            setIsSubmitting(false);
        }
    };

    return (
        <div className="min-h-screen bg-[#0a0a0a] flex items-center justify-center p-6 sm:p-12 relative overflow-hidden">
            {/* Background Accents */}
            <div className="absolute top-0 left-0 w-full h-full -z-10">
                <div className="absolute top-1/4 left-1/4 w-96 h-96 bg-cyan-500/10 blur-[120px] rounded-full" />
                <div className="absolute bottom-1/4 right-1/4 w-96 h-96 bg-purple-500/10 blur-[120px] rounded-full" />
            </div>

            <motion.div
                initial={{ opacity: 0, scale: 0.95 }}
                animate={{ opacity: 1, scale: 1 }}
                className="w-full max-w-md"
            >
                <div className="bg-[#1a1a1a] rounded-[3rem] shadow-2xl border border-white/5 overflow-hidden">
                    <div className="p-10 pb-6 text-center">
                        <Link href="/" className="relative inline-block w-40 h-16 mx-auto hover:scale-105 transition-transform">
                            <Image 
                                src="/logo1.png" 
                                alt="Menuvi Logo" 
                                fill 
                                className="object-contain"
                                priority
                            />
                        </Link>
                        <p className="text-white/40 font-bold uppercase tracking-widest text-[10px]">Bienvenido de nuevo</p>
                    </div>

                    <div className="p-10 pt-0">
                        <form onSubmit={handleSubmit} className="space-y-6">
                            {error && (
                                <motion.div
                                    initial={{ opacity: 0, x: -10 }}
                                    animate={{ opacity: 1, x: 0 }}
                                    className="bg-red-500/10 border border-red-500/20 p-4 rounded-2xl flex items-center gap-3 text-red-500 text-xs font-bold"
                                >
                                    <AlertCircle size={18} />
                                    {error}
                                </motion.div>
                            )}

                            <div className="space-y-2">
                                <label className="text-[10px] font-black text-white/30 uppercase tracking-widest ml-1">Email</label>
                                <div className="relative group">
                                    <div className="absolute inset-y-0 left-4 flex items-center pointer-events-none text-white/20 group-focus-within:text-white transition-colors">
                                        <Mail size={18} />
                                    </div>
                                    <input
                                        type="email"
                                        required
                                        value={email}
                                        onChange={(e) => setEmail(e.target.value)}
                                        className="w-full pl-12 pr-4 py-4 bg-white/5 border border-white/5 rounded-2xl outline-none focus:ring-1 focus:ring-white/20 focus:bg-white/10 transition-all font-bold text-white text-sm"
                                        placeholder="tu@email.com"
                                    />
                                </div>
                            </div>

                            <div className="space-y-2">
                                <label className="text-[10px] font-black text-white/30 uppercase tracking-widest ml-1">Contraseña</label>
                                <div className="relative group">
                                    <div className="absolute inset-y-0 left-4 flex items-center pointer-events-none text-white/20 group-focus-within:text-white transition-colors">
                                        <Lock size={18} />
                                    </div>
                                    <input
                                        type="password"
                                        required
                                        value={password}
                                        onChange={(e) => setPassword(e.target.value)}
                                        className="w-full pl-12 pr-4 py-4 bg-white/5 border border-white/5 rounded-2xl outline-none focus:ring-1 focus:ring-white/20 focus:bg-white/10 transition-all font-bold text-white text-sm"
                                        placeholder="••••••••"
                                    />
                                </div>
                            </div>

                            <button
                                type="submit"
                                disabled={isSubmitting}
                                className="w-full bg-white text-black rounded-2xl py-4 font-black text-xs uppercase tracking-widest shadow-xl shadow-white/5 hover:bg-cyan-400 hover:text-white active:scale-[0.98] transition-all flex items-center justify-center gap-3 group disabled:opacity-50"
                            >
                                {isSubmitting ? (
                                    <div className="w-5 h-5 border-2 border-black border-t-transparent rounded-full animate-spin" />
                                ) : (
                                    <>
                                        Entrar a mi cuenta
                                        <ArrowRight size={18} className="group-hover:translate-x-1 transition-transform" />
                                    </>
                                )}
                            </button>
                        </form>

                        <div className="mt-10 pt-8 border-t border-white/5 space-y-4">
                            <p className="text-white/30 text-[10px] font-black uppercase tracking-widest text-center">
                                ¿Eres un nuevo negocio?
                            </p>
                            <Link 
                                href="/partner/register"
                                className="w-full flex items-center justify-center gap-3 p-4 bg-cyan-500/10 border border-cyan-500/20 rounded-2xl text-cyan-400 font-black text-xs uppercase tracking-widest hover:bg-cyan-500/20 transition-all"
                            >
                                <Store size={18} />
                                Regístrate como Socio
                            </Link>
                        </div>
                    </div>
                </div>
            </motion.div>
        </div>
    );
}
