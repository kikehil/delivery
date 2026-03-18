<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Liquidacion extends Model
{
    protected $table = 'liquidaciones';

    protected $fillable = [
        'negocio_id',
        'periodo_inicio',
        'periodo_fin',
        'total_ventas',
        'comision_plataforma',
        'monto_liquidar',
        'estado',
        'fecha_pago',
        'comprobante_url',
        'notas'
    ];

    protected $casts = [
        'periodo_inicio' => 'date',
        'periodo_fin' => 'date',
        'total_ventas' => 'decimal:2',
        'comision_plataforma' => 'decimal:2',
        'monto_liquidar' => 'decimal:2',
        'fecha_pago' => 'datetime'
    ];

    public function negocio(): BelongsTo
    {
        return $this->belongsTo(Negocio::class);
    }
}
