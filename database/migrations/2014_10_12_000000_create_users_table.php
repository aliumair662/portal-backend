<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('company_name');
            $table->string('function')->nullable();
            $table->string('phone');

            $table->boolean('active')->default(false);

            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');

            $table->integer('odoo_user_id')->unsigned()->nullable();
            //$table->integer('odoo_location_id')->unsigned()->nullable();
            $table->integer('odoo_organisation_type_id')->unsigned()->nullable();

            //$table->foreignId('user_parent_ud');
            //$table->foreignId('user_type_id');

            $table->integer('parent_user_id')->unsigned()->nullable();

            $table->rememberToken();

            $table->timestamp('signup_confirmed_at')->nullable();
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
        Schema::dropIfExists('users');
    }
}
