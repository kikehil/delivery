'use client';

import React from 'react';
import { CreditCard, Activity, ArrowUpRight, ShieldCheck } from 'lucide-react';
import { motion } from 'framer-motion';

export default function AdminPayments() {
    return (
        <div className="space-y-8 max-w-5xl mx-auto">
            <div className="text-center space-y-4 py-20">
                <div className="w-24 h-24 bg-indigo-50 rounded-[2.5rem] flex items-center justify-center text-indigo-600 mx-auto shadow-xl shadow-indigo-500/10">
                    <CreditCard size={48} strokeWidth={1.5} />
                </div>
                <div className="space-y-1">
                    <h1 className="text-4xl font-black text-indigo-950 tracking-tighter">Módulo de Cobros</h1>
                    <p className="text-indigo-400 font-bold max-w-md mx-auto">Próximamente estaremos integrando la pasarela de pagos automática para socios.</p>
                </div>
                <div className="pt-6">
                    <span className="p-4 bg-indigo-950 text-white rounded-3xl font-black text-sm uppercase tracking-widest flex items-center gap-3 w-fit mx-auto shadow-2xl shadow-indigo-500/30">
                        <ShieldCheck size={20} className="text-indigo-400" />
                        Seguridad Menuvi 256-bit
                    </span>
                </div>
            </div>
        </div>
    );
}
