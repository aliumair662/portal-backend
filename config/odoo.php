<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Odoo Configuration
    |--------------------------------------------------------------------------
    */

    'username' => env('ODOO_USERNAME', 'portaal@vanwijkuitvaartkisten.nl'),

    'password' => env('ODOO_PASSWORD'),

    'db' => env('ODOO_DB', 'nathanbaart-pvw-master-833347'),

    //https://vanwijkuitvaartkisten-staging-4811001.dev.odoo.com
    //https://vanwijkuitvaartkisten-15-0-staging-6381514.dev.odoo.com/web#cids=1&action=menu
    //6431176
    //https://vanwijkuitvaartkisten-for-portal-no-maintenance-6431176.dev.odoo.com/web/login#cids=1&action=menu
    'host' => env('ODOO_HOST', 'https://vanwijkuitvaartkisten.odoo.com'),

];