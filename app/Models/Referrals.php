<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Referrals extends Model
{
    protected $fillable = [
    'user_id',
    'referral_bonus_amount',
    'total_referrals',
    'referral_code'
];
public function user()
{
    return $this->belongsTo(User::class);
}

}
