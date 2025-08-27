<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Loyalty extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'days_required',
        'bonus_percentage',
        'is_active',
        'description'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'days_required' => 'integer',
        'bonus_percentage' => 'decimal:2'
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('days_required', 'asc');
    }
}

