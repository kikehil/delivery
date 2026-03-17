'use client';

import React, { useEffect, useState } from 'react';
import { motion, AnimatePresence } from 'framer-motion';
import { 
    Plus, 
    Megaphone, 
    Eye, 
    EyeOff, 
    Trash2, 
    Search, 
    Image as ImageIcon,
    ExternalLink,
    ChevronUp,
    ChevronDown,
    Save,
    X
} from 'lucide-react';
import api from '@/lib/api';

interface Promotion {
    id: number;
    titulo: string;
    subtitulo: string;
    tag_text: string;
    boton_text: string;
    imagen_url: string;
    link_url: string;
    orden: number;
    activo: boolean;
    color_fondo: string;
}

export default function AdminPromotions() {
    const [promotions, setPromotions] = useState<Promotion[]>([]);
    const [loading, setLoading] = useState(true);
    const [isModalOpen, setIsModalOpen] = useState(false);
    const [formLoading, setFormLoading] = useState(false);
    const [formData, setFormData] = useState({
        titulo: '',
        subtitulo: '',
        tag_text: '',
        boton_text: 'Explorar',
        imagen_url: '',
        link_url: '',
        color_fondo: '#4f46e5',
        orden: 0
    });

    const fetchData = async () => {
        try {
            const { data } = await api.get('/admin/promotions');
            setPromotions(data.data);
        } catch (e) {
            console.error('Error fetching promotions');
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        fetchData();
    }, []);

    const handleToggle = async (id: number, currentStatus: boolean) => {
        try {
            await api.put(`/admin/promotions/${id}`, { activo: !currentStatus });
            setPromotions(promotions.map(p => p.id === id ? { ...p, activo: !currentStatus } : p));
        } catch (e) {
            console.error('Error toggling promotion');
        }
    };

    const handleDelete = async (id: number) => {
        if (!confirm('¿Estás seguro de eliminar este banner?')) return;
        try {
            await api.delete(`/admin/promotions/${id}`);
            setPromotions(promotions.filter(p => p.id !== id));
        } catch (e) {
            console.error('Error deleting promotion');
        }
    };

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault();
        setFormLoading(true);
        try {
            await api.post('/admin/promotions', formData);
            await fetchData();
            setIsModalOpen(false);
            setFormData({
                titulo: '',
                subtitulo: '',
                tag_text: '',
                boton_text: 'Explorar',
                imagen_url: '',
                link_url: '',
                color_fondo: '#4f46e5',
                orden: 0
            });
        } catch (e) {
            console.error('Error creating promotion');
        } finally {
            setFormLoading(false);
        }
    };

    return (
        <div className="space-y-8">
            <div className="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h1 className="text-4xl font-black text-indigo-950 tracking-tighter mb-1">Banners Promocionales</h1>
                    <p className="text-sm font-bold text-indigo-400 uppercase tracking-widest">Gestiona el carrusel de la página de inicio (Máx. 4 recomendados)</p>
                </div>
                <button 
                    onClick={() => setIsModalOpen(true)}
                    className="flex items-center gap-3 px-6 py-4 bg-indigo-600 text-white rounded-3xl font-black text-xs uppercase tracking-widest hover:bg-indigo-700 transition-all shadow-xl shadow-indigo-600/20 active:scale-95"
                >
                    <Plus size={18} /> Nuevo Banner
                </button>
            </div>

            <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <AnimatePresence>
                    {promotions.map((promo) => (
                        <motion.div 
                            key={promo.id}
                            initial={{ opacity: 0, y: 20 }}
                            animate={{ opacity: 1, y: 0 }}
                            className="bg-white rounded-[2.5rem] border border-indigo-50 shadow-sm overflow-hidden flex flex-col sm:flex-row h-full"
                        >
                            <div className="w-full sm:w-48 h-48 sm:h-auto relative bg-slate-100">
                                <img 
                                    src={promo.imagen_url.includes('http') ? promo.imagen_url : `/api/placeholder?w=200&h=200`} 
                                    className="w-full h-full object-cover"
                                    alt={promo.titulo}
                                />
                                <div className="absolute top-4 left-4">
                                    <span className={`px-3 py-1 rounded-full text-[9px] font-black uppercase tracking-widest bg-white shadow-lg ${promo.activo ? 'text-indigo-600' : 'text-slate-400'}`}>
                                        {promo.activo ? 'Activo' : 'Pausado'}
                                    </span>
                                </div>
                            </div>

                            <div className="flex-1 p-6 flex flex-col justify-between space-y-4">
                                <div>
                                    <div className="flex items-start justify-between">
                                        <h3 className="text-xl font-black text-indigo-950 leading-tight mb-1">{promo.titulo}</h3>
                                        <div className="flex gap-1">
                                            <button 
                                                onClick={() => handleToggle(promo.id, promo.activo)}
                                                className={`p-2 rounded-xl transition-colors ${promo.activo ? 'bg-indigo-50 text-indigo-600 hover:bg-indigo-100' : 'bg-slate-50 text-slate-400 hover:bg-slate-100'}`}
                                            >
                                                {promo.activo ? <Eye size={18} /> : <EyeOff size={18} />}
                                            </button>
                                            <button 
                                                onClick={() => handleDelete(promo.id)}
                                                className="p-2 bg-rose-50 text-rose-500 rounded-xl hover:bg-rose-100 transition-colors"
                                            >
                                                <Trash2 size={18} />
                                            </button>
                                        </div>
                                    </div>
                                    <p className="text-sm font-medium text-slate-500 line-clamp-2">{promo.subtitulo}</p>
                                </div>

                                <div className="flex items-center gap-4 pt-4 border-t border-indigo-50">
                                    <div className="flex-1">
                                        <p className="text-[9px] font-black uppercase tracking-widest text-indigo-300 mb-1">Redirección</p>
                                        <div className="flex items-center gap-2 text-indigo-600 font-bold text-xs truncate">
                                            <ExternalLink size={12} /> {promo.link_url || 'Sin enlace'}
                                        </div>
                                    </div>
                                    <div className="text-right">
                                        <p className="text-[9px] font-black uppercase tracking-widest text-indigo-300 mb-1">Orden</p>
                                        <p className="font-black text-indigo-950 text-sm">#{promo.orden}</p>
                                    </div>
                                </div>
                            </div>
                        </motion.div>
                    ))}
                </AnimatePresence>

                {promotions.length === 0 && !loading && (
                    <div className="col-span-full py-20 bg-indigo-50/50 border-2 border-dashed border-indigo-100 rounded-[3rem] flex flex-col items-center justify-center space-y-4">
                        <Megaphone size={48} className="text-indigo-200" />
                        <p className="text-indigo-400 font-black uppercase tracking-widest text-sm">No hay banners configurados</p>
                    </div>
                )}
            </div>

            {/* Modal para Nuevo Banner */}
            <AnimatePresence>
                {isModalOpen && (
                    <div className="fixed inset-0 z-[60] flex items-center justify-center p-6">
                        <motion.div 
                            initial={{ opacity: 0 }}
                            animate={{ opacity: 1 }}
                            exit={{ opacity: 0 }}
                            onClick={() => setIsModalOpen(false)}
                            className="absolute inset-0 bg-indigo-950/40 backdrop-blur-sm"
                        />
                        <motion.div 
                            initial={{ scale: 0.9, opacity: 0, y: 20 }}
                            animate={{ scale: 1, opacity: 1, y: 0 }}
                            exit={{ scale: 0.9, opacity: 0, y: 20 }}
                            className="bg-white w-full max-w-2xl rounded-[3rem] shadow-2xl relative overflow-hidden flex flex-col max-h-[90vh]"
                        >
                            <div className="p-8 border-b border-indigo-50 flex items-center justify-between">
                                <h2 className="text-2xl font-black text-indigo-950 tracking-tighter">Configurar Nuevo Banner</h2>
                                <button onClick={() => setIsModalOpen(false)} className="p-2 hover:bg-indigo-50 rounded-2xl transition-colors">
                                    <X size={24} className="text-indigo-400" />
                                </button>
                            </div>

                            <form onSubmit={handleSubmit} className="p-8 space-y-6 overflow-y-auto">
                                <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div className="space-y-2">
                                        <label className="text-[10px] font-black uppercase tracking-widest text-indigo-400 pl-1">Título Llamativo</label>
                                        <input 
                                            type="text" 
                                            required
                                            className="w-full px-5 py-4 bg-slate-50 border border-slate-100 rounded-2xl focus:ring-2 focus:ring-indigo-500/20 outline-none font-bold text-sm"
                                            placeholder="Ej. Pizza 2x1 Martes"
                                            value={formData.titulo}
                                            onChange={e => setFormData({...formData, titulo: e.target.value})}
                                        />
                                    </div>
                                    <div className="space-y-2">
                                        <label className="text-[10px] font-black uppercase tracking-widest text-indigo-400 pl-1">Tag Superior (Opcional)</label>
                                        <input 
                                            type="text" 
                                            className="w-full px-5 py-4 bg-slate-50 border border-slate-100 rounded-2xl focus:ring-2 focus:ring-indigo-500/20 outline-none font-bold text-sm"
                                            placeholder="Ej. PROMO, NUEVO, LIMITADO"
                                            value={formData.tag_text}
                                            onChange={e => setFormData({...formData, tag_text: e.target.value})}
                                        />
                                    </div>
                                </div>

                                <div className="space-y-2">
                                    <label className="text-[10px] font-black uppercase tracking-widest text-indigo-400 pl-1">Subtítulo Descriptivo</label>
                                    <textarea 
                                        className="w-full px-5 py-4 bg-slate-50 border border-slate-100 rounded-2xl focus:ring-2 focus:ring-indigo-500/20 outline-none font-bold text-sm h-24 resize-none"
                                        placeholder="Escribe una breve descripción del beneficio..."
                                        value={formData.subtitulo}
                                        onChange={e => setFormData({...formData, subtitulo: e.target.value})}
                                    />
                                </div>

                                <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div className="space-y-2">
                                        <label className="text-[10px] font-black uppercase tracking-widest text-indigo-400 pl-1">URL de la Imagen</label>
                                        <div className="relative">
                                            <ImageIcon className="absolute left-4 top-1/2 -translate-y-1/2 text-slate-300" size={18} />
                                            <input 
                                                type="text" 
                                                required
                                                className="w-full pl-12 pr-5 py-4 bg-slate-50 border border-slate-100 rounded-2xl focus:ring-2 focus:ring-indigo-500/20 outline-none font-bold text-sm"
                                                placeholder="https://images.unsplash..."
                                                value={formData.imagen_url}
                                                onChange={e => setFormData({...formData, imagen_url: e.target.value})}
                                            />
                                        </div>
                                    </div>
                                    <div className="space-y-2">
                                        <label className="text-[10px] font-black uppercase tracking-widest text-indigo-400 pl-1">Link de Redirección (Rutas o URLs)</label>
                                        <div className="relative">
                                            <ExternalLink className="absolute left-4 top-1/2 -translate-y-1/2 text-slate-300" size={18} />
                                            <input 
                                                type="text" 
                                                className="w-full pl-12 pr-5 py-4 bg-slate-50 border border-slate-100 rounded-2xl focus:ring-2 focus:ring-indigo-500/20 outline-none font-bold text-sm"
                                                placeholder="Ej. /stores o /category/pizzas"
                                                value={formData.link_url}
                                                onChange={e => setFormData({...formData, link_url: e.target.value})}
                                            />
                                        </div>
                                    </div>
                                </div>

                                <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
                                    <div className="space-y-2">
                                        <label className="text-[10px] font-black uppercase tracking-widest text-indigo-400 pl-1">Texto del Botón</label>
                                        <input 
                                            type="text" 
                                            className="w-full px-5 py-4 bg-slate-50 border border-slate-100 rounded-2xl focus:ring-2 focus:ring-indigo-500/20 outline-none font-bold text-sm"
                                            value={formData.boton_text}
                                            onChange={e => setFormData({...formData, boton_text: e.target.value})}
                                        />
                                    </div>
                                    <div className="space-y-2">
                                        <label className="text-[10px] font-black uppercase tracking-widest text-indigo-400 pl-1">Color de Fondo</label>
                                        <input 
                                            type="color" 
                                            className="w-full h-[54px] p-1 bg-slate-50 border border-slate-100 rounded-2xl cursor-pointer"
                                            value={formData.color_fondo}
                                            onChange={e => setFormData({...formData, color_fondo: e.target.value})}
                                        />
                                    </div>
                                    <div className="space-y-2">
                                        <label className="text-[10px] font-black uppercase tracking-widest text-indigo-400 pl-1">Orden de Visualización</label>
                                        <input 
                                            type="number" 
                                            className="w-full px-5 py-4 bg-slate-50 border border-slate-100 rounded-2xl focus:ring-2 focus:ring-indigo-500/20 outline-none font-bold text-sm"
                                            value={formData.orden}
                                            onChange={e => setFormData({...formData, orden: parseInt(e.target.value)})}
                                        />
                                    </div>
                                </div>

                                <div className="pt-6 flex gap-4">
                                    <button 
                                        type="button" 
                                        onClick={() => setIsModalOpen(false)}
                                        className="flex-1 px-6 py-4 border-2 border-slate-100 text-slate-400 rounded-3xl font-black text-xs uppercase tracking-widest hover:bg-slate-50 transition-all"
                                    >
                                        Cancelar
                                    </button>
                                    <button 
                                        type="submit"
                                        disabled={formLoading}
                                        className="flex-[2] px-6 py-4 bg-indigo-600 text-white rounded-3xl font-black text-xs uppercase tracking-widest hover:bg-indigo-700 transition-all shadow-xl shadow-indigo-600/20 flex items-center justify-center gap-3 active:scale-95 disabled:opacity-50"
                                    >
                                        {formLoading ? 'Guardando...' : <><Save size={18} /> Publicar Banner</>}
                                    </button>
                                </div>
                            </form>
                        </motion.div>
                    </div>
                )}
            </AnimatePresence>
        </div>
    );
}
