<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCantidadBultoToAlimentosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('alimentos', function (Blueprint $up) {
            // Usamos decimal para permitir pesos exactos (ej: 25.5 kg) 
            // Se coloca después de 'cantidad' para mantener orden
            $up->decimal('cantidad_bulto', 8, 2)->default(0)->after('cantidad');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('alimentos', function (Blueprint $down) {
            $down->dropColumn('cantidad_bulto');
        });
    }
}