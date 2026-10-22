<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSiembraAlimentosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('siembra_alimentos', function (Blueprint $table) {
            $table->bigIncrements('id');

            // OJO: estos tipos deben EMPATAR con las tablas padre
            // siembras.id  -> int(11)
            // alimentos.id -> bigint(20) unsigned (muy probable)
            $table->integer('siembra_id');           // empata con siembras.id
            $table->unsignedBigInteger('alimento_id'); // empata con alimentos.id

            $table->boolean('activo')->default(true);
            $table->timestamps();

            // Foreign keys
            $table->foreign('siembra_id')
                ->references('id')
                ->on('siembras')
                ->onDelete('cascade');

            $table->foreign('alimento_id')
                ->references('id')
                ->on('alimentos')
                ->onDelete('cascade');

            // Evitar duplicados siembra–alimento
            $table->unique(['siembra_id', 'alimento_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('siembra_alimentos');
    }
}
