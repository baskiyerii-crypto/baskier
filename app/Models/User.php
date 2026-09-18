<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'vendor_id',
            'is_freelancer',
        'public_id',
        'phone',
        'email_verified_at',
        'phone_verified_at',
        'is_active',
    ];

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
            'phone_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (User $user): void {
            if (empty($user->public_id)) {
                $user->public_id = static::generateUniquePublicId();
            }
        });
    }

    public static function generateUniquePublicId(): string
    {
        do {
            $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        } while (static::query()->where('public_id', $code)->exists());

        return $code;
    }

    public function publicCode(): string
    {
        $id = $this->public_id ?: '000000';

        return 'BY-'.$id;
    }

    public function vendor(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isVendor(): bool
    {
        return $this->role === 'vendor';
    }

    public function vendorHomeRoute(): string
    {
        $vendor = $this->relationLoaded('vendor') ? $this->vendor : $this->vendor()->first();
        if ($vendor && $vendor->prefersOutdoorPanel() && \Illuminate\Support\Facades\Route::has('outdoor-panel.dashboard')) {
            return 'outdoor-panel.dashboard';
        }

        return 'vendor.dashboard';
    }

    public function isCustomer(): bool
    {
        return $this->role === 'customer';
    }

    public function isFreelancer(): bool
    {
        if ($this->role === 'freelancer') {
            return true;
        }

        if ($this->isVendor()) {
            return (bool) ($this->vendor?->freelancer_enabled ?? false);
        }

        return $this->freelancerProfile()->exists();
    }

    public function orders(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function conversations(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Conversation::class);
    }

    public function freelancerProfile(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(FreelancerProfile::class);
    }

    public function freelancerJobListings(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(FreelancerJobListing::class, 'user_id');
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class);
    }

    public function billingProfiles(): HasMany
    {
        return $this->hasMany(BillingProfile::class);
    }

    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function favorites(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'favorites')->withTimestamps();
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }
}
