<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pedido extends Model
{
    protected $fillable = [
        'comercio_id',
        'cliente_zona',
        'items_json',
        'subtotal',
        'descuento',
        'envio',
        'total',
        'cupon',
        'instrucciones',
        'estado',
        'metodo_pago',
        'modalidad',
        'repartidor_nombre',
        'repartidor_telefono',
    ];

    protected $casts = [
        'items_json' => 'json',
        'subtotal' => 'decimal:2',
        'descuento' => 'decimal:2',
        'envio' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function negocio(): BelongsTo
    {
        return $this->belongsTo(Negocio::class, 'comercio_id');
    }
}
