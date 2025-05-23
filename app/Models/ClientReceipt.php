<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientReceipt extends Model
{
    use HasFactory;

    protected $fillable = [
        'receipt_number',
        'order_id',
        'user_id',
        'cooperative_id',
        'total_amount',
        'verification_code',
        'qr_code_data',
        'is_void',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'is_void' => 'boolean',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function cooperative()
    {
        return $this->belongsTo(Cooperative::class);
    }

    public function authorizationReceipts()
    {
        return $this->hasMany(AuthorizationReceipt::class);
    }
}
