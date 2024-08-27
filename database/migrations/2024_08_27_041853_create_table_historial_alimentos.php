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
        Schema::create('historial_costos_alimentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('alimento_id')->nullable();
            $table->float("costo")->default(0);
            $table->date("fecha_registro")->nullable();
            $table->foreign('alimento_id')
                ->references('id')
                ->on('alimentos');
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
        Schema::dropIfExists('historial_costos_alimentos');
    }
};
