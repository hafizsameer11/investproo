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
    'total_balance',
    'is_invested',
     'locked_amount'
];
protected static function booted()
    {
        static::saving(function ($wallet) {
            $wallet->total_balance =
                ($wallet->deposit_amount ?? 0)
                + ($wallet->withdrawal_amount ?? 0)
                + ($wallet->profit_amount ?? 0)
                + ($wallet->bonus_amount ?? 0)
                + ($wallet->referral_amount ?? 0);
        });
    }
}
