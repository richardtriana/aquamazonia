<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRegisteredUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('registered_users', function (Blueprint $table) {
            $table->bigIncrements('id');

            // Datos básicos
            $table->string('name');
            $table->string('phone', 20)->unique(); // teléfono del usuario (único)
            
            // PIN de acceso (4 dígitos; puedes luego decidir si lo guardas plano o en hash)
            $table->string('pin', 10);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('registered_users');
    }
}
