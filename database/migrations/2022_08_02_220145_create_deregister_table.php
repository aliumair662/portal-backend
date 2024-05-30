<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDeregisterTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('deregisters', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->unsigned();
            $table->string('undertaker')->nullable();
            $table->integer('undertaker_id')->nullable(); // Undertaker / Client
            $table->integer('quantity')->nullable();
            $table->integer('product_id')->nullable();
            $table->text('product_data')->nullable();
            $table->string('deceased')->nullable();
            $table->text('reason')->nullable();
            $table->string('lot_id')->nullable();
            $table->string('file_number')->nullable();
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
        Schema::dropIfExists('deregisters');
    }
}
