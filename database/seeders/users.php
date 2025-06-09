<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class users extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('users')->insert([
            [
                'nombre' => 'Juan',
                'apellidos' => 'Perez',
                'email' => 'juan.perez@gmail.com',
                'email_verified_at' => now(), // Puedes añadir esto si quieres que estén verificados
                'fecha_nacimiento' => '2000-04-10',
                'numero_telefono' => '555111111',
                'dni' => '12345678U',
                'direccion' => 'Av. Siempre Viva 123',
                'ciudad_id' => 1,                       // CORREGIDO: ciudad -> ciudad_id
                'codigo_postal' => '12345',
                'tipo_usuario' => 2,                 // CORREGIDO: tipo_usuario -> tipo_usuario_id
                'mayor_edad_confirmado' => true,        // CORREGIDO: mayor_edad -> mayor_edad_confirmado (y usando boolean)
                'password' => bcrypt('password123'),// Usar Hash::make()
                'id_descuento' => 1,
                // 'google_id' => null, // Opcional si no se registran con Google
                // 'avatar' => null,    // Opcional
                'acepta_terminos' => false, // Valor por defecto para el nuevo campo
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Pepe',
                'apellidos' => 'Tolini',
                'email' => 'pepe@gmail.com',
                'email_verified_at' => now(),
                'fecha_nacimiento' => '1998-07-19',
                'numero_telefono' => '758694257',
                'dni' => '55324856Y',
                'direccion' => 'Av. Siempre Muerta 321',
                'ciudad_id' => 12,
                'codigo_postal' => '65432',
                'tipo_usuario' => 3,
                'mayor_edad_confirmado' => true,
                'password' => bcrypt('123456789'),
                'id_descuento' => 2,
                'acepta_terminos' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],[
                'nombre' => 'Diego',
                'apellidos' => 'Pérez',
                'email' => 'diego.perez@cosmosAdmin.com',
                'email_verified_at' => now(),
                'fecha_nacimiento' => '2000-02-03',
                'numero_telefono' => '123456789',
                'dni' => '55324856J',
                'direccion' => 'Av. Siempre Muerta 321',
                'ciudad_id' => 13,
                'codigo_postal' => '65432',
                'tipo_usuario' => 1,
                'mayor_edad_confirmado' => true,
                'password' => bcrypt('CosmosAdmin123'),
                'id_descuento' => 1,
                'acepta_terminos' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Carlos',
                'apellidos' => 'García',
                'email' => 'carlos.garcia@cosmosAdmin.com',
                'email_verified_at' => now(),
                'fecha_nacimiento' => '1990-02-20',
                'numero_telefono' => '555654321',
                'dni' => '55324859P',
                'direccion' => 'Av. Siempre Muerta 321',
                'ciudad_id' => 14,
                'codigo_postal' => '65432',
                'tipo_usuario' => 1,
                'mayor_edad_confirmado' => true,
                'password' => bcrypt('CosmosAdmin456'),
                'id_descuento' => 1,
                'acepta_terminos' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Carlos',
                'apellidos' => 'García',
                'email' => 'cozar1995@hotmail.com',
                'email_verified_at' => now(),
                'fecha_nacimiento' => '1990-02-20',
                'numero_telefono' => '555654321',
                'dni' => '53904949X',
                'direccion' => 'Av. Siempre Muerta 321',
                'ciudad_id' => 14,
                'codigo_postal' => '65432',
                'tipo_usuario' => 1,
                'mayor_edad_confirmado' => true,
                'password' => bcrypt('asdasd123'),
                'id_descuento' => 1,
                'acepta_publicidad' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
