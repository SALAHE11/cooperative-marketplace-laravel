<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'phone',
        'role',
        'status',
        'cooperative_id',
        'address',
        'email_verified_at',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
    ];

    public function cooperative()
    {
        return $this->belongsTo(Cooperative::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function sentMessages()
    {
        return $this->hasMany(Message::class, 'from_user_id');
    }

    public function receivedMessages()
    {
        return $this->hasMany(Message::class, 'to_user_id');
    }

    public function clientReceipts()
    {
        return $this->hasMany(ClientReceipt::class);
    }

    public function getFullNameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    public function isSystemAdmin()
    {
        return $this->role === 'system_admin';
    }

    public function isCooperativeAdmin()
    {
        return $this->role === 'cooperative_admin';
    }

    public function isClient()
    {
        return $this->role === 'client';
    }

    // NEW: Check if user is primary admin of their cooperative
    public function isPrimaryAdmin()
    {
        if (!$this->isCooperativeAdmin() || !$this->cooperative_id) {
            return false;
        }

        return $this->cooperative && $this->cooperative->primary_admin_id === $this->id;
    }

    // NEW: Check if user can manage other admins (only primary admin can)
    public function canManageAdmins()
    {
        return $this->isPrimaryAdmin();
    }

    public function removedBy()
    {
        return $this->belongsTo(User::class, 'removed_by');
    }

    public function cooperativeAdminRequests()
    {
        return $this->hasMany(CooperativeAdminRequest::class);
    }

    // NEW: Cooperatives where this user is the primary admin
    public function primaryCooperatives()
    {
        return $this->hasMany(Cooperative::class, 'primary_admin_id');
    }
}
