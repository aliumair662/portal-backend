<?php

namespace App\Http\Controllers;

use App\Models\Odoo\Tax;

class getTaxesController extends Controller
{
    public function index(){
        return (new Tax())->all();
    }
}
