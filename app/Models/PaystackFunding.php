<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaystackFunding extends Model
{
    use HasFactory;
    protected $table = "paystack_fundings";
    protected $guarded = [];

}
