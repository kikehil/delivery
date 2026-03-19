'use client';

import React, { useEffect, useState } from 'react';
import api from '@/lib/api';
import { motion, AnimatePresence } from 'framer-motion';
import {
    ShoppingBag,
    MapPin,
    Clock,
    ChevronRight,
    User,
    CheckCircle2,
    Truck,
    Package,
    XCircle,
    AlertCircle
} from 'lucide-react';

interface Order {
    id: number;
    cliente_zona: string;
    items_json: any;
    total: number;
    estado: 'pendiente' | 'aceptado' | 'en_preparacion' | 'en_camino' | 'entregado' | 'cancelado';
    metodo_pago: string;
    modalidad: string;
    created_at: string;
}

export default function PartnerOrders() {
    const [orders, setOrders] = useState<Order[]>([]);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        fetchOrders();
        const interval = setInterval(fetchOrders, 30000); // 30s Refresh
        return () => clearInterval(interval);
    }, []);

    const fetchOrders = async () => {
        try {
            const { data } = await api.get('/partner/orders');
            setOrders(data.data);
        } catch (e) {
            console.error('Error fetching orders');
        } finally {
            setLoading(false);
        }
    };

    const updateStatus = async (id: number, newStatus: string) => {
        try {
            await api.put(`/partner/orders/${id}/status`, { estado: newStatus });
            setOrders(orders.map(o =>
                o.id === id ? { ...o, estado: newStatus as any } : o
            ));
        } catch (e) {
            console.error('Error updating status');
        }
    };

    const getStatusConfig = (status: string) => {
        switch (status) {
            case 'pendiente': return { color: 'bg-orange-100 text-orange-700', icon: AlertCircle, label: 'POR ACEPTAR', badge: 'bg-orange-500' };
            case 'aceptado': return { color: 'bg-blue-100 text-blue-700', icon: CheckCircle2, label: 'ACEPTADO', badge: 'bg-blue-500' };
            case 'en_preparacion': return { color: 'bg-indigo-100 text-indigo-700', icon: Package, label: 'PREPARANDO', badge: 'bg-indigo-500' };
            case 'en_camino': return { color: 'bg-emerald-100 text-emerald-700', icon: Truck, label: 'EN CAMINO', badge: 'bg-emerald-500' };
            case 'entregado': return { color: 'bg-slate-100 text-slate-500', icon: CheckCircle2, label: 'ENTREGADO', badge: 'bg-slate-300' };
            default: return { color: 'bg-rose-100 text-rose-700', icon: XCircle, label: 'CANCELADO', badge: 'bg-rose-500' };
        }
    };

    return (
        <div className="space-y-6">
            <div className="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h1 className="text-3xl font-black text-slate-900 tracking-tight leading-none mb-1">Pedidos Activos</h1>
                    <p className="text-slate-500 font-bold">Resumen de pedidos en curso.</p>
                </div>
                <div className="flex bg-white rounded-2xl p-1 shadow-sm border border-slate-100">
                    <button className="px-4 py-2 bg-slate-900 text-white rounded-xl font-bold text-sm transition-all focus:outline-none">Hoy</button>
                    <button className="px-4 py-2 text-slate-500 rounded-xl font-bold text-sm hover:bg-slate-50 transition-all focus:outline-none">Semana</button>
                </div>
            </div>

            <div className="space-y-4">
                {orders.length === 0 && !loading && (
                    <div className="py-24 text-center space-y-4 bg-white rounded-[2.5rem] border border-slate-100">
                        <ShoppingBag size={48} className="mx-auto text-slate-200" />
                        <p className="font-black text-slate-900 text-lg">No hay pedidos pendientes</p>
                        <p className="text-slate-400 font-bold">Relájate un poco, ¡ya vendrán!</p>
                    </div>
                )}

                <AnimatePresence>
                    {orders.map((order) => {
                        const config = getStatusConfig(order.estado);
                        const Icon = config.icon;
                        return (
                            <motion.div
                                key={order.id}
                                initial={{ opacity: 0, x: -10 }}
                                animate={{ opacity: 1, x: 0 }}
                                exit={{ opacity: 0, x: 10 }}
                                className="bg-white rounded-[2rem] border border-slate-100 p-6 flex flex-col md:flex-row md:items-center gap-6 shadow-sm hover:shadow-xl hover:shadow-slate-200/50 transition-all duration-300 group"
                            >
                                {/* Order Icon & Status */}
                                <div className="flex items-center gap-4 min-w-[180px]">
                                    <div className={`${config.color} p-4 rounded-2xl`}>
                                        <Icon size={24} />
                                    </div>
                                    <div>
                                        <span className="text-xs font-black text-slate-400 uppercase tracking-widest leading-none block mb-1">Orden #{order.id}</span>
                                        <span className={`text-[10px] font-black px-2 py-0.5 rounded text-white ${config.badge}`}>
                                            {config.label}
                                        </span>
                                    </div>
                                </div>

                                {/* Order Content */}
                                <div className="flex-1 space-y-1">
                                    <p className="text-slate-900 font-black text-lg line-clamp-1">
                                        {order.items_json.map((item: any) => `${item.qty}x ${item.nombre}`).join(', ')}
                                    </p>
                                    <div className="flex flex-wrap items-center gap-4 text-slate-500 text-xs font-bold leading-none">
                                        <div className="flex items-center gap-1.5"><MapPin size={14} className="text-slate-400" /> {order.cliente_zona}</div>
                                        <div className="flex items-center gap-1.5"><Clock size={14} className="text-slate-400" /> {new Date(order.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}</div>
                                        <div className="flex items-center gap-1.5"><User size={14} className="text-slate-400" /> {order.modalidad === 'delivery' ? 'Entrega a domicilio' : 'Para llevar'}</div>
                                    </div>
                                </div>

                                {/* Price & Actions */}
                                <div className="flex items-center justify-between md:justify-end gap-6 sm:pl-0 pl-16">
                                    <div className="text-right">
                                        <p className="text-3xl font-black text-slate-900 font-mono tracking-tighter leading-none italic">${parseFloat(order.total.toString()).toFixed(2)}</p>
                                        <p className="text-[10px] font-black text-slate-400 uppercase tracking-widest">{order.metodo_pago}</p>
                                    </div>

                                    <div className="flex items-center gap-2">
                                        {order.estado === 'pendiente' && (
                                            <button
                                                onClick={() => updateStatus(order.id, 'aceptado')}
                                                className="bg-slate-900 text-white px-6 py-3 rounded-2xl font-black text-sm hover:bg-slate-800 transition-all shadow-lg shadow-slate-900/10 active:scale-95"
                                            >
                                                Aceptar
                                            </button>
                                        )}
                                        {order.estado === 'aceptado' && (
                                            <button
                                                onClick={() => updateStatus(order.id, 'en_preparacion')}
                                                className="bg-indigo-600 text-white px-6 py-3 rounded-2xl font-black text-sm hover:bg-indigo-700 transition-all shadow-lg shadow-indigo-600/10 active:scale-95"
                                            >
                                                Preparar
                                            </button>
                                        )}
                                        {order.estado === 'en_preparacion' && (
                                            <button
                                                onClick={() => updateStatus(order.id, 'en_camino')}
                                                className="bg-emerald-600 text-white px-6 py-3 rounded-2xl font-black text-sm hover:bg-emerald-700 transition-all shadow-lg shadow-emerald-600/10 active:scale-95"
                                            >
                                                Notificar Envío
                                            </button>
                                        )}
                                        {order.estado === 'en_camino' && (
                                            <button
                                                onClick={() => updateStatus(order.id, 'entregado')}
                                                className="bg-slate-400 text-white px-6 py-3 rounded-2xl font-black text-sm hover:bg-slate-500 transition-all active:scale-95"
                                            >
                                                Finalizar
                                            </button>
                                        )}

                                        {(['pendiente', 'aceptado']).includes(order.estado) && (
                                            <button
                                                onClick={() => updateStatus(order.id, 'cancelado')}
                                                className="p-3 text-rose-500 hover:bg-rose-50 rounded-2xl transition-colors"
                                            >
                                                <XCircle size={24} />
                                            </button>
                                        )}

                                        <button className="p-3 text-slate-300 hover:bg-slate-50 rounded-2xl transition-all">
                                            <ChevronRight size={24} />
                                        </button>
                                    </div>
                                </div>
                            </motion.div>
                        );
                    })}
                </AnimatePresence>
            </div>
        </div>
    );
}
