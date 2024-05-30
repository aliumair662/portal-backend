<?php

namespace App\Models\Odoo;

class Tarifs extends Base
{
    //public $resource = 'x_tarievenlijsten_2022'; //x_tarievenlijsten_adv._2022
    public $resource = 'product.pricelist';
    public $fields = [
        'id',
        'display_name',
    ];

    public function __construct($data=null)
    {
        $this->data = $data;
    }
}
