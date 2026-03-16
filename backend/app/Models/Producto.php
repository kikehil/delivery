<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Producto extends Model
{
    protected $fillable = [
        'id_negocio',
        'nombre',
        'precio',
        'descripcion',
        'foto_url',
        'complementos',
        'disponible',
    ];

    protected $casts = [
        'precio' => 'decimal:2',
        'complementos' => 'json',
        'disponible' => 'boolean',
    ];

    public function negocio(): BelongsTo
    {
        return $this->belongsTo(Negocio::class, 'id_negocio');
    }
}
