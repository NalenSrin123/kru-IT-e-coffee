<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use HasProfilePhoto;
    use Notifiable;
    use TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'role_id',
        'name',
        'email',
        'password',
        'provider',
        'provider_id',
        'google_id',
        'avatar',
        'avatar_url',
        'otp_code',
        'otp_expires_at',
        'last_login_at',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'otp_expires_at' => 'datetime',
        'last_login_at' => 'datetime',
        'two_factor_confirmed_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'profile_photo_url',
    ];

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * ឆែកមើលថាតើគាត់ជា Super Admin មែនទេ?
     */
    public function isSuperAdmin(): bool
    {
        return $this->role?->name === 'Super Admin';
    }

    /**
     * ឆែកមើលថាតើគាត់ជា Admin ធម្មតាមែនទេ?
     */
    public function isAdmin(): bool
    {
        return $this->role?->name === 'Admin';
    }

    /**
     * 🌟 Function ថ្មី៖ កំណត់ថាតើ Role ណាខ្លះដែលត្រូវទាមទារ OTP ពេល Login
     */
    public function requiresOtp(): bool
    {
        // បើគាត់ជា Super Admin ឬ Admin នោះនឹង Return True (ទាមទារ OTP)
        return in_array($this->role?->name, ['Super Admin', 'Admin']);
    }
}
