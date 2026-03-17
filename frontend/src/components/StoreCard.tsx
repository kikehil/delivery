import { Star, Award, Clock } from 'lucide-react'
import Image from 'next/image'

interface StoreCardProps {
    name: string
    category: string
    rating: number
    deliveryTime: string
    shippingFee: string
    imageUrl: string
    isElite?: boolean
}

export default function StoreCard({
    name,
    category,
    rating,
    deliveryTime,
    shippingFee,
    imageUrl,
    isElite = false
}: StoreCardProps) {
    return (
        <div className="group cursor-pointer space-y-3 transition-all">
            <div className="relative aspect-video rounded-2xl overflow-hidden shadow-sm bg-slate-100">
                <div className="absolute top-3 right-3 z-10 bg-white/95 backdrop-blur px-2 py-1 rounded-lg shadow-sm flex items-center gap-1 group-hover:bg-slate-900 group-hover:text-white transition-all duration-300">
                    <Star size={14} className="fill-yellow-400 text-yellow-400" />
                    <span className="text-xs font-bold font-mono">{rating.toFixed(1)}</span>
                </div>

                <Image
                    src={imageUrl}
                    alt={name}
                    fill
                    className="object-cover group-hover:scale-105 transition-transform duration-700"
                />

                {isElite && (
                    <div className="absolute bottom-3 left-3 z-10 bg-emerald-500 text-white px-2 py-1 rounded-md shadow-sm flex items-center gap-1">
                        <Award size={12} />
                        <span className="text-[10px] font-black uppercase tracking-tighter">Selección Urbix</span>
                    </div>
                )}
            </div>

            <div className="space-y-1 px-1">
                <div className="flex items-center justify-between">
                    <h3 className="text-lg font-black text-slate-900 truncate group-hover:text-slate-700 transition-colors">{name}</h3>
                </div>
                <div className="flex items-center gap-2 text-slate-500 text-sm font-semibold">
                    <span>{category}</span>
                    <span>•</span>
                    <div className="flex items-center gap-1 text-slate-900">
                        <Clock size={14} />
                        <span>{deliveryTime}</span>
                    </div>
                    <span>•</span>
                    <span className={shippingFee === '$0 envío' ? 'text-emerald-600 font-bold' : ''}>{shippingFee}</span>
                </div>
            </div>
        </div>
    )
}
