<?php

use App\Models\Odoo\Partner;
use App\Models\Odoo\PricelistItem;
use App\Models\Odoo\Product;
use App\Services\PricelistService;
use App\Services\ProductService;
use App\Services\ProductVariantService;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Redis;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
Route::get('cache-depot-cron', function () {
    Artisan::call('schedule:run');
    return "Task run successfully!";
});
Route::get('refresh-locations', function () {
    Redis::del('stock.location');
    return "Locations refreshed successfully!";
});
Route::get('php-info', function () {
    phpinfo();
});
Route::get('pro', function () {
    dd((new \App\Http\Controllers\VariantController())->getVariant()->take(10));
});

Route::get('clear', function () {
    dd(\Illuminate\Support\Facades\Redis::connection()->client()->flushAll());
});

Route::get('x', function () {

    $partners = (new Partner())->get();
    $partner = $partners->where('id', 107468);

    dd($partner);

});

Route::get('delivery', function () {


    $partners = (new Partner())->get();
    $partner = $partners->where('id', request()->input('id'));

    return (new \App\Http\Controllers\Odoo\CompanyController())->clients();

    $products = (new \App\Models\Odoo\Base())->connect();
    $partner = (new \App\Models\Odoo\Partner());
    $product = $products->where('id', 106967)->fields($partner->fields)->get('res.partner');

    dd($product);


});

Route::get('calcprice', function () {

    $product_id = 8006;
    $products = (new Product())->get();
    $product = $products->where('id', $product_id)->first();

    $variants = $product['product_variant_ids'];

    $attributes_per_variant_id = collect((new \App\Services\ProductService())->attributesByVariants($variants));
    $variants = collect($attributes_per_variant_id)->pluck('variant');

    dd($variants->groupBy('product_tmpl_id.0')->keys());

    $product_ids = $variants->where('product_tmpl_id.0', 8006)->pluck('id');
    dd($product_ids);


    $prices = (new PricelistService())->getItemsByPricelist(config('app.pricelist_id'))->whereIn('product_id.0', '7756')->keyBy('product_id.0');
    $alternative_prices = (new PricelistService())->getAlternativePricingByPricelist(7);

    dd($prices, $alternative_prices, (new \App\Services\AlternativePricelistService())->apply($prices, $alternative_prices));

});

Route::get('pricelist', function () {

    $pricelist = new \App\Services\PricelistService();

    //return $pricelist->getItemsByPricelist( 8 );
    //return $pricelist->getAll();

    //return (new \App\Services\PricelistService())->getAll();
    //return (new \App\Services\PricelistService())->getItemsByPricelist();

    $con = new \App\Models\Odoo\Base();
    //return $con->connect()->fieldsOf('res.users');

    //return $con->connect()->fields(['name', 'avatar_256', 'image_256', 'work_phone'])->get('res.users');
    return $con->connect()->fieldsOf('product.pricelist.item');
    //return $con->connect()->fieldsOf('res.users');

    //return  (new PricelistItem())->fromPricelistId(15);

    $pricelistItem = new PricelistItem();
    return $con->connect()->where('pricelist_id', 15)->where('active', true)->fields($pricelistItem->fields)->get($pricelistItem->resource);


    // Stoppelburg
    return (new PricelistItem())->fromPricelistId(15);

    //return $pricelist->getItemsByPricelist(15);

});

Route::get('/', function () {
    return ['Laravel' => app()->version()];
});

require __DIR__ . '/auth.php';

Route::get('/noti', function () {

    return (new \App\Notifications\UserRegistered())->toMail(\App\Models\User::find(1));

});