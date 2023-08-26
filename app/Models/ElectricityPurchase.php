<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ElectricityPurchase extends Model
{
    use HasFactory;

    protected $table = "electricity_purchases";
    protected $guarded = [];

    public function getResponseAttribute($value)
    {
        return json_decode($value);
    }
}
