<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuthorizationReceipt extends Model
{
    use HasFactory;

    protected $fillable = [
        'auth_number',
        'client_receipt_id',
        'authorized_person_name',
        'validity_start',
        'validity_end',
        'unique_code',
        'qr_code_data',
        'is_revoked',
        'is_used',
        'used_at',
    ];

    protected $casts = [
        'validity_start' => 'datetime',
        'validity_end' => 'datetime',
        'is_revoked' => 'boolean',
        'is_used' => 'boolean',
        'used_at' => 'datetime',
    ];

    public function clientReceipt()
    {
        return $this->belongsTo(ClientReceipt::class);
    }
}
