<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;


class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable,HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'user_name',
        'email',
        'phone',
        'role',
        'referral_code',
        'user_code',    
        'status',
        'email_verified_at',
        'password',
        'first_investment_date',
        'last_withdrawal_date',
        'loyalty_days',
        'loyalty_bonus_earned',
    ];

    public function transactions()
{
    return $this->hasMany(Transaction::class);
}
// app/Models/User.php

public function deposits()
{
    return $this->hasMany(Deposit::class);
}
public function withdrawals()
{
    return $this->hasMany(Withdrawal::class);
}
public function investments()
{
    return $this->hasMany(Investment::class);
}
public function referral()
{
    return $this->hasOne(Referrals::class);
}
public function wallet()
 {
     return $this->hasOne(Wallet::class); 
}

public function referrals()
{
    return $this->hasMany(User::class, 'referral_code', 'user_code');
}

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'first_investment_date' => 'datetime',
            'last_withdrawal_date' => 'datetime',
            'loyalty_days' => 'integer',
            'loyalty_bonus_earned' => 'decimal:2',
        ];
    }
    // app/Models/User.php

public function directReferrals()
{
    // My level-1 referrals are users whose referral_code equals my user_code
    return $this->hasMany(User::class, 'referral_code', 'user_code');
}

public function sponsor()
{
    // The user who referred me (their user_code == my referral_code)
    return $this->belongsTo(User::class, 'referral_code', 'user_code');
}

// Loyalty methods
public function calculateLoyaltyDays()
{
    if (!$this->first_investment_date) {
        return 0;
    }

    $startDate = $this->first_investment_date;
    $endDate = $this->last_withdrawal_date ?: now();
    
    return $startDate->diffInDays($endDate);
}
public function claimedAmounts()
{
    return $this->hasMany(ClaimedAmount::class);
}

public function getNextLoyaltyTier()
{
    $currentDays = $this->calculateLoyaltyDays();
    
    return \App\Models\Loyalty::active()
        ->ordered()
        ->where('days_required', '>', $currentDays)
        ->first();
}

public function getCurrentLoyaltyTier()
{
    $currentDays = $this->calculateLoyaltyDays();
    
    return \App\Models\Loyalty::active()
        ->ordered()
        ->where('days_required', '<=', $currentDays)
        ->orderBy('days_required', 'desc')
        ->first();
}

public function getLoyaltyProgress()
{
    $currentDays = $this->calculateLoyaltyDays();
    $nextTier = $this->getNextLoyaltyTier();
    
    if (!$nextTier) {
        return [
            'current_days' => $currentDays,
            'next_tier' => null,
            'days_remaining' => 0,
            'progress_percentage' => 100
        ];
    }
    
    $previousTier = \App\Models\Loyalty::active()
        ->ordered()
        ->where('days_required', '<', $nextTier->days_required)
        ->orderBy('days_required', 'desc')
        ->first();
    
    $previousDays = $previousTier ? $previousTier->days_required : 0;
    $daysInCurrentTier = $nextTier->days_required - $previousDays;
    $daysCompletedInTier = $currentDays - $previousDays;
    $progressPercentage = min(100, ($daysCompletedInTier / $daysInCurrentTier) * 100);
    
    return [
        'current_days' => $currentDays,
        'next_tier' => $nextTier,
        'days_remaining' => $nextTier->days_required - $currentDays,
        'progress_percentage' => round($progressPercentage, 2)
    ];
}

}
