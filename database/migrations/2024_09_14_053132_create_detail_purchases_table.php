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
        Schema::create('detail_purchases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_id');
            $table->foreignId('recurso_id')->nullable();
            $table->foreignId('alimento_id')->nullable();
            $table->float('price', 20, 4);
            $table->float('total_cost', 20, 4);
            $table->float('discount_value', 20, 4);
            $table->float('discount_percentage', 20, 4);
            $table->float('quantity', 20, 4);
            $table->string('item', 20, 4);

            $table->foreign('recurso_id')
                ->references('id')
                ->on('recursos');

            $table->foreign('alimento_id')
                ->references('id')
                ->on('alimentos');
            $table->foreign('purchase_id')
                ->references('id')
                ->on('purchases');
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
        Schema::dropIfExists('detail_purchases');
    }
};
