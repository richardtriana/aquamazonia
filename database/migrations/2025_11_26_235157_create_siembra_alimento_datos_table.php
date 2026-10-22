<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSiembraAlimentoDatosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('siembra_alimento_datos', function (Blueprint $table) {
            // Puedes usar increments o bigIncrements, es independiente
            $table->bigIncrements('id');

            // IMPORTANTE: tipos que coincidan con las tablas padre

            // siembras.id = int(11)  -> aquí usamos integer (firmado)
            $table->integer('siembra_id');

            // alimentos.id = bigint(20) unsigned -> aquí usamos unsignedBigInteger
            $table->unsignedBigInteger('alimento_id');

            // Tus campos de configuración por defecto
            $table->decimal('minutos_hombre', 10, 2)->nullable();
            $table->decimal('conv_alimenticia', 10, 3)->nullable();
            $table->text('detalles')->nullable();

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
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('siembra_alimento_datos', function (Blueprint $table) {
            $table->dropForeign(['siembra_id']);
            $table->dropForeign(['alimento_id']);
        });

        Schema::dropIfExists('siembra_alimento_datos');
    }
}
