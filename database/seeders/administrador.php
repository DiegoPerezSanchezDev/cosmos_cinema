<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class administrador extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('administrador')->insert([
            [
                'nombre_user_admin' => 'Diego_Cosmos',
                'codigo_administrador' => 'A001',
                'email' => 'diego.perez@cosmosAdmin.com',
            ],
            [
                'nombre_user_admin' => 'Carlos_Cosmos',
                'codigo_administrador' => 'A002',
                'email' => 'carlos.garcia@cosmosAdmin.com',
            ],
        ]);
    }
}
