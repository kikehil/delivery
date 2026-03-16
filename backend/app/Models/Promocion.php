<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Promocion extends Model
{
    protected $fillable = [
        'titulo',
        'subtitulo',
        'tag_text',
        'boton_text',
        'imagen_url',
        'link_url',
        'orden',
        'activo',
        'color_fondo'
    ];
}
