<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'amount',
        'status',
        'description',
        'reference_id',
        'withdrawal_id',
        'deposit_id',
    ];


    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function withdrawal()
    {
        return $this->belongsTo(Withdrawal::class);
    }

    public function deposit()
    {
        return $this->belongsTo(Deposit::class);
    }
}
