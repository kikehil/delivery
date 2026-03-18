'use client';

import React, { useState } from 'react';
import { motion } from 'framer-motion';
import { 
    Store, 
    User, 
    Mail, 
    Lock, 
    Phone, 
    Tag, 
    ArrowRight,
    CheckCircle,
    ChevronLeft,
    MapPin,
    Clock,
    Truck,
    ShoppingBag,
    Utensils
} from 'lucide-react';
import Link from 'next/link';
import Image from 'next/image';
import api from '@/lib/api';

export default function PartnerRegisterPage() {
    const days = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'];
    
    interface DayConfig {
        open: string;
        close: string;
        active: boolean;
    }

    const [formData, setFormData] = useState({
        name: '',
        business_name: '',
        email: '',
        password: '',
        password_confirmation: '',
        phone: '',
        category: '',
        address: '',
        entrega_domicilio: true,
        recolecta_pedidos: true,
        consumo_sucursal: true,
        acepta_efectivo: true,
        acepta_tarjeta: false,
        acepta_transferencia: false,
        horarios: days.reduce((acc, day) => ({
            ...acc,
            [day]: { open: '09:00', close: '22:00', active: true }
        }), {} as Record<string, DayConfig>)
    });

    const [loading, setLoading] = useState(false);
    const [success, setSuccess] = useState(false);
    const [error, setError] = useState<string | null>(null);

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault();
        setLoading(true);
        setError(null);

        try {
            await api.post('/auth/register-partner', formData);
            setSuccess(true);
        } catch (err: any) {
            const message = err.response?.data?.message || 'Error al procesar el registro';
            setError(message);
        } finally {
            setLoading(false);
        }
    };

    if (success) {
        return (
            <div className="min-h-screen bg-[#0a0a0a] flex items-center justify-center p-6 text-white">
                <motion.div 
                    initial={{ scale: 0.9, opacity: 0 }}
                    animate={{ scale: 1, opacity: 1 }}
                    className="bg-[#1a1a1a] p-10 rounded-[3rem] border border-white/5 text-center max-w-md shadow-2xl"
                >
                    <div className="w-20 h-20 bg-emerald-500/10 text-emerald-500 rounded-full flex items-center justify-center mx-auto mb-6">
                        <CheckCircle size={40} />
                    </div>
                    <h1 className="text-3xl font-black mb-4 tracking-tight">¡Solicitud Enviada!</h1>
                    <p className="text-white/50 font-bold mb-8 leading-relaxed text-sm">
                        Hemos recibido tus datos correctamente. El equipo de administración revisará tu negocio y te contactará pronto vía email.
                    </p>
                    <Link 
                        href="/login"
                        className="block w-full bg-white text-black py-4 rounded-2xl font-black text-xs uppercase tracking-widest hover:bg-emerald-500 hover:text-white transition-all"
                    >
                        Volver al Inicio
                    </Link>
                </motion.div>
            </div>
        );
    }

    return (
        <div className="min-h-screen bg-[#0a0a0a] flex flex-col lg:flex-row">
            {/* Left Column: Marketing Section */}
            <div className="relative w-full lg:w-1/2 h-64 lg:h-screen flex items-center justify-center overflow-hidden">
                {/* Background Marketing Image */}
                <div className="absolute inset-0 z-0">
                    <Image 
                        src="/partner_bg.png" 
                        alt="Join Menuvi" 
                        fill 
                        className="object-cover opacity-60"
                        priority
                    />
                    <div className="absolute inset-0 bg-gradient-to-t lg:bg-gradient-to-r from-black/60 via-black/20 to-transparent z-10" />
                </div>

                {/* Marketing Content */}
                <div className="relative z-20 p-8 lg:p-16 w-full max-w-2xl text-center lg:text-left">
                    <Link href="/" className="relative inline-block h-12 lg:h-20 w-44 lg:w-64 mb-8 lg:mb-16">
                        <Image 
                            src="/logo1.png" 
                            alt="Menuvi Logo" 
                            fill 
                            className="object-contain object-left"
                            priority
                        />
                    </Link>
                    
                    <motion.div
                        initial={{ opacity: 0, y: 20 }}
                        animate={{ opacity: 1, y: 0 }}
                        transition={{ delay: 0.2 }}
                        className="space-y-4 lg:space-y-6"
                    >
                        <h2 className="text-3xl lg:text-7xl font-black text-white leading-tight tracking-tighter">
                            Empieza <span className="text-orange-500">GRATIS</span> <br className="hidden lg:block" /> 15 días
                        </h2>
                        <p className="text-white/60 text-sm lg:text-xl font-bold leading-relaxed max-w-lg mx-auto lg:mx-0">
                            Digitaliza tu menú, gestiona tus pedidos y aumenta tus ventas con la plataforma de delivery más innovadora.
                        </p>
                        
                        <div className="hidden lg:flex gap-4 pt-8">
                            <div className="bg-white/10 backdrop-blur-md p-6 rounded-3xl border border-white/10 flex-1">
                                <h4 className="text-white font-black mb-1">+ Ventas</h4>
                                <p className="text-white/40 text-[10px] font-black uppercase tracking-widest leading-none">Aumenta tu alcance</p>
                            </div>
                            <div className="bg-white/10 backdrop-blur-md p-6 rounded-3xl border border-white/10 flex-1">
                                <h4 className="text-white font-black mb-1">0% Estrés</h4>
                                <p className="text-white/40 text-[10px] font-black uppercase tracking-widest leading-none">Gestión simplificada</p>
                            </div>
                        </div>
                    </motion.div>
                </div>
            </div>

            {/* Right Column: Registration Form */}
            <div className="w-full lg:w-1/2 flex items-center justify-center p-6 lg:p-12 overflow-y-auto lg:h-screen bg-[#0a0a0a]">
                <motion.div 
                    initial={{ x: 20, opacity: 0 }}
                    animate={{ x: 0, opacity: 1 }}
                    className="w-full max-w-md space-y-8 py-12"
                >
                    <Link href="/login" className="flex items-center gap-2 text-white/40 hover:text-white transition-colors text-xs font-black uppercase tracking-widest mb-4">
                        <ChevronLeft size={16} />
                        Volver
                    </Link>

                    <div>
                        <h1 className="text-4xl font-black text-white tracking-tighter mb-2">Crea tu cuenta de restaurante</h1>
                        <p className="text-white/30 font-bold">Completa el formulario para dar de alta tu negocio gratis por 15 días.</p>
                    </div>

                    <form onSubmit={handleSubmit} className="space-y-4">
                        {error && (
                            <div className="p-4 bg-red-500/10 border border-red-500/20 rounded-2xl text-red-500 text-xs font-bold">
                                {error}
                            </div>
                        )}

                        <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div className="space-y-2">
                                <label className="text-[10px] font-black uppercase tracking-widest text-white/30 pl-1">Tu Nombre</label>
                                <div className="relative group">
                                    <User className="absolute left-4 top-1/2 -translate-y-1/2 text-white/20 group-focus-within:text-white transition-colors" size={18} />
                                    <input 
                                        type="text" 
                                        required
                                        className="w-full pl-12 pr-4 py-4 bg-white/5 border border-white/5 rounded-2xl text-white focus:ring-1 focus:ring-white/20 outline-none font-bold text-sm"
                                        placeholder="Ej. Juan Pérez"
                                        value={formData.name}
                                        onChange={e => setFormData({...formData, name: e.target.value})}
                                    />
                                </div>
                            </div>
                            <div className="space-y-2">
                                <label className="text-[10px] font-black uppercase tracking-widest text-white/30 pl-1">Nombre del Negocio</label>
                                <div className="relative group">
                                    <Store className="absolute left-4 top-1/2 -translate-y-1/2 text-white/20 group-focus-within:text-white transition-colors" size={18} />
                                    <input 
                                        type="text" 
                                        required
                                        className="w-full pl-12 pr-4 py-4 bg-white/5 border border-white/5 rounded-2xl text-white focus:ring-1 focus:ring-white/20 outline-none font-bold text-sm"
                                        placeholder="Ej. Taquería El Jefe"
                                        value={formData.business_name}
                                        onChange={e => setFormData({...formData, business_name: e.target.value})}
                                    />
                                </div>
                            </div>
                        </div>

                        <div className="space-y-2">
                            <label className="text-[10px] font-black uppercase tracking-widest text-white/30 pl-1">Email Corporativo</label>
                            <div className="relative group">
                                <Mail className="absolute left-4 top-1/2 -translate-y-1/2 text-white/20 group-focus-within:text-white transition-colors" size={18} />
                                <input 
                                    type="email" 
                                    required
                                    className="w-full pl-12 pr-4 py-4 bg-white/5 border border-white/5 rounded-2xl text-white focus:ring-1 focus:ring-white/20 outline-none font-bold text-sm"
                                    placeholder="contacto@negocio.com"
                                    value={formData.email}
                                    onChange={e => setFormData({...formData, email: e.target.value})}
                                />
                            </div>
                        </div>

                        <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div className="space-y-2">
                                <label className="text-[10px] font-black uppercase tracking-widest text-white/30 pl-1">WhatsApp</label>
                                <div className="relative group">
                                    <Phone className="absolute left-4 top-1/2 -translate-y-1/2 text-white/20 group-focus-within:text-white transition-colors" size={18} />
                                    <input 
                                        type="text" 
                                        required
                                        className="w-full pl-12 pr-4 py-4 bg-white/5 border border-white/5 rounded-2xl text-white focus:ring-1 focus:ring-white/20 outline-none font-bold text-sm"
                                        placeholder="52..."
                                        value={formData.phone}
                                        onChange={e => setFormData({...formData, phone: e.target.value})}
                                    />
                                </div>
                            </div>
                            <div className="space-y-2">
                                <label className="text-[10px] font-black uppercase tracking-widest text-white/30 pl-1">Categoría</label>
                                <div className="relative group">
                                    <Tag className="absolute left-4 top-1/2 -translate-y-1/2 text-white/20 group-focus-within:text-white transition-colors" size={18} />
                                    <select 
                                        required
                                        className="w-full pl-12 pr-4 py-4 bg-white/5 border border-white/5 rounded-2xl text-white focus:ring-1 focus:ring-white/20 outline-none font-bold text-sm appearance-none"
                                        value={formData.category}
                                        onChange={e => setFormData({...formData, category: e.target.value})}
                                    >
                                        <option value="" className="bg-[#1a1a1a]">Seleccionar...</option>
                                        <option value="Antojitos" className="bg-[#1a1a1a]">Antojitos</option>
                                        <option value="Hamburguesas" className="bg-[#1a1a1a]">Hamburguesas</option>
                                        <option value="Hotdog" className="bg-[#1a1a1a]">Hotdogs</option>
                                        <option value="Tacos" className="bg-[#1a1a1a]">Tacos</option>
                                        <option value="Pizza" className="bg-[#1a1a1a]">Pizzas</option>
                                        <option value="Snacks" className="bg-[#1a1a1a]">Snacks</option>
                                        <option value="Postres" className="bg-[#1a1a1a]">Postres / Pasteles</option>
                                        <option value="Cenas" className="bg-[#1a1a1a]">Cenas</option>
                                        <option value="Desayunos" className="bg-[#1a1a1a]">Desayunos</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div className="space-y-2">
                            <label className="text-[10px] font-black uppercase tracking-widest text-white/30 pl-1">Dirección Física o Ubicación</label>
                            <div className="relative group">
                                <MapPin className="absolute left-4 top-1/2 -translate-y-1/2 text-white/20 group-focus-within:text-white transition-colors" size={18} />
                                <input 
                                    type="text" 
                                    required
                                    className="w-full pl-12 pr-4 py-4 bg-white/5 border border-white/5 rounded-2xl text-white focus:ring-1 focus:ring-white/20 outline-none font-bold text-sm"
                                    placeholder="Calle, Número, Colonia, Ciudad"
                                    value={formData.address}
                                    onChange={e => setFormData({...formData, address: e.target.value})}
                                />
                            </div>
                        </div>

                        <div className="space-y-3 p-6 bg-white/5 rounded-3xl border border-white/5">
                            <label className="text-[10px] font-black uppercase tracking-widest text-white/30">Opciones de Servicio</label>
                            <div className="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                {[
                                    { key: 'entrega_domicilio', label: 'Domicilio', icon: Truck },
                                    { key: 'recolecta_pedidos', label: 'Llevar', icon: ShoppingBag },
                                    { key: 'consumo_sucursal', label: 'Local', icon: Utensils }
                                ].map(opt => (
                                    <button
                                        key={opt.key}
                                        type="button"
                                        onClick={() => setFormData({...formData, [opt.key]: !formData[opt.key as keyof typeof formData]})}
                                        className={`flex items-center gap-2 p-3 rounded-2xl border transition-all ${formData[opt.key as keyof typeof formData] ? 'bg-orange-500/10 border-orange-500/50 text-orange-500' : 'bg-white/5 border-white/5 text-white/20'}`}
                                    >
                                        <opt.icon size={16} />
                                        <span className="text-[10px] font-black uppercase tracking-widest">{opt.label}</span>
                                    </button>
                                ))}
                            </div>
                        </div>
                        <div className="space-y-3 p-6 bg-white/5 rounded-3xl border border-white/5">
                            <label className="text-[10px] font-black uppercase tracking-widest text-white/30">Métodos de Pago Aceptados</label>
                            <div className="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                {[
                                    { key: 'acepta_efectivo', label: 'Efectivo', icon: CheckCircle },
                                    { key: 'acepta_tarjeta', label: 'Tarjeta', icon: CheckCircle },
                                    { key: 'acepta_transferencia', label: 'Transf.', icon: CheckCircle }
                                ].map(opt => (
                                    <button
                                        key={opt.key}
                                        type="button"
                                        onClick={() => setFormData({...formData, [opt.key]: !formData[opt.key as keyof typeof formData]})}
                                        className={`flex items-center gap-2 p-3 rounded-2xl border transition-all ${formData[opt.key as keyof typeof formData] ? 'bg-cyan-500/10 border-cyan-500/50 text-cyan-400' : 'bg-white/5 border-white/5 text-white/20'}`}
                                    >
                                        <opt.icon size={16} />
                                        <span className="text-[10px] font-black uppercase tracking-widest">{opt.label}</span>
                                    </button>
                                ))}
                            </div>
                        </div>

                        <div className="space-y-4">
                            <div className="flex items-center justify-between pl-1">
                                <label className="text-[10px] font-black uppercase tracking-widest text-white/30">Horarios de Atención</label>
                                <span className="text-[9px] font-bold text-white/20 uppercase tracking-widest">Configura tus horas</span>
                            </div>
                            
                            <div className="bg-white/5 rounded-3xl border border-white/5 overflow-hidden divide-y divide-white/5">
                                {days.map(day => (
                                    <div key={day} className="flex items-center justify-between p-4 group">
                                        <div className="flex items-center gap-3">
                                            <button 
                                                type="button"
                                                onClick={() => {
                                                    const h = {...formData.horarios as any};
                                                    h[day].active = !h[day].active;
                                                    setFormData({...formData, horarios: h});
                                                }}
                                                className={`w-4 h-4 rounded-full border-2 transition-all ${formData.horarios[day as keyof typeof formData.horarios].active ? 'bg-orange-500 border-orange-500' : 'border-white/10'}`}
                                            />
                                            <span className={`text-xs font-black uppercase tracking-widest ${formData.horarios[day as keyof typeof formData.horarios].active ? 'text-white' : 'text-white/20'}`}>{day}</span>
                                        </div>
                                        
                                        <div className={`flex items-center gap-2 transition-opacity ${formData.horarios[day as keyof typeof formData.horarios].active ? 'opacity-100' : 'opacity-20 pointer-events-none'}`}>
                                            <input 
                                                type="time" 
                                                className="bg-transparent text-white text-[10px] font-bold outline-none border border-white/10 rounded-lg p-1"
                                                value={formData.horarios[day as keyof typeof formData.horarios].open}
                                                onChange={e => {
                                                    const h = {...formData.horarios as any};
                                                    h[day].open = e.target.value;
                                                    setFormData({...formData, horarios: h});
                                                }}
                                            />
                                            <span className="text-white/10">-</span>
                                            <input 
                                                type="time" 
                                                className="bg-transparent text-white text-[10px] font-bold outline-none border border-white/10 rounded-lg p-1"
                                                value={formData.horarios[day as keyof typeof formData.horarios].close}
                                                onChange={e => {
                                                    const h = {...formData.horarios as any};
                                                    h[day].close = e.target.value;
                                                    setFormData({...formData, horarios: h});
                                                }}
                                            />
                                        </div>
                                    </div>
                                ))}
                            </div>
                        </div>

                        <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div className="space-y-2">
                                <label className="text-[10px] font-black uppercase tracking-widest text-white/30 pl-1">Contraseña</label>
                                <div className="relative group">
                                    <Lock className="absolute left-4 top-1/2 -translate-y-1/2 text-white/20 group-focus-within:text-white transition-colors" size={18} />
                                    <input 
                                        type="password" 
                                        required
                                        className="w-full pl-12 pr-4 py-4 bg-white/5 border border-white/5 rounded-2xl text-white focus:ring-1 focus:ring-white/20 outline-none font-bold text-sm"
                                        placeholder="••••••••"
                                        value={formData.password}
                                        onChange={e => setFormData({...formData, password: e.target.value})}
                                    />
                                </div>
                            </div>
                            <div className="space-y-2">
                                <label className="text-[10px] font-black uppercase tracking-widest text-white/30 pl-1">Confirmar</label>
                                <div className="relative group">
                                    <Lock className="absolute left-4 top-1/2 -translate-y-1/2 text-white/20 group-focus-within:text-white transition-colors" size={18} />
                                    <input 
                                        type="password" 
                                        required
                                        className="w-full pl-12 pr-4 py-4 bg-white/5 border border-white/5 rounded-2xl text-white focus:ring-1 focus:ring-white/20 outline-none font-bold text-sm"
                                        value={formData.password_confirmation}
                                        onChange={e => setFormData({...formData, password_confirmation: e.target.value})}
                                    />
                                </div>
                            </div>
                        </div>

                        <button 
                            type="submit"
                            disabled={loading}
                            className="w-full bg-orange-500 text-white py-4 rounded-2xl font-black text-xs uppercase tracking-widest flex items-center justify-center gap-3 hover:bg-orange-600 transition-all shadow-xl shadow-orange-500/20 active:scale-95 disabled:opacity-50"
                        >
                            {loading ? (
                                <div className="w-5 h-5 border-2 border-white border-t-transparent rounded-full animate-spin" />
                            ) : (
                                <>
                                    Crear Cuenta y Continuar <ArrowRight size={18} />
                                </>
                            )}
                        </button>

                        <p className="text-center text-white/30 text-[10px] font-black uppercase tracking-widest pt-4">
                            ¿Ya tienes cuenta? <Link href="/login" className="text-white hover:text-orange-500 transition-colors">Iniciar Sesión</Link>
                        </p>
                    </form>
                </motion.div>
            </div>
        </div>
    );
}
