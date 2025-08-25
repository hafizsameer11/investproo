<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KycDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'document_type',
        'file_path',
        'original_filename',
        'status',
        'admin_notes',
        'reviewed_by',
        'reviewed_at'
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function reviewedBy()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * Get pending KYC documents
     */
    public static function getPendingDocuments()
    {
        return self::with(['user', 'reviewedBy'])
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get KYC documents by user
     */
    public static function getUserDocuments($userId)
    {
        return self::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get document type display name
     */
    public function getDocumentTypeDisplayName()
    {
        $types = [
            'passport' => 'Passport',
            'national_id' => 'National ID',
            'drivers_license' => 'Driver\'s License',
            'utility_bill' => 'Utility Bill',
            'bank_statement' => 'Bank Statement'
        ];

        return $types[$this->document_type] ?? $this->document_type;
    }

    /**
     * Get status badge color
     */
    public function getStatusBadgeColor()
    {
        $colors = [
            'pending' => '#F59E0B',
            'approved' => '#10B981',
            'rejected' => '#EF4444'
        ];

        return $colors[$this->status] ?? '#6B7280';
    }

    /**
     * Get status display name
     */
    public function getStatusDisplayName()
    {
        return ucfirst($this->status);
    }

    /**
     * Check if document is approved
     */
    public function isApproved()
    {
        return $this->status === 'approved';
    }

    /**
     * Check if document is pending
     */
    public function isPending()
    {
        return $this->status === 'pending';
    }

    /**
     * Check if document is rejected
     */
    public function isRejected()
    {
        return $this->status === 'rejected';
    }
}
