<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToDeregistersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('deregisters', function (Blueprint $table) {
            $table->string('order_id')->default('');
            $table->string('order_line_id')->default('');
            $table->string('picking_line_id')->default('');
            $table->enum('status',['open','pending','done'])->default('open')->comment('this status is for all data is pushed to ODOO or not');
            // order_id, order_line_id, picking_line_id, status
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('deregisters', function (Blueprint $table) {
            $table->dropColumn(['order_id', 'order_line_id', 'picking_line_id', 'status']);
        });
    }
}
