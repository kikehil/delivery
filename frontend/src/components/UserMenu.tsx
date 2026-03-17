'use client';

import { useAuth } from '@/context/AuthContext';
import { LogOut, User as UserIcon, Settings, ShoppingBag } from 'lucide-react';
import { useState } from 'react';
import { motion, AnimatePresence } from 'framer-motion';

export default function UserMenu() {
    const { user, logout } = useAuth();
    const [isOpen, setIsOpen] = useState(false);

    if (!user) {
        return (
            <a
                href="/login"
                className="flex items-center gap-2 px-4 py-2 bg-slate-900 text-white rounded-full font-bold text-sm hover:bg-slate-800 transition-all"
            >
                <UserIcon size={16} />
                Entrar
            </a>
        );
    }

    return (
        <div className="relative">
            <button
                onClick={() => setIsOpen(!isOpen)}
                className="flex items-center gap-2 p-1 bg-slate-100 rounded-full hover:bg-slate-200 transition-all border border-slate-200"
            >
                <div className="w-8 h-8 bg-slate-900 text-white rounded-full flex items-center justify-center font-bold text-xs uppercase">
                    {user.name.charAt(0)}
                </div>
                <span className="text-sm font-bold pr-2 hidden md:block">{user.name}</span>
            </button>

            <AnimatePresence>
                {isOpen && (
                    <>
                        <div
                            className="fixed inset-0 z-40"
                            onClick={() => setIsOpen(false)}
                        />
                        <motion.div
                            initial={{ opacity: 0, y: 10, scale: 0.95 }}
                            animate={{ opacity: 1, y: 0, scale: 1 }}
                            exit={{ opacity: 0, y: 10, scale: 0.95 }}
                            className="absolute right-0 mt-2 w-56 bg-white rounded-2xl shadow-xl shadow-slate-200 border border-slate-100 p-2 z-50"
                        >
                            <div className="px-4 py-3 border-b border-slate-100 mb-2">
                                <p className="text-xs font-bold text-slate-400 uppercase tracking-widest leading-none mb-1">Cuenta</p>
                                <p className="text-sm font-bold text-slate-900 truncate">{user.email}</p>
                                <span className="inline-block mt-1 px-2 py-0.5 bg-slate-100 text-slate-600 rounded text-[10px] font-black uppercase tracking-tighter">
                                    {user.role}
                                </span>
                            </div>

                            <div className="space-y-1">
                                <button className="w-full flex items-center gap-3 px-3 py-2 text-sm font-bold text-slate-700 hover:bg-slate-50 hover:text-slate-900 rounded-xl transition-all">
                                    <ShoppingBag size={18} />
                                    Mis Pedidos
                                </button>
                                <button className="w-full flex items-center gap-3 px-3 py-2 text-sm font-bold text-slate-700 hover:bg-slate-50 hover:text-slate-900 rounded-xl transition-all">
                                    <Settings size={18} />
                                    Configuración
                                </button>
                                <div className="h-px bg-slate-100 my-1 mx-2" />
                                <button
                                    onClick={logout}
                                    className="w-full flex items-center gap-3 px-3 py-2 text-sm font-bold text-rose-600 hover:bg-rose-50 rounded-xl transition-all"
                                >
                                    <LogOut size={18} />
                                    Cerrar Sesión
                                </button>
                            </div>
                        </motion.div>
                    </>
                )}
            </AnimatePresence>
        </div>
    );
}
