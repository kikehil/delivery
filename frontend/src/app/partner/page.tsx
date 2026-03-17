'use client';

import React, { useEffect, useState } from 'react';
import api from '@/lib/api';
import { motion } from 'framer-motion';
import {
    TrendingUp,
    ShoppingBag,
    Users,
    CheckCircle2,
    Clock,
    AlertCircle,
    ArrowRight,
    UtensilsCrossed
} from 'lucide-react';
import Link from 'next/link';

interface DashboardData {
    business: any;
    stats: {
        pending_orders: number;
        today_orders: number;
        today_revenue: number;
    };
}

export default function PartnerDashboard() {
    const [data, setData] = useState<DashboardData | null>(null);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        async function fetchDashboard() {
            try {
                const { data } = await api.get('/partner/dashboard');
                setData(data.data);
            } catch (e) {
                console.error('Error fetching dashboard data');
            } finally {
                setLoading(false);
            }
        }
        fetchDashboard();
    }, []);

    if (loading) {
        return (
            <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
                {[1, 2, 3].map((n) => (
                    <div key={n} className="h-40 bg-white rounded-3xl border border-slate-100 animate-pulse" />
                ))}
            </div>
        );
    }

    const statCards = [
        {
            label: 'Ingresos hoy',
            value: `$${Number(data?.stats.today_revenue || 0).toLocaleString()}`,
            icon: TrendingUp,
            color: 'bg-emerald-500',
            description: 'Solo pedidos entregados'
        },
        {
            label: 'Pedidos hoy',
            value: data?.stats.today_orders || 0,
            icon: ShoppingBag,
            color: 'bg-slate-900',
            description: 'Total acumulado hoy'
        },
        {
            label: 'Pendientes',
            value: data?.stats.pending_orders || 0,
            icon: Clock,
            color: 'bg-orange-500',
            description: 'Requieren tu atención'
        },
    ];

    return (
        <div className="space-y-8">
            {/* Welcome Banner */}
            <motion.div
                initial={{ opacity: 0, y: 10 }}
                animate={{ opacity: 1, y: 0 }}
                className="bg-slate-900 text-white p-10 rounded-[2.5rem] relative overflow-hidden shadow-2xl shadow-slate-900/20"
            >
                <div className="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
                    <div className="space-y-4 max-w-lg">
                        <h1 className="text-4xl font-black text-white leading-tight">
                            ¡Hola, {data?.business.nombre}! 👋
                        </h1>
                        <p className="text-slate-300 font-bold text-lg leading-relaxed">
                            Hoy es un excelente día para vender. Tienes
                            <span className="text-orange-400"> {data?.stats.pending_orders} pedidos </span>
                            esperando a ser preparados.
                        </p>
                        <div className="pt-2">
                            <Link
                                href="/partner/orders"
                                className="inline-flex items-center gap-2 bg-white text-slate-900 px-6 py-3 rounded-2xl font-black hover:bg-slate-100 transition-all group"
                            >
                                Ver Pedidos
                                <ArrowRight size={20} className="group-hover:translate-x-1 transition-transform" />
                            </Link>
                        </div>
                    </div>

                    <div className="hidden lg:block relative w-64 h-64 opacity-20">
                        <div className="absolute inset-0 bg-gradient-to-br from-indigo-500 to-emerald-500 rounded-full blur-3xl animate-pulse" />
                    </div>
                </div>
            </motion.div>

            {/* Stats Grid */}
            <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
                {statCards.map((card, idx) => {
                    const Icon = card.icon;
                    return (
                        <motion.div
                            key={card.label}
                            initial={{ opacity: 0, y: 10 }}
                            animate={{ opacity: 1, y: 0 }}
                            transition={{ delay: idx * 0.1 }}
                            className="bg-white p-8 rounded-[2rem] border border-slate-100 shadow-sm space-y-4"
                        >
                            <div className="flex items-center justify-between">
                                <div className={`${card.color} p-4 rounded-2xl text-white shadow-lg shadow-${card.color.split('-')[1]}-500/20`}>
                                    <Icon size={24} />
                                </div>
                            </div>
                            <div>
                                <p className="text-slate-500 font-black text-xs uppercase tracking-widest leading-none mb-1">{card.label}</p>
                                <h3 className="text-4xl font-black text-slate-900 font-mono italic">{card.value}</h3>
                            </div>
                            <p className="text-slate-400 text-xs font-bold">{card.description}</p>
                        </motion.div>
                    );
                })}
            </div>

            {/* Shortcuts */}
            <div className="bg-white p-8 rounded-[2.5rem] border border-slate-100 shadow-sm space-y-6">
                <h2 className="text-xl font-black text-slate-900 flex items-center gap-2 px-2">
                    Accesos Rápidos
                </h2>
                <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <Link href="/partner/products" className="group p-6 rounded-3xl bg-slate-50 hover:bg-slate-900 transition-all duration-300">
                        <UtensilsCrossed size={32} className="text-slate-900 group-hover:text-white transition-colors mb-4" />
                        <p className="font-black text-slate-900 group-hover:text-white transition-colors">Gestionar Menú</p>
                        <p className="text-xs font-bold text-slate-500 group-hover:text-slate-400 transition-colors">Activa o pausa tus productos</p>
                    </Link>
                    <div className="group p-6 rounded-3xl bg-slate-50 hover:bg-slate-900 transition-all duration-300 cursor-pointer">
                        <TrendingUp size={32} className="text-slate-900 group-hover:text-white transition-colors mb-4" />
                        <p className="font-black text-slate-900 group-hover:text-white transition-colors">Análisis de Ventas</p>
                        <p className="text-xs font-bold text-slate-500 group-hover:text-slate-400 transition-colors">Gráficos de rendimiento</p>
                    </div>
                    <Link href="/partner/settings" className="group p-6 rounded-3xl bg-slate-50 hover:bg-slate-900 transition-all duration-300">
                        <Users size={32} className="text-slate-900 group-hover:text-white transition-colors mb-4" />
                        <p className="font-black text-slate-900 group-hover:text-white transition-colors">Configurar Tienda</p>
                        <p className="text-xs font-bold text-slate-500 group-hover:text-slate-400 transition-colors">Horarios y logos</p>
                    </Link>
                    <div className="group p-6 rounded-3xl bg-slate-50 hover:bg-slate-900 transition-all duration-300 cursor-pointer">
                        <AlertCircle size={32} className="text-slate-900 group-hover:text-white transition-colors mb-4" />
                        <p className="font-black text-slate-900 group-hover:text-white transition-colors">Soporte Técnico</p>
                        <p className="text-xs font-bold text-slate-500 group-hover:text-slate-400 transition-colors">Ayuda 24/7 de Menuvi</p>
                    </div>
                </div>
            </div>
        </div>
    );
}
