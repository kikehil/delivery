'use client';

import React, { useState, useEffect } from 'react';
import { motion, AnimatePresence } from 'framer-motion';
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
    Utensils,
    CreditCard,
    DollarSign,
    Repeat,
    ShieldCheck,
    Briefcase
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

    const [currentStep, setCurrentStep] = useState(1);
    const [formData, setFormData] = useState({
        first_name: '',
        last_name: '',
        business_name: '',
        email: '',
        password: '',
        password_confirmation: '',
        phone: '',
        category: '',
        address: '',
        entrega_domicilio: true,
        recolecta_pedidos: true,
        consumo_sucursal: false,
        acepta_efectivo: true,
        acepta_tarjeta: false,
        acepta_transferencia: false,
        horarios: days.reduce((acc, day) => ({
            ...acc,
            [day]: { open: '09:00', close: '22:00', active: false } // Inactivo por default
        }), {} as Record<string, DayConfig>)
    });

    const [loading, setLoading] = useState(false);
    const [success, setSuccess] = useState(false);
    const [error, setError] = useState<string | null>(null);
    const [passwordStrength, setPasswordStrength] = useState(0);

    // Calculate password strength
    useEffect(() => {
        const pass = formData.password;
        let score = 0;
        if (pass.length > 5) score += 25;
        if (/[A-Z]/.test(pass)) score += 25;
        if (/[0-9]/.test(pass)) score += 25;
        if (/[^A-Za-z0-9]/.test(pass)) score += 25;
        setPasswordStrength(score);
    }, [formData.password]);

    const handleSubmit = async () => {
        setLoading(true);
        setError(null);

        // Map first/last name back to single name field if API expects it
        const submissionData = {
            ...formData,
            name: `${formData.first_name} ${formData.last_name}`
        };

        try {
            await api.post('/auth/register-partner', submissionData);
            setSuccess(true);
        } catch (err: any) {
            console.error('Error de registro:', err.response?.data);
            
            // Try to extract the most descriptive error message
            let message = 'Error al procesar el registro';
            
            if (err.response?.data) {
                const data = err.response.data;
                
                if (data.message) {
                    message = data.message;
                } else if (data.error) {
                    message = data.error;
                } else if (typeof data === 'object') {
                    // It might be a validation error object: { email: ["..."], phone: ["..."] }
                    const firstKey = Object.keys(data)[0];
                    if (firstKey && Array.isArray(data[firstKey])) {
                        message = `${firstKey}: ${data[firstKey][0]}`;
                    } else if (typeof data[firstKey] === 'string') {
                        message = data[firstKey];
                    }
                }
            } else if (err.message) {
                message = err.message;
            }
            
            setError(message);
        } finally {
            setLoading(false);
        }
    };

    const nextStep = () => {
        if (currentStep < 4) setCurrentStep(currentStep + 1);
        else handleSubmit();
    };

    const prevStep = () => {
        if (currentStep > 1) setCurrentStep(currentStep - 1);
    };

    const steps = [
        { id: 1, title: 'Tus datos', subtitle: 'Datos personales' },
        { id: 2, title: 'Tu negocio', subtitle: 'Información del local' },
        { id: 3, title: 'Operación', subtitle: 'Servicios y horarios' },
        { id: 4, title: 'Seguridad', subtitle: 'Acceso a la cuenta' }
    ];

    if (success) {
        return (
            <div className="min-h-screen bg-[#070707] flex items-center justify-center p-6 text-white">
                <motion.div 
                    initial={{ scale: 0.9, opacity: 0 }}
                    animate={{ scale: 1, opacity: 1 }}
                    className="bg-[#111111] p-12 rounded-[3.5rem] border border-white/5 text-center max-w-lg shadow-2xl relative overflow-hidden"
                >
                    <div className="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-orange-500 to-orange-600" />
                    <div className="w-24 h-24 bg-orange-500/10 text-orange-500 rounded-full flex items-center justify-center mx-auto mb-8">
                        <CheckCircle size={48} />
                    </div>
                    <h1 className="text-4xl font-black mb-4 tracking-tighter">¡Registro Exitoso!</h1>
                    <p className="text-white/50 font-medium mb-10 leading-relaxed">
                        Bienvenido a la comunidad de <span className="text-white font-bold">menuvi</span>. 
                        Tu solicitud está siendo procesada. Te notificaremos vía WhatsApp y correo una vez que tu panel esté listo.
                    </p>
                    <Link 
                        href="/login"
                        className="block w-full bg-orange-500 text-white py-5 rounded-2xl font-black text-xs uppercase tracking-[0.2em] hover:bg-orange-600 transition-all shadow-lg shadow-orange-500/20 active:scale-[0.98]"
                    >
                        Acceder a mi panel
                    </Link>
                </motion.div>
            </div>
        );
    }

    return (
        <div className="min-h-screen bg-[#070707] flex flex-col lg:flex-row font-sans text-white selection:bg-orange-500/30">
            {/* Sidebar Navigation */}
            <div className="w-full lg:w-[380px] bg-[#0c0c0c] border-r border-white/5 flex flex-col p-8 lg:p-12 relative">
                <div className="flex flex-col h-full">
                    {/* Brand */}
                    <div className="mb-16">
                        <div className="flex items-center gap-2 mb-6">
                            <span className="text-2xl font-black tracking-tighter">menu<span className="text-orange-500">vi</span></span>
                        </div>
                        <div className="inline-flex items-center gap-2 px-3 py-1 bg-white/5 border border-white/10 rounded-full">
                            <div className="w-1.5 h-1.5 bg-orange-500 rounded-full animate-pulse" />
                            <span className="text-[10px] font-black uppercase tracking-widest text-white/50">15 días gratis</span>
                        </div>
                    </div>

                    {/* Hero Text */}
                    <div className="mb-16">
                        <h2 className="text-4xl lg:text-5xl font-black tracking-tighter leading-[0.9] mb-4">
                            Empieza a <br /> vender <span className="text-orange-500">hoy</span>
                        </h2>
                        <p className="text-white/40 text-sm font-medium leading-relaxed max-w-[240px]">
                            Digitaliza tu menú, gestiona pedidos y aumenta tus ventas con la plataforma de delivery más innovadora.
                        </p>
                    </div>

                    {/* Features List */}
                    <div className="hidden lg:flex flex-col gap-6 mb-auto">
                        {[
                            { icon: ArrowRight, text: 'Más ventas, mayor alcance' },
                            { icon: Truck, text: 'Domicilio, llevar o local' },
                            { icon: Repeat, text: 'Gestión desde WhatsApp' },
                            { icon: Clock, text: 'Configuración en minutos' }
                        ].map((item, i) => (
                            <div key={i} className="flex items-center gap-4 text-white/60">
                                <div className="w-8 h-8 rounded-xl bg-white/5 flex items-center justify-center">
                                    <item.icon size={14} className="text-orange-500" />
                                </div>
                                <span className="text-xs font-bold tracking-tight">{item.text}</span>
                            </div>
                        ))}
                    </div>

                    {/* Sidebar Stepper */}
                    <div className="mt-12 flex flex-col gap-8">
                        {steps.map((step) => (
                            <div 
                                key={step.id}
                                className={`flex items-center gap-4 transition-all duration-300 ${currentStep === step.id ? 'opacity-100 translate-x-2' : 'opacity-30'}`}
                            >
                                <div className={`w-8 h-8 rounded-full flex items-center justify-center border-2 font-black text-xs transition-all ${
                                    currentStep > step.id ? 'bg-orange-500 border-orange-500 text-white' : 
                                    currentStep === step.id ? 'border-orange-500 text-orange-500' : 'border-white/20 text-white/50'
                                }`}>
                                    {currentStep > step.id ? <CheckCircle size={14} /> : step.id}
                                </div>
                                <div>
                                    <h4 className="text-[10px] font-black uppercase tracking-widest leading-none mb-1">{step.title}</h4>
                                    <p className="text-[11px] font-bold text-white/40">{step.subtitle}</p>
                                </div>
                            </div>
                        ))}
                    </div>
                </div>
            </div>

            {/* Main Content Area */}
            <div className="flex-1 flex flex-col relative overflow-hidden">
                {/* Top Progress Bar */}
                <div className="absolute top-0 left-0 w-full h-1 bg-white/5">
                    <motion.div 
                        initial={{ width: '0%' }}
                        animate={{ width: `${(currentStep / 4) * 100}%` }}
                        className="h-full bg-gradient-to-r from-orange-500 to-orange-600 shadow-[0_0_15px_rgba(249,115,22,0.5)]"
                    />
                </div>

                <div className="flex-1 flex flex-col items-center justify-center p-6 lg:p-12 overflow-y-auto">
                    <motion.div 
                        key={currentStep}
                        initial={{ x: 20, opacity: 0 }}
                        animate={{ x: 0, opacity: 1 }}
                        exit={{ x: -20, opacity: 0 }}
                        className="w-full max-w-xl py-12"
                    >
                        {/* Header Section */}
                        <div className="mb-12">
                            <span className="text-[10px] font-black uppercase tracking-[0.3em] text-orange-500 mb-2 block">Paso {currentStep} de 4</span>
                            <h1 className="text-5xl font-black text-white tracking-tighter mb-4">
                                {currentStep === 1 && 'Tus datos personales'}
                                {currentStep === 2 && 'Tu negocio'}
                                {currentStep === 3 && 'Operación'}
                                {currentStep === 4 && 'Seguridad'}
                            </h1>
                            <p className="text-white/40 font-bold text-lg">
                                {currentStep === 1 && '¿Quién está registrando el negocio?'}
                                {currentStep === 2 && 'Cuéntanos sobre tu establecimiento.'}
                                {currentStep === 3 && 'Configura cómo funcionará tu servicio.'}
                                {currentStep === 4 && 'Protege el acceso a tu plataforma.'}
                            </p>
                        </div>

                        {/* Error Message */}
                        {error && (
                            <motion.div 
                                initial={{ height: 0, opacity: 0 }}
                                animate={{ height: 'auto', opacity: 1 }}
                                className="mb-8 p-4 bg-red-500/10 border border-red-500/20 rounded-2xl text-red-500 text-xs font-bold flex items-center gap-3"
                            >
                                <div className="w-2 h-2 rounded-full bg-red-500 animate-pulse" />
                                {error}
                            </motion.div>
                        )}

                        {/* Form Steps */}
                        <div className="space-y-8">
                            {/* Step 1: Personal Data */}
                            {currentStep === 1 && (
                                <div className="space-y-6">
                                    <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                        <div className="space-y-2">
                                            <label className="text-[10px] font-black uppercase tracking-widest text-white/30 pl-1">Tu Nombre</label>
                                            <div className="relative">
                                                <input 
                                                    type="text" 
                                                    className="w-full px-6 py-5 bg-white/5 border border-white/5 rounded-2xl text-white focus:ring-1 focus:ring-orange-500/50 focus:border-orange-500/50 outline-none font-bold text-sm transition-all placeholder:text-white/10"
                                                    placeholder="Ej. Juan"
                                                    value={formData.first_name}
                                                    onChange={e => setFormData({...formData, first_name: e.target.value})}
                                                />
                                            </div>
                                        </div>
                                        <div className="space-y-2">
                                            <label className="text-[10px] font-black uppercase tracking-widest text-white/30 pl-1">Apellido</label>
                                            <input 
                                                type="text" 
                                                className="w-full px-6 py-5 bg-white/5 border border-white/5 rounded-2xl text-white focus:ring-1 focus:ring-orange-500/50 outline-none font-bold text-sm transition-all placeholder:text-white/10"
                                                placeholder="Ej. Pérez"
                                                value={formData.last_name}
                                                onChange={e => setFormData({...formData, last_name: e.target.value})}
                                            />
                                        </div>
                                    </div>

                                    <div className="space-y-2">
                                        <label className="text-[10px] font-black uppercase tracking-widest text-white/30 pl-1">Email Corporativo</label>
                                        <input 
                                            type="email" 
                                            className="w-full px-6 py-5 bg-white/5 border border-white/5 rounded-2xl text-white focus:ring-1 focus:ring-orange-500/50 outline-none font-bold text-sm transition-all placeholder:text-white/10"
                                            placeholder="contacto@tunegocio.com"
                                            value={formData.email}
                                            onChange={e => setFormData({...formData, email: e.target.value})}
                                        />
                                    </div>

                                    <div className="space-y-2">
                                        <label className="text-[10px] font-black uppercase tracking-widest text-white/30 pl-1">WhatsApp de contacto</label>
                                        <div className="flex gap-3">
                                            <div className="w-24 px-4 py-5 bg-white/5 border border-white/5 rounded-2xl text-white/30 font-black text-xs flex items-center justify-center">
                                                +52 MX
                                            </div>
                                            <input 
                                                type="tel" 
                                                className="flex-1 px-6 py-5 bg-white/5 border border-white/5 rounded-2xl text-white focus:ring-1 focus:ring-orange-500/50 outline-none font-bold text-sm transition-all placeholder:text-white/10"
                                                placeholder="Ej. 8331234567"
                                                value={formData.phone}
                                                onChange={e => setFormData({...formData, phone: e.target.value})}
                                            />
                                        </div>
                                    </div>
                                </div>
                            )}

                            {/* Step 2: Business Info */}
                            {currentStep === 2 && (
                                <div className="space-y-6">
                                    <div className="space-y-2">
                                        <label className="text-[10px] font-black uppercase tracking-widest text-white/30 pl-1">Nombre del Local</label>
                                        <input 
                                            type="text" 
                                            className="w-full px-6 py-5 bg-white/5 border border-white/5 rounded-2xl text-white focus:ring-1 focus:ring-orange-500/50 outline-none font-bold text-sm transition-all placeholder:text-white/10"
                                            placeholder="Ej. Tacos El Rey"
                                            value={formData.business_name}
                                            onChange={e => setFormData({...formData, business_name: e.target.value})}
                                        />
                                    </div>

                                    <div className="space-y-2">
                                        <label className="text-[10px] font-black uppercase tracking-widest text-white/30 pl-1">Categoría</label>
                                        <select 
                                            className="w-full px-6 py-5 bg-white/5 border border-white/5 rounded-2xl text-white focus:ring-1 focus:ring-orange-500/50 outline-none font-bold text-sm appearance-none"
                                            value={formData.category}
                                            onChange={e => setFormData({...formData, category: e.target.value})}
                                        >
                                            <option value="" className="bg-[#111]">Seleccionar...</option>
                                            <option value="Antojitos" className="bg-[#111]">Antojitos</option>
                                            <option value="Hamburguesas" className="bg-[#111]">Hamburguesas</option>
                                            <option value="Pizza" className="bg-[#111]">Pizza</option>
                                            <option value="Sushi" className="bg-[#111]">Sushi / Japonesa</option>
                                            <option value="Postres" className="bg-[#111]">Postres / Repostería</option>
                                            <option value="Cafeteria" className="bg-[#111]">Cafetería</option>
                                        </select>
                                    </div>

                                    <div className="space-y-2">
                                        <label className="text-[10px] font-black uppercase tracking-widest text-white/30 pl-1">Dirección exacta</label>
                                        <div className="relative">
                                            <MapPin className="absolute left-6 top-1/2 -translate-y-1/2 text-white/10" size={18} />
                                            <input 
                                                type="text" 
                                                className="w-full pl-14 pr-6 py-5 bg-white/5 border border-white/5 rounded-2xl text-white focus:ring-1 focus:ring-orange-500/50 outline-none font-bold text-sm transition-all placeholder:text-white/10"
                                                placeholder="Calle, número, colonia..."
                                                value={formData.address}
                                                onChange={e => setFormData({...formData, address: e.target.value})}
                                            />
                                        </div>
                                    </div>
                                </div>
                            )}

                            {/* Step 3: Operation */}
                            {currentStep === 3 && (
                                <div className="space-y-8">
                                    {/* Services */}
                                    <div className="space-y-3">
                                        <label className="text-[10px] font-black uppercase tracking-widest text-white/30 pl-1">Canales de Servicio</label>
                                        <div className="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                            {[
                                                { key: 'entrega_domicilio', label: 'A Domicilio', icon: Truck },
                                                { key: 'recolecta_pedidos', label: 'Para Llevar', icon: ShoppingBag },
                                                { key: 'consumo_sucursal', label: 'En Local', icon: Utensils }
                                            ].map(opt => (
                                                <button
                                                    key={opt.key}
                                                    type="button"
                                                    onClick={() => setFormData({...formData, [opt.key]: !formData[opt.key as keyof typeof formData]})}
                                                    className={`flex flex-col items-center justify-center gap-3 p-6 rounded-3xl border transition-all ${formData[opt.key as keyof typeof formData] ? 'bg-orange-500 border-orange-500 text-white' : 'bg-white/5 border-white/5 text-white/20 hover:border-white/10'}`}
                                                >
                                                    <opt.icon size={24} />
                                                    <span className="text-[9px] font-black uppercase tracking-widest">{opt.label}</span>
                                                </button>
                                            ))}
                                        </div>
                                    </div>

                                    {/* Payments */}
                                    <div className="space-y-3">
                                        <label className="text-[10px] font-black uppercase tracking-widest text-white/30 pl-1">Métodos de Pago</label>
                                        <div className="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                            {[
                                                { key: 'acepta_efectivo', label: 'Efectivo', icon: DollarSign },
                                                { key: 'acepta_transferencia', label: 'Transferencia', icon: Repeat },
                                                { key: 'acepta_tarjeta', label: 'Tarjeta / Terminal', icon: CreditCard }
                                            ].map(opt => (
                                                <button
                                                    key={opt.key}
                                                    type="button"
                                                    onClick={() => setFormData({...formData, [opt.key]: !formData[opt.key as keyof typeof formData]})}
                                                    className={`flex items-center gap-3 p-4 rounded-2xl border transition-all ${formData[opt.key as keyof typeof formData] ? 'bg-white text-[#070707] border-white' : 'bg-white/5 border-white/5 text-white/20 hover:border-white/10'}`}
                                                >
                                                    <opt.icon size={16} />
                                                    <span className="text-[10px] font-black uppercase tracking-widest">{opt.label}</span>
                                                </button>
                                            ))}
                                        </div>
                                    </div>

                                    {/* Schedules */}
                                    <div className="space-y-4">
                                        <div className="flex items-center justify-between pl-1">
                                            <label className="text-[10px] font-black uppercase tracking-widest text-white/30">Horarios Detallados</label>
                                            <span className="text-[9px] font-bold text-white/20 uppercase tracking-widest">Activa los días que abres</span>
                                        </div>
                                        
                                        <div className="bg-white/5 rounded-3xl border border-white/5 divide-y divide-white/5 overflow-hidden">
                                            {days.map(day => (
                                                <div key={day} className="flex items-center justify-between p-4 px-6 hover:bg-white/[0.02] transition-colors">
                                                    <div className="flex items-center gap-4">
                                                        <button 
                                                            type="button"
                                                            onClick={() => {
                                                                const h = {...formData.horarios as any};
                                                                h[day].active = !h[day].active;
                                                                setFormData({...formData, horarios: h});
                                                            }}
                                                            className={`w-10 h-6 rounded-full p-1 transition-all flex items-center ${formData.horarios[day].active ? 'bg-emerald-500 justify-end' : 'bg-white/10 justify-start'}`}
                                                        >
                                                            <div className="w-4 h-4 rounded-full bg-white shadow-sm" />
                                                        </button>
                                                        <span className={`text-[11px] font-black uppercase tracking-widest transition-opacity ${formData.horarios[day].active ? 'text-white' : 'text-white/20'}`}>{day}</span>
                                                    </div>
                                                    
                                                    {formData.horarios[day].active && (
                                                        <motion.div 
                                                            initial={{ opacity: 0, x: 10 }}
                                                            animate={{ opacity: 1, x: 0 }}
                                                            className="flex items-center gap-2"
                                                        >
                                                            <input 
                                                                type="time" 
                                                                className="bg-transparent text-white text-[10px] font-bold outline-none border border-white/10 rounded-lg p-1.5 focus:border-orange-500/50"
                                                                value={formData.horarios[day].open}
                                                                onChange={e => {
                                                                    const h = {...formData.horarios as any};
                                                                    h[day].open = e.target.value;
                                                                    setFormData({...formData, horarios: h});
                                                                }}
                                                            />
                                                            <span className="text-white/10 text-[8px] font-black">A</span>
                                                            <input 
                                                                type="time" 
                                                                className="bg-transparent text-white text-[10px] font-bold outline-none border border-white/10 rounded-lg p-1.5 focus:border-orange-500/50"
                                                                value={formData.horarios[day].close}
                                                                onChange={e => {
                                                                    const h = {...formData.horarios as any};
                                                                    h[day].close = e.target.value;
                                                                    setFormData({...formData, horarios: h});
                                                                }}
                                                            />
                                                        </motion.div>
                                                    )}
                                                </div>
                                            ))}
                                        </div>
                                    </div>
                                </div>
                            )}

                            {/* Step 4: Security */}
                            {currentStep === 4 && (
                                <div className="space-y-8">
                                    <div className="bg-emerald-500/10 border border-emerald-500/20 p-6 rounded-3xl flex items-start gap-4 mb-8">
                                        <div className="p-3 bg-emerald-500/20 rounded-2xl text-emerald-500">
                                            <ShieldCheck size={24} />
                                        </div>
                                        <div>
                                            <h4 className="text-xs font-black uppercase tracking-widest text-emerald-500 mb-1">Casi terminamos</h4>
                                            <p className="text-[11px] font-medium text-white/50 leading-relaxed">Solo falta configurar una contraseña segura para que puedas acceder a tu panel de administración (Partner Dashboard).</p>
                                        </div>
                                    </div>

                                    <div className="space-y-6">
                                        <div className="space-y-2">
                                            <label className="text-[10px] font-black uppercase tracking-widest text-white/30 pl-1">Nueva Contraseña</label>
                                            <div className="relative">
                                                <input 
                                                    type="password" 
                                                    className="w-full px-6 py-5 bg-white/5 border border-white/5 rounded-2xl text-white focus:ring-1 focus:ring-orange-500/50 outline-none font-bold text-sm transition-all"
                                                    placeholder="••••••••"
                                                    value={formData.password}
                                                    onChange={e => setFormData({...formData, password: e.target.value})}
                                                />
                                            </div>
                                            
                                            {/* Password Strength Indicator */}
                                            <div className="pt-2 px-1">
                                                <div className="flex justify-between items-center mb-1.5">
                                                    <span className="text-[8px] font-black uppercase tracking-widest text-white/20">Fortaleza</span>
                                                    <span className={`text-[8px] font-black uppercase tracking-widest ${
                                                        passwordStrength < 30 ? 'text-red-500' : 
                                                        passwordStrength < 60 ? 'text-yellow-500' : 'text-emerald-500'
                                                    }`}>
                                                        {passwordStrength < 30 ? 'Débil' : 
                                                         passwordStrength < 60 ? 'Media' : 
                                                         passwordStrength < 100 ? 'Fuerte' : 'Excelente'}
                                                    </span>
                                                </div>
                                                <div className="h-1 bg-white/5 rounded-full overflow-hidden">
                                                    <motion.div 
                                                        initial={{ width: 0 }}
                                                        animate={{ width: `${passwordStrength}%` }}
                                                        className={`h-full transition-colors ${
                                                            passwordStrength < 30 ? 'bg-red-500' : 
                                                            passwordStrength < 60 ? 'bg-yellow-500' : 'bg-emerald-500'
                                                        }`}
                                                    />
                                                </div>
                                            </div>
                                        </div>

                                        <div className="space-y-2">
                                            <label className="text-[10px] font-black uppercase tracking-widest text-white/30 pl-1">Confirmar Contraseña</label>
                                            <input 
                                                type="password" 
                                                className="w-full px-6 py-5 bg-white/5 border border-white/5 rounded-2xl text-white focus:ring-1 focus:ring-orange-500/50 outline-none font-bold text-sm transition-all"
                                                placeholder="••••••••"
                                                value={formData.password_confirmation}
                                                onChange={e => setFormData({...formData, password_confirmation: e.target.value})}
                                            />
                                        </div>
                                    </div>

                                    <div className="pt-8">
                                        <div className="flex items-center gap-3 text-white/20 mb-6">
                                            <div className="w-10 h-[1px] bg-white/10" />
                                            <span className="text-[8px] font-black uppercase tracking-[0.3em]">Resumen del Registro</span>
                                            <div className="flex-1 h-[1px] bg-white/10" />
                                        </div>
                                        <div className="flex gap-4">
                                            <div className="flex-1 bg-white/5 p-4 rounded-2xl border border-white/5">
                                                <div className="text-[8px] font-black uppercase tracking-widest text-white/30 mb-1">Negocio</div>
                                                <div className="text-[10px] font-black truncate">{formData.business_name || 'Sin nombre'}</div>
                                            </div>
                                            <div className="flex-1 bg-white/5 p-4 rounded-2xl border border-white/5">
                                                <div className="text-[8px] font-black uppercase tracking-widest text-white/30 mb-1">Propietario</div>
                                                <div className="text-[10px] font-black truncate">{formData.first_name || 'Sin nombre'}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            )}
                        </div>

                        {/* Navigation Buttons */}
                        <div className="mt-16 flex items-center justify-between gap-4">
                            <button 
                                onClick={prevStep}
                                disabled={currentStep === 1 || loading}
                                className={`flex items-center gap-2 text-[10px] font-black uppercase tracking-widest transition-all ${currentStep === 1 ? 'opacity-0 pointer-events-none' : 'text-white/40 hover:text-white'}`}
                            >
                                <ChevronLeft size={16} />
                                Atrás
                            </button>

                            <button 
                                onClick={nextStep}
                                disabled={loading}
                                className="bg-white text-[#070707] px-10 py-5 rounded-2xl font-black text-xs uppercase tracking-widest flex items-center gap-3 hover:bg-orange-500 hover:text-white transition-all active:scale-95 disabled:opacity-50"
                            >
                                {loading ? (
                                    <div className="w-4 h-4 border-2 border-current border-t-transparent rounded-full animate-spin" />
                                ) : (
                                    <>
                                        {currentStep === 4 ? 'Finalizar Registro' : 'Continuar'}
                                        <ArrowRight size={16} />
                                    </>
                                )}
                            </button>
                        </div>
                    </motion.div>
                </div>
            </div>
            
            <style jsx global>{`
                input[type="time"]::-webkit-calendar-picker-indicator {
                    filter: invert(1);
                    opacity: 0.2;
                }
                @media (max-width: 1024px) {
                    .lg\\:h-screen { height: auto !important; }
                }
            `}</style>
        </div>
    );
}
