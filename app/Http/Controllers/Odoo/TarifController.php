<?php

namespace App\Http\Controllers\Odoo;

use App\Http\Controllers\Controller;
use App\Models\Odoo\Tarifs;
use Illuminate\Http\Request;

class TarifController extends Controller
{
    public function tarifs(){
        return (new Tarifs())->get();
    }
}
