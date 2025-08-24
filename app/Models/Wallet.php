<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Wallet extends Model
{
    protected $fillable = [
    'user_id',
    'withdrawal_amount',
    'deposit_amount',
    'profit_amount',
    'bonus_amount',
    'referral_amount',
    'status',
];
}
