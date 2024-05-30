<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\CompanyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;

class postCreateAccountController extends Controller
{
    public function index()
    {
        $company = User::findOrFail(request()->input('company_id'));

        /** Merge needed fields to pass validation */
        $data['company_name'] = $company->company_name;
        $password = Hash::make(Str::random(8));
        $data['password'] = $password;
        $data['password_confirmation'] = $password;

        Permission::updateOrCreate(['name' => 'view setting']);

        request()->merge($data);
        // return request()->all();
        request()->validate(User::$createRules);
        return (new CompanyService())->createAccount();

    }
}