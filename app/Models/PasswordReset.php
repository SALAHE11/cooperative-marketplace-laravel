<?php

// File: app/Models/PasswordReset.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PasswordReset extends Model
{
    use HasFactory;

    // Specify the table name explicitly to avoid conflicts with Laravel's default
    protected $table = 'password_resets';

    protected $fillable = [
        'email',
        'token',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    // Enable timestamps
    public $timestamps = true;

    public function isExpired()
    {
        return $this->expires_at->isPast();
    }
}

?>
