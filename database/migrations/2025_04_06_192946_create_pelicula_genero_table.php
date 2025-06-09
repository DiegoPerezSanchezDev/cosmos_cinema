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
        Schema::create('pelicula_genero', function (Blueprint $table) {
            $table->unsignedBigInteger('id_pelicula');
            $table->unsignedBigInteger('id_genero_pelicula');
            $table->foreign('id_pelicula')->references('id')->on('pelicula');
            $table->foreign('id_genero_pelicula')->references('id_genero_pelicula')->on('genero_pelicula');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pelicula_genero');
    }
};
