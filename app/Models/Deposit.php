<?php

namespace App\Models;

use App\Models\InvestmentPlan;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class Deposit extends Model
{
    protected $fillable = [
    'user_id',
    'amount',
    'deposit_date',
    'status',
    'deposit_picture',
    'investment_plan_id',
    'chain_id'
];
public function transactions()
{
    return $this->hasMany(Transaction::class);
}
// app/Models/Deposit.php

public function user()
{
    return $this->belongsTo(User::class);
}
public function investmentPlan()
{
    return $this->belongsTo(InvestmentPlan::class);
}

    public function chain()
    {
        return $this->belongsTo(Chain::class);
    }
}
