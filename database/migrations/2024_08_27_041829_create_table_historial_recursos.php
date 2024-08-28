<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('historial_costos_recursos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_recurso')->nullable();
            $table->float("costo")->default(0);
            $table->date("fecha_registro")->nullable();


            $table->foreign('id_recurso')
                ->references('id')
                ->on('recursos');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('historial_costos_recursos');
    }
};
