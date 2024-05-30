<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Odoo\Partner;
use Illuminate\Support\Facades\Redis;

class CompaniesDataCache extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'company:cache';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        $partner = new Partner;
        // print_r($partner->fields);
        // return '';
        $companies = $partner->customConnect()->fields(['id'])->where('active', true)
        ->where('company_type', 'company')
        ->where('parent_id', false)->get('res.partner');
        // print_r($check);
        // return '';
        // \Log::debug($company);
        $company_list = [];
        $ierationLimit = 50;
        $ieration = 0;
        $company_ids = [];
        foreach($companies as $company){
            if($ierationLimit != $ieration){
                $company_ids[] = $company['id'];
            }
            else{
                $ieration = 0;
                $companies_get = self::getData($company_ids);
                foreach($companies_get as $cp){
                    $company_list[] = $cp;
                }
                $company_ids = [];
            }
            $ieration++;

        }
        if(count($company_ids) > 0){
            $companies_get = self::getData($company_ids);
            foreach($companies_get as $cp){
                $company_list[] = $cp;
            }
        }
        // chunk(100,function($users) {
        //     foreach ($users as $user) {
        //         print_r($user);
        //         \Log::info($user);
        //         // echo "\n";
        //     }
        // });
        Redis::del( 'res.partner');
        Redis::set( 'res.partner', json_encode($company_list) ); // set cache
        // Redis::set( $this->resource, $res );
        // $company = (new Partner)->customConnect()->get('res.partner',0,10);
        // $company = (new Partner)->customConnect()->search('res.partner')->chunk(3);
        // print_r($company);
        return 'Run command';
    }
    private static function getData($ids){
        $partner = new Partner;
        return $get_list = $partner->customConnect()->fields($partner->fields)->where('active', true)->where('id',$ids)->get('res.partner');
    }
}
