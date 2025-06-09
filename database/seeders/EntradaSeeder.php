<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Entrada;
use App\Models\User;
use App\Models\Pelicula;
use App\Models\Sala;
use App\Models\Asiento;
use App\Models\Factura;
use App\Models\TipoEntrada;
use Illuminate\Support\Str; // Para generar códigos QR aleatorios
use Carbon\Carbon;

class EntradaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // --- Obtener datos de tablas relacionadas ---
        // Es importante que estas tablas tengan datos antes de ejecutar este seeder.

        $usuario = User::where('tipo_usuario', '<>', 1)->inRandomOrder()->first(); // Un usuario que no sea admin
        $pelicula = Pelicula::where('activa', true)->inRandomOrder()->first();
        $sala = Sala::inRandomOrder()->first();
        $asiento = Asiento::inRandomOrder()->first();
        $facturas = Factura::all();
        $tipoEntrada = TipoEntrada::inRandomOrder()->first(); // Asume que tienes un seeder para tipos de entrada

        // Verificar si se encontraron todos los datos necesarios
        if (!$usuario || !$pelicula || !$sala || !$asiento || !$tipoEntrada) {
            $this->command->warn('No se encontraron datos suficientes en las tablas relacionadas (User, Pelicula, Sala, Asiento, Factura, TipoEntrada) para crear entradas. Asegúrate de ejecutar sus seeders primero.');
            return;
        }

        // --- Crear algunas entradas de ejemplo ---
        $numeroDeEntradasACrear = 50;

        for ($i = 0; $i < $numeroDeEntradasACrear; $i++) {
            // Simular datos para la entrada
            $precioTotal = rand(700, 1200) / 100; // Precio entre 7.00 y 12.00
            $descuentoPorcentaje = rand(0, 2) * 10; // 0%, 10%, o 20% de descuento
            $precioFinal = $precioTotal * (1 - ($descuentoPorcentaje / 100));
            $factura = $facturas->random();

            // Simular datos de sesión
            $fechaSesion = Carbon::now()->addDays(rand(1, 7))->format('Y-m-d'); // Fecha en los próximos 7 días
            // Horas comunes de cine
            $horasComunes = ['16:00', '16:30', '18:00', '18:45', '20:15', '20:30', '22:00', '22:30'];
            $horaSesion = $horasComunes[array_rand($horasComunes)];


            // Obtener un asiento diferente para cada entrada si es posible,
            // o al menos simular diferentes filas/columnas si no tienes muchos asientos.
            // Esta es una simplificación. En un sistema real, la disponibilidad de asientos sería clave.
            $asientoFila = $asiento ? $asiento->fila : rand(1, 10); // Asumiendo que Asiento tiene 'fila'
            $asientoColumna = $asiento ? $asiento->columna : rand(1, 15); // Asumiendo que Asiento tiene 'columna'
            $asientoId = $asiento ? $asiento->id_asiento : 1; // Fallback si no hay asiento

            Entrada::create([
                'codigo_qr' => 'ENTRADA-' . Str::uuid()->toString(),
                'ruta_pdf' => "",
                'estado' => 'pagado',
                'precio_total' => $precioTotal,
                'descuento' => $descuentoPorcentaje,
                'precio_final' => $precioFinal,
                // Sala
                'sala' => $sala->numero_sala ?? $sala->id_sala, 
                'sala_id' => $sala->id_sala,
                // Sesión
                'poster_ruta' => $pelicula->poster_ruta,
                'pelicula_titulo' => $pelicula->titulo,
                'pelicula_id' => $pelicula->id,
                'hora' => $horaSesion,
                'fecha' => $fechaSesion,
                // Asiento
                'asiento_id' => $asientoId,
                'asiento_fila' => $asientoFila,
                'asiento_columna' => $asientoColumna,
                // Usuario
                'usuario_id' => $usuario->id,
                // Tipo Entrada
                'factura_id' => $factura->id_factura,
                'tipo_entrada' => $tipoEntrada->id_tipo_entrada,
                // Timestamps se manejan automáticamente
            ]);
            
        }
    }
}