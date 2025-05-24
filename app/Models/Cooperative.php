<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
        'email_verified_at',  // Add this line
        'logo_path',
        'description',
        'sector_of_activity',
        'status',
        'rejection_reason',  // Add this line
    ];

    protected $casts = [
        'date_created' => 'date',
        'email_verified_at' => 'datetime',  // Add this line
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

     // Add helper method to check if cooperative email is verified
    public function isEmailVerified()
    {
        return !is_null($this->email_verified_at);
    }
}
