<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MonifyTransaction extends Model
{
    const verify = "VERIFY";
    const unverify = "UNVERIFY";
    use HasFactory;

    protected $table = "monify_transactions";
    protected $guarded = [];
}
