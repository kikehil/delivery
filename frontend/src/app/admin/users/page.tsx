'use client';

import React, { useEffect, useState } from 'react';
import api from '@/lib/api';
import { motion } from 'framer-motion';
import { User, Shield, Search, MoreVertical, Trash2, Edit3, UserCheck, ShieldOff } from 'lucide-react';

interface AppUser {
    id: number;
    name: string;
    email: string;
    role: string;
    created_at: string;
}

export default function AdminUsers() {
    const [users, setUsers] = useState<AppUser[]>([]);
    const [loading, setLoading] = useState(true);
    const [searchTerm, setSearchTerm] = useState('');

    useEffect(() => {
        fetchUsers();
    }, []);

    const fetchUsers = async () => {
        try {
            const { data } = await api.get('/admin/users');
            setUsers(data.data);
        } catch (e) {
            console.error('Error fetching users');
        } finally {
            setLoading(false);
        }
    };

    const filtered = users.filter(u =>
        u.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
        u.email.toLowerCase().includes(searchTerm.toLowerCase())
    );

    return (
        <div className="space-y-6">
            <div className="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h1 className="text-3xl font-black text-indigo-950 tracking-tight leading-none mb-1">Usuarios del Sistema</h1>
                    <p className="text-indigo-400 font-bold">Gestiona roles y permisos de tu comunidad.</p>
                </div>
                <div className="relative group">
                    <Search size={18} className="absolute left-4 top-1/2 -translate-y-1/2 text-indigo-300" />
                    <input
                        type="text"
                        placeholder="Buscar por nombre o email..."
                        value={searchTerm}
                        onChange={(e) => setSearchTerm(e.target.value)}
                        className="pl-12 pr-6 py-3.5 bg-white border border-indigo-50 rounded-2xl outline-none focus:ring-2 focus:ring-indigo-500 transition-all font-bold text-indigo-950 shadow-sm w-[350px]"
                    />
                </div>
            </div>

            <div className="bg-white rounded-[2.5rem] border border-indigo-50 shadow-sm overflow-hidden">
                <table className="w-full text-left">
                    <thead>
                        <tr className="border-b border-indigo-50">
                            <th className="px-8 py-6 text-xs font-black uppercase tracking-widest text-indigo-400">Usuario</th>
                            <th className="px-8 py-6 text-xs font-black uppercase tracking-widest text-indigo-400">Rol</th>
                            <th className="px-8 py-6 text-xs font-black uppercase tracking-widest text-indigo-400">Desde</th>
                            <th className="px-8 py-6 text-xs font-black uppercase tracking-widest text-indigo-400 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-indigo-50">
                        {filtered.map((u) => (
                            <tr key={u.id} className="hover:bg-indigo-50/30 transition-colors group">
                                <td className="px-8 py-5">
                                    <div className="flex items-center gap-4">
                                        <div className={`w-12 h-12 rounded-2xl flex items-center justify-center font-black ${u.role === 'admin' ? 'bg-indigo-600 text-white' :
                                                u.role === 'socio' ? 'bg-amber-100 text-amber-600' :
                                                    'bg-slate-100 text-slate-600'
                                            }`}>
                                            {u.name.charAt(0)}
                                        </div>
                                        <div>
                                            <p className="font-black text-indigo-950">{u.name}</p>
                                            <p className="text-xs font-bold text-indigo-400 tracking-tight">{u.email}</p>
                                        </div>
                                    </div>
                                </td>
                                <td className="px-8 py-5">
                                    <div className="flex items-center gap-2">
                                        {u.role === 'admin' && <Shield size={14} className="text-indigo-600" />}
                                        <span className={`text-[10px] font-black uppercase tracking-widest px-3 py-1.5 rounded-xl border ${u.role === 'admin' ? 'bg-indigo-50 text-indigo-600 border-indigo-100' :
                                                u.role === 'socio' ? 'bg-amber-50 text-amber-600 border-amber-100' :
                                                    'bg-emerald-50 text-emerald-600 border-emerald-100'
                                            }`}>
                                            {u.role}
                                        </span>
                                    </div>
                                </td>
                                <td className="px-8 py-5 text-sm font-bold text-indigo-400">
                                    {new Date(u.created_at).toLocaleDateString()}
                                </td>
                                <td className="px-8 py-5 text-right">
                                    <div className="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                        <button className="p-3 bg-indigo-50 text-indigo-600 rounded-2xl hover:bg-indigo-100 transition-all">
                                            <Edit3 size={18} />
                                        </button>
                                        <button className="p-3 bg-rose-50 text-rose-500 rounded-2xl hover:bg-rose-100 transition-all">
                                            <Trash2 size={18} />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>
        </div>
    );
}
