<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvestmentPlan extends Model
{
      protected $fillable = [
    'plan_name',
    'min_amount',
    'max_amount',
    'profit_percentage',
    'duration',
    'status',
    'description'
];
public function investments()
{
    return $this->hasMany(Deposit::class); // or Investment, depending on your model
}

}
