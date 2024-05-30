<?php

namespace App\Http\Controllers\Odoo;

use App\Http\Controllers\Controller;
use App\Models\Odoo\OrganisationType;
use App\Enums\OrganisationType as eOrganisationType;
use Illuminate\Http\Request;

class OrganisationTypeController extends Controller
{
    public function types(){

        return (new OrganisationType())->get()->filter(function($type) {
            return in_array($type['id'], [eOrganisationType::DEPOT, eOrganisationType::FUNERAL_DIRECTOR]);
        })->values();

    }
}
