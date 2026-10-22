<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSiembraRecursoDatosTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('siembra_recurso_datos', function (Blueprint $table) {
            $table->increments('id');

            // Usamos INT sin FK para evitar el error 150
            $table->unsignedInteger('siembra_id');
            $table->unsignedInteger('recurso_id');
            $table->unsignedInteger('tipo_actividad_id')->nullable();

            $table->decimal('minutos', 10, 2)->nullable();
            $table->decimal('minutos_hombre', 10, 2)->nullable();
            $table->text('detalles')->nullable();

            $table->timestamps();

            // Índice único para evitar duplicados por siembra + recurso + actividad
            $table->unique(
                ['siembra_id', 'recurso_id', 'tipo_actividad_id'],
                'sr_datos_unique'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('siembra_recurso_datos');
    }
}
