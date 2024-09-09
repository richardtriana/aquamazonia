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
        Schema::create('kardexes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recurso_id')->nullable();
            $table->foreignId('alimento_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->float('cantidad', 20, 4);
            $table->date('fecha');
            $table->string('descripcion');
            $table->string('tipo')->comment("COMPRA, VENTA, INVENTARIO INICIAL");

            $table->foreign('recurso_id')
                ->references('id')
                ->on('recursos');

            $table->foreign('alimento_id')
                ->references('id')
                ->on('alimentos');

            $table->foreign('user_id')
                ->references('id')
                ->on('users');

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
        Schema::dropIfExists('kardexes');
    }
};
