<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminEdit extends Model
{
    protected $fillable = [
        'admin_id',
        'user_id',
        'field_name',
        'old_value',
        'new_value',
        'edit_type',
        'reason'
    ];

    protected $casts = [
        'old_value' => 'array',
        'new_value' => 'array',
    ];

    // Relationships
    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
