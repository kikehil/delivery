'use client';

import React, { useEffect, useState, useMemo } from 'react';
import { useParams } from 'next/navigation';
import api from '@/lib/api';
import { motion, AnimatePresence } from 'framer-motion';
import { 
    Star, 
    Clock, 
    Instagram, 
    MessageCircle, 
    Phone, 
    ChevronDown, 
    Plus, 
    ShoppingBag,
    Search,
    X,
    ChevronRight,
    ArrowLeft,
    MapPin,
    Truck,
    Utensils,
    Facebook,
    Youtube,
    Trash2,
    Minus,
    CheckCircle
} from 'lucide-react';
import Image from 'next/image';
import Link from 'next/link';

interface Store {
    id: number;
    nombre: string;
    categoria: string;
    logo_url: string;
    banner_url: string;
    telefono_contacto: string;
    estado: string;
    direccion: string;
    horarios?: Record<string, { open: string, close: string, active: boolean }>;
    entrega_domicilio: boolean;
    recolecta_pedidos: boolean;
    consumo_sucursal: boolean;
    facebook_url?: string;
    instagram_url?: string;
    youtube_url?: string;
    acepta_efectivo?: boolean;
    acepta_tarjeta?: boolean;
    acepta_transferencia?: boolean;
}

interface Product {
    id: number;
    nombre: string;
    precio: number;
    descripcion: string;
    foto_url: string;
    categoria?: string;
}

interface CartItem extends Product {
    qty: number;
}

export default function StoreMenuPage() {
    const params = useParams();
    const storeId = params.id;
    
    const [store, setStore] = useState<Store | null>(null);
    const [products, setProducts] = useState<Product[]>([]);
    const [loading, setLoading] = useState(true);
    const [searchTerm, setSearchTerm] = useState('');
    const [showHours, setShowHours] = useState(false);
    const [showCart, setShowCart] = useState(false);
    
    // Proper cart state
    const [cartItems, setCartItems] = useState<CartItem[]>([]);

    const today = new Intl.DateTimeFormat('es-MX', { weekday: 'long' }).format(new Date());
    const capitalizedToday = today.charAt(0).toUpperCase() + today.slice(1);

    useEffect(() => {
        if (storeId) {
            fetchStoreData();
        }
    }, [storeId]);

    const fetchStoreData = async () => {
        try {
            const [storeRes, productsRes] = await Promise.all([
                api.get(`/stores/${storeId}`),
                api.get(`/stores/${storeId}/products`)
            ]);
            setStore(storeRes.data.data);
            setProducts(productsRes.data.data);
        } catch (e) {
            console.error('Error fetching store data');
        } finally {
            setLoading(false);
        }
    };

    const addToCart = (product: Product) => {
        setCartItems(prev => {
            const existing = prev.find(item => item.id === product.id);
            if (existing) {
                return prev.map(item => item.id === product.id ? { ...item, qty: item.qty + 1 } : item);
            }
            return [...prev, { ...product, qty: 1 }];
        });
    };

    const removeFromCart = (productId: number) => {
        setCartItems(prev => {
            const existing = prev.find(item => item.id === productId);
            if (existing?.qty === 1) {
                return prev.filter(item => item.id !== productId);
            }
            return prev.map(item => item.id === productId ? { ...item, qty: item.qty - 1 } : item);
        });
    };

    const subtotal = cartItems.reduce((acc, item) => acc + (item.precio * item.qty), 0);
    const totalItems = cartItems.reduce((acc, item) => acc + item.qty, 0);

    const [zones, setZones] = useState<any[]>([]);
    const [selectedZone, setSelectedZone] = useState('');
    const [isSubmitting, setIsSubmitting] = useState(false);

    const fetchZones = async () => {
        try {
            const res = await api.get('/zones');
            setZones(res.data.data);
            if (res.data.data.length > 0) setSelectedZone(res.data.data[0].nombre_colonia);
        } catch (e) {
            console.error('Error fetching zones');
        }
    };

    useEffect(() => {
        fetchZones();
    }, []);

    const handleConfirmOrder = async () => {
        if (!store) return;
        if (!selectedZone) return alert('Por favor selecciona una zona de entrega');
        
        setIsSubmitting(true);
        try {
            const orderData = {
                comercio_id: store.id,
                items: cartItems.map(item => ({
                    id: item.id,
                    nombre: item.nombre,
                    precio: item.precio,
                    qty: item.qty
                })),
                cliente_zona: selectedZone,
                metodo_pago: 'efectivo',
                modalidad: 'delivery'
            };

            const res = await api.post('/orders', orderData);
            
            if (res.data.status === 'success') {
                setShowCart(false);
                setCartItems([]);
                alert('¡Pedido enviado con éxito! Recibirás una confirmación por WhatsApp en breve.');
            }
        } catch (e) {
            console.error('Error creating order', e);
            alert('Ocurrió un error al procesar tu pedido');
        } finally {
            setIsSubmitting(false);
        }
    };

    // Group products by category (mocking categories if not present)
    const groupedProducts = useMemo(() => {
        const groups: Record<string, Product[]> = {};
        products.forEach(p => {
            const cat = p.categoria || 'Menú Principal';
            if (!groups[cat]) groups[cat] = [];
            groups[cat].push(p);
        });
        return groups;
    }, [products]);

    const filteredGroups = useMemo(() => {
        if (!searchTerm) return groupedProducts;
        
        const filtered: Record<string, Product[]> = {};
        Object.entries(groupedProducts).forEach(([cat, items]) => {
            const matching = items.filter(p => 
                p.nombre.toLowerCase().includes(searchTerm.toLowerCase()) ||
                p.descripcion?.toLowerCase().includes(searchTerm.toLowerCase())
            );
            if (matching.length > 0) filtered[cat] = matching;
        });
        return filtered;
    }, [groupedProducts, searchTerm]);

    if (loading) {
        return (
            <div className="min-h-screen bg-black flex items-center justify-center">
                <div className="w-10 h-10 border-4 border-white/20 border-t-white rounded-full animate-spin" />
            </div>
        );
    }

    if (!store) return <div className="p-10 text-white text-center">Tienda no encontrada</div>;

    return (
        <div className="min-h-screen bg-[#0a0a0a] text-white pb-32">
            {/* Header / Banner Area */}
            <div className="relative h-64 md:h-80 w-full overflow-hidden bg-slate-900">
                <Image 
                    src={(store.banner_url && store.banner_url.length > 5) ? store.banner_url : 'https://images.unsplash.com/photo-1504674900247-0877df9cc836?q=80&w=1200'} 
                    alt="Cover" 
                    fill 
                    priority
                    unoptimized
                    className="object-cover"
                />
                <div className="absolute inset-0 bg-gradient-to-t from-[#0a0a0a] via-black/20 to-transparent" />
                <Link 
                    href="/"
                    className="absolute top-6 left-6 z-20 bg-black/40 backdrop-blur-md p-3 rounded-2xl hover:bg-black/60 transition-all text-white border border-white/10"
                >
                    <ArrowLeft size={20} />
                </Link>
            </div>

            {/* Store Card Floating */}
            <div className="relative z-10 -mt-32 px-4 max-w-2xl mx-auto">
                <motion.div 
                    initial={{ y: 20, opacity: 0 }}
                    animate={{ y: 0, opacity: 1 }}
                    className="bg-[#1a1a1a] rounded-[2.5rem] p-8 shadow-2xl border border-white/5 text-center"
                >
                    <div className="relative w-48 h-48 mx-auto -mt-32 mb-6 rounded-[3rem] overflow-hidden border-8 border-[#1a1a1a] shadow-2xl bg-white">
                        <Image src={store.logo_url} alt={store.nombre} fill className="object-cover" unoptimized />
                    </div>
                    
                    <h1 className="text-4xl font-black tracking-tighter mb-2 uppercase">{store.nombre}</h1>
                    
                    <div className="flex flex-wrap items-center justify-center gap-3 mb-4">
                        <span className="bg-emerald-500/10 text-emerald-500 px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest">
                            {store.estado === 'activo' ? 'Abierto' : 'Cerrado'}
                        </span>
                        <div className="flex items-center gap-1 text-yellow-400">
                            <Star size={14} className="fill-yellow-400" />
                            <span className="text-xs font-bold text-white">4.8</span>
                        </div>
                        <span className="text-white/40 text-xs font-bold">•</span>
                        <span className="text-white/60 text-xs font-bold">{store.categoria}</span>
                    </div>

                    <div className="flex flex-col items-center gap-2 mb-6 text-white/50">
                        <div className="flex items-center gap-2 text-xs font-bold">
                            <MapPin size={16} className="text-cyan-400" />
                            <span>{store.direccion || 'Ubicación no especificada'}</span>
                        </div>
                        
                        <div className="flex items-center gap-4 mt-2">
                            {store.entrega_domicilio && (
                                <div className="flex items-center gap-1.5 px-3 py-1.5 bg-white/5 rounded-xl border border-white/5">
                                    <Truck size={14} className="text-cyan-400" />
                                    <span className="text-[9px] font-black uppercase tracking-widest">Envío</span>
                                </div>
                            )}
                            {store.recolecta_pedidos && (
                                <div className="flex items-center gap-1.5 px-3 py-1.5 bg-white/5 rounded-xl border border-white/5">
                                    <ShoppingBag size={14} className="text-emerald-400" />
                                    <span className="text-[9px] font-black uppercase tracking-widest">Llevar</span>
                                </div>
                            )}
                            {store.consumo_sucursal && (
                                <div className="flex items-center gap-1.5 px-3 py-1.5 bg-white/5 rounded-xl border border-white/5">
                                    <Utensils size={14} className="text-orange-400" />
                                    <span className="text-[9px] font-black uppercase tracking-widest">Local</span>
                                </div>
                            )}
                        </div>

                        <div className="flex items-center gap-2 mt-4 flex-wrap justify-center">
                            <span className="text-[9px] font-black uppercase tracking-widest text-white/20 w-full mb-1">Pagos aceptados:</span>
                            {store.acepta_efectivo && (
                                <div className="flex items-center gap-1.5 px-3 py-1.5 bg-cyan-500/5 rounded-xl border border-cyan-500/10">
                                    <CheckCircle size={12} className="text-cyan-400" />
                                    <span className="text-[9px] font-black uppercase tracking-widest text-cyan-400/80">Efectivo</span>
                                </div>
                            )}
                            {store.acepta_tarjeta && (
                                <div className="flex items-center gap-1.5 px-3 py-1.5 bg-cyan-500/5 rounded-xl border border-cyan-500/10">
                                    <CheckCircle size={12} className="text-cyan-400" />
                                    <span className="text-[9px] font-black uppercase tracking-widest text-cyan-400/80">Tarjeta</span>
                                </div>
                            )}
                            {store.acepta_transferencia && (
                                <div className="flex items-center gap-1.5 px-3 py-1.5 bg-cyan-500/5 rounded-xl border border-cyan-500/10">
                                    <CheckCircle size={12} className="text-cyan-400" />
                                    <span className="text-[9px] font-black uppercase tracking-widest text-cyan-400/80">Transf.</span>
                                </div>
                            )}
                        </div>
                    </div>

                    <div className="flex items-center justify-center gap-3 border-t border-white/5 pt-6">
                        {store.facebook_url && (
                            <a href={store.facebook_url} target="_blank" className="p-3 bg-white/5 rounded-2xl hover:bg-white/10 transition-all text-blue-500">
                                <Facebook size={20} />
                            </a>
                        )}
                        <a href={`https://instagram.com`} target="_blank" className="p-3 bg-white/5 rounded-2xl hover:bg-white/10 transition-all text-pink-500">
                            <Instagram size={20} />
                        </a>
                        <a href={`https://wa.me/${store.telefono_contacto}`} target="_blank" className="p-3 bg-white/5 rounded-2xl hover:bg-white/10 transition-all text-emerald-500">
                            <MessageCircle size={20} />
                        </a>
                        <button 
                            onClick={() => setShowHours(!showHours)}
                            className="flex items-center gap-2 px-5 py-3 bg-white/5 rounded-2xl hover:bg-white/10 transition-all text-xs font-bold"
                        >
                            <Clock size={18} />
                            Horarios
                            <ChevronDown size={16} className={`transition-transform duration-300 ${showHours ? 'rotate-180' : ''}`} />
                        </button>
                    </div>

                    <AnimatePresence>
                        {showHours && (
                            <motion.div 
                                initial={{ height: 0, opacity: 0 }}
                                animate={{ height: 'auto', opacity: 1 }}
                                exit={{ height: 0, opacity: 0 }}
                                className="overflow-hidden mt-4 pt-4 border-t border-white/5 text-left"
                            >
                                <div className="space-y-2">
                                    {store.horarios ? Object.entries(store.horarios).map(([day, hours]) => (
                                        <div key={day} className="flex justify-between text-[11px] font-bold">
                                            <span className={`${day === capitalizedToday ? 'text-cyan-400' : 'text-white/40'} flex items-center gap-2`}>
                                                {day === capitalizedToday && <div className="w-1.5 h-1.5 bg-cyan-400 rounded-full animate-pulse" />}
                                                {day}
                                            </span>
                                            <span className={day === capitalizedToday ? 'text-white font-black' : 'text-white/60'}>
                                                {hours.active ? `${hours.open} - ${hours.close}` : 'Cerrado'}
                                            </span>
                                        </div>
                                    )) : (
                                        <div className="flex justify-between text-xs font-bold">
                                            <span className="text-white/40">Lunes - Domingo</span>
                                            <span>09:00 AM - 10:00 PM</span>
                                        </div>
                                    )}
                                </div>
                            </motion.div>
                        )}
                    </AnimatePresence>
                </motion.div>
            </div>

            {/* Menu Sections */}
            <div className="mt-12 px-4 max-w-4xl mx-auto space-y-12">
                {/* Search Bar */}
                <div className="relative group">
                    <div className="absolute inset-y-0 left-5 flex items-center pointer-events-none text-white/20 group-focus-within:text-white transition-colors">
                        <Search size={20} />
                    </div>
                    <input 
                        type="text" 
                        placeholder="Buscar platillo..."
                        className="w-full bg-[#1a1a1a] border border-white/5 rounded-[2rem] py-5 pl-14 pr-6 text-sm font-bold focus:ring-1 focus:ring-white/20 transition-all outline-none"
                        value={searchTerm}
                        onChange={(e) => setSearchTerm(e.target.value)}
                    />
                </div>

                {Object.keys(filteredGroups).length > 0 ? (
                    Object.entries(filteredGroups).map(([category, items], idx) => (
                        <div key={category} className="space-y-6">
                            <div className="flex items-center gap-4">
                                <h2 className="text-xl font-black tracking-tight">{category}</h2>
                                <div className="h-[2px] flex-1 bg-gradient-to-r from-white/10 to-transparent" />
                            </div>

                            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                {items.map((product) => (
                                    <motion.div 
                                        whileHover={{ scale: 1.01 }}
                                        key={product.id}
                                        className="bg-[#1a1a1a] p-5 rounded-[2rem] flex gap-5 border border-white/5 hover:border-white/10 transition-all group"
                                    >
                                        <div className="relative w-24 h-24 rounded-2xl overflow-hidden bg-[#0a0a0a] flex-shrink-0">
                                            {product.foto_url ? (
                                                <Image 
                                                    src={product.foto_url} 
                                                    alt={product.nombre} 
                                                    fill 
                                                    className="object-cover group-hover:scale-110 transition-transform duration-500" 
                                                    unoptimized
                                                />
                                            ) : (
                                                <div className="w-full h-full flex items-center justify-center text-2xl opacity-20">🍔</div>
                                            )}
                                        </div>
                                        
                                        <div className="flex-1 flex flex-col justify-between py-1">
                                            <div>
                                                <h3 className="font-black text-md leading-tight mb-1">{product.nombre}</h3>
                                                <p className="text-white/40 text-[10px] font-medium line-clamp-2 leading-relaxed h-8">
                                                    {product.descripcion || 'Sin descripción disponible.'}
                                                </p>
                                            </div>
                                            
                                            <div className="flex items-center justify-between mt-2">
                                                <span className="font-mono font-black text-lg tracking-tighter">${parseFloat(product.precio.toString()).toFixed(0)}</span>
                                                <div className="flex items-center gap-3">
                                                    {cartItems.find(i => i.id === product.id) && (
                                                        <div className="flex items-center gap-3 bg-white/5 px-2 py-1 rounded-full border border-white/5">
                                                            <button 
                                                                onClick={(e) => { e.stopPropagation(); removeFromCart(product.id); }}
                                                                className="w-8 h-8 rounded-full flex items-center justify-center hover:bg-white/10 transition-all"
                                                            >
                                                                <Minus size={14} />
                                                            </button>
                                                            <span className="text-xs font-black w-4 text-center">
                                                                {cartItems.find(i => i.id === product.id)?.qty}
                                                            </span>
                                                            <button 
                                                                onClick={(e) => { e.stopPropagation(); addToCart(product); }}
                                                                className="w-8 h-8 rounded-full flex items-center justify-center hover:bg-white/10 transition-all font-black"
                                                            >
                                                                <Plus size={14} />
                                                            </button>
                                                        </div>
                                                    )}
                                                    {!cartItems.find(i => i.id === product.id) && (
                                                        <button 
                                                            onClick={(e) => { e.stopPropagation(); addToCart(product); }}
                                                            className="w-10 h-10 bg-white text-black rounded-full flex items-center justify-center hover:scale-110 active:scale-90 transition-all shadow-xl shadow-white/5"
                                                        >
                                                            <Plus size={20} />
                                                        </button>
                                                    )}
                                                </div>
                                            </div>
                                        </div>
                                    </motion.div>
                                ))}
                            </div>
                        </div>
                    ))
                ) : (
                    <div className="py-20 text-center space-y-8 flex flex-col items-center">
                        <div className="relative w-56 h-56 md:w-72 md:h-72 opacity-90 group-hover:opacity-100 transition-opacity">
                            <Image 
                                src="/empty_menu.png" 
                                alt="Menú vacío" 
                                fill 
                                className="object-contain"
                            />
                        </div>
                        <div className="max-w-md mx-auto space-y-4">
                            <div className="space-y-2">
                                <p className="text-white font-black uppercase tracking-widest text-lg leading-tight px-4">
                                    ¡Vaya! Parece que {store.nombre} aún no sube su menú
                                </p>
                                <p className="text-cyan-400 font-bold text-[10px] uppercase tracking-[0.3em] px-4">
                                    Recuérdales que para ti es más fácil pedir por Menuvi
                                </p>
                            </div>
                            <button 
                                onClick={() => window.open(`https://wa.me/${store.whatsapp_pedidos}?text=Hola%20${store.nombre},%20vi%20tu%20perfil%20en%20Menuvi%20pero%20aún%20no%20tienes%20tu%20menú%20actualizado.%20¡Súbelo%20para%20poder%20pedirte%20fácil!`, '_blank')}
                                className="px-8 py-3 bg-white/5 border border-white/10 rounded-2xl text-[10px] font-black uppercase tracking-widest hover:bg-white/10 transition-all text-white/60 hover:text-white"
                            >
                                Enviar recordatorio por WhatsApp
                            </button>
                        </div>
                    </div>
                )}
            </div>

            {/* Floating Cart Button */}
            <AnimatePresence>
                {totalItems > 0 && (
                    <motion.div 
                        initial={{ y: 100 }}
                        animate={{ y: 0 }}
                        exit={{ y: 100 }}
                        className="fixed bottom-8 left-0 right-0 z-50 px-4"
                    >
                        <button 
                            onClick={() => setShowCart(true)}
                            className="w-full max-w-md mx-auto bg-white text-black py-6 rounded-[2.5rem] flex items-center justify-between px-8 shadow-2xl hover:scale-[1.02] active:scale-95 transition-all"
                        >
                            <div className="flex items-center gap-4">
                                <div className="bg-black text-white w-8 h-8 rounded-full flex items-center justify-center text-xs font-black">
                                    {totalItems}
                                </div>
                                <span className="font-black text-sm uppercase tracking-widest">Ver Pedido</span>
                            </div>
                            <div className="flex items-center gap-4">
                                <span className="font-black text-lg tracking-tighter">${subtotal}</span>
                                <div className="flex items-center gap-2">
                                    <ShoppingBag size={20} />
                                    <ChevronRight size={18} />
                                </div>
                            </div>
                        </button>
                    </motion.div>
                )}
            </AnimatePresence>

            {/* Cart Modal */}
            <AnimatePresence>
                {showCart && (
                    <div className="fixed inset-0 z-[100] flex items-center justify-center p-4">
                        <motion.div 
                            initial={{ opacity: 0 }}
                            animate={{ opacity: 1 }}
                            exit={{ opacity: 0 }}
                            onClick={() => setShowCart(false)}
                            className="absolute inset-0 bg-black/80 backdrop-blur-md"
                        />
                        <motion.div 
                            initial={{ scale: 0.9, opacity: 0, y: 20 }}
                            animate={{ scale: 1, opacity: 1, y: 0 }}
                            exit={{ scale: 0.9, opacity: 0, y: 20 }}
                            className="bg-[#1a1a1a] w-full max-w-lg rounded-[3rem] p-8 relative overflow-hidden border border-white/10"
                        >
                            <div className="flex justify-between items-center mb-8">
                                <div className="flex items-center gap-3">
                                    <div className="bg-white/10 p-3 rounded-2xl">
                                        <ShoppingBag className="text-white" size={24} />
                                    </div>
                                    <h2 className="text-2xl font-black uppercase tracking-tighter">Tu Pedido</h2>
                                </div>
                                <button onClick={() => setShowCart(false)} className="p-3 bg-white/5 rounded-2xl hover:bg-white/10 transition-all">
                                    <X size={20} />
                                </button>
                            </div>

                            <div className="space-y-4 max-h-[40vh] overflow-y-auto pr-2 custom-scrollbar">
                                {cartItems.map(item => (
                                    <div key={item.id} className="flex items-center gap-4 bg-white/5 p-4 rounded-3xl border border-white/5">
                                        <div className="relative w-16 h-16 rounded-2xl overflow-hidden bg-black flex-shrink-0">
                                            {item.foto_url ? (
                                                <Image src={item.foto_url} alt={item.nombre} fill className="object-cover" unoptimized />
                                            ) : (
                                                <div className="w-full h-full flex items-center justify-center text-xl">🍔</div>
                                            )}
                                        </div>
                                        <div className="flex-1">
                                            <h4 className="font-black text-xs uppercase tracking-tight leading-tight">{item.nombre}</h4>
                                            <p className="text-white/40 text-[10px] font-bold mt-1">${parseFloat(item.precio.toString()).toFixed(0)} c/u</p>
                                        </div>
                                        <div className="flex items-center gap-3 bg-black/40 px-3 py-1.5 rounded-full border border-white/5">
                                            <button 
                                                onClick={() => removeFromCart(item.id)}
                                                className="hover:scale-110 active:scale-90 transition-all"
                                            >
                                                {item.qty === 1 ? <Trash2 size={14} className="text-red-400" /> : <Minus size={14} />}
                                            </button>
                                            <span className="text-xs font-black min-w-[20px] text-center">{item.qty}</span>
                                            <button 
                                                onClick={() => addToCart(item)}
                                                className="hover:scale-110 active:scale-90 transition-all"
                                            >
                                                <Plus size={14} />
                                            </button>
                                        </div>
                                    </div>
                                ))}
                            </div>

                            <div className="mt-8 space-y-4">
                                {/* Zone Selector */}
                                <div className="space-y-2 mb-4">
                                    <label className="text-[10px] font-black uppercase tracking-widest text-white/30 px-2">Zona de Entrega</label>
                                    <div className="relative">
                                        <MapPin size={16} className="absolute left-4 top-1/2 -translate-y-1/2 text-cyan-400" />
                                        <select 
                                            value={selectedZone}
                                            onChange={(e) => setSelectedZone(e.target.value)}
                                            className="w-full bg-white/5 border border-white/5 py-4 pl-12 pr-4 rounded-2xl outline-none focus:ring-1 focus:ring-cyan-400/30 transition-all font-bold text-sm appearance-none"
                                        >
                                            {zones.length > 0 ? zones.map(z => (
                                                <option key={z.id} value={z.nombre_colonia} className="bg-[#1a1a1a]">{z.nombre_colonia}</option>
                                            )) : (
                                                <option className="bg-[#1a1a1a]">Cargando zonas...</option>
                                            )}
                                        </select>
                                        <ChevronDown size={16} className="absolute right-4 top-1/2 -translate-y-1/2 text-white/20 pointer-events-none" />
                                    </div>
                                </div>

                                <div className="space-y-2 border-t border-white/5 pt-6">
                                    <div className="flex justify-between text-xs font-bold text-white/40">
                                        <span>Subtotal</span>
                                        <span className="text-white">${subtotal}</span>
                                    </div>
                                    <div className="flex justify-between text-xs font-bold text-emerald-400">
                                        <span>Envío</span>
                                        <span>
                                            {zones.find(z => z.nombre_colonia === selectedZone)?.costo_envio ? 
                                                `$${zones.find(z => z.nombre_colonia === selectedZone).costo_envio}` : 
                                                'Gratis'}
                                        </span>
                                    </div>
                                    <div className="flex justify-between text-xl font-black text-white pt-2">
                                        <span className="uppercase tracking-tighter">Total</span>
                                        <span className="font-mono">
                                            ${subtotal + (parseFloat(zones.find(z => z.nombre_colonia === selectedZone)?.costo_envio || 0))}
                                        </span>
                                    </div>
                                </div>

                                <button 
                                    onClick={handleConfirmOrder}
                                    disabled={isSubmitting || cartItems.length === 0}
                                    className={`w-full ${isSubmitting ? 'bg-white/20 text-white/40' : 'bg-white text-black'} py-6 rounded-3xl font-black uppercase tracking-widest text-sm hover:scale-[1.02] active:scale-95 transition-all flex items-center justify-center gap-3 group disabled:opacity-50`}
                                >
                                    {isSubmitting ? (
                                        <div className="w-5 h-5 border-2 border-white/20 border-t-white rounded-full animate-spin" />
                                    ) : (
                                        <>
                                            Confirmar Orden
                                            <ChevronRight size={20} className="group-hover:translate-x-1 transition-transform" />
                                        </>
                                    )}
                                </button>
                                
                                <p className="text-center text-[10px] font-bold text-white/20 uppercase tracking-widest">
                                    Recibirás una notificación automática por WhatsApp
                                </p>
                            </div>
                        </motion.div>
                    </div>
                )}
            </AnimatePresence>

            {Object.keys(filteredGroups).length === 0 && (
                <div className="text-center py-20">
                    <div className="text-6xl mb-4">🔍</div>
                    <p className="text-white/40 font-bold">No encontramos lo que buscas.</p>
                </div>
            )}
        </div>
    );
}
