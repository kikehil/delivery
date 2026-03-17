'use client';

import React, { useEffect } from 'react';
import { useAuth } from '@/context/AuthContext';
import { useRouter, usePathname } from 'next/navigation';
import {
    BarChart3,
    Store,
    Users,
    LogOut,
    ShieldCheck,
    Menu as MenuIcon,
    X,
    CreditCard,
    Settings,
    Megaphone
} from 'lucide-react';
import Link from 'next/link';

export default function AdminLayout({
    children,
}: {
    children: React.ReactNode;
}) {
    const { user, loading, logout } = useAuth();
    const router = useRouter();
    const pathname = usePathname();
    const [isSidebarOpen, setIsSidebarOpen] = React.useState(false);

    useEffect(() => {
        if (!loading && (!user || user.role !== 'admin')) {
            router.push('/login');
        }
    }, [user, loading, router]);

    if (loading || !user || user.role !== 'admin') {
        return (
            <div className="min-h-screen bg-slate-50 flex items-center justify-center">
                <div className="w-10 h-10 border-4 border-indigo-600 border-t-transparent rounded-full animate-spin" />
            </div>
        );
    }

    const navItems = [
        { name: 'Global', path: '/admin', icon: BarChart3 },
        { name: 'Banners', path: '/admin/promotions', icon: Megaphone },
        { name: 'Negocios', path: '/admin/stores', icon: Store },
        { name: 'Usuarios', path: '/admin/users', icon: Users },
        { name: 'Cobros', path: '/admin/payments', icon: CreditCard },
    ];

    return (
        <div className="min-h-screen bg-slate-50 flex font-sans">
            {/* Sidebar for Desktop */}
            <aside className={`
        fixed translate-z-0 inset-y-0 left-0 w-72 bg-indigo-950 text-indigo-100 z-50 transition-transform duration-300 lg:translate-x-0
        ${isSidebarOpen ? 'translate-x-0' : '-translate-x-full'}
      `}>
                <div className="h-full flex flex-col p-8">
                    <div className="flex items-center gap-4 mb-12">
                        <div className="p-3 bg-indigo-500 rounded-2xl text-white shadow-xl shadow-indigo-500/20">
                            <ShieldCheck size={28} strokeWidth={2.5} />
                        </div>
                        <div>
                            <h1 className="text-2xl font-black text-white leading-tight tracking-tight">YaLoAdmin</h1>
                            <p className="text-[10px] font-black uppercase tracking-widest text-indigo-400">Master Console</p>
                        </div>
                    </div>

                    <nav className="flex-1 space-y-2">
                        {navItems.map((item) => {
                            const Icon = item.icon;
                            const isActive = pathname === item.path;
                            return (
                                <Link
                                    key={item.path}
                                    href={item.path}
                                    onClick={() => setIsSidebarOpen(false)}
                                    className={`
                    flex items-center gap-4 px-5 py-4 rounded-2xl font-bold transition-all duration-300
                    ${isActive
                                            ? 'bg-white text-indigo-950 shadow-2xl shadow-indigo-500/20'
                                            : 'hover:bg-indigo-900/50 hover:text-white'
                                        }
                  `}
                                >
                                    <Icon size={22} className={isActive ? 'text-indigo-600' : 'text-indigo-400'} />
                                    {item.name}
                                </Link>
                            );
                        })}
                    </nav>

                    <div className="pt-8 border-t border-indigo-900/50 space-y-4">
                        <div className="p-4 bg-indigo-900/30 rounded-2xl border border-indigo-800/50">
                            <p className="text-[10px] font-black uppercase tracking-widest text-indigo-400 mb-2">Connected as</p>
                            <div className="flex items-center gap-3">
                                <div className="w-10 h-10 rounded-full bg-indigo-500 flex items-center justify-center font-black text-white border-2 border-indigo-400">
                                    {user.name.charAt(0)}
                                </div>
                                <div className="flex-1 min-w-0">
                                    <p className="text-sm font-black truncate">{user.name}</p>
                                    <p className="text-[10px] font-bold text-indigo-400 truncate tracking-tight">{user.email}</p>
                                </div>
                            </div>
                        </div>

                        <button
                            onClick={logout}
                            className="w-full flex items-center gap-4 px-5 py-4 rounded-2xl font-black text-rose-400 hover:bg-rose-500/10 transition-all border border-transparent hover:border-rose-500/20"
                        >
                            <LogOut size={22} />
                            Sign Out
                        </button>
                    </div>
                </div>
            </aside>

            {/* Main Area */}
            <div className="flex-1 lg:ml-72 flex flex-col">
                {/* Header */}
                <header className="sticky top-0 h-20 bg-white/80 backdrop-blur-xl border-b border-indigo-100 px-8 flex items-center justify-between z-40">
                    <button
                        onClick={() => setIsSidebarOpen(true)}
                        className="lg:hidden p-3 -ml-2 text-indigo-950 hover:bg-indigo-50 rounded-2xl transition-colors"
                        aria-label="Open Menu"
                    >
                        <MenuIcon size={24} />
                    </button>

                    <div className="flex-1 lg:pl-0 pl-1">
                        <p className="text-[10px] font-black uppercase tracking-[0.2em] text-indigo-400 mb-0.5">YaLoPido Control Center</p>
                        <h2 className="text-lg font-black text-indigo-950">
                            {navItems.find(i => i.path === pathname)?.name || 'Control Panel'}
                        </h2>
                    </div>

                    <div className="flex items-center gap-2">
                        <button className="p-3 text-indigo-400 hover:text-indigo-950 hover:bg-indigo-50 rounded-2x transition-all">
                            <Settings size={22} />
                        </button>
                        <div className="h-8 w-[1px] bg-indigo-100 mx-2" />
                        <div className="flex items-center gap-4">
                            <div className="text-right">
                                <p className="text-xs font-black text-indigo-950">{user.name}</p>
                                <p className="text-[9px] font-black uppercase tracking-widest text-indigo-400">Administrator</p>
                            </div>
                        </div>
                    </div>
                </header>

                <main className="flex-1 p-8 overflow-y-auto">
                    {children}
                </main>
            </div>
        </div>
    );
}
