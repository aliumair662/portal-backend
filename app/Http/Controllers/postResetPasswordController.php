<?php

namespace App\Http\Controllers;

use App\Services\UserService;
use Illuminate\Http\Request;

class postResetPasswordController extends Controller
{
    public function index(){
        $credentials = request()->validate([
            'email'=>'required|email'
        ]);

        return (new UserService())->resetPassword($credentials);
    }
}
