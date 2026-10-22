<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMissingHoursToParametrosAguaAvanzadosPrelogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('parametros_agua_avanzados_prelogs', function (Blueprint $table) {
            // Agregar los campos faltantes
            if (!Schema::hasColumn('parametros_agua_avanzados_prelogs', '4_am')) {
                $table->decimal('4_am', 8, 3)->nullable()->after('12_am');
            }
            
            if (!Schema::hasColumn('parametros_agua_avanzados_prelogs', '4_pm')) {
                $table->decimal('4_pm', 8, 3)->nullable()->after('7_am');
            }
            
            // Renombrar 7_am a 4_am si es necesario (opcional)
            // Si quieres mantener ambos, déjalo como está
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('parametros_agua_avanzados_prelogs', function (Blueprint $table) {
            // Revertir los cambios
            if (Schema::hasColumn('parametros_agua_avanzados_prelogs', '4_am')) {
                $table->dropColumn('4_am');
            }
            
            if (Schema::hasColumn('parametros_agua_avanzados_prelogs', '4_pm')) {
                $table->dropColumn('4_pm');
            }
        });
    }
}