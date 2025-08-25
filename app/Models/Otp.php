<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Otp extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'email',
        'otp_code',
        'type',
        'expires_at',
        'used'
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'used' => 'boolean'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Generate a random 6-digit OTP
     */
    public static function generateOtp(): string
    {
        return str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    /**
     * Check if OTP is expired
     */
    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    /**
     * Check if OTP is valid (not used and not expired)
     */
    public function isValid(): bool
    {
        return !$this->used && !$this->isExpired();
    }

    /**
     * Mark OTP as used
     */
    public function markAsUsed(): void
    {
        $this->update(['used' => true]);
    }

    /**
     * Create a new OTP for the given email and type
     */
    public static function createOtp(string $email, string $type, ?int $userId = null): self
    {
        // Invalidate any existing unused OTPs for this email and type
        self::where('email', $email)
            ->where('type', $type)
            ->where('used', false)
            ->update(['used' => true]);

        return self::create([
            'user_id' => $userId,
            'email' => $email,
            'otp_code' => self::generateOtp(),
            'type' => $type,
            'expires_at' => Carbon::now()->addMinutes(10), // 10 minutes expiry
        ]);
    }

    /**
     * Verify OTP for the given email and type
     */
    public static function verifyOtp(string $email, string $otpCode, string $type): ?self
    {
        $otp = self::where('email', $email)
            ->where('otp_code', $otpCode)
            ->where('type', $type)
            ->where('used', false)
            ->first();

        if ($otp && $otp->isValid()) {
            $otp->markAsUsed();
            return $otp;
        }

        return null;
    }
}
