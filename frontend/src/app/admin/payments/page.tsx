'use client';

import React, { useEffect, useState } from 'react';
import { 
    CreditCard, 
    Activity, 
    ArrowUpRight, 
    ShieldCheck, 
    Plus, 
    Search,
    Filter,
    MoreVertical,
    CheckCircle2,
    Clock,
    XCircle,
    FileText,
    Calendar,
    ChevronRight,
    Loader2
} from 'lucide-react';
import { motion, AnimatePresence } from 'framer-motion';
import api from '@/lib/api';

interface Liquidacion {
    id: number;
    negocio_id: number;
    negocio: {
        nombre: string;
        logo_url: string;
    };
    periodo_inicio: string;
    periodo_fin: string;
    total_ventas: number;
    comision_plataforma: number;
    monto_liquidar: number;
    estado: 'pendiente' | 'pagado' | 'cancelado';
    fecha_pago: string | null;
    comprobante_url: string | null;
    notas: string | null;
    created_at: string;
}

export default function AdminPayments() {
    const [liquidaciones, setLiquidaciones] = useState<Liquidacion[]>([]);
    const [loading, setLoading] = useState(true);
    const [filter, setFilter] = useState('all');
    const [searchTerm, setSearchTerm] = useState('');

    useEffect(() => {
        fetchLiquidaciones();
    }, []);

    const fetchLiquidaciones = async () => {
        try {
            setLoading(true);
            const res = await api.get('/admin/payments');
            if (res.data.status === 'success') {
                setLiquidaciones(res.data.data);
            }
        } catch (error) {
            console.error('Error fetching liquidaciones:', error);
        } finally {
            setLoading(false);
        }
    };

    const updateStatus = async (id: number, nuevoEstado: string) => {
        try {
            const res = await api.put(`/admin/payments/${id}/status`, { estado: nuevoEstado });
            if (res.data.status === 'success') {
                setLiquidaciones(liquidaciones.map(l => l.id === id ? { ...l, ...res.data.data } : l));
            }
        } catch (error) {
            console.error('Error updating status:', error);
        }
    };

    const stats = {
        totalPendiente: liquidaciones.filter(l => l.estado === 'pendiente').reduce((acc, curr) => acc + Number(curr.monto_liquidar), 0),
        totalPagado: liquidaciones.filter(l => l.estado === 'pagado').reduce((acc, curr) => acc + Number(curr.monto_liquidar), 0),
        countPendiente: liquidaciones.filter(l => l.estado === 'pendiente').length
    };

    const filteredLiquidaciones = liquidaciones.filter(l => {
        const matchesSearch = l.negocio.nombre.toLowerCase().includes(searchTerm.toLowerCase());
        const matchesFilter = filter === 'all' || l.estado === filter;
        return matchesSearch && matchesFilter;
    });

    const getStatusStyles = (estado: string) => {
        switch (estado) {
            case 'pagado': return 'bg-emerald-500/10 text-emerald-500 border-emerald-500/20';
            case 'pendiente': return 'bg-orange-500/10 text-orange-500 border-orange-500/20';
            case 'cancelado': return 'bg-rose-500/10 text-rose-500 border-rose-500/20';
            default: return 'bg-white/5 text-white/40 border-white/5';
        }
    };

    const getStatusIcon = (estado: string) => {
        switch (estado) {
            case 'pagado': return <CheckCircle2 size={14} />;
            case 'pendiente': return <Clock size={14} />;
            case 'cancelado': return <XCircle size={14} />;
            default: return null;
        }
    };

    if (loading && liquidaciones.length === 0) {
        return (
            <div className="min-h-[60vh] flex flex-col items-center justify-center space-y-4">
                <Loader2 className="w-10 h-10 text-indigo-500 animate-spin" />
                <p className="text-indigo-400 font-bold animate-pulse">Cargando módulo de cobros...</p>
            </div>
        );
    }

    return (
        <div className="space-y-8 max-w-7xl mx-auto px-4 sm:px-6">
            {/* Header Section */}
            <div className="flex flex-col md:flex-row md:items-end justify-between gap-6">
                <div className="space-y-1">
                    <div className="flex items-center gap-2 text-indigo-500 mb-2">
                        <CreditCard size={20} />
                        <span className="text-[10px] font-black uppercase tracking-[0.2em]">Finanzas & Pagos</span>
                    </div>
                    <h1 className="text-4xl lg:text-5xl font-black text-indigo-950 tracking-tighter">Liquidaciones</h1>
                    <p className="text-indigo-400 font-bold">Gestiona los pagos y facturación de tus socios comerciales.</p>
                </div>

                <div className="flex items-center gap-3">
                    <button className="flex items-center gap-2 bg-indigo-50 text-indigo-600 px-6 py-4 rounded-3xl font-black text-xs uppercase tracking-widest hover:bg-indigo-100 transition-all border border-indigo-100">
                        <FileText size={18} />
                        Reporte General
                    </button>
                    <button className="flex items-center gap-2 bg-indigo-600 text-white px-6 py-4 rounded-3xl font-black text-xs uppercase tracking-widest hover:bg-indigo-700 transition-all shadow-xl shadow-indigo-600/20 active:scale-95">
                        <Plus size={18} />
                        Nueva Liquidación
                    </button>
                </div>
            </div>

            {/* Stats Cards */}
            <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div className="bg-white p-8 rounded-[2.5rem] border border-indigo-50 shadow-sm space-y-4">
                    <div className="w-12 h-12 bg-orange-50 text-orange-600 rounded-2xl flex items-center justify-center">
                        <Clock size={24} />
                    </div>
                    <div>
                        <p className="text-slate-400 font-black text-[10px] uppercase tracking-widest mb-1">Total Pendiente</p>
                        <h3 className="text-3xl font-black text-slate-900">${stats.totalPendiente.toLocaleString()}</h3>
                    </div>
                    <p className="text-xs font-bold text-orange-500">{stats.countPendiente} liquidaciones por pagar</p>
                </div>

                <div className="bg-white p-8 rounded-[2.5rem] border border-indigo-50 shadow-sm space-y-4">
                    <div className="w-12 h-12 bg-emerald-50 text-emerald-600 rounded-2xl flex items-center justify-center">
                        <CheckCircle2 size={24} />
                    </div>
                    <div>
                        <p className="text-slate-400 font-black text-[10px] uppercase tracking-widest mb-1">Total Pagado</p>
                        <h3 className="text-3xl font-black text-slate-900">${stats.totalPagado.toLocaleString()}</h3>
                    </div>
                    <p className="text-xs font-bold text-emerald-500">Historial de pagos exitosos</p>
                </div>

                <div className="bg-indigo-950 p-8 rounded-[2.5rem] text-white shadow-2xl shadow-indigo-900/20 space-y-4 relative overflow-hidden">
                    <div className="absolute right-0 top-0 w-32 h-32 bg-indigo-500/10 blur-3xl rounded-full" />
                    <div className="w-12 h-12 bg-white/10 text-indigo-400 rounded-2xl flex items-center justify-center">
                        <ShieldCheck size={24} />
                    </div>
                    <div>
                        <p className="text-white/30 font-black text-[10px] uppercase tracking-widest mb-1">Seguridad</p>
                        <h3 className="text-xl font-bold">Menuvi Pay Secure</h3>
                    </div>
                    <p className="text-xs font-bold text-white/40">Cumplimiento con estándares bancarios 256-bit.</p>
                </div>
            </div>

            {/* Main Table Section */}
            <div className="bg-white rounded-[3rem] border border-indigo-50 shadow-sm overflow-hidden">
                <div className="p-8 border-b border-indigo-50 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div className="relative group flex-1 max-w-md">
                        <Search className="absolute left-5 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-indigo-500 transition-colors" size={18} />
                        <input 
                            type="text" 
                            placeholder="Buscar socio o negocio..."
                            className="w-full bg-slate-50 border-none rounded-2xl py-4 pl-14 pr-6 text-sm font-bold focus:ring-2 focus:ring-indigo-500/20 transition-all outline-none"
                            value={searchTerm}
                            onChange={(e) => setSearchTerm(e.target.value)}
                        />
                    </div>

                    <div className="flex items-center gap-3">
                        {['all', 'pendiente', 'pagado'].map((f) => (
                            <button
                                key={f}
                                onClick={() => setFilter(f)}
                                className={`px-5 py-3 rounded-xl text-[10px] font-black uppercase tracking-widest border transition-all ${filter === f ? 'bg-indigo-600 border-indigo-600 text-white shadow-lg shadow-indigo-600/20' : 'bg-white border-indigo-50 text-slate-400 hover:border-indigo-200'}`}
                            >
                                {f === 'all' ? 'Todos' : f}
                            </button>
                        ))}
                    </div>
                </div>

                <div className="overflow-x-auto">
                    <table className="w-full text-left">
                        <thead>
                            <tr className="bg-slate-50">
                                <th className="px-8 py-5 text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">Socio</th>
                                <th className="px-8 py-5 text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">Periodo</th>
                                <th className="px-8 py-5 text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">Ventas</th>
                                <th className="px-8 py-5 text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">Comisión</th>
                                <th className="px-8 py-5 text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">A Liquidar</th>
                                <th className="px-8 py-5 text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">Estado</th>
                                <th className="px-8 py-5 text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-indigo-50">
                            {filteredLiquidaciones.map((l) => (
                                <motion.tr 
                                    layout
                                    key={l.id} 
                                    className="hover:bg-slate-50/50 transition-colors group"
                                >
                                    <td className="px-8 py-6">
                                        <div className="flex items-center gap-3">
                                            <div className="w-10 h-10 rounded-2xl bg-indigo-50 flex items-center justify-center overflow-hidden border border-indigo-100">
                                                {l.negocio.logo_url ? (
                                                    <img src={l.negocio.logo_url} className="w-full h-full object-cover" />
                                                ) : (
                                                    <CreditCard size={18} className="text-indigo-500" />
                                                )}
                                            </div>
                                            <div>
                                                <p className="font-black text-slate-900 text-sm leading-none mb-1">{l.negocio.nombre}</p>
                                                <p className="text-[10px] font-bold text-slate-400 uppercase tracking-widest">ID #{l.id}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td className="px-8 py-6 whitespace-nowrap">
                                        <div className="flex items-center gap-2 text-slate-500">
                                            <Calendar size={14} />
                                            <span className="text-xs font-bold">
                                                {new Date(l.periodo_inicio).toLocaleDateString()} - {new Date(l.periodo_fin).toLocaleDateString()}
                                            </span>
                                        </div>
                                    </td>
                                    <td className="px-8 py-6">
                                        <p className="text-sm font-black text-slate-900">${Number(l.total_ventas).toLocaleString()}</p>
                                    </td>
                                    <td className="px-8 py-6">
                                        <p className="text-sm font-bold text-rose-500">-${Number(l.comision_plataforma).toLocaleString()}</p>
                                    </td>
                                    <td className="px-8 py-6">
                                        <p className="text-sm font-black text-emerald-600">${Number(l.monto_liquidar).toLocaleString()}</p>
                                    </td>
                                    <td className="px-8 py-6">
                                        <span className={`px-4 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest border flex items-center gap-2 w-fit ${getStatusStyles(l.estado)}`}>
                                            {getStatusIcon(l.estado)}
                                            {l.estado}
                                        </span>
                                    </td>
                                    <td className="px-8 py-6 text-right">
                                        <div className="flex items-center justify-end gap-2">
                                            {l.estado === 'pendiente' && (
                                                <button 
                                                    onClick={() => updateStatus(l.id, 'pagado')}
                                                    className="p-3 bg-emerald-50 text-emerald-600 rounded-xl hover:bg-emerald-100 transition-all active:scale-95"
                                                    title="Marcar como pagado"
                                                >
                                                    <CheckCircle2 size={18} />
                                                </button>
                                            )}
                                            <button className="p-3 bg-slate-100 text-slate-400 rounded-xl hover:bg-slate-200 transition-all active:scale-95 group-hover:text-slate-600">
                                                <FileText size={18} />
                                            </button>
                                        </div>
                                    </td>
                                </motion.tr>
                            ))}
                        </tbody>
                    </table>

                    {filteredLiquidaciones.length === 0 && (
                        <div className="py-20 text-center space-y-4">
                            <div className="w-16 h-16 bg-slate-50 text-slate-200 rounded-full flex items-center justify-center mx-auto">
                                <Search size={32} />
                            </div>
                            <div>
                                <h4 className="text-slate-900 font-black">No se encontraron liquidaciones</h4>
                                <p className="text-slate-400 text-sm font-bold">Ajusta tus filtros o busca por otro socio.</p>
                            </div>
                        </div>
                    )}
                </div>
            </div>
        </div>
    );
}
