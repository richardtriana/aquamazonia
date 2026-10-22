<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRegisteredUserIdAndIdSiembraToCalidadAguaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('calidad_agua', function (Blueprint $table) {
            // Agregar columnas nuevas
            $table->unsignedBigInteger('registered_user_id')->nullable()->after('id');
            $table->unsignedBigInteger('id_siembra')->nullable()->after('registered_user_id');
            $table->string('tipo', 50)->nullable()->after('otros');
            $table->text('descripcion')->nullable()->after('tipo');
            $table->date('fecha_registro')->nullable()->after('descripcion');
            $table->time('hora_registro')->nullable()->after('fecha_registro');
            
            // Agregar índices para mejorar performance
            $table->index(['id_siembra', 'fecha_parametro']);
            $table->index('registered_user_id');
            $table->index('id_contenedor');
            $table->index('tipo');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('calidad_agua', function (Blueprint $table) {
            // Eliminar índices
            $table->dropIndex(['id_siembra', 'fecha_parametro']);
            $table->dropIndex(['registered_user_id']);
            $table->dropIndex(['id_contenedor']);
            $table->dropIndex(['tipo']);
            
            // Eliminar columnas
            $table->dropColumn('registered_user_id');
            $table->dropColumn('id_siembra');
            $table->dropColumn('tipo');
            $table->dropColumn('descripcion');
            $table->dropColumn('fecha_registro');
            $table->dropColumn('hora_registro');
        });
    }
}