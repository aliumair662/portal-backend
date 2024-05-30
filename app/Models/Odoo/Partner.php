<?php

namespace App\Models\Odoo;

class Partner extends Base
{
    public $resource = 'res.partner';
    public $fields = [
        'id',
        'name',
        'parent_id',
        'child_ids',
        'display_name',
        'function',
        'company_type',
        'type',
        'date',
        'website',
        'active',
        'email',
        'city',
        'street',
        'street_name',
        'street_number',
        'zip',
        'phone',
        'country_id',
        'property_product_pricelist',
        //'x_studio_franco_levering_vanaf_1',
        'user_id',
        /*'x_studio_facturatie_voorkeuren',
        'x_studio_status_organisatie',
        'x_studio_brancheorganisatie',
        'x_studio_field_J7TlG', // Tarievenlijsten Ink. 2022
        'x_studio_field_mPJWX', // Tarievenlijsten Adv. 2022*/
        //'x_studio_field_ZxHzg' // Type organisatie
        "x_studio_many2one_field_CmfIR",
        "property_payment_term_id"
    ];
    public static $fillable = [
        'street',
        'street_name',
        'street_number',
        'city',
        'zip'
    ];

    public function __construct($data = null)
    {
        $this->data = $data;
    }
    public function fetchAll()
    {
        return (new Partner)->connect()->fields([
            'id',
            'name',
            'parent_id',
            'child_ids',
            'display_name',
            'function',
            'company_type',
            'type',
            'active'
        ])->get($this->resource);
    }
    public function collect()
    {
        $data = collect($this->data);
        $data = $data->map(function ($item) {
            $item['street_number'] = ($item['street_number'] == false) ? '' : $item['street_number'];
            return $item;
        });
        return $data;
    }

    public function find($id)
    {
        $company = (new Partner)->cache('company_id_' . $id, function () use ($id) {
            return (new Partner)->connect()->where('id', '=', $id)->fields($this->fields)->get($this->resource);
        });
        return $company;
    }
    public function findWithoutCache($id)
    {
        return (new Partner)->connect()->where('id', '=', $id)->fields($this->fields)->get($this->resource);
    }
    public function all()
    {
        return (new Partner)->connect()->fields($this->fields)->where('active', true)
            ->where('company_type', 'company')
            ->where('parent_id', false)->get($this->resource);
    }

    public function organisationType()
    {
        $organisation_types = (new OrganisationType())->get();

        $organisation_mapping = collect($this->data['x_studio_field_ZxHzg'])->map(function ($organisation_type_id) use ($organisation_types) {
            return $organisation_types->where('id', $organisation_type_id)->first();
        });

        return $organisation_mapping;
    }
    public function singleDataWithAllFields($id)
    {
        return $this->connect()
            ->where('id', $id)
            ->fields($this->fields)
            ->get('res.partner')->first() ?? [];
    }
    public function getDeliveryContact($id)
    {
        return $this->connect()
            ->where('parent_id', $id)
            ->where('type', 'delivery')
            // ->fields($this->fields)
            ->fields(['id'])
            ->get('res.partner')->first() ?? [];
    }
    public function customConnect()
    {
        return $this->connect();
    }
}
