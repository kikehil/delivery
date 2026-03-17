'use client';

import React, { useEffect, useState } from 'react';
import api from '@/lib/api';
import { motion } from 'framer-motion';
import {
    TrendingUp,
    ShoppingBag,
    Users,
    Store,
    ArrowUpRight,
    ArrowDownRight,
    MoreVertical,
    Activity,
    CreditCard,
    Package,
    CheckCircle
} from 'lucide-react';
import {
    AreaChart,
    Area,
    XAxis,
    YAxis,
    CartesianGrid,
    Tooltip,
    ResponsiveContainer
} from 'recharts';

interface AdminStats {
    revenue: number;
    orders: number;
    businesses: number;
    customers: number;
    pending_requests: number;
}

interface Order {
    id: number;
    total: number;
    estado: string;
    created_at: string;
    negocio: {
        nombre: string;
    };
}

interface PendingBusiness {
    id: number;
    nombre: string;
    categoria: string;
    telefono_contacto: string;
    user: {
        name: string;
        email: string;
    };
}

const dataChart = [
    { name: 'Lun', sales: 4000 },
    { name: 'Mar', sales: 3000 },
    { name: 'Mie', sales: 2000 },
    { name: 'Jue', sales: 2780 },
    { name: 'Vie', sales: 1890 },
    { name: 'Sab', sales: 2390 },
    { name: 'Dom', sales: 3490 },
];

export default function AdminDashboard() {
    const [stats, setStats] = useState<AdminStats | null>(null);
    const [latestOrders, setLatestOrders] = useState<Order[]>([]);
    const [pendingBusinesses, setPendingBusinesses] = useState<PendingBusiness[]>([]);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        async function fetchStats() {
            try {
                const { data } = await api.get('/admin/dashboard');
                setStats(data.data.stats);
                setLatestOrders(data.data.latest_orders);
                setPendingBusinesses(data.data.pending_businesses);
            } catch (e) {
                console.error('Error fetching admin stats');
            } finally {
                setLoading(false);
            }
        }
        fetchStats();
    }, []);

    const handleApprove = async (id: number) => {
        try {
            await api.put(`/admin/businesses/${id}/status`, { estado: 'activo' });
            // Refresh
            setPendingBusinesses(pendingBusinesses.filter(b => b.id !== id));
            // Update stats if needed (local update for speed)
            if (stats) setStats({...stats, pending_requests: stats.pending_requests - 1, businesses: stats.businesses + 1});
        } catch (e) {
            alert('Error al aprobar negocio');
        }
    };

    if (loading) {
        return (
            <div className="grid grid-cols-1 md:grid-cols-4 gap-6 animate-pulse">
                {[1, 2, 3, 4].map(i => (
                    <div key={i} className="h-32 bg-white rounded-3xl border border-indigo-50 shadow-sm" />
                ))}
            </div>
        );
    }

    const statCards = [
        {
            label: 'Ventas Totales',
            value: `$${stats?.revenue.toLocaleString()}`,
            icon: TrendingUp,
            color: 'bg-emerald-500',
            trend: '+12.5%',
            isPositive: true
        },
        {
            label: 'Pedidos Totales',
            value: stats?.orders,
            icon: ShoppingBag,
            color: 'bg-indigo-600',
            trend: '+4.2%',
            isPositive: true
        },
        {
            label: 'Negocios Activos',
            value: stats?.businesses,
            icon: Store,
            color: 'bg-orange-500',
            trend: '+2',
            isPositive: true
        },
        {
            label: 'Solicitudes',
            value: stats?.pending_requests,
            icon: Activity,
            color: 'bg-rose-500',
            trend: 'PENDIENTES',
            isPositive: stats?.pending_requests === 0
        },
    ];

    return (
        <div className="space-y-8 max-w-7xl mx-auto">
            {/* Stats Grid */}
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                {statCards.map((card, idx) => {
                    const Icon = card.icon;
                    return (
                        <motion.div
                            key={card.label}
                            initial={{ opacity: 0, y: 10 }}
                            animate={{ opacity: 1, y: 0 }}
                            transition={{ delay: idx * 0.1 }}
                            className="bg-white p-6 rounded-[2.5rem] border border-indigo-50 shadow-sm hover:shadow-xl hover:shadow-indigo-500/5 transition-all group"
                        >
                            <div className="flex items-center justify-between mb-4">
                                <div className={`${card.color} p-4 rounded-3xl text-white shadow-lg shadow-indigo-500/10`}>
                                    <Icon size={24} />
                                </div>
                                <div className={`flex items-center gap-1 text-[10px] font-black uppercase tracking-widest px-3 py-1.5 rounded-full ${card.isPositive ? 'bg-emerald-50 text-emerald-600' : 'bg-rose-50 text-rose-600'}`}>
                                    {card.isPositive ? <ArrowUpRight size={14} /> : <ArrowDownRight size={14} />}
                                    {card.trend}
                                </div>
                            </div>
                            <div>
                                <p className="text-[10px] font-black uppercase tracking-widest text-indigo-400 mb-1">{card.label}</p>
                                <h3 className="text-3xl font-black text-indigo-950 font-mono tracking-tighter">{card.value}</h3>
                            </div>
                        </motion.div>
                    );
                })}
            </div>

            {/* Pending Requests Section */}
            {pendingBusinesses.length > 0 && (
                <motion.div 
                    initial={{ opacity: 0, y: 20 }}
                    animate={{ opacity: 1, y: 0 }}
                    className="bg-rose-50 border border-rose-100 p-8 rounded-[3rem] shadow-sm overflow-hidden relative"
                >
                    <div className="flex flex-col md:flex-row md:items-center justify-between gap-6 relative z-10">
                        <div className="space-y-1">
                            <h4 className="text-xl font-black text-rose-950 flex items-center gap-3">
                                <Activity size={24} className="text-rose-500" />
                                Nuevas Solicitudes de Socios
                            </h4>
                            <p className="text-xs font-bold text-rose-400">Hay {pendingBusinesses.length} negocios esperando aprobación</p>
                        </div>
                        <div className="flex flex-wrap gap-4">
                            {pendingBusinesses.map((b) => (
                                <div key={b.id} className="bg-white p-4 rounded-3xl border border-rose-100 flex items-center gap-4 shadow-sm min-w-[280px]">
                                    <div className="w-12 h-12 bg-rose-50 rounded-2xl flex items-center justify-center text-rose-500 font-black">
                                        {b.nombre.charAt(0)}
                                    </div>
                                    <div className="flex-1">
                                        <p className="text-sm font-black text-rose-950">{b.nombre}</p>
                                        <p className="text-[10px] font-black uppercase tracking-widest text-rose-400">{b.categoria}</p>
                                    </div>
                                    <button 
                                        onClick={() => handleApprove(b.id)}
                                        className="bg-emerald-500 text-white p-2.5 rounded-xl hover:bg-emerald-400 transition-colors shadow-lg shadow-emerald-500/20"
                                    >
                                        <CheckCircle size={18} />
                                    </button>
                                </div>
                            ))}
                        </div>
                    </div>
                    {/* Decorative background circle */}
                    <div className="absolute top-0 right-0 w-32 h-32 bg-rose-100/50 blur-[50px] rounded-full -mr-10 -mt-10" />
                </motion.div>
            )}

            <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
                {/* Performance Chart */}
                <div className="lg:col-span-2 bg-white p-8 rounded-[3rem] border border-indigo-50 shadow-sm space-y-8">
                    <div className="flex items-center justify-between px-4">
                        <div className="space-y-1">
                            <h4 className="text-xl font-black text-indigo-950 flex items-center gap-3">
                                <Activity size={24} className="text-indigo-600" />
                                Rendimiento semanal
                            </h4>
                            <p className="text-xs font-bold text-indigo-400">Distribución de ventas por día</p>
                        </div>
                        <button className="p-3 bg-indigo-50 text-indigo-600 rounded-2xl hover:bg-indigo-100 transition-colors">
                            <MoreVertical size={20} />
                        </button>
                    </div>

                    <div className="h-[300px] w-full mt-4">
                        <ResponsiveContainer width="100%" height="100%">
                            <AreaChart data={dataChart}>
                                <defs>
                                    <linearGradient id="colorSales" x1="0" y1="0" x2="0" y2="1">
                                        <stop offset="5%" stopColor="#4f46e5" stopOpacity={0.1} />
                                        <stop offset="95%" stopColor="#4f46e5" stopOpacity={0} />
                                    </linearGradient>
                                </defs>
                                <CartesianGrid strokeDasharray="3 3" vertical={false} stroke="#f3f4f6" />
                                <XAxis dataKey="name" axisLine={false} tickLine={false} tick={{ fill: '#94a3b8', fontSize: 10, fontWeight: 900 }} />
                                <YAxis axisLine={false} tickLine={false} tick={{ fill: '#94a3b8', fontSize: 10, fontWeight: 900 }} tickFormatter={(val) => `$${val}`} />
                                <Tooltip />
                                <Area type="monotone" dataKey="sales" stroke="#4f46e5" strokeWidth={4} fillOpacity={1} fill="url(#colorSales)" />
                            </AreaChart>
                        </ResponsiveContainer>
                    </div>
                </div>

                {/* Recent Activity */}
                <div className="bg-indigo-950 text-white p-10 rounded-[3rem] shadow-2xl shadow-indigo-500/20 relative overflow-hidden flex flex-col">
                    <div className="relative z-10 space-y-8 flex-1">
                        <div className="space-y-2">
                            <h4 className="text-2xl font-black text-white px-2">Actividad Reciente</h4>
                            <p className="text-xs font-black uppercase tracking-widest text-indigo-400 px-2 opacity-80">Últimos pedidos del sistema</p>
                        </div>

                        <div className="space-y-3">
                            {latestOrders.map((order, i) => (
                                <motion.div
                                    key={order.id}
                                    initial={{ opacity: 0, x: 20 }}
                                    animate={{ opacity: 1, x: 0 }}
                                    transition={{ delay: i * 0.1 }}
                                    className="bg-indigo-900/40 border border-white/5 p-5 rounded-3xl flex items-center justify-between group hover:bg-indigo-900/60 transition-all cursor-pointer"
                                >
                                    <div className="flex items-center gap-4">
                                        <div className="w-12 h-12 bg-indigo-500/20 rounded-2xl flex items-center justify-center text-indigo-400 group-hover:scale-110 transition-transform">
                                            <Package size={20} />
                                        </div>
                                        <div>
                                            <p className="text-sm font-black truncate max-w-[120px]">{order.negocio.nombre}</p>
                                            <p className="text-[10px] font-black uppercase tracking-widest text-indigo-400">{new Date(order.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}</p>
                                        </div>
                                    </div>
                                    <div className="text-right">
                                        <p className="text-lg font-black text-white font-mono tracking-tighter italic">${Number(order.total).toFixed(2)}</p>
                                        <div className="flex items-center gap-1 justify-end">
                                            <div className="w-1.5 h-1.5 bg-emerald-500 rounded-full animate-pulse" />
                                            <p className="text-[9px] font-black uppercase text-emerald-500 tracking-widest">{order.estado}</p>
                                        </div>
                                    </div>
                                </motion.div>
                            ))}

                            {latestOrders.length === 0 && (
                                <div className="text-center py-12 space-y-4 opacity-40">
                                    <ShoppingBag size={48} className="mx-auto" />
                                    <p className="text-xs font-black uppercase tracking-widest">Sin actividad reciente</p>
                                </div>
                            )}
                        </div>

                        <button className="w-full bg-indigo-500 text-white p-5 rounded-[1.7rem] font-black hover:bg-indigo-400 transition-all flex items-center justify-center gap-3 mt-auto shadow-xl shadow-indigo-500/20 group">
                            <CreditCard size={20} />
                            Gestionar Cobros
                            <ArrowUpRight size={18} className="group-hover:translate-x-1 group-hover:-translate-y-1 transition-transform" />
                        </button>
                    </div>

                    {/* Background elements */}
                    <div className="absolute top-0 right-0 w-64 h-64 bg-indigo-500/10 blur-[100px] rounded-full" />
                    <div className="absolute bottom-0 left-0 w-64 h-64 bg-indigo-800/10 blur-[100px] rounded-full" />
                </div>
            </div>
        </div>
    );
}
