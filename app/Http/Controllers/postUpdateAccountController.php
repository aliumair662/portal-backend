<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\CompanyService;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;

class postUpdateAccountController extends Controller
{
    public function index()
    {
        Permission::updateOrCreate(['name' => 'view setting']);
        Permission::updateOrCreate(['name' => 'app access']);
        request()->validate(User::$updateRules);

        return (new CompanyService())->updateAccount();

    }
}