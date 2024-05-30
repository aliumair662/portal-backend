<?php

namespace App\Http\Controllers;

use App\Enums\OrganisationType;
use App\Models\{User, Deregister};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\getStockByLocationIdController as GetStockByLocation;
use Illuminate\Support\Facades\Cache;

class DepotController extends Controller
{
    /**
     * Retrieve all depot users to show in overview
     * Edit pagination items to include locations ids as well
     */
    public function all_old()
    {
        // return $cancellationCount = CancelationToken::count();
        $search = (request()->keyword) ? request()->keyword : '';
        $getStockByLocation = new GetStockByLocation();
        // $depot_users = DB::table('users')->where('odoo_organisation_type_id', OrganisationType::DEPOT);
        // if($search){
        //     $depot_users->where('company_name','LIKE',"%{$search}%");
        // }
        // $depot_users->paginate(15);
        $depot_users = User::where('odoo_organisation_type_id', OrganisationType::DEPOT)
            ->when($search, function ($qry) use ($search) {
                $qry->where('company_name', 'LIKE', "%{$search}%");
            })->paginate(15);
        $locations = $depot_users->getCollection()->transform(function ($depot_user) use ($getStockByLocation) {
            $depot_user->locations = User::find($depot_user->id)->locations->pluck('odoo_location_id')->toArray();
            $depot_user->used = 0;
            foreach ($depot_user->locations as $location) {
                $stockData = $getStockByLocation->index($location);
                // dd($stockData);
                $depot_user->used += $stockData['stock_list']->sum('incoming_stock');
                //$depot_user->current_stock_count += $stockData['stock_list']->count();
                $depot_user->current_stock_count += $stockData['stock_list']->sum('quantity');
                // $depot_user->used += array_sum(array_values($stockData['incoming_stock_production']));
            }
            // $depot_user->deregister = Deregister::where("user_id", $depot_user->id)->count();
            $depot_user->deregister = Deregister::where("user_id", $depot_user->id)->whereHas('request', function ($qr) {
                $qr->where('status', 'open');
            })->count();
            //$depot_user->current_stock_count = $depot_user->current_stock_count - $depot_user->deregister;
            $depot_user->total_stock = $depot_user->current_stock_count + $depot_user->used - $depot_user->deregister;
            //$depot_user->total_stock = $depot_user->current_stock_count + $depot_user->used;
            return $depot_user;
        });
        // return $locations;
        return $depot_users;
    }


    public function all()
    {
        $cacheKey = 'depot_users_cache'; // Include the keyword in the cache key
        $depotUsers = Cache::get($cacheKey);
        if ($depotUsers !== null) {
            return $depotUsers;
        }
        $data = $this->prepareCache();
        Cache::put($cacheKey, $data, 3600);
        return $data;
    }

    public function cacheDepotAll()
    {
        // $keyword = request()->input('keyword', ''); // Fetch the 'keyword' parameter
        $keyword = ''; // Fetch the 'keyword' parameter
        $cacheKey = 'depot_users_cache'; // Include the keyword in the cache key
        $sec = 3600; // Define the cache duration in minutes (e.g., 60 minutes)

        $data = $this->prepareCache($keyword, true);
        // Clear Cache
        Cache::forget($cacheKey);

        // Cache Data
        Cache::put($cacheKey, $data, $sec);
    }
     // Prepare Cache Data
    public function prepareCache($keyword = '', $notFromRoute = false)
    {
        $track = [];
        $getStockByLocation = new GetStockByLocation();

        $depotUsers = User::where('odoo_organisation_type_id', OrganisationType::DEPOT)
            ->when($keyword, function ($qry) use ($keyword) {
                $qry->where('company_name', 'LIKE', "%{$keyword}%");
            })->paginate(15);

        $depotUsers->getCollection()->transform(function ($depotUser) use ($getStockByLocation, &$track) {
            $depotUser->locations = User::find($depotUser->id)->locations->pluck('odoo_location_id')->toArray();
            $depotUser->used = 0;

            foreach ($depotUser->locations as $location) {
                $cachedLocation = $this->arrayHasLocation($track, $location);
                if ($cachedLocation) {
                    $depotUser->used += $cachedLocation['deposit_used'];
                    $depotUser->current_stock_count += $cachedLocation['current_stock_count'];
                } else {
                    $stockData = $getStockByLocation->index($location);
                    $depotUser->used += $stockData['stock_list']->sum('incoming_stock');
                    $depotUser->current_stock_count += $stockData['stock_list']->count();
                    array_push($track, [
                        'userId' => $depotUser->id,
                        'location' => $location,
                        'deposit_used' => $stockData['stock_list']->sum('incoming_stock'),
                        'current_stock_count' => $stockData['stock_list']->count(),
                    ]);
                }
            }

            $depotUser->deregister = Deregister::where("user_id", $depotUser->id)->whereHas('request', function ($qr) {
                $qr->where('status', 'open');
            })->count();

            $deregisterData = Deregister::where("user_id", $depotUser->id)->whereHas('request', function ($qr) {
                $qr->where('status', 'open');
            })->get();
            $depotUser->deregisterObj = json_decode($deregisterData->toJson()); // invoke toJson() to load all the relations/appends of the model

            $depotUser->current_stock_count = $depotUser->current_stock_count - $depotUser->deregister;
            $depotUser->total_stock = $depotUser->current_stock_count + $depotUser->used;

            return $depotUser;
        });
        if ($notFromRoute) {
            $depotUsers->withPath('http://backend-vanwijk.test/api/depots/all');
        }
        return json_decode($depotUsers->toJson());
    }
    public function arrayHasLocation($array, $targetLocation)
    {
        foreach ($array as $element) {
            if (isset($element['location']) && $element['location'] == $targetLocation) {
                return $element; // Found the location
            }
        }
        return false; // Location not found
    }
}
