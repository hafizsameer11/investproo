<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class News extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'content',
        'type',
        'status',
        'created_by'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get active news items
     */
    public static function getActiveNews($limit = 10)
    {
        return self::where('status', 'active')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get news by type
     */
    public static function getNewsByType($type, $limit = 10)
    {
        return self::where('status', 'active')
            ->where('type', $type)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get type badge text
     */
    public function getTypeBadgeText()
    {
        return strtoupper($this->type);
    }

    /**
     * Get relative time
     */
    public function getRelativeTime()
    {
        $now = now();
        $diff = $now->diffInDays($this->created_at);
        
        if ($diff == 0) {
            return 'Today';
        } elseif ($diff == 1) {
            return 'Yesterday';
        } elseif ($diff < 7) {
            return $diff . ' days ago';
        } else {
            return $this->created_at->format('M j, Y');
        }
    }
}
