'use client';

import React, { useEffect, useState } from 'react';
import api, { uploadFile } from '@/lib/api';
import { motion } from 'framer-motion';
import { 
    Store, 
    Phone, 
    Image as ImageIcon, 
    Tag, 
    Save, 
    CheckCircle,
    Info,
    Upload
} from 'lucide-react';
import Image from 'next/image';

interface BusinessSettings {
    nombre: string;
    categoria: string;
    logo_url: string;
    banner_url: string;
    telefono_contacto: string;
    plan: string;
}

export default function PartnerSettings() {
    const [settings, setSettings] = useState<BusinessSettings | null>(null);
    const [loading, setLoading] = useState(true);
    const [saving, setSaving] = useState(false);
    const [success, setSuccess] = useState(false);
    const [uploadingLogo, setUploadingLogo] = useState(false);
    const [uploadingBanner, setUploadingBanner] = useState(false);

    useEffect(() => {
        async function fetchSettings() {
            try {
                const { data } = await api.get('/partner/settings');
                setSettings(data.data);
            } catch (e) {
                console.error('Error fetching settings');
            } finally {
                setLoading(false);
            }
        }
        fetchSettings();
    }, []);

    const handleLogoChange = async (e: React.ChangeEvent<HTMLInputElement>) => {
        if (e.target.files && e.target.files[0] && settings) {
            setUploadingLogo(true);
            try {
                const url = await uploadFile(e.target.files[0], 'logos');
                setSettings({ ...settings, logo_url: url });
            } catch (err) {
                console.error("Upload failed", err);
            } finally {
                setUploadingLogo(false);
            }
        }
    };

    const handleBannerChange = async (e: React.ChangeEvent<HTMLInputElement>) => {
        if (e.target.files && e.target.files[0] && settings) {
            setUploadingBanner(true);
            try {
                const url = await uploadFile(e.target.files[0], 'banners');
                setSettings({ ...settings, banner_url: url });
            } catch (err) {
                console.error("Upload failed", err);
            } finally {
                setUploadingBanner(false);
            }
        }
    };

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault();
        if (!settings) return;
        
        setSaving(true);
        setSuccess(false);
        try {
            await api.put('/partner/settings', settings);
            setSuccess(true);
            setTimeout(() => setSuccess(false), 3000);
        } catch (e) {
            console.error('Error updating settings');
        } finally {
            setSaving(false);
        }
    };

    if (loading) {
        return <div className="animate-pulse space-y-8 max-w-2xl mx-auto py-10">
            <div className="h-10 bg-slate-200 rounded-xl w-1/2" />
            <div className="h-64 bg-slate-200 rounded-3xl" />
        </div>;
    }

    return (
        <div className="max-w-2xl mx-auto py-10 space-y-10">
            <div>
                <h1 className="text-4xl font-black text-slate-900 tracking-tight leading-none mb-2">Mi Negocio</h1>
                <p className="text-slate-500 font-bold">Personaliza la apariencia de tu tienda para tus clientes.</p>
            </div>

            <form onSubmit={handleSubmit} className="space-y-8">
                {/* Logo Preview Section */}
                <div className="bg-white p-8 rounded-[2.5rem] border border-slate-100 shadow-sm space-y-6">
                    <div className="flex items-center gap-3">
                        <div className="p-2 bg-slate-900 text-white rounded-xl">
                            <ImageIcon size={20} />
                        </div>
                        <h3 className="text-lg font-black text-slate-900 tracking-tight">Identidad Visual</h3>
                    </div>
                    
                    <div className="flex flex-col items-center gap-6 py-4">
                        <div className="relative w-32 h-32 rounded-[2rem] overflow-hidden border-4 border-slate-50 shadow-xl shadow-slate-200/50 bg-slate-100 flex items-center justify-center">
                            {settings?.logo_url ? (
                                <Image 
                                    src={settings.logo_url} 
                                    alt="Logo" 
                                    fill 
                                    unoptimized
                                    className="object-cover"
                                />
                            ) : (
                                <Store size={48} className="text-slate-300" />
                            )}
                        </div>
                        
                        <div className="w-full space-y-2">
                            <label className="text-[10px] font-black uppercase tracking-widest text-slate-400 pl-1">Subir Logo del Negocio</label>
                            <div className="relative group/upload">
                                <input 
                                    type="file" 
                                    accept="image/*"
                                    className="absolute inset-0 opacity-0 cursor-pointer z-10"
                                    onChange={handleLogoChange}
                                />
                                <div className="w-full p-4 bg-slate-50 rounded-2xl border-2 border-dashed border-slate-200 group-hover/upload:border-slate-900 transition-all flex items-center justify-center gap-3">
                                    {uploadingLogo ? (
                                        <div className="w-5 h-5 border-2 border-slate-900 border-t-transparent rounded-full animate-spin" />
                                    ) : (
                                        <>
                                            <Upload size={20} className="text-slate-400 group-hover/upload:text-slate-900 transition-colors" />
                                            <span className="text-sm font-bold text-slate-500 group-hover/upload:text-slate-900 transition-colors">
                                                {settings?.logo_url ? 'Cambiar imagen' : 'Seleccionar logo'}
                                            </span>
                                        </>
                                    )}
                                </div>
                            </div>
                        </div>
                    </div>

                    <div className="pt-4 border-t border-slate-100 flex flex-col gap-6">
                         <div className="w-full space-y-2">
                            <label className="text-[10px] font-black uppercase tracking-widest text-slate-400 pl-1">Banner / Portada de Fondo</label>
                            
                            <div className="relative w-full h-32 rounded-3xl overflow-hidden bg-slate-100 mb-4 border-2 border-slate-50 shadow-inner">
                                {settings?.banner_url ? (
                                    <Image 
                                        src={settings.banner_url} 
                                        alt="Banner Preview" 
                                        fill 
                                        unoptimized
                                        className="object-cover"
                                    />
                                ) : (
                                    <div className="w-full h-full flex flex-col items-center justify-center text-slate-300">
                                        <ImageIcon size={32} />
                                        <span className="text-[10px] font-bold mt-1 uppercase tracking-tighter">Sin Portada</span>
                                    </div>
                                )}
                            </div>

                            <div className="relative group/banner">
                                <input 
                                    type="file" 
                                    accept="image/*"
                                    className="absolute inset-0 opacity-0 cursor-pointer z-10"
                                    onChange={handleBannerChange}
                                />
                                <div className="w-full p-4 bg-slate-50 rounded-2xl border-2 border-dashed border-slate-200 group-hover/banner:border-slate-900 transition-all flex items-center justify-center gap-3">
                                    {uploadingBanner ? (
                                        <div className="w-5 h-5 border-2 border-slate-900 border-t-transparent rounded-full animate-spin" />
                                    ) : (
                                        <>
                                            <Upload size={20} className="text-slate-400 group-hover/banner:text-slate-900 transition-colors" />
                                            <span className="text-sm font-bold text-slate-500 group-hover/banner:text-slate-900 transition-colors">
                                                {settings?.banner_url ? 'Cambiar portada' : 'Seleccionar banner (1200x400)'}
                                            </span>
                                        </>
                                    )}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {/* Info Section */}
                <div className="bg-white p-8 rounded-[2.5rem] border border-slate-100 shadow-sm space-y-6">
                    <div className="flex items-center gap-3">
                        <div className="p-2 bg-slate-900 text-white rounded-xl">
                            <Info size={20} />
                        </div>
                        <h3 className="text-lg font-black text-slate-900 tracking-tight">Información General</h3>
                    </div>

                    <div className="space-y-4">
                        <div className="space-y-2">
                             <label className="text-[10px] font-black uppercase tracking-widest text-slate-400 pl-1">Nombre Comercial</label>
                             <div className="relative">
                                <Store className="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400" size={18} />
                                <input 
                                    type="text" 
                                    required
                                    className="w-full pl-12 pr-4 py-4 bg-slate-50 rounded-2xl border-none outline-none focus:ring-2 focus:ring-slate-900 transition-all font-bold"
                                    value={settings?.nombre || ''}
                                    onChange={(e) => setSettings({...settings!, nombre: e.target.value})}
                                />
                             </div>
                        </div>

                        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div className="space-y-2">
                                <label className="text-[10px] font-black uppercase tracking-widest text-slate-400 pl-1">Categoría</label>
                                <div className="relative">
                                    <Tag className="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400" size={18} />
                                    <input 
                                        type="text" 
                                        required
                                        className="w-full pl-12 pr-4 py-4 bg-slate-50 rounded-2xl border-none outline-none focus:ring-2 focus:ring-slate-900 transition-all font-bold"
                                        value={settings?.categoria || ''}
                                        onChange={(e) => setSettings({...settings!, categoria: e.target.value})}
                                    />
                                </div>
                            </div>
                            <div className="space-y-2">
                                <label className="text-[10px] font-black uppercase tracking-widest text-slate-400 pl-1">WhatsApp / Contacto</label>
                                <div className="relative">
                                    <Phone className="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400" size={18} />
                                    <input 
                                        type="text" 
                                        required
                                        className="w-full pl-12 pr-4 py-4 bg-slate-50 rounded-2xl border-none outline-none focus:ring-2 focus:ring-slate-900 transition-all font-bold"
                                        value={settings?.telefono_contacto || ''}
                                        onChange={(e) => setSettings({...settings!, telefono_contacto: e.target.value})}
                                    />
                                </div>
                            </div>
                        </div>

                        <div className="p-4 bg-indigo-50 rounded-2xl border border-indigo-100 flex items-center justify-between">
                            <div>
                                <p className="text-[10px] font-black uppercase tracking-widest text-indigo-400">Plan actual</p>
                                <p className="text-indigo-900 font-black text-lg uppercase">{settings?.plan}</p>
                            </div>
                            <span className="bg-white text-indigo-600 px-4 py-2 rounded-xl font-black text-xs shadow-sm">
                                Mejorar Plan
                            </span>
                        </div>
                    </div>
                </div>

                <div className="flex gap-4">
                    <button 
                        type="submit"
                        disabled={saving}
                        className={`
                            flex-1 flex items-center justify-center gap-3 p-5 rounded-[1.7rem] font-black transition-all shadow-xl
                            ${success 
                                ? 'bg-emerald-500 text-white shadow-emerald-500/20' 
                                : 'bg-slate-900 text-white shadow-slate-900/20 hover:bg-slate-800'
                            }
                            ${saving ? 'opacity-70 cursor-not-allowed' : ''}
                        `}
                    >
                        {success ? (
                            <>
                                <CheckCircle size={24} />
                                Cambios Guardados
                            </>
                        ) : (
                            <>
                                <Save size={24} />
                                {saving ? 'Guardando...' : 'Guardar Configuración'}
                            </>
                        )}
                    </button>
                </div>
            </form>
        </div>
    );
}
