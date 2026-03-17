'use client';

import React, { useEffect, useState } from 'react';
import UserMenu from '@/components/UserMenu'
import { MapPin, ChevronDown, Search, Star, Award, ShoppingCart, Clock, ArrowRight } from 'lucide-react'
import Image from 'next/image'
import api from '@/lib/api';
import Link from 'next/link';
import { motion, AnimatePresence } from 'framer-motion';

const categories = [
  { name: 'Antojitos', icon: '🥪' },
  { name: 'Hamburguesas', icon: '🍔' },
  { name: 'Hotdogs', icon: '🌭' },
  { name: 'Pasteles', icon: '🍰' },
  { name: 'Pizza', icon: '🍕' },
  { name: 'Pollos', icon: '🍗' },
  { name: 'Snacks', icon: '🍟' },
  { name: 'Tacos/Tortas', icon: '🌮' },
]

interface Store {
  id: number;
  nombre: string;
  categoria: string;
  logo_url: string;
  banner_url?: string;
  rating?: number;
}

interface Promotion {
    id: number;
    titulo: string;
    subtitulo: string;
    tag_text: string;
    boton_text: string;
    imagen_url: string;
    link_url: string;
    color_fondo: string;
}

export default function Home() {
  const [stores, setStores] = useState<Store[]>([]);
  const [promotions, setPromotions] = useState<Promotion[]>([]);
  const [loading, setLoading] = useState(true);
  const [currentPromo, setCurrentPromo] = useState(0);

  useEffect(() => {
    async function fetchData() {
      try {
        const [storesRes, promoRes] = await Promise.all([
            api.get('/stores'),
            api.get('/promotions')
        ]);
        setStores(storesRes.data.data);
        setPromotions(promoRes.data.data);
      } catch (e) {
        console.error('Error fetching data');
      } finally {
        setLoading(false);
      }
    }
    fetchData();
  }, []);

  // Autoplay for carousel
  useEffect(() => {
    if (promotions.length > 0) {
        const timer = setInterval(() => {
            setCurrentPromo((prev) => (prev + 1) % promotions.length);
        }, 5000);
        return () => clearInterval(timer);
    }
  }, [promotions]);

  return (
    <main className="min-h-screen bg-[#0a0a0a] text-white pb-24">
      {/* Header */}
      <header className="sticky top-0 z-50 bg-[#0a0a0a]/80 backdrop-blur-md border-b border-white/5 px-4 py-4">
        <div className="max-w-7xl mx-auto flex flex-col gap-5">
          <div className="flex items-center justify-between">
            <span className="text-2xl font-black text-white tracking-tighter">YaLoPido</span>
            <div className="flex items-center gap-4">
              <div className="hidden md:flex items-center gap-2 px-4 py-2 bg-white/5 rounded-2xl cursor-pointer hover:bg-white/10 transition-all border border-white/5">
                <MapPin size={16} className="text-cyan-400" />
                <span className="text-xs font-black uppercase tracking-widest">Pánuco, Ver.</span>
                <ChevronDown size={14} className="text-white/40" />
              </div>
              <UserMenu />
            </div>
          </div>

          <div className="relative group">
            <div className="absolute inset-y-0 left-5 flex items-center pointer-events-none text-white/20 group-focus-within:text-cyan-400 transition-colors">
              <Search size={20} />
            </div>
            <input
              type="text"
              placeholder="¿Qué se te antoja hoy?"
              className="w-full pl-14 pr-6 py-4 bg-white/5 border border-white/5 rounded-2xl text-white placeholder:text-white/20 focus:ring-1 focus:ring-white/20 focus:bg-white/10 transition-all outline-none font-bold text-sm"
            />
          </div>
        </div>
      </header>

      <div className="max-w-7xl mx-auto px-4 py-8 space-y-12">
        {/* Categories Horizontal Scroll */}
        <div className="flex gap-6 overflow-x-auto pb-4 scrollbar-hide -mx-4 px-4 mask-fade-right">
          {categories.map((cat) => (
            <button key={cat.name} className="flex flex-col items-center gap-3 group min-w-[80px]">
              <div className="w-16 h-16 bg-[#1a1a1a] border border-white/5 rounded-2xl flex items-center justify-center text-3xl shadow-2xl group-hover:scale-110 group-active:scale-95 transition-all group-hover:border-white/20">
                {cat.icon}
              </div>
              <span className="text-[10px] font-black uppercase tracking-widest text-white/30 group-hover:text-white transition-colors text-center whitespace-nowrap">{cat.name}</span>
            </button>
          ))}
        </div>

        {/* Promotion Carousel */}
        <div className="relative overflow-hidden rounded-[2.5rem]">
            <div className="relative h-64 md:h-80 w-full overflow-hidden">
                <AnimatePresence mode="wait">
                    {promotions.length > 0 ? (
                        <motion.div
                            key={currentPromo}
                            initial={{ opacity: 0, x: 50 }}
                            animate={{ opacity: 1, x: 0 }}
                            exit={{ opacity: 0, x: -50 }}
                            transition={{ duration: 0.5, ease: "circOut" }}
                            className="absolute inset-0 flex"
                        >
                            <Link 
                                href={promotions[currentPromo].link_url || '#'}
                                className="relative w-full h-full flex items-center px-8 md:px-16 group"
                            >
                                {/* Background Image */}
                                <div className="absolute inset-0 z-0">
                                    <div 
                                        className="absolute inset-0 bg-gradient-to-r z-10" 
                                        style={{ backgroundImage: `linear-gradient(to right, ${promotions[currentPromo].color_fondo}ff, ${promotions[currentPromo].color_fondo}88, transparent)` }}
                                    />
                                    <Image 
                                        src={promotions[currentPromo].imagen_url} 
                                        alt={promotions[currentPromo].titulo}
                                        fill
                                        className="object-cover group-hover:scale-105 transition-all duration-[3s]"
                                        unoptimized
                                    />
                                </div>

                                {/* Content */}
                                <div className="relative z-20 max-w-lg space-y-4">
                                    {promotions[currentPromo].tag_text && (
                                        <span className="inline-block px-3 py-1 bg-white/20 backdrop-blur-md rounded-full text-[10px] font-black uppercase tracking-[0.2em] text-white">
                                            {promotions[currentPromo].tag_text}
                                        </span>
                                    )}
                                    <h2 className="text-4xl md:text-5xl font-black tracking-tighter leading-none text-white drop-shadow-2xl">
                                        {promotions[currentPromo].titulo}
                                    </h2>
                                    <p className="text-white/80 font-bold text-sm md:text-base max-w-sm">
                                        {promotions[currentPromo].subtitulo}
                                    </p>
                                    <button className="bg-white text-black px-6 py-3 rounded-xl font-black text-xs uppercase tracking-widest hover:bg-cyan-400 hover:text-white transition-all transform group-hover:translate-x-1 shadow-2xl">
                                        {promotions[currentPromo].boton_text}
                                    </button>
                                </div>
                            </Link>
                        </motion.div>
                    ) : (
                        <div className="w-full h-full bg-[#1a1a1a] flex items-center justify-center animate-pulse">
                            <span className="text-white/10 font-black uppercase tracking-widest">Cargando ofertas...</span>
                        </div>
                    )}
                </AnimatePresence>
            </div>

            {/* Carousel Indicators */}
            {promotions.length > 1 && (
                <div className="absolute bottom-6 left-1/2 -translate-x-1/2 flex gap-2 z-30">
                    {promotions.map((_, i) => (
                        <button
                            key={i}
                            onClick={() => setCurrentPromo(i)}
                            className={`h-1.5 rounded-full transition-all duration-300 ${
                                currentPromo === i ? 'w-8 bg-white' : 'w-2 bg-white/30'
                            }`}
                        />
                    ))}
                </div>
            )}
        </div>

        {/* Store Grid */}
        <div className="space-y-8">
          <div className="flex items-end justify-between">
            <div>
              <h2 className="text-3xl font-black text-white tracking-tighter mb-1">Negocios Locales</h2>
              <p className="text-white/40 text-xs font-bold uppercase tracking-widest">Lo mejor de tu zona en un clic</p>
            </div>
          </div>
          
          {loading ? (
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
               {[1,2,3].map(i => (
                 <div key={i} className="h-72 bg-white/5 rounded-[2.5rem] animate-pulse border border-white/5" />
               ))}
            </div>
          ) : (
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
              {stores.map((store) => (
                <Link href={`/store/${store.id}`} key={store.id} className="group cursor-pointer">
                  <div className="relative aspect-video rounded-[2.5rem] overflow-hidden shadow-2xl bg-[#1a1a1a] border border-white/5 group-hover:border-white/10 transition-all mb-4">
                    <div className="absolute top-4 right-4 z-10 bg-black/60 backdrop-blur-md px-3 py-1.5 rounded-2xl border border-white/10 flex items-center gap-1.5 shadow-xl">
                      <Star size={12} className="fill-yellow-400 text-yellow-400" />
                      <span className="text-[10px] font-black font-mono text-white">{store.rating || '4.5'}</span>
                    </div>

                    {store.logo_url ? (
                      <Image 
                        src={store.logo_url} 
                        alt={store.nombre} 
                        fill 
                        unoptimized
                        className="object-cover group-hover:scale-110 transition-transform duration-500" 
                      />
                    ) : (
                      <div className="w-full h-full bg-gradient-to-br from-[#1a1a1a] to-[#0a0a0a] flex items-center justify-center group-hover:scale-105 transition-transform duration-[1s]">
                         <span className="text-6xl opacity-20">🏪</span>
                      </div>
                    )}
                  </div>

                  <div className="px-2 space-y-2">
                    <div className="flex items-center justify-between">
                      <h3 className="text-xl font-black text-white group-hover:text-cyan-400 transition-colors uppercase tracking-tight">{store.nombre}</h3>
                      <Award className="text-emerald-500" size={18} />
                    </div>
                    <div className="flex items-center gap-3 text-white/30 text-[10px] font-black uppercase tracking-widest">
                      <span className="text-white/60">{store.categoria}</span>
                      <span className="w-1 h-1 bg-white/10 rounded-full" />
                      <div className="flex items-center gap-1">
                        <Clock size={12} className="text-white/20" />
                        <span>20-30 min</span>
                      </div>
                      <span className="w-1 h-1 bg-white/10 rounded-full" />
                      <span className="text-emerald-500/80">Envío Gratis</span>
                    </div>
                  </div>
                </Link>
              ))}

              {stores.length === 0 && (
                <div className="col-span-full py-20 text-center space-y-4">
                   <div className="text-6xl opacity-20 mb-4">🛵</div>
                   <p className="text-white/30 font-black uppercase tracking-widest text-sm">No hay tiendas abiertas por ahora</p>
                </div>
              )}
            </div>
          )}
        </div>
      </div>
    </main>
  )
}
