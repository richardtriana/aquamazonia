<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('parametros_agua_avanzados_prelogs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('registered_user_id');
            $table->unsignedBigInteger('id_siembra');
            
            $table->date('fecha_parametro');
            $table->decimal('ph', 8, 3)->nullable();
            $table->decimal('amonio', 8, 3)->nullable();
            $table->decimal('nitrito', 8, 3)->nullable();
            $table->decimal('nitrato', 8, 3)->nullable();
            $table->text('otros')->nullable();
            $table->decimal('temperatura', 8, 3)->nullable();
            $table->decimal('12_am', 8, 3)->nullable();
            $table->decimal('7_am', 8, 3)->nullable();
            $table->decimal('8_pm', 8, 3)->nullable();
            $table->string('tipo', 50)->default('PARAMETROS_AVANZADOS');
            $table->text('descripcion')->nullable();
            $table->date('fecha_registro');
            $table->time('hora_registro')->nullable();
            
            $table->timestamps();
            
            // ÍNDICES CON NOMBRES MÁS CORTOS
            $table->index(['id_siembra', 'fecha_parametro'], 'idx_siembra_fecha');
            $table->index('registered_user_id', 'idx_user_id');
            $table->index('tipo', 'idx_tipo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('parametros_agua_avanzados_prelogs');
    }
};