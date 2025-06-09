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
        Schema::create('asiento', function (Blueprint $table) {
            $table->id('id_asiento');
            $table->unsignedBigInteger('id_sesion_pelicula');       // id sesion
            $table->unsignedBigInteger('estado');                   // estado asiento
            $table->unsignedBigInteger('id_sala');                  // id sala
            $table->unsignedBigInteger('id_tipo_asiento');          // tipo asiento
            $table->integer('columna');
            $table->integer('fila');
            $table->timestamps();

            $table->foreign('id_sesion_pelicula')->references('id')->on('sesion_pelicula');     // id sesion
            $table->foreign('estado')->references('id')->on('asiento_estado');                  // id estado
            $table->foreign('id_sala')->references('id_sala')->on('sala');                      // id sala
            $table->foreign('id_tipo_asiento')->references('id_tipo_asiento')->on('tipo_asiento');  // tipo asiento
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asiento');
    }
};
