<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('entrada', function (Blueprint $table) {
            $table->id('id_entrada');
            $table->string('codigo_qr', 50);
            $table->string('ruta_pdf', 255);
            $table->string('estado', 255);
            // Precio
            $table->double('precio_total');
            $table->mediumInteger('descuento');
            $table->double('precio_final');
            // Sala
            $table->bigInteger('sala');
            $table->unsignedBigInteger('sala_id');
            // Sesión
            $table->string('poster_ruta', 255);
            $table->string('pelicula_titulo', 255);
            $table->unsignedBigInteger('pelicula_id');
            $table->string('hora');
            $table->string('fecha');
            // Asiento
            $table->unsignedBigInteger('asiento_id');
            $table->mediumInteger('asiento_fila');
            $table->mediumInteger('asiento_columna');
            // Usuario
            $table->unsignedBigInteger('usuario_id')->nullable();
            // Factura
            $table->unsignedBigInteger('factura_id');
            // Tipo Entrada
            $table->unsignedBigInteger('tipo_entrada');
            // Timestamps
            $table->timestamps();

            // Relaciones
            $table->foreign('sala_id')->references('id_sala')->on('sala');
            $table->foreign('pelicula_id')->references('id')->on('pelicula');
            $table->foreign('asiento_id')->references('id_asiento')->on('asiento');
            $table->foreign('usuario_id')->references('id')->on('users');
            $table->foreign('factura_id')->references('id_factura')->on('factura');
            $table->foreign('tipo_entrada')->references('id_tipo_entrada')->on('tipo_entrada');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('entrada');
    }
};
