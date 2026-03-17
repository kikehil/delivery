'use client';

import React, { useEffect } from 'react';
import { useAuth } from '@/context/AuthContext';
import { useRouter, usePathname } from 'next/navigation';
import {
    LayoutDashboard,
    ShoppingBag,
    UtensilsCrossed,
    LogOut,
    Store,
    Menu as MenuIcon,
    X
} from 'lucide-react';
import Link from 'next/link';

export default function PartnerLayout({
    children,
}: {
    children: React.ReactNode;
}) {
    const { user, loading, logout } = useAuth();
    const router = useRouter();
    const pathname = usePathname();
    const [isSidebarOpen, setIsSidebarOpen] = React.useState(false);
    const isRegisterPage = pathname === '/partner/register';

    useEffect(() => {
        if (!loading && !isRegisterPage && (!user || user.role !== 'socio')) {
            router.push('/login');
        }
    }, [user, loading, router, isRegisterPage]);

    if (loading) {
        return (
            <div className="min-h-screen bg-[#0a0a0a] flex items-center justify-center">
                <div className="w-10 h-10 border-4 border-white/20 border-t-white rounded-full animate-spin" />
            </div>
        );
    }

    // Si es la página de registro, renderizar directamente sin el layout del panel
    if (isRegisterPage) {
        return <>{children}</>;
    }

    if (!user || user.role !== 'socio') {
        return null; // Opcional: Evitar parpadeo antes del redirect
    }

    const navItems = [
        { name: 'Inicio', path: '/partner', icon: LayoutDashboard },
        { name: 'Pedidos', path: '/partner/orders', icon: ShoppingBag },
        { name: 'Menú', path: '/partner/products', icon: UtensilsCrossed },
        { name: 'Configuración', path: '/partner/settings', icon: Store },
    ];

    return (
        <div className="min-h-screen bg-slate-50">
            {/* Mobile Sidebar Overlay */}
            {isSidebarOpen && (
                <div
                    className="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-40 lg:hidden"
                    onClick={() => setIsSidebarOpen(false)}
                />
            )}

            {/* Sidebar */}
            <aside className={`
        fixed top-0 left-0 bottom-0 w-64 bg-white border-r border-slate-200 z-50 transition-transform duration-300 lg:translate-x-0
        ${isSidebarOpen ? 'translate-x-0' : '-translate-x-full'}
      `}>
                <div className="p-6">
                    <div className="flex items-center gap-3 mb-8">
                        <div className="p-2 bg-slate-900 rounded-xl text-white">
                            <Store size={24} />
                        </div>
                        <span className="text-xl font-black text-slate-900 tracking-tight">Cruce Socio</span>
                    </div>

                    <nav className="space-y-1">
                        {navItems.map((item) => {
                            const Icon = item.icon;
                            const isActive = pathname === item.path;
                            return (
                                <Link
                                    key={item.path}
                                    href={item.path}
                                    className={`
                    flex items-center gap-3 px-4 py-3 rounded-xl font-bold transition-all
                    ${isActive
                                            ? 'bg-slate-900 text-white shadow-lg shadow-slate-900/10'
                                            : 'text-slate-500 hover:bg-slate-100 hover:text-slate-900'
                                        }
                  `}
                                >
                                    <Icon size={20} />
                                    {item.name}
                                </Link>
                            );
                        })}
                    </nav>
                </div>

                <div className="absolute bottom-0 left-0 right-0 p-6 border-t border-slate-100">
                    <button
                        onClick={logout}
                        className="flex items-center gap-3 w-full px-4 py-3 rounded-xl font-bold text-rose-600 hover:bg-rose-50 transition-all"
                    >
                        <LogOut size={20} />
                        Cerrar Sesión
                    </button>
                </div>
            </aside>

            {/* Main Content */}
            <main className="lg:ml-64 min-h-screen">
                {/* Header */}
                <header className="sticky top-0 bg-white/80 backdrop-blur-md border-b border-slate-200 px-6 py-4 flex items-center justify-between z-30">
                    <button
                        onClick={() => setIsSidebarOpen(true)}
                        className="p-2 -ml-2 text-slate-500 hover:text-slate-900 lg:hidden"
                    >
                        <MenuIcon size={24} />
                    </button>

                    <div className="flex-1 lg:pl-0 pl-4">
                        <h2 className="text-lg font-bold text-slate-900 truncate">Estadísticas</h2>
                    </div>

                    <div className="flex items-center gap-4">
                        <div className="hidden sm:block text-right">
                            <p className="text-sm font-black text-slate-900">{user.name}</p>
                            <p className="text-[10px] font-black uppercase tracking-widest text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded ml-auto w-fit">ONLINE</p>
                        </div>
                        <div className="w-10 h-10 bg-slate-900 text-white rounded-full flex items-center justify-center font-bold text-sm">
                            {user.name.charAt(0)}
                        </div>
                    </div>
                </header>

                {/* Page Body */}
                <div className="p-6">
                    {children}
                </div>
            </main>
        </div>
    );
}
