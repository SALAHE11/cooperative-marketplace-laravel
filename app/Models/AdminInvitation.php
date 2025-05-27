<?php

// File: app/Models/AdminInvitation.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class AdminInvitation extends Model
{
    use HasFactory;

    protected $fillable = [
        'email',
        'token',
        'invited_by',
        'is_used',
        'expires_at',
    ];

    protected $casts = [
        'is_used' => 'boolean',
        'expires_at' => 'datetime',
    ];

    /**
     * Get the user who sent the invitation
     */
    public function invitedBy()
    {
        return $this->belongsTo(User::class, 'invited_by');
    }

    /**
     * Check if the invitation has expired
     */
    public function isExpired()
    {
        return $this->expires_at->isPast();
    }

    /**
     * Check if the invitation is valid (not used and not expired)
     */
    public function isValid()
    {
        return !$this->is_used && !$this->isExpired();
    }

    /**
     * Mark invitation as used
     */
    public function markAsUsed()
    {
        $this->update(['is_used' => true]);
    }

    /**
     * Get expired invitations
     */
    public static function expired()
    {
        return static::where('expires_at', '<', now());
    }

    /**
     * Get unused invitations
     */
    public static function unused()
    {
        return static::where('is_used', false);
    }

    /**
     * Clean up expired invitations
     */
    public static function cleanupExpired()
    {
        return static::expired()->delete();
    }
}

?>
