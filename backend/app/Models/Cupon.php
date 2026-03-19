<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cupon extends Model
{
    protected $table = 'cupones';
    
    protected $fillable = [
        'codigo',
        'tipo',
        'valor',
        'limite_uso',
        'usos_actuales',
        'estado'
    ];
}
