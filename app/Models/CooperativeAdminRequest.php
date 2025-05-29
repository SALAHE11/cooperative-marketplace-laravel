<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CooperativeAdminRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'cooperative_id',
        'status',
        'message',
        'response_message',
        'responded_by',
        'requested_at',
        'responded_at',
    ];

    protected $casts = [
        'requested_at' => 'datetime',
        'responded_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function cooperative()
    {
        return $this->belongsTo(Cooperative::class);
    }

    public function respondedBy()
    {
        return $this->belongsTo(User::class, 'responded_by');
    }

    public function isPending()
    {
        return $this->status === 'pending';
    }

    public function isApproved()
    {
        return $this->status === 'approved';
    }

    public function isRejected()
    {
        return $this->status === 'rejected';
    }

    public function approve($respondedBy, $responseMessage = null)
    {
        $this->update([
            'status' => 'approved',
            'responded_by' => $respondedBy,
            'response_message' => $responseMessage,
            'responded_at' => now(),
        ]);
    }

    public function reject($respondedBy, $responseMessage = null)
    {
        $this->update([
            'status' => 'rejected',
            'responded_by' => $respondedBy,
            'response_message' => $responseMessage,
            'responded_at' => now(),
        ]);
    }
}

