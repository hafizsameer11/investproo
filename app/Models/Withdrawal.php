<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Withdrawal extends Model
{
    protected $fillable = [
        'user_id',
        'amount',
        'wallet_address',
        'crypto_type',
        'notes',
        'withdrawal_date',
        'status',
        'rejection_reason',
    ];
public function transactions()
{
    return $this->hasMany(Transaction::class);
}
public function user()
{
    return $this->belongsTo(User::class);
}

}
