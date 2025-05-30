<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'cooperative_id',
        'category_id',
        'name',
        'description',
        'price',
        'stock_quantity',
        'image_path', // Keep for backward compatibility
        'status',
        'rejection_reason',
        'is_active',
        'submitted_at',
        'reviewed_at',
        'reviewed_by',
        'admin_notes',
        'original_data', // NEW: Store original approved version
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
        'submitted_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'original_data' => 'array', // NEW: Cast to array for JSON handling
    ];

    public function cooperative()
    {
        return $this->belongsTo(Cooperative::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    public function primaryImage()
    {
        return $this->hasOne(ProductImage::class)->where('is_primary', true);
    }

    public function reviewedBy()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    // Status helper methods
    public function isDraft()
    {
        return $this->status === 'draft';
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

    public function needsInfo()
    {
        return $this->status === 'needs_info';
    }

    public function canBeEdited()
    {
        return in_array($this->status, ['draft', 'rejected', 'needs_info', 'approved']); // NEW: Allow editing approved products
    }

    public function canBeSubmitted()
    {
        return in_array($this->status, ['draft', 'rejected', 'needs_info']);
    }

    // NEW: Check if this is an updated version of a previously approved product
    public function isUpdatedVersion()
    {
        return !empty($this->original_data);
    }

    // NEW: Store current data as original version before update
    public function storeOriginalData()
    {
        $this->original_data = [
            'name' => $this->name,
            'description' => $this->description,
            'price' => $this->price,
            'stock_quantity' => $this->stock_quantity,
            'category_id' => $this->category_id,
            'category_name' => $this->category->name ?? 'N/A',
            'images_count' => $this->images->count(),
            'updated_at' => $this->updated_at->toISOString(),
        ];
    }

    // Get primary image URL or fallback
    public function getPrimaryImageUrlAttribute()
    {
        $primaryImage = $this->primaryImage;
        if ($primaryImage) {
            return $primaryImage->image_url;
        }

        // Fallback to first image
        $firstImage = $this->images()->first();
        if ($firstImage) {
            return $firstImage->image_url;
        }

        // Fallback to old image_path field
        if ($this->image_path) {
            return asset('storage/' . $this->image_path);
        }

        return null;
    }

    public function getStatusBadgeAttribute()
    {
        $badges = [
            'draft' => 'secondary',
            'pending' => 'warning',
            'approved' => 'success',
            'rejected' => 'danger',
            'needs_info' => 'info'
        ];

        return $badges[$this->status] ?? 'secondary';
    }

    public function getStatusTextAttribute()
    {
        $texts = [
            'draft' => 'Brouillon',
            'pending' => 'En attente',
            'approved' => 'Approuvé',
            'rejected' => 'Rejeté',
            'needs_info' => 'Info demandée'
        ];

        return $texts[$this->status] ?? 'Inconnu';
    }
}
