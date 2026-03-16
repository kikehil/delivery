<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InitialDataSeeder extends Seeder
{
    public function run(): void
    {
        // 0. Usuarios de prueba
        DB::table('users')->insert([
            [
                'name' => 'Admin Maestro',
                'email' => 'admin@yalopido.com',
                'password' => bcrypt('admin123'),
                'role' => 'admin',
            ],
            [
                'name' => 'Socio Burger Lab',
                'email' => 'burgerlab@partner.com',
                'password' => bcrypt('socio123'),
                'role' => 'socio',
            ],
            [
                'name' => 'Cliente Demo',
                'email' => 'cliente@gmail.com',
                'password' => bcrypt('cliente123'),
                'role' => 'cliente',
            ],
        ]);

        // 1. Zonas
        DB::table('zonas')->insert([
            ['nombre_colonia' => 'Centro Pánuco', 'costo_envio' => 20.00],
            ['nombre_colonia' => 'Loma Linda', 'costo_envio' => 30.00],
            ['nombre_colonia' => 'Colonia Moralillo', 'costo_envio' => 35.00],
            ['nombre_colonia' => 'Mora', 'costo_envio' => 25.00],
        ]);

        // 2. Negocios
        $socioId = DB::table('users')->where('email', 'burgerlab@partner.com')->value('id');

        DB::table('negocios')->insert([
            [
                'user_id' => $socioId,
                'nombre' => 'The Burger Lab',
                'categoria' => 'Hamburguesas',
                'plan' => 'elite',
                'id_zona_base' => 1,
                'telefono_contacto' => '521234567890',
                'logo_url' => 'https://images.unsplash.com/photo-1550547660-d9450f859349?w=200',
            ],
            [
                'user_id' => $socioId, // Link both for demo, or create another user
                'nombre' => 'Sushi Zen',
                'categoria' => 'Japonesa',
                'plan' => 'pro',
                'id_zona_base' => 1,
                'telefono_contacto' => '521234567891',
                'logo_url' => 'https://images.unsplash.com/photo-1579871494447-9811cf80d66c?w=200',
            ],
        ]);

        // 3. Productos
        DB::table('productos')->insert([
            [
                'id_negocio' => 1,
                'nombre' => 'Bacon Cheese Burger',
                'precio' => 120.00,
                'descripcion' => 'Deliciosa hamburguesa con tocino rústico y queso cheddar.',
                'foto_url' => 'https://images.unsplash.com/photo-1568901346375-23c9450c58cd?w=400',
            ],
            [
                'id_negocio' => 1,
                'nombre' => 'Papas Gajo',
                'precio' => 45.00,
                'descripcion' => 'Crujientes y sazonadas con especias secretas.',
                'foto_url' => 'https://images.unsplash.com/photo-1573080496219-bb080dd4f877?w=400',
            ],
        ]);
    }
}
