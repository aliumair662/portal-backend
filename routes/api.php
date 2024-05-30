<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

use App\Http\Controllers\JWTController;

Route::get('ok', function () {
    return response()->json(['data' => 'sup']);
});

Route::group(['middleware' => 'api'], function ($router) {

    Route::post('/register', [JWTController::class, 'register']);
    Route::post('/login', [JWTController::class, 'login']);
    Route::post('/login-app', [JWTController::class, 'loginAsAppUser']);
    Route::post('/logout', [JWTController::class, 'logout']);
    Route::post('/refresh', [JWTController::class, 'refresh']);
    Route::post('/profile', [JWTController::class, 'profile']);

    Route::post('/password/reset', [\App\Http\Controllers\postResetPasswordController::class, 'index']);
    Route::post('/password/new', [\App\Http\Controllers\postSetNewPasswordController::class, 'index']);

});

Route::group(['middleware' => ['api', 'jwt.auth', 'role:' . \App\Enums\Roles::SUPER_ADMIN, 'role:' . \App\Enums\Roles::VAN_WIJK,]], function ($router) {

    /** Requests **/
    Route::post('/request/all', [\App\Http\Controllers\Requests\RequestController::class, 'all']);

    Route::post('/request/signups', [\App\Http\Controllers\Requests\SignupController::class, 'all']);
    Route::get('/request/signup/{id}', [\App\Http\Controllers\Requests\SignupController::class, 'show']);
    Route::post('/request/signup/{request}', [\App\Http\Controllers\Requests\SignupController::class, 'save']);
    Route::post('/request/company/update/{user}', [\App\Http\Controllers\Requests\SignupController::class, 'updateCompany']);

    Route::get('/request/orders', [\App\Http\Controllers\getOrderRequestsController::class, 'index']);

    // Requests
    Route::get('/request/depotExtension', [\App\Http\Controllers\getDepotExtensionController::class, 'index']);
    Route::post('/request/confirmOrReject', [\App\Http\Controllers\postDepotExtensionController::class, 'confirmOrReject']);
    Route::post('/request/return/confirmOrReject', [\App\Http\Controllers\postReturnController::class, 'confirmOrReject']);

    Route::get('request/return', [\App\Http\Controllers\getReturnController::class, 'index']);




    Route::get('/request/deregisters', [\App\Http\Controllers\getDeregistersController::class, 'index']);
    Route::post('/request/deregister/decline', [\App\Http\Controllers\postDeregisterDeclineController::class, 'index']);
    Route::post('/request/deregister/accept', [\App\Http\Controllers\postDeregisterAcceptController::class, 'index']);

    Route::get('/request/clients', [\App\Http\Controllers\getClientRequestsController::class, 'index']);
    Route::post('/request/clients/decline', [\App\Http\Controllers\postClientRequestDeclineController::class, 'index']);
    Route::post('/request/clients/accept', [\App\Http\Controllers\postClientRequestAcceptController::class, 'index']);
    //Route::get('/request/history', [\App\Http\Controllers\getDepotExtensionController::class, 'history']);


    Route::get('/request/addresses', [\App\Http\Controllers\Requests\AddressController::class, 'all']);
    Route::post('/request/addresses/refuse', [\App\Http\Controllers\Requests\AddressController::class, 'refuse']);
    Route::post('/request/addresses/accept', [\App\Http\Controllers\Requests\AddressController::class, 'accept']);
    Route::get('/request/address/{id}', [\App\Http\Controllers\Requests\AddressController::class, 'show']);
    //Route::post('/request/address/{request}', [\App\Http\Controllers\Requests\AddressController::class, 'save']);

    /** Odoo related routes **/
    Route::post('/odoo/companies', [\App\Http\Controllers\Odoo\CompanyController::class, 'companies']);
    Route::post('/odoo/company/addresses', [\App\Http\Controllers\Odoo\CompanyController::class, 'addresses']);
    Route::post('/odoo/company/clients', [\App\Http\Controllers\Odoo\CompanyController::class, 'clients']);


    Route::post('/odoo/locations', [\App\Http\Controllers\Odoo\LocationController::class, 'locations']);
    Route::post('/odoo/organisationType', [\App\Http\Controllers\Odoo\OrganisationTypeController::class, 'types']);
    Route::post('/odoo/tarifs', [\App\Http\Controllers\Odoo\TarifController::class, 'tarifs']);

    // Depot
    Route::get('/depots/all', [\App\Http\Controllers\DepotController::class, 'all']);

    // Company
    // Addresses
    Route::post('/addresses/add', [\App\Http\Controllers\AddressController::class, 'add']);
    Route::post('/company/client/create', [\App\Http\Controllers\postCreateClientController::class, 'index']);

    // Login as user
    Route::post('/login/as/{id}', [JWTController::class, 'loginAs']);

    // Clear Odoo Caches
    Route::get('/odoo/clear', function () {
        \Illuminate\Support\Facades\Redis::connection()->client()->flushAll();
    });

});

Route::group(['middleware' => ['api', 'jwt.auth']], function ($router) {

    Route::post('/odoo/company/{id}', [\App\Http\Controllers\Odoo\CompanyController::class, 'company']);
    Route::post('/request/addresses', [\App\Http\Controllers\Requests\AddressController::class, 'create']);
    Route::post('/request/depot/extension', [\App\Http\Controllers\postDepotExtensionController::class, 'index']);
    Route::post('/request/stock/deregister', [\App\Http\Controllers\postDeregisterController::class, 'index']);
    Route::post('request/return', [\App\Http\Controllers\postReturnController::class, 'index']);
    Route::get('/request/deregister/history', [\App\Http\Controllers\getDeregisterHistoryController::class, 'index']);


    // Orders
    Route::get('/orders/{order}', [\App\Http\Controllers\getOrderController::class, 'index']);
    Route::get('/orders', [\App\Http\Controllers\getOrdersController::class, 'index']);

    // Company / Accounts
    Route::post('/company/account/update', [\App\Http\Controllers\postUpdateAccountController::class, 'index']);
    Route::post('/company/account/create', [\App\Http\Controllers\postCreateAccountController::class, 'index']);

    Route::post('/company/client/create/request', [\App\Http\Controllers\postCreateRequestClientController::class, 'index']);
    Route::post('/company/client/remove', [\App\Http\Controllers\postRemoveClientController::class, 'index']);

    // Shop
    Route::post('/shop/order', [\App\Http\Controllers\postCreateOrderController::class, 'index']);

    // Products
    Route::get('/odoo/products', [\App\Http\Controllers\getProductsController::class, 'index']);
    Route::get('/odoo/products/prices', [\App\Http\Controllers\getProductsPricesController::class, 'index']);
    Route::get('/odoo/products/attributes/{id}', [\App\Http\Controllers\getProductAttributesController::class, 'index']);
    Route::post('/odoo/products/attributes', [\App\Http\Controllers\getProductAttributesFromVariantsController::class, 'index']);
    Route::get('/odoo/productvariant/sizes', [\App\Http\Controllers\getProductVariantSizesController::class, 'index']);
    Route::get('/odoo/productvariant/interior', [\App\Http\Controllers\getProductVariantInteriorController::class, 'index']);
    Route::post('/odoo/product/variants', [\App\Http\Controllers\VariantController::class, 'getVariant']);

    Route::get('/odoo/taxes', [\App\Http\Controllers\getTaxesController::class, 'index']);

    // Stock
    Route::get('/odoo/stock/locations', [\App\Http\Controllers\getStockLocationsController::class, 'index']);
    Route::get('/odoo/stock/{id}', [\App\Http\Controllers\getStockByLocationIdController::class, 'index']);

    // Shipping
    Route::post('/odoo/shipping/delivery', [\App\Http\Controllers\getOrderDeliveryOptionsController::class, 'index']);
    Route::post('/odoo/shipping/pickup', [\App\Http\Controllers\getOrderPickupOptionsController::class, 'index']);

    // Clients
    Route::get('/clients/all', [\App\Http\Controllers\getAllClientsController::class, 'index']);
    Route::get('/portal/clients/all', [\App\Http\Controllers\getPortalClientsController::class, 'index']);
    Route::get('/portal/clients/mobile', [\App\Http\Controllers\getPortalClientsController::class, 'portalClientsMobile']);

    /** Scan app **/

    Route::post('/scan/deregister', [\App\Http\Controllers\postScanDeregisterController::class, 'index']);
    Route::post('/scan/deregister/complete', [\App\Http\Controllers\postScanDeregisterCompleteController::class, 'index'])->name('deregisterFromApp');

    Route::post('/scan/return', [\App\Http\Controllers\postScanReturnController::class, 'index']);
    Route::post('/scan/return/complete', [\App\Http\Controllers\postScanReturnCompleteController::class, 'index'])->name('returnCompleteFromApp');

    Route::post('/scan/deregister/cancel', [\App\Http\Controllers\postScanDeregisterCancelController::class, 'index']);
    Route::post('/scan/deregister/cancel/complete', [\App\Http\Controllers\postScanDeregisterCancelCompleteController::class, 'index']);

    /**************/

    /** --------------------- */

    /** Odoo push **/
    Route::post('/odoo/push/company', [\App\Http\Controllers\Odoo\CompanyController::class, 'update']);

    Route::post('/user/store', [\App\Http\Controllers\UserController::class, 'store']);
    Route::get('/get-all-users', [\App\Http\Controllers\UserController::class, 'index']);
    Route::post('/user/{id}/update', [\App\Http\Controllers\UserController::class, 'updateUser']);
    /** Company / Accounts */
    #Route::post('/company/account/update', [\App\Http\Controllers\UserController::class, 'update']);

    /** Users */
    Route::get('/users', function () {
        return Illuminate\Support\Facades\Redis::get('users');
    });
    Route::get('/user/{id}', [\App\Http\Controllers\UserController::class, 'user']);

    /** Addresses */
    Route::get('/addresses/{id}/requests', [\App\Http\Controllers\AddressController::class, 'requests']);
    Route::post('/addresses/delete', [\App\Http\Controllers\AddressController::class, 'delete']);

    /** Tickets **/
    Route::get('/tickets', [\App\Http\Controllers\TicketController::class, 'all']);
    Route::post('/ticket', [\App\Http\Controllers\TicketController::class, 'ticket']);
    Route::get('/ticket/{ticket}', [\App\Http\Controllers\TicketController::class, 'show']);
    Route::get('/tickets/companies', [\App\Http\Controllers\TicketController::class, 'companies']);
    Route::post('/ticket/{ticket}', [\App\Http\Controllers\TicketController::class, 'message']);
    Route::post('/ticket/{ticket}/status', [\App\Http\Controllers\TicketController::class, 'status']);

});
