<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CabletvPurchase extends Model
{
    use HasFactory;
    protected $table = "cabletv_purchases";
    protected $guarded = [];
    public function getResponseAttribute($value)
    {
        return json_decode($value);
    }
}
