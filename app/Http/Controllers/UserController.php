<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Enums\Roles;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Notifications\Notifiable;
use App\Notifications\{UserRegistered, AdminRegistered};
use Spatie\Permission\Models\Permission;

class UserController extends Controller
{
    public function user($id)
    {
        $user = User::with('locations', 'addressRequests', 'addressRequests.request')->findOrFail($id);
        $user['accounts_with_user'] = $user->accounts_with_user;

        return $user;
    }

    public function update()
    {

        $user = User::findOrfail(request()->input('data.id'));

        $user->first_name = request()->input('data.first_name');
        $user->last_name = request()->input('data.last_name');
        $user->function = request()->input('data.function');
        $user->save();

        return $user;
    }
    public function store(Request $request)
    {

        request()->validate(User::$updateRules);

        $user = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'active' => $request->active,
            'function' => $request->function,
            'date_of_birth' => $request->date_of_birth,
            'company_name' => ($request->company_name) ? $request->company_name : '',
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
        ]);
        // Mail::to($request->email[$i])->send(new UserRegistered());

        $user->assignRole(Roles::VAN_WIJK);
        $user->assignRole(Roles::SUPER_ADMIN);

        $user->givePermissionTo('view history');
        $user->givePermissionTo('view requests');
        $user->givePermissionTo('view dashboard');

        if (count($request->all_permissions) > 0) {

            foreach ($request->all_permissions as $permission) {
                $user->givePermissionTo($permission);
            }
            
        }

        $user->notify((new AdminRegistered($request->email, $request->password)));
        return $user;
    }
    public function updateUser(User $id, Request $request)
    {
        request()->validate(User::$updateRules);
        $password = '';
        $params = $request->only([
            'first_name', 'last_name', 'function', 'date_of_birth', 'phone', 'active'
        ]);
        if ($request->password) {
            $params['password'] = Hash::make($request->password);
        } else {
            unset($params['password']);
        }
        // $params['company_name'] = '';
    
        if (count($request->all_permissions) > 0 && in_array('user page', $request->all_permissions)) {
            foreach ($request->all_permissions as $permissionName) {
                $permission = Permission::updateOrCreate(['name' => $permissionName]);
                $id->givePermissionTo($permission);
            }

            $id->givePermissionTo('user page');
        } else {
            $permission = Permission::updateOrCreate(['name' => 'user page']);
            $id->revokePermissionTo($permission);
        }

        $id->update($params);
        return $id;
    }
    public function index(Request $request)
    {
        $sortByName = (isset($request->sortByName) && !empty($request->sortByName)) ? $request->sortByName : 'id';
        $sortByType = (isset($request->sortByType) && !empty($request->sortByType)) ? $request->sortByType : 'DESC';
        return User::role(Roles::VAN_WIJK)->orderBy($sortByName, $sortByType)->get();
    }
}
