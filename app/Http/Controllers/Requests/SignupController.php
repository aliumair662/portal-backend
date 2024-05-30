<?php

namespace App\Http\Controllers\Requests;

use App\Enums\OrganisationType;
use App\Enums\RequestType;
use App\Enums\Roles;
use App\Http\Controllers\Controller;
use App\Models\Odoo\Partner;
use App\Models\Request as VanWijkRequest;
use App\Models\User;
use App\Models\UserDeliveryAddress;
use App\Models\UserLocation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class SignupController extends Controller
{
    public function all()
    {
        return VanWijkRequest::where('requestable_type', 'App\Models\User')
            ->where('status', RequestType::OPEN)
            ->orWhere('status', RequestType::ON_HOLD)->get();
    }

    public function show($id)
    {
        $signup = VanWijkRequest::findOrFail($id);

        return $signup;
    }

    public function save(VanWijkRequest $request)
    {
        request()->validate([
            //'odoo_location' => 'required',
            'odoo_relationship' => 'required',
            'odoo_organisation_type' => 'required',
        ]);

        /** Handle signup request **/
        // Update request
        $request->close();

        $request->requestable->odoo_organisation_type_id = request()->input('odoo_organisation_type')['id'];
        $request->requestable->odoo_user_id = request()->input('odoo_relationship')['id'];

        // Save locations for user
        if (request()->input('odoo_location')) {
            foreach (request()->input('odoo_location') as $location) {
                UserLocation::updateOrCreate([
                    'user_id' => $request->requestable_id,
                    'odoo_location_id' => $location['id'],
                ]);
            }
        }

        // Save roles depending on Organisation type
        /** @var User $user */
        $user = $request->requestable;
        if (request()->input('odoo_organisation_type')['id'] == OrganisationType::DEPOT) {
            $user->roles()->detach();
            $user->assignRole(Roles::PORTAL);
        }
        if (request()->input('odoo_organisation_type')['id'] == OrganisationType::FUNERAL_DIRECTOR) {
            $user->roles()->detach();
            $user->assignRole(Roles::PORTAL_FUNERAL_DIRECTOR);
        }

        // Save delivery addresses for user
        $odoo_user = (new Partner)->getById(request()->input('odoo_relationship')['id'])->collect()->first();
        // Get all child contacts connected
        if (isset($odoo_user['children'])) {
            foreach ($odoo_user['children'] as $child) {
                if ($child['type'] == 'delivery')
                    UserDeliveryAddress::create([
                        'user_id' => $request->requestable_id,
                        'odoo_delivery_address_id' => $child['id'],
                    ]);
            }
        }

        // Save tarif list
        if (request()->input('odoo_tarif')) {
            $user->odoo_tarif_id = request()->input('odoo_tarif')['id'];
            $user->save();
        }

        if (request()->input('odoo_invoice_rules')) {
            $user->odoo_invoice_rules = request()->input('odoo_invoice_rules');
            $user->save();
        }

        // Update user (activate)
        $request->requestable->activate();

        return $request->requestable;
    }
    public function updateCompany(User $user)
    {
        request()->validate([
            //'odoo_location' => 'required',
            'odoo_relationship' => 'required',
            'odoo_organisation_type' => 'required',
        ]);
        $user->odoo_organisation_type_id = request()->input('odoo_organisation_type')['id'];
        $user->odoo_user_id = request()->input('odoo_relationship')['id'];

        // Save locations for user
        $user->locations()->delete();
        if (request()->input('odoo_location')) {
            foreach (request()->input('odoo_location') as $location) {
                $userLocations = [
                    'user_id' => $user->id,
                    'odoo_location_id' => $location['id'],
                ];
                $user->locations()->create($userLocations);
            }
        }
        // Save roles depending on Organisation type
        /** @var User $user */
        if (request()->input('odoo_organisation_type')['id'] == OrganisationType::DEPOT) {
            $user->roles()->detach();
            $user->assignRole(Roles::PORTAL);
        }
        if (request()->input('odoo_organisation_type')['id'] == OrganisationType::FUNERAL_DIRECTOR) {
            $user->roles()->detach();
            $user->assignRole(Roles::PORTAL_FUNERAL_DIRECTOR);
        }

        // Save delivery addresses for user
        $odoo_user = (new Partner)->getById(request()->input('odoo_relationship')['id'])->collect()->first();
        // Get all child contacts connected
        if (isset($odoo_user['children'])) {
            foreach ($odoo_user['children'] as $child) {
                if ($child['type'] == 'delivery')
                    UserDeliveryAddress::create([
                        'user_id' => $user->id,
                        'odoo_delivery_address_id' => $child['id'],
                    ]);
            }
        }

        // Save tarif list
        if (request()->input('odoo_tarif')) {
            $user->odoo_tarif_id = request()->input('odoo_tarif')['id'];
            $user->save();
        }

        if (request()->input('odoo_invoice_rules')) {
            $user->odoo_invoice_rules = request()->input('odoo_invoice_rules');
            $user->save();
        }

        // Update user (activate)
        $user->activate();

        return $user;
    }
}
