<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Investment extends Model
{
  protected $fillable = [
    'user_id',
    'investment_plan_id',
    'start_date',
    'end_date',
    'status',
];

}
