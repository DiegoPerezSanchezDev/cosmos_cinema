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
        Schema::create('factura', function (Blueprint $table) {
            $table->id('id_factura');
            $table->double('monto_total');
            $table->string('titular_email');
            $table->string('num_factura');
            $table->string('pedido_redsys_id');
            $table->string('estado');
            $table->string('codigo_autorizacion_redsys')->nullable();
            $table->date('fecha_pago')->nullable();
            $table->unsignedBigInteger('id_user')->nullable();
            $table->unsignedBigInteger('id_impuesto');
            $table->timestamps();
            $table->foreign('id_user')->references('id')->on('users');  
            $table->foreign('id_impuesto')->references('id_impuesto')->on('impuesto');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('factura');
    }
};
