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
        'rewards_earned',
        'rewards_claimed',
        'investment_id'
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'stopped_at' => 'datetime',
        'rewards_earned' => 'decimal:2',
        'rewards_claimed' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function investment()
    {
        return $this->belongsTo(Investment::class);
    }
}
