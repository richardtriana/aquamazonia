<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MakeIdAlimentoNullableInFeedingPrelogs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Primero verificamos si existe la tabla
        if (Schema::hasTable('feeding_prelogs')) {
            // Verificamos si la columna existe
            if (Schema::hasColumn('feeding_prelogs', 'id_alimento')) {
                // Cambiamos la columna para permitir NULL
                Schema::table('feeding_prelogs', function (Blueprint $table) {
                    $table->integer('id_alimento')->nullable()->change();
                });
                
                // También podemos verificar otras columnas que podrían necesitar ser NULL
                $nullableColumns = ['cant_manana', 'cant_tarde', 'conv_alimenticia'];
                
                foreach ($nullableColumns as $column) {
                    if (Schema::hasColumn('feeding_prelogs', $column)) {
                        Schema::table('feeding_prelogs', function (Blueprint $table) use ($column) {
                            $table->decimal($column, 10, 2)->nullable()->change();
                        });
                    }
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('feeding_prelogs')) {
            if (Schema::hasColumn('feeding_prelogs', 'id_alimento')) {
                Schema::table('feeding_prelogs', function (Blueprint $table) {
                    $table->integer('id_alimento')->nullable(false)->change();
                });
            }
            
            // Revertir columnas nullable
            $nullableColumns = ['cant_manana', 'cant_tarde', 'conv_alimenticia'];
            
            foreach ($nullableColumns as $column) {
                if (Schema::hasColumn('feeding_prelogs', $column)) {
                    Schema::table('feeding_prelogs', function (Blueprint $table) use ($column) {
                        $table->decimal($column, 10, 2)->nullable(false)->change();
                    });
                }
            }
        }
    }
}