<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Bavix\Wallet\Traits\HasWallet;
use Bavix\Wallet\Interfaces\Wallet;

class Customer extends Authenticatable implements Wallet
{
    use HasApiTokens, HasFactory, Notifiable, HasWallet;
    protected $table = "customers";
    protected $guarded = [];

    protected $appends = ['customerbalance', 'banks'];
    protected $hidden = ['monify_response_body'];
    public function getcustomerbalanceAttribute()
    {

        return date('YmdHis');
        // return $this->balance;

    }

    public function getbanksAttribute()
    {

        return json_decode($this->monify_response_body)->accounts;
        // return $this->balance;

    }
}
