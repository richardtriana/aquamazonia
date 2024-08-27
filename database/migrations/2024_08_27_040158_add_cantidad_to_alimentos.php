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
        Schema::table('alimentos', function (Blueprint $table) {
            $table->unsignedBigInteger("id")->autoIncrement()->change();
            $table->float('cantidad', 20, 2)->default(0);
            $table->float('cantidad_minima', 20, 2)->default(0);
            $table->float('cantidad_maxima', 20, 2)->default(0); 
            $table->boolean('activo', 20, 2)->default(true); 
            $table->boolean('inventario', 20, 2)->default(true); 
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('alimentos', function (Blueprint $table) {
            //
        });
    }
};
