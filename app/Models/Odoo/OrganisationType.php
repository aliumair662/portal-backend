<?php

namespace App\Models\Odoo;

class OrganisationType extends Base
{
    public $resource = 'x_type_organisatie';
    public $fields = [
        'id', 'display_name', 'x_name',
    ];

    public function __construct($data=null)
    {
        $this->data = $data;
    }
}
