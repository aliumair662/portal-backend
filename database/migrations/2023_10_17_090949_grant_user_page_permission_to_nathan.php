<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;

class GrantUserPagePermissionToNathan extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $user = User::where('email', 'nathan@vanwijkuitvaartkisten.nl')->first();

        if($user){
            Permission::updateOrCreate(['name' => 'user page']);
            $user->givePermissionTo('user page');
            $user->save();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('nathan', function (Blueprint $table) {
            //
        });
    }
}
