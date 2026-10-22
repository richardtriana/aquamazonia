<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRegisteredUserIdToRecursosNecesariosTable extends Migration
{
    public function up()
    {
        Schema::table('recursos_necesarios', function (Blueprint $table) {
            // si usas users.id tipo bigIncrements
            $table->unsignedBigInteger('registered_user_id')->nullable()->after('id');

            // si quieres relación con users:
            $table->foreign('registered_user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('recursos_necesarios', function (Blueprint $table) {
            $table->dropForeign(['registered_user_id']);
            $table->dropColumn('registered_user_id');
        });
    }
}
