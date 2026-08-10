<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name', 'email', 'user_id', 'phone', 'password', 'role', 'designation', 'vendors_id',
        'dob', 'gender', 'blood_group', 'national_id', 'religion', 'is_active',
        'present_address', 'permanent_address', 'photo',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'otp',
        'tokens',
    ];

    protected static function booted()
    {
        static::creating(function ($user) {
            if (empty($user->password)) {
                $user->password = Hash::make('password');
            }
        });
    }

    protected $casts = [
        'email_verified_at' => 'datetime',
        'wallet_balance' => 'decimal:2',
        'dob' => 'date',
        'is_active' => 'boolean',
    ];

    // --- Relationships ---
    public function orders() { return $this->hasMany(Order::class, 'user_id'); }

    public function transaction() { return $this->hasMany(Transaction::class); }

    public function productRatings(){ return $this->hasMany(ProductRating::class); }

    // --- Accessors (Calculated Fields) ---

    /**
     * 1. Bonus Balance (Amount)
     */
    public function getBonusBalanceAttribute()
    {
        $credit = $this->pointTransactions()
            ->where('bonus_status', 'credit')
            ->sum('bonus_amount') ?? 0;

        $debit = $this->pointTransactions()
            ->where('bonus_status', 'debit')
            ->sum('bonus_amount') ?? 0;

        return number_format($credit - $debit, 2, '.', '');
    }

    /**
     * 2. Total Points
     */
    public function getTotalPointsAttribute()
    {
        return (int) ($this->pointTransactions()->sum('points') ?? 0);
    }

    /**
     * 3. Total Points (Accessor)
     * এটি এখন সরাসরি ডাটাবেসের own_total_point রিটার্ন করবে
     */
    public function getTotalOwnPointsAttribute()
    {
        return (int) ($this->own_total_point ?? 0);
    }

    /**
     * 4. Account is_active observe codition ar jonno
     */
    public function getTotalCalculationAttribute()
    {
        return (int) (
            ($this->left_total_point ?? 0) +
            ($this->right_total_point ?? 0) +
            ($this->own_total_point ?? 0)
        );
    }

    public function isActive()
    {
        return (bool) $this->is_active;
    }

    public function notice()
    {
        return $this->hasMany(Notice::class, 'user_id');
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class, 'vendors_id');
    }

    public function couponUsages()
    {
        return $this->hasMany(CouponUsage::class);
    }

    public function deliveryCharge()
    {
        return $this->hasMany(DeliveryChargePayment::class);
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(CustomerAddress::class);
    }

    public function orderPayments()
    {
        return $this->hasMany(OrderPayment::class);
    }

    public function verifiedPayments()
    {
        return $this->hasMany(OrderPayment::class, 'verified_by');
    }

    public function expense()
    {
        return $this->hasMany(User::class, 'user_id', 'id');
    }
}
