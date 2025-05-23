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
        'logo_path',
        'description',
        'sector_of_activity',
        'status',
    ];

    protected $casts = [
        'date_created' => 'date',
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
}
