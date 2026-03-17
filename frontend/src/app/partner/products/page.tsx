'use client';

import React, { useEffect, useState } from 'react';
import api, { uploadFile } from '@/lib/api';
import { motion, AnimatePresence } from 'framer-motion';
import {
    Plus,
    Search,
    ToggleLeft,
    ToggleRight,
    Edit3,
    Trash2,
    UtensilsCrossed,
    Upload
} from 'lucide-react';
import Image from 'next/image';

interface Product {
    id: number;
    nombre: string;
    precio: number;
    descripcion: string;
    foto_url: string;
    disponible: boolean;
}

export default function PartnerProducts() {
    const [products, setProducts] = useState<Product[]>([]);
    const [loading, setLoading] = useState(true);
    const [searchTerm, setSearchTerm] = useState('');
    const [uploading, setUploading] = useState(false);
    
    // Modal states
    const [isModalOpen, setIsModalOpen] = useState(false);
    const [editingProduct, setEditingProduct] = useState<Product | null>(null);
    const [formData, setFormData] = useState({
        nombre: '',
        precio: '',
        descripcion: '',
        foto_url: ''
    });

    useEffect(() => {
        fetchProducts();
    }, []);

    const fetchProducts = async () => {
        try {
            const { data } = await api.get('/partner/products');
            setProducts(data.data);
        } catch (e) {
            console.error('Error fetching products');
        } finally {
            setLoading(false);
        }
    };

    const handleFileChange = async (e: React.ChangeEvent<HTMLInputElement>) => {
        if (e.target.files && e.target.files[0]) {
            setUploading(true);
            try {
                const url = await uploadFile(e.target.files[0], 'products');
                setFormData({ ...formData, foto_url: url });
            } catch (err) {
                console.error("Upload failed", err);
            } finally {
                setUploading(false);
            }
        }
    };

    const handleOpenModal = (product: Product | null = null) => {
        if (product) {
            setEditingProduct(product);
            setFormData({
                nombre: product.nombre,
                precio: product.precio.toString(),
                descripcion: product.descripcion,
                foto_url: product.foto_url
            });
        } else {
            setEditingProduct(null);
            setFormData({
                nombre: '',
                precio: '',
                descripcion: '',
                foto_url: ''
            });
        }
        setIsModalOpen(true);
    };

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault();
        try {
            if (editingProduct) {
                await api.put(`/partner/products/${editingProduct.id}`, formData);
            } else {
                await api.post('/partner/products', formData);
            }
            setIsModalOpen(false);
            fetchProducts();
        } catch (e) {
            console.error('Error saving product');
        }
    };

    const deleteProduct = async (id: number) => {
        if (!confirm('¿Estás seguro de eliminar este producto?')) return;
        try {
            await api.delete(`/partner/products/${id}`);
            setProducts(products.filter(p => p.id !== id));
        } catch (e) {
            console.error('Error deleting product');
        }
    };

    const toggleProduct = async (id: number) => {
        try {
            await api.post(`/partner/products/${id}/toggle`);
            setProducts(products.map(p =>
                p.id === id ? { ...p, disponible: !p.disponible } : p
            ));
        } catch (e) {
            console.error('Error toggling product');
        }
    };

    const filteredProducts = products.filter(p =>
        p.nombre.toLowerCase().includes(searchTerm.toLowerCase())
    );

    return (
        <div className="space-y-6">
            <div className="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h1 className="text-3xl font-black text-slate-900 tracking-tight">Tu Menú</h1>
                    <p className="text-slate-500 font-bold">Gestiona tus platillos y su disponibilidad.</p>
                </div>
                <button 
                    onClick={() => handleOpenModal()}
                    className="inline-flex items-center gap-2 bg-slate-900 text-white px-6 py-3 rounded-2xl font-black hover:bg-slate-800 transition-all shadow-lg shadow-slate-900/10"
                >
                    <Plus size={20} />
                    Nuevo Producto
                </button>
            </div>

            <div className="relative group">
                <div className="absolute inset-y-0 left-4 flex items-center pointer-events-none text-slate-400 group-focus-within:text-slate-900 transition-colors">
                    <Search size={20} />
                </div>
                <input
                    type="text"
                    value={searchTerm}
                    onChange={(e) => setSearchTerm(e.target.value)}
                    placeholder="Buscar producto por nombre..."
                    className="w-full pl-12 pr-4 py-4 bg-white border border-slate-200 rounded-[1.5rem] outline-none focus:ring-2 focus:ring-slate-900 transition-all font-bold text-slate-900 shadow-sm"
                />
            </div>

            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <AnimatePresence>
                    {filteredProducts.map((product) => (
                        <motion.div
                            layout
                            key={product.id}
                            initial={{ opacity: 0, scale: 0.9 }}
                            animate={{ opacity: 1, scale: 1 }}
                            exit={{ opacity: 0, scale: 0.9 }}
                            className={`
                                bg-white rounded-[2rem] border border-slate-100 overflow-hidden shadow-sm hover:shadow-xl hover:shadow-slate-200/50 transition-all duration-300 group
                                ${!product.disponible ? 'opacity-70 grayscale-[0.5]' : ''}
                            `}
                        >
                            <div className="relative aspect-video bg-slate-100 overflow-hidden">
                                {product.foto_url && (
                                    <Image
                                        src={product.foto_url}
                                        alt={product.nombre}
                                        fill
                                        unoptimized
                                        className="object-cover group-hover:scale-110 transition-transform duration-700"
                                    />
                                )}
                                <div className="absolute top-4 right-4 z-10">
                                    <button 
                                        onClick={() => handleOpenModal(product)}
                                        className="bg-white/90 backdrop-blur-md p-2 rounded-xl text-slate-900 shadow-sm opacity-0 group-hover:opacity-100 transition-opacity"
                                    >
                                        <Edit3 size={18} />
                                    </button>
                                </div>
                                {!product.disponible && (
                                    <div className="absolute inset-0 bg-slate-900/40 backdrop-blur-sm flex items-center justify-center">
                                        <span className="bg-white text-slate-900 px-4 py-2 rounded-xl font-black text-sm uppercase tracking-wider shadow-xl">
                                            PAUSADO
                                        </span>
                                    </div>
                                )}
                            </div>

                            <div className="p-6 space-y-4">
                                <div className="space-y-1">
                                    <div className="flex items-center justify-between">
                                        <h3 className="text-xl font-black text-slate-900 leading-tight">{product.nombre}</h3>
                                        <p className="text-lg font-black text-slate-900 font-mono tracking-tighter">${parseFloat(product.precio.toString()).toFixed(2)}</p>
                                    </div>
                                    <p className="text-slate-500 text-sm font-bold line-clamp-2 leading-relaxed h-10">{product.descripcion}</p>
                                </div>

                                <div className="pt-2 flex items-center justify-between border-t border-slate-50">
                                    <div className="flex items-center gap-2">
                                        <button
                                            onClick={() => toggleProduct(product.id)}
                                            className={`
                                                flex items-center gap-2 px-4 py-2 rounded-xl font-black text-xs uppercase tracking-widest transition-all
                                                ${product.disponible
                                                    ? 'bg-emerald-100 text-emerald-700 hover:bg-emerald-200'
                                                    : 'bg-slate-200 text-slate-700 hover:bg-slate-300'
                                                }
                                            `}
                                        >
                                            {product.disponible ? <ToggleRight size={18} className="text-emerald-600" /> : <ToggleLeft size={18} className="text-slate-400" />}
                                            {product.disponible ? 'Disponible' : 'Agotado'}
                                        </button>
                                    </div>

                                    <button 
                                        onClick={() => deleteProduct(product.id)}
                                        className="p-2 text-slate-300 hover:text-rose-500 transition-colors"
                                    >
                                        <Trash2 size={20} />
                                    </button>
                                </div>
                            </div>
                        </motion.div>
                    ))}
                </AnimatePresence>
            </div>

            {isModalOpen && (
                <div className="fixed inset-0 z-[100] flex items-center justify-center p-4">
                    <motion.div 
                        initial={{ opacity: 0 }} 
                        animate={{ opacity: 1 }} 
                        className="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"
                        onClick={() => setIsModalOpen(false)}
                    />
                    <motion.div 
                        initial={{ scale: 0.9, opacity: 0 }} 
                        animate={{ scale: 1, opacity: 1 }} 
                        className="relative bg-white w-full max-w-lg rounded-[2.5rem] p-10 shadow-2xl overflow-hidden"
                    >
                        <div className="space-y-6">
                            <div>
                                <h2 className="text-3xl font-black text-slate-900 tracking-tight">
                                    {editingProduct ? 'Editar Producto' : 'Nuevo Producto'}
                                </h2>
                                <p className="text-slate-500 font-bold">Completa los detalles de tu platillo.</p>
                            </div>

                            <form onSubmit={handleSubmit} className="space-y-4">
                                <div className="space-y-2">
                                    <label className="text-xs font-black uppercase tracking-widest text-slate-400 pl-1">Nombre</label>
                                    <input 
                                        type="text" 
                                        required
                                        className="w-full p-4 bg-slate-50 rounded-2xl border-none outline-none focus:ring-2 focus:ring-slate-900 transition-all font-bold"
                                        value={formData.nombre}
                                        onChange={(e) => setFormData({...formData, nombre: e.target.value})}
                                    />
                                </div>

                                <div className="grid grid-cols-2 gap-4">
                                    <div className="space-y-2">
                                        <label className="text-xs font-black uppercase tracking-widest text-slate-400 pl-1">Precio ($)</label>
                                        <input 
                                            type="number" 
                                            step="0.01"
                                            required
                                            className="w-full p-4 bg-slate-50 rounded-2xl border-none outline-none focus:ring-2 focus:ring-slate-900 transition-all font-bold"
                                            value={formData.precio}
                                            onChange={(e) => setFormData({...formData, precio: e.target.value})}
                                        />
                                    </div>
                                    <div className="space-y-2">
                                        <label className="text-xs font-black uppercase tracking-widest text-slate-400 pl-1">Imagen del Producto</label>
                                        <div className="relative group/upload">
                                            <input 
                                                type="file" 
                                                accept="image/*"
                                                className="absolute inset-0 opacity-0 cursor-pointer z-10"
                                                onChange={handleFileChange}
                                            />
                                            <div className="w-full p-4 bg-slate-50 rounded-2xl border-2 border-dashed border-slate-200 group-hover/upload:border-slate-900 transition-all flex items-center justify-center gap-2">
                                                {uploading ? (
                                                     <div className="w-5 h-5 border-2 border-slate-900 border-t-transparent rounded-full animate-spin" />
                                                ) : formData.foto_url ? (
                                                    <span className="text-[10px] font-black text-emerald-600 truncate max-w-[120px]">Archivo cargado ✓</span>
                                                ) : (
                                                    <>
                                                        <Upload size={18} className="text-slate-400" />
                                                        <span className="text-[10px] font-black uppercase tracking-widest text-slate-400">Subir imagen</span>
                                                    </>
                                                )}
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div className="space-y-2">
                                    <label className="text-xs font-black uppercase tracking-widest text-slate-400 pl-1">Descripción</label>
                                    <textarea 
                                        rows={3}
                                        className="w-full p-4 bg-slate-50 rounded-2xl border-none outline-none focus:ring-2 focus:ring-slate-900 transition-all font-bold resize-none"
                                        value={formData.descripcion}
                                        onChange={(e) => setFormData({...formData, descripcion: e.target.value})}
                                    />
                                </div>

                                <div className="pt-4 flex gap-4">
                                    <button 
                                        type="button"
                                        onClick={() => setIsModalOpen(false)}
                                        className="flex-1 p-5 rounded-2xl font-black text-slate-500 hover:bg-slate-100 transition-all"
                                    >
                                        Cancelar
                                    </button>
                                    <button 
                                        type="submit"
                                        className="flex-[2] bg-slate-900 text-white p-5 rounded-2xl font-black hover:bg-slate-800 transition-all shadow-xl shadow-slate-900/20"
                                    >
                                        {editingProduct ? 'Guardar Cambios' : 'Crear Producto'}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </motion.div>
                </div>
            )}

            {filteredProducts.length === 0 && !loading && (
                <div className="bg-white py-20 rounded-[2.5rem] border border-dashed border-slate-200 flex flex-col items-center justify-center text-center space-y-4">
                    <div className="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center text-slate-300">
                        <UtensilsCrossed size={32} />
                    </div>
                    <div>
                        <p className="text-lg font-black text-slate-900">No se encontraron productos</p>
                        <p className="text-slate-500 font-bold">Intenta con otro término o agrega uno nuevo.</p>
                    </div>
                    <button 
                        onClick={() => setSearchTerm('')}
                        className="bg-slate-100 text-slate-900 px-6 py-3 rounded-2xl font-black hover:bg-slate-200 transition-all"
                    >
                        Limpiar filtros
                    </button>
                </div>
            )}
        </div>
    );
}
