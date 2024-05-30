<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Enums\RequestType;
use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Odoo\Partner;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Cache, Log};
use App\Enums\OrganisationType;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\getStockByLocationIdController as GetStockByLocation;
use App\Http\Controllers\Odoo\CompanyController;

class CompanyCache extends Command
{
    public $cacheDuration = 21600;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'odoo:company-information';

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
        $depot_users = DB::table('users')->where('odoo_organisation_type_id', OrganisationType::DEPOT)->get();
        foreach ($depot_users as $key => $value) {
            // Log::info(json_encode($value));
            $this->company($value->id);
        }
    }

    public function company($id)
    {
        $companyController = new CompanyController();
        $companyController->renderCompanyDetail($id);

    }
}
