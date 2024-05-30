<?php

namespace App\Models\Odoo;

class TarifsAdv extends Base
{
    public $resource = 'x_tarievenlijsten_adv._2022';
    public $fields = [
        'id',
        'display_name',
    ];

    public function __construct($data=null)
    {
        $this->data = $data;
    }
}
