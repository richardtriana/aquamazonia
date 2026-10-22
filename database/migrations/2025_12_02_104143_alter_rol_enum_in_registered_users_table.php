<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AlterRolEnumInRegisteredUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Agregamos el nuevo valor 'celador' al ENUM
        DB::statement("
            ALTER TABLE registered_users
            MODIFY rol ENUM('alimentador', 'parametros', 'mixto', 'celador')
                NOT NULL
                DEFAULT 'alimentador'
        ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Volvemos al ENUM original sin 'celador'
        DB::statement("
            ALTER TABLE registered_users
            MODIFY rol ENUM('alimentador', 'parametros', 'mixto')
                NOT NULL
                DEFAULT 'alimentador'
        ");
    }
}
