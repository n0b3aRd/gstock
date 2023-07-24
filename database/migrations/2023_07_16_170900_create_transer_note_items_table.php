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
        Schema::create('transfer_note_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('transfer_note_id');
            $table->unsignedBigInteger('product_id');
            $table->integer('qty')->default(0);
            $table->double('price', 15, 2)->default(0);
            $table->timestamps();

            $table->foreign('transfer_note_id')->references('id')->on('transfer_notes');
            $table->foreign('product_id')->references('id')->on('inventories');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('transfer_note_items');
    }
};
