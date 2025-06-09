<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class MenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Insertar datos de menús temáticos espaciales en la tabla 'menus'
        DB::table('menus')->insert([
            [
                'nombre' => 'Nebulosa Burger',
                'descripcion' => 'Una explosión de sabor con carne estelar, queso de luna fundido y aderezo de cometa. Acompañado de patatas fritas espaciales.',
                'precio' => 12.99,
                'activo' => true,
                'imagen_url' => '/images/menus/nebulosa-burger.webp',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Estrella Fugaz Nachos',
                'descripcion' => 'Crujientes nachos que caen como estrellas, con salsa de queso cósmico, guacamole galáctico y frijoles de asteroide.',
                'precio' => 8.50,
                'activo' => true,
                'imagen_url' => '/images/menus/estrella-nachos.webp',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Orbitrón Dog',
                'descripcion' => 'Un perrito caliente que le da la vuelta a tu paladar, con salchicha de pulso, cebolla caramelizada de anillo y mostaza de supernova.',
                'precio' => 9.75,
                'activo' => true,
                'imagen_url' => '/images/menus/orbitron-dog.webp',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Bebida de Agujero Negro',
                'descripcion' => 'Una refrescante bebida misteriosa y burbujeante que te absorbe el calor del espacio. Sabor a frutos oscuros.',
                'precio' => 4.00,
                'activo' => true,
                'imagen_url' => '/images/menus/agujero-negro-bebida.webp',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Palomitas Súpernova',
                'descripcion' => 'Palomitas recién hechas con una chispa picante y explosiva que simula una supernova.',
                'precio' => 6.25,
                'activo' => true,
                'imagen_url' => '/images/menus/supernova-popcorn.webp',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Batido Vía Láctea',
                'descripcion' => 'Un cremoso y dulce batido que te transporta por la Vía Láctea con cada sorbo. Varios sabores disponibles.',
                'precio' => 5.50,
                'activo' => true,
                'imagen_url' => '/images/menus/via-lactea-shake.webp',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
}

}

