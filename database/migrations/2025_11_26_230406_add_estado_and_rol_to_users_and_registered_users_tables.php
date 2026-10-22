<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEstadoAndRolToUsersAndRegisteredUsersTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 👉 OJO: ya NO tocamos la tabla users porque ya tiene 'estado'

        // Tabla de usuarios de registro -> estado y rol
        Schema::table('registered_users', function (Blueprint $table) {
            if (!Schema::hasColumn('registered_users', 'estado')) {
                $table->boolean('estado')
                    ->default(1)
                    ->after('pin');
            }

            if (!Schema::hasColumn('registered_users', 'rol')) {
                $table->enum('rol', ['alimentador', 'parametros', 'mixto'])
                    ->default('alimentador')
                    ->after('estado');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('registered_users', function (Blueprint $table) {
            if (Schema::hasColumn('registered_users', 'rol')) {
                $table->dropColumn('rol');
            }
            if (Schema::hasColumn('registered_users', 'estado')) {
                $table->dropColumn('estado');
            }
        });

        // No tocamos users en el down tampoco
    }
}
