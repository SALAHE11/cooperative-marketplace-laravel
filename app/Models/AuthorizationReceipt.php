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
        'authorized_person_cin',  // FIXED: Added this missing field
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

    /**
     * Check if the authorization receipt is currently valid
     */
    public function isValid()
    {
        return !$this->is_revoked
               && !$this->is_used
               && $this->validity_start <= now()
               && $this->validity_end >= now();
    }

    /**
     * Check if the authorization receipt has expired
     */
    public function isExpired()
    {
        return $this->validity_end < now();
    }

    /**
     * Mark the authorization receipt as used
     */
    public function markAsUsed()
    {
        $this->update([
            'is_used' => true,
            'used_at' => now()
        ]);
    }

    /**
     * Revoke the authorization receipt
     */
    public function revoke()
    {
        $this->update([
            'is_revoked' => true
        ]);
    }

    /**
     * Get the status of the authorization receipt
     */
    public function getStatusAttribute()
    {
        if ($this->is_used) {
            return 'used';
        }

        if ($this->is_revoked) {
            return 'revoked';
        }

        if ($this->isExpired()) {
            return 'expired';
        }

        return 'valid';
    }

    /**
     * Get human-readable status
     */
    public function getStatusLabelAttribute()
    {
        switch ($this->status) {
            case 'used':
                return 'Utilisé';
            case 'revoked':
                return 'Révoqué';
            case 'expired':
                return 'Expiré';
            case 'valid':
                return 'Valide';
            default:
                return 'Inconnu';
        }
    }

    /**
     * Scope for valid authorization receipts
     */
    public function scopeValid($query)
    {
        return $query->where('is_revoked', false)
                    ->where('is_used', false)
                    ->where('validity_start', '<=', now())
                    ->where('validity_end', '>=', now());
    }

    /**
     * Scope for expired authorization receipts
     */
    public function scopeExpired($query)
    {
        return $query->where('validity_end', '<', now());
    }

    /**
     * Scope for used authorization receipts
     */
    public function scopeUsed($query)
    {
        return $query->where('is_used', true);
    }

    /**
     * Scope for revoked authorization receipts
     */
    public function scopeRevoked($query)
    {
        return $query->where('is_revoked', true);
    }
}
