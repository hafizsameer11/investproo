<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClaimedAmount extends Model
{
    protected $fillable = [
        'user_id',
        'investment_id',
        'amount',
        'reason',
    ];

 
    public function user()        { return $this->belongsTo(User::class); }
    public function investment()  { return $this->belongsTo(Investment::class); }

}
