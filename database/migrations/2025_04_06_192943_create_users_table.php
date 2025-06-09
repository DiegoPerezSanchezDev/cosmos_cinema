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
        Schema::create('users', function (Blueprint $table) {
            $table->id(); // Laravel por defecto usa 'id' como bigIncrements. Si necesitas 'id_usuario', sería $table->id('id_usuario');
            $table->string('nombre', 50)->nullable(); // Permite nulo, se llenará después o desde Google (parcialmente)
            $table->string('apellidos', 50)->nullable(); // Permite nulo, se llenará después o desde Google (parcialmente)
            $table->string('email', 191)->unique(); // Email de Google. Aumenté un poco la longitud por estándar.
            $table->timestamp('email_verified_at')->nullable(); // Para marcar si el email fue verificado (Google lo hace)
            $table->string('email_verification_token')->nullable()->unique();
            $table->date('fecha_nacimiento')->nullable(); // Permite nulo, se llenará después
            $table->string('numero_telefono', 15)->nullable(); // Permite nulo, se llenará después. Aumentado un poco por si acaso.
            
            // Usando foreignId para ciudad, es más conciso.
            // Asumiendo que tu tabla 'ciudades' tiene una columna 'id'.
            $table->foreignId('ciudad_id')->nullable()->constrained('ciudades')->onDelete('set null'); // 'ciudad_id' es más convencional para FKs

            $table->string('dni', 9)->unique()->nullable(); // Permite nulo, se llenará después
            $table->string('direccion', 150)->nullable(); // Ya era nullable, lo cual es bueno
            $table->string('codigo_postal', 10)->nullable(); // Permite nulo, se llenará después
            
            $table->string('password', 255)->nullable(); // MUY IMPORTANTE: Permite nulo para usuarios de Google. Aumentado para hashes.
            
            $table->string('google_id')->nullable()->unique(); // Ya lo tenías, perfecto
            $table->string('avatar', 300)->nullable();      // Ya lo tenías, perfecto
            
            $table->rememberToken();
            
            $table->boolean('mayor_edad_confirmado')->default(false); // Para el checkbox "Soy mayor de 14 años". Más semántico.
            $table->boolean('acepta_terminos')->default(false); // Para el checkbox de publicidad

            // Tus campos personalizados
            // Asumiendo que 'descuento' tiene 'id_descuento' como PK
            $table->foreignId('id_descuento')->nullable()->constrained('descuento', 'id_descuento')->onDelete('set null');
            // Asumiendo que 'tipo_usuario' tiene 'id_tipo_usuario' como PK
            // Renombré la columna local a tipo_usuario_id por convención
            $table->foreignId('tipo_usuario')->nullable()->constrained('tipo_usuario', 'id_tipo_usuario')->onDelete('set null');

            // Timestamps
            $table->timestamps();
        });

        // Estas tablas son estándar de Laravel y usualmente están en sus propias migraciones
        // Si las estás creando aquí intencionadamente, está bien.
        // De lo contrario, podrían estar en 0001_01_01_000001_create_password_reset_tokens_table.php y
        // 0001_01_01_000002_create_sessions_table.php
        /* if (!Schema::hasTable('password_reset_tokens')) {
            Schema::create('password_reset_tokens', function (Blueprint $table) {
                $table->string('email')->primary();
                $table->string('token');
                $table->timestamp('created_at')->nullable();
            });
        }

        if (!Schema::hasTable('sessions')) {
            Schema::create('sessions', function (Blueprint $table) {
                $table->string('id')->primary();
                $table->foreignId('user_id')->nullable()->index();
                $table->string('ip_address', 45)->nullable();
                $table->text('user_agent')->nullable();
                $table->longText('payload');
                $table->integer('last_activity')->index();
            });
        } */
    }

    /**
     * Reverse the migrations.
     * El método down para una migración de creación de tabla debería eliminar la tabla.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens'); // Si las creaste aquí
        Schema::dropIfExists('sessions'); // Si las creaste aquí
    }
};