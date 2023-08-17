<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Airtime extends Model
{
    use HasFactory;
    protected $table = "airtimes";
    protected $guarded = [];
    protected $hidden = [];
    protected $appends = ['serialref'];


    public function getResponseAttribute($value)
    {
        return json_decode($value);
    }

    public function getSerialrefAttribute()
    {
        return "airtime-".$this->provider."-".$this->request_id;
    }
}
