<?php
// app/Models/Product.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

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
        'status',
        'rejection_reason',
        'is_active',
        'submitted_at',
        'reviewed_at',
        'reviewed_by',
        'admin_notes',
        'original_data',
        'primary_image_id',
        'images_count',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
        'submitted_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'original_data' => 'array',
        'images_count' => 'integer',
    ];

    // Relationships
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
        return $this->hasMany(ProductImage::class)->whereNull('deleted_at')->orderBy('sort_order');
    }

    public function allImages()
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    public function primaryImage()
    {
        return $this->belongsTo(ProductImage::class, 'primary_image_id');
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
        return in_array($this->status, ['draft', 'rejected', 'needs_info', 'approved']);
    }

    public function canBeSubmitted()
    {
        return in_array($this->status, ['draft', 'rejected', 'needs_info']);
    }

    public function isUpdatedVersion()
    {
        return !empty($this->original_data);
    }

    public function storeOriginalData()
    {
        $this->original_data = [
            'name' => $this->name,
            'description' => $this->description,
            'price' => $this->price,
            'stock_quantity' => $this->stock_quantity,
            'category_id' => $this->category_id,
            'category_name' => $this->category->name ?? 'N/A',
            'images_count' => $this->images_count,
            'updated_at' => $this->updated_at->toISOString(),
        ];
    }

    // Image management methods
    public function updateImagesCount()
    {
        $this->images_count = $this->images()->count();
        $this->save();
    }

    public function setPrimaryImage($imageId = null)
    {
        if ($imageId) {
            $image = $this->images()->find($imageId);
            if ($image) {
                $this->primary_image_id = $imageId;
                $this->save();
                return true;
            }
        } else {
            // Auto-select first image as primary
            $firstImage = $this->images()->orderBy('sort_order')->first();
            if ($firstImage) {
                $this->primary_image_id = $firstImage->id;
                $this->save();
                return true;
            }
        }
        return false;
    }

    public function hasImages()
    {
        return $this->images_count > 0;
    }

    // Get primary image URL or fallback
    public function getPrimaryImageUrlAttribute()
    {
        if ($this->primaryImage && $this->primaryImage->isReady()) {
            return $this->primaryImage->image_url;
        }

        $firstImage = $this->images()->where('processing_status', 'ready')->first();
        if ($firstImage) {
            return $firstImage->image_url;
        }

        return null;
    }

    public function getPrimaryThumbnailUrlAttribute()
    {
        if ($this->primaryImage && $this->primaryImage->isReady()) {
            return $this->primaryImage->thumbnail_url;
        }

        $firstImage = $this->images()->where('processing_status', 'ready')->first();
        if ($firstImage) {
            return $firstImage->thumbnail_url;
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

    // Model events
    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($product) {
            // Soft delete all images
            $product->allImages()->update(['deleted_at' => now()]);
        });
    }
}
