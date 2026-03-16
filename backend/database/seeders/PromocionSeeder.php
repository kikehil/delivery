<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PromocionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('promocions')->insert([
            [
                'titulo' => 'Noches de Pizza 2x1',
                'subtitulo' => 'Aplica en todas nuestras especialidades grandes.',
                'tag_text' => 'PROMO',
                'boton_text' => 'Explorar Ofertas',
                'imagen_url' => 'promo_pizza_2x1_v2_1773199170461.png',
                'link_url' => '/category/Pizza',
                'orden' => 1,
                'activo' => true,
                'color_fondo' => '#e11d48',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'titulo' => 'El Rey del Hotdog',
                'subtitulo' => 'Prueba el nuevo Hotdog Gigante con tocino rústico.',
                'tag_text' => 'NUEVO',
                'boton_text' => 'Ver Menú',
                'imagen_url' => 'promo_hotdog_rey_1773199118327.png',
                'link_url' => '/category/Hotdog',
                'orden' => 2,
                'activo' => true,
                'color_fondo' => '#f59e0b',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'titulo' => 'Burger Week: 20% OFF',
                'subtitulo' => 'En todos los combos de hamburguesas gourmet.',
                'tag_text' => 'OFERTA',
                'boton_text' => 'Pedir Ahora',
                'imagen_url' => 'promo_burger_week_1773199135540.png',
                'link_url' => '/category/Hamburguesas',
                'orden' => 3,
                'activo' => true,
                'color_fondo' => '#4f46e5',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'titulo' => 'Sushi Fest: 2x1',
                'subtitulo' => 'Pide un rollo y el segundo va por nuestra cuenta.',
                'tag_text' => 'LIMITADO',
                'boton_text' => 'Ver Sushi',
                'imagen_url' => 'promo_sushi_fest_1773199150865.png',
                'link_url' => '/category/Japonesa',
                'orden' => 4,
                'activo' => true,
                'color_fondo' => '#10b981',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
