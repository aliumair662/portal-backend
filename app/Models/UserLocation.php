<?php

namespace App\Models;

use App\Models\Request as VanWijkRequest;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserLocation extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $guarded = ['id'];
}
