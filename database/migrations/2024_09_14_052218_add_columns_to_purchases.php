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
        Schema::table('purchases', function (Blueprint $table) {
            $table->dropColumn("product_id");
            $table->dropColumn("quantity");
            $table->dropColumn("price");
            $table->unsignedBigInteger('user_id')->nullable();
            $table->float('total_discount', 20, 4);
            $table->string('state');

            $table->foreign('user_id')
                ->references('id')
                ->on('users');
            $table->foreign('provider_id')
                ->references('id')
                ->on('providers');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('purchases', function (Blueprint $table) {
            //
        });
    }
};
