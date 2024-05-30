<?php

namespace App\Services;

use App\Enums\Roles;
use App\Models\Odoo\Partner;
use App\Models\User;
use App\Notifications\UserRegistered;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Odoo\CompanyController;
use App\Services\UserService;
class CompanyService
{

    function createAccount()
    {
        // Log::info('Creating account');
        $user = new User();
        $user->parent_user_id = request()->input('company_id');
        $user->first_name = request()->input('first_name');
        $user->last_name = request()->input('last_name');
        $user->function = request()->input('function');
        $user->active = request()->input('active');
        $user->email = request()->input('email');
        $user->date_of_birth = request()->input('date_of_birth');
        $user->company_name = request()->input('company_name');
        $user->phone = request()->input('phone');
        $user->password = request()->input('password');

        // set permissions
        if (request()->input('all_permissions')) {
            $user->syncPermissions(request()->input('all_permissions'));
        }

        // add role
        // Log::info('assingin roles');
        $user->assignRole(Roles::PORTAL_ACCOUNT);

        // Log::info('saving account');
        $user->save();

        // Send "UserCreated" with passwrd reset included
        /*(new UserService())->resetPassword([
            'email' => $user->email,
        ]);*/
        // Log::info('triggering user created');
        // (new UserService())->userCreated($user);
        // Log::info('triggered user created');

        // Log::info('returning created user');
        (new CompanyController)->renderCompanyDetail(request()->input('company_id'));
        if(!empty(request()->input('email'))){
            (new UserService())->resetPassword(array('email'=>request()->input('email')));
        }
        return $user;
    }

    public function updateAccount()
    {
        /** @var User $user */
        $user = User::findOrfail(request()->input('id'));
        request()->validate(['email' => 'unique:users,email,' . $user->id]);

        $user->first_name = request()->input('first_name');
        $user->last_name = request()->input('last_name');
        $user->function = request()->input('function');
        $user->active = request()->input('active');
        $user->email = request()->input('email');
        $user->date_of_birth = request()->input('date_of_birth');

        // Update permission
        $user->syncPermissions(request()->input('all_permissions'));
        // if (request()->input('all_permissions')) {
        // }

        $user->save();
        $user_id = $user->parent_user_id;
        if(!$user_id) $user_id = $user->id;
        (new CompanyController)->renderCompanyDetail($user_id);
        return $user;
    }


    function addClient()
    {

    }

    public function find($id)
    {
        return (new Partner)->find($id);
    }

}
