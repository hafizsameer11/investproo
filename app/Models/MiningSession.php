<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MiningSession extends Model
{
    protected $fillable = [
        'user_id',
        'started_at',
        'stopped_at',
        'status',
        'progress',
        'rewards_claimed',
        'investment_id'
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'stopped_at' => 'datetime',
        'rewards_claimed' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
