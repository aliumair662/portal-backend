<?php

namespace App\Console\Commands;

use App\Models\Odoo\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class OdooStoreProductImages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'odoo:product:images';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Store product images on server.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $products = (new Product())->connect()->fields(['id','image_256'])->get('product.template');

        foreach( $products as $product ){
            if( $product['image_256'] != null ) {
                Storage::disk('public')->put('/products/' . $product['id'] . '.png', base64_decode($product['image_256']));
                $this->info($product['id'] . ' saved!');
            }
        }
    }
}
