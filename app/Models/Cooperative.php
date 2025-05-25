<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Cooperative extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'legal_status',
        'date_created',
        'address',
        'phone',
        'email',
        'email_verified_at',
        'logo_path',
        'description',
        'sector_of_activity',
        'status',
        'rejection_reason',
        'suspended_at',
        'suspension_reason',
        'suspended_by',
    ];

    protected $casts = [
        'date_created' => 'date',
        'email_verified_at' => 'datetime',
        'suspended_at' => 'datetime',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function clientReceipts()
    {
        return $this->hasMany(ClientReceipt::class);
    }

    public function admin()
    {
        return $this->hasOne(User::class)->where('role', 'cooperative_admin');
    }

    public function suspendedBy()
    {
        return $this->belongsTo(User::class, 'suspended_by');
    }

    // Helper method to check if cooperative email is verified
    public function isEmailVerified()
    {
        return !is_null($this->email_verified_at);
    }

    // Helper method to check if cooperative is suspended
    public function isSuspended()
    {
        return $this->status === 'suspended';
    }

    // Helper method to get logo URL
    public function getLogoUrlAttribute()
    {
        if ($this->logo_path) {
            return Storage::url($this->logo_path);
        }
        return null;
    }

    // Helper method to check if logo exists
    public function hasLogo()
    {
        return !empty($this->logo_path) && Storage::disk('public')->exists($this->logo_path);
    }

    // Clean up logo when cooperative is deleted
    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($cooperative) {
            if ($cooperative->logo_path && Storage::disk('public')->exists($cooperative->logo_path)) {
                Storage::disk('public')->delete($cooperative->logo_path);
            }
        });
    }
}
