'use client';

import React, { useEffect, useState } from 'react';
import api from '@/lib/api';
import { motion, AnimatePresence } from 'framer-motion';
import { Search, Store, CheckCircle, Clock, MoreVertical, ExternalLink } from 'lucide-react';
import Image from 'next/image';

interface Business {
    id: number;
    nombre: string;
    categoria: string;
    logo_url: string;
    estado: string;
    plan: string;
    user: {
        name: string;
        email: string;
    };
}

export default function AdminStores() {
    const [businesses, setBusinesses] = useState<Business[]>([]);
    const [loading, setLoading] = useState(true);
    const [searchTerm, setSearchTerm] = useState('');

    useEffect(() => {
        fetchBusinesses();
    }, []);

    const fetchBusinesses = async () => {
        try {
            const { data } = await api.get('/admin/businesses');
            setBusinesses(data.data);
        } catch (e) {
            console.error('Error fetching businesses');
        } finally {
            setLoading(false);
        }
    };

    const updateStatus = async (id: number, newStatus: string) => {
        try {
            await api.put(`/admin/businesses/${id}/status`, { estado: newStatus });
            setBusinesses(businesses.map(b => b.id === id ? { ...b, estado: newStatus } : b));
        } catch (e) {
            console.error('Error updating status');
        }
    };

    const filtered = businesses.filter(b =>
        b.nombre.toLowerCase().includes(searchTerm.toLowerCase())
    );

    return (
        <div className="space-y-6">
            <div className="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h1 className="text-3xl font-black text-indigo-950 tracking-tight leading-none mb-1">Negocios Registrados</h1>
                    <p className="text-indigo-400 font-bold">Administra y aprueba los comercios en tu plataforma.</p>
                </div>
                <div className="relative group">
                    <Search size={18} className="absolute left-4 top-1/2 -translate-y-1/2 text-indigo-300" />
                    <input
                        type="text"
                        placeholder="Buscar por nombre..."
                        value={searchTerm}
                        onChange={(e) => setSearchTerm(e.target.value)}
                        className="pl-12 pr-6 py-3.5 bg-white border border-indigo-50 rounded-2xl outline-none focus:ring-2 focus:ring-indigo-500 transition-all font-bold text-indigo-950 shadow-sm w-[300px]"
                    />
                </div>
            </div>

            <div className="bg-white rounded-[2.5rem] border border-indigo-50 shadow-sm overflow-hidden">
                <table className="w-full text-left">
                    <thead>
                        <tr className="border-b border-indigo-50">
                            <th className="px-8 py-6 text-xs font-black uppercase tracking-widest text-indigo-400">Negocio</th>
                            <th className="px-8 py-6 text-xs font-black uppercase tracking-widest text-indigo-400">Dueño</th>
                            <th className="px-8 py-6 text-xs font-black uppercase tracking-widest text-indigo-400">Plan</th>
                            <th className="px-8 py-6 text-xs font-black uppercase tracking-widest text-indigo-400">Estado</th>
                            <th className="px-8 py-6 text-xs font-black uppercase tracking-widest text-indigo-400 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-indigo-50">
                        {filtered.map((b) => (
                            <tr key={b.id} className="hover:bg-indigo-50/30 transition-colors">
                                <td className="px-8 py-5">
                                    <div className="flex items-center gap-4">
                                        <div className="relative w-12 h-12 rounded-2xl overflow-hidden bg-indigo-50 flex items-center justify-center">
                                            {b.logo_url ? (
                                                <Image src={b.logo_url} alt={b.nombre} fill className="object-cover" />
                                            ) : (
                                                <Store size={20} className="text-indigo-300" />
                                            )}
                                        </div>
                                        <div>
                                            <p className="font-black text-indigo-950">{b.nombre}</p>
                                            <p className="text-[10px] font-black uppercase tracking-widest text-indigo-400">{b.categoria}</p>
                                        </div>
                                    </div>
                                </td>
                                <td className="px-8 py-5">
                                    <p className="text-sm font-black text-indigo-950">{b.user?.name}</p>
                                    <p className="text-xs font-bold text-indigo-400 tracking-tight">{b.user?.email}</p>
                                </td>
                                <td className="px-8 py-5">
                                    <span className={`text-[10px] font-black uppercase tracking-widest px-3 py-1.5 rounded-xl border ${b.plan === 'elite' ? 'bg-amber-50 text-amber-600 border-amber-100' :
                                            b.plan === 'pro' ? 'bg-indigo-50 text-indigo-600 border-indigo-100' :
                                                'bg-slate-50 text-slate-600 border-slate-100'
                                        }`}>
                                        {b.plan}
                                    </span>
                                </td>
                                <td className="px-8 py-5">
                                    <div className="flex items-center gap-2">
                                        <div className={`w-2 h-2 rounded-full ${b.estado === 'activo' ? 'bg-emerald-500 animate-pulse' : 'bg-orange-500'}`} />
                                        <span className={`text-xs font-black uppercase tracking-widest ${b.estado === 'activo' ? 'text-emerald-600' : 'text-orange-600'}`}>
                                            {b.estado}
                                        </span>
                                    </div>
                                </td>
                                <td className="px-8 py-5 text-right">
                                    <div className="flex items-center justify-end gap-2">
                                        {b.estado === 'pendiente' && (
                                            <button
                                                onClick={() => updateStatus(b.id, 'activo')}
                                                className="p-3 bg-emerald-500 text-white rounded-2xl hover:bg-emerald-400 transition-all shadow-xl shadow-emerald-500/20"
                                            >
                                                <CheckCircle size={18} />
                                            </button>
                                        )}
                                        <button className="p-3 text-indigo-400 hover:text-indigo-950 hover:bg-indigo-50 rounded-2xl transition-all">
                                            <ExternalLink size={18} />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        ))}
                    </tbody>
                </table>

                {filtered.length === 0 && !loading && (
                    <div className="py-20 text-center space-y-4 opacity-50">
                        <Store size={48} className="mx-auto text-indigo-300" />
                        <p className="font-black text-indigo-950">No hay negocios registrados aún</p>
                        <p className="text-sm font-bold text-indigo-400">¡Hora de buscar nuevos socios!</p>
                    </div>
                )}
            </div>
        </div>
    );
}
