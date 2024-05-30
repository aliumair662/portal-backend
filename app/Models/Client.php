<?php

namespace App\Models;

use App\Models\Request as VanWijkRequest;
use App\Services\CompanyService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class Client extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    protected $appends = ['odoo', 'option_label'];

    public function getOdooAttribute()
    {
        if (isset($this->attributes['odoo_id'])) {
            return (new CompanyService())->find($this->attributes['odoo_id'])->first();
        }
        return false;
    }

    public function getOptionLabelAttribute()
    {
        try {
            //code...
            return $this->attributes['name'] . ' - ' . $this->odoo['display_name'] ;
        } catch (\Throwable $th) {
            return '';
        }
    }

    public function request(): MorphOne
    {
        return $this->morphOne(VanWijkRequest::class, 'requestable');
    }

}