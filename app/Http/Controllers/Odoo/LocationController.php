<?php

namespace App\Http\Controllers\Odoo;

use App\Http\Controllers\Controller;
use App\Models\Odoo\Location;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    /**
     * Return all Companies from Odoo
     * that are active, is a company
     * and doens't have a parent
     *
     * @return \Illuminate\Support\Collection|void
     */
    public function locations()
    {
        // Getting locations from cache
        $locations = (new Location)->get();

        return array_values($locations->where('active', true)
            ->where('usage', 'internal')->toArray());
    }
}