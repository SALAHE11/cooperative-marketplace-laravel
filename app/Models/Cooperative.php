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
        'primary_admin_id', // NEW: Primary admin field
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

    // NEW: Primary admin relationship
    public function primaryAdmin()
    {
        return $this->belongsTo(User::class, 'primary_admin_id');
    }

    // NEW: Get all active admins for this cooperative
    public function activeAdmins()
    {
        return $this->hasMany(User::class)->where('role', 'cooperative_admin')->where('status', 'active');
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

    // NEW: Check if user is the primary admin of this cooperative
    public function isPrimaryAdmin($userId)
    {
        return $this->primary_admin_id === $userId;
    }

    // NEW: Set primary admin
    public function setPrimaryAdmin($userId)
    {
        $this->update(['primary_admin_id' => $userId]);
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
