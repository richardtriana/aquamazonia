<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBackupsTable extends Migration
{
    public function up()
    {
        Schema::create('backups', function (Blueprint $table) {
            $table->id();
            $table->string('filename');
            $table->string('filepath');
            $table->unsignedBigInteger('size')->default(0);
            $table->enum('tipo', ['manual', 'automatico'])->default('manual');
            $table->string('dia_semana', 20);       // lunes … domingo
            $table->string('creado_por')->nullable(); // nombre del usuario o 'sistema'
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('backups');
    }
}
