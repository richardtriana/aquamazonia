<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFeedingPrelogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('feeding_prelogs', function (Blueprint $table) {
            $table->id();

            // Quién registró (registrador)
            $table->unsignedBigInteger('registered_user_id');

            // Datos de alimentación
            $table->unsignedBigInteger('id_siembra');
            $table->unsignedBigInteger('id_alimento');
            $table->date('fecha_ra');

            $table->decimal('cant_manana', 12, 2)->nullable();
            $table->decimal('cant_tarde', 12, 2)->nullable();
            $table->decimal('minutos_hombre', 10, 2)->nullable();
            $table->decimal('conv_alimenticia', 10, 3)->nullable();
            $table->text('detalles')->nullable();

            // Campos de tipo (compatibles con recursos_necesarios)
            $table->tinyInteger('tipo_actividad')->default(1);
            $table->string('tipo', 50)->default('ALIMENTACION');
            $table->string('descripcion', 255)->nullable();

            // Para futura sincronización
            $table->timestamp('synced_at')->nullable();

            $table->timestamps();

            $table->foreign('registered_user_id')
                ->references('id')
                ->on('registered_users')
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
        Schema::dropIfExists('feeding_prelogs');
    }
}
