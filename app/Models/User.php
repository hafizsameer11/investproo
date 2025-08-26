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
public function referral()
{
    return $this->hasOne(Referrals::class);
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

}
