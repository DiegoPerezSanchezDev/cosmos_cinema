<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Esta es la clase de la migración generada por Laravel
return new class extends Migration
{
    /**
     * Ejecuta las migraciones (crea la tabla en la base de datos).
     */
    public function up(): void
    {
        Schema::create('nominaEmpleado', function (Blueprint $table) {
            // Define la columna 'id' como clave primaria auto-incremental
            $table->id();

            // Esta columna guardará el ID del empleado que viene de la tabla 'users'
            $table->unsignedBigInteger('id_empleado');

            // Define las columnas para el período de la nómina
            $table->integer('mes')->comment('Mes al que corresponde la nómina (1-12)'); // Columna para el número del mes
            $table->integer('anio')->comment('Año al que corresponde la nómina');       // Columna para el año

            // Define la fecha de generación de esta entrada de nómina
            $table->date('generacion_fecha');

            // Define las columnas para los valores monetarios usando tipo decimal para precisión (10 dígitos en total, 2 después del punto decimal)
            $table->decimal('salario_bruto', 10, 2)->comment('Salario bruto del período');
            $table->decimal('deducciones_seguridad_social', 10, 2)->default(0.00)->comment('Total de deducciones por Seguridad Social');
            $table->decimal('irpf', 10, 2)->default(0.00)->comment('Total de deducciones por IRPF');
            $table->decimal('otras_deducciones', 10, 2)->default(0.00)->comment('Suma de otras posibles deducciones');
            $table->decimal('salario_neto', 10, 2)->comment('Salario neto (Salario Bruto - Deducciones)');

            //Columna para almacenar la ruta o nombre del archivo PDF si se generan y almacenan previamente
            $table->string('ruta_pdf', 255)->nullable()->comment('Ruta al archivo PDF de la nómina');

            // Columnas automáticas para la fecha de creación y última actualización del registro
            $table->timestamps(); // Crea las columnas 'created_at' y 'updated_at'

            $table->foreign('id_empleado') // La columna en la tabla 'payslips'
                  ->references('id')->on('users') // ** Referencia a la columna 'id' en la tabla 'users' **
                ->onDelete('cascade');

            // Asegura que no se pueda crear más de una nómina para el mismo empleado en el mismo mes y año
            $table->unique(['id_empleado', 'mes', 'anio']);
        });
    }

    /**
     * Revierte las migraciones (elimina la tabla de la base de datos).
     */
    public function down(): void
    {
        Schema::dropIfExists('nominaEmpleado');
    }
};