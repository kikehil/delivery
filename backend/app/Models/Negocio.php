<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Negocio extends Model
{
    protected $fillable = [
        'user_id',
        'nombre',
        'categoria',
        'logo_url',
        'plan',
        'estado',
        'id_zona_base',
        'nombre_responsable',
        'email',
        'telefono_contacto',
        'whatsapp_pedidos',
        'direccion',
        'banner_url',
        'modulo_abierto',
        'entrega_domicilio',
        'recolecta_pedidos',
        'consumo_sucursal',
        'horarios',
        'opciones_servicio',
        'facebook_url',
        'instagram_url',
        'youtube_url'
    ];

    protected $casts = [
        'horarios' => 'json',
        'opciones_servicio' => 'json',
        'modulo_abierto' => 'boolean',
        'entrega_domicilio' => 'boolean',
        'recolecta_pedidos' => 'boolean',
        'consumo_sucursal' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function productos(): HasMany
    {
        return $this->hasMany(Producto::class, 'id_negocio');
    }

    public function pedidos(): HasMany
    {
        return $this->hasMany(Pedido::class, 'comercio_id');
    }

    public function zona(): BelongsTo
    {
        return $this->belongsTo(Zona::class, 'id_zona_base');
    }
}
