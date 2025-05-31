<?php
// app/Models/ProductImage.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class ProductImage extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'product_id',
        'image_path',
        'thumbnail_path',
        'file_size',
        'width',
        'height',
        'mime_type',
        'original_filename',
        'processing_status',
        'failure_reason',
        'file_hash',
        'is_primary',
        'alt_text',
        'sort_order',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'sort_order' => 'integer',
        'file_size' => 'integer',
        'width' => 'integer',
        'height' => 'integer',
        'deleted_at' => 'datetime',
    ];

    protected $dates = ['deleted_at'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // Status methods
    public function isPending()
    {
        return $this->processing_status === 'pending';
    }

    public function isProcessing()
    {
        return $this->processing_status === 'processing';
    }

    public function isReady()
    {
        return $this->processing_status === 'ready';
    }

    public function isFailed()
    {
        return $this->processing_status === 'failed';
    }

    public function markAsProcessing()
    {
        $this->update(['processing_status' => 'processing']);
    }

    public function markAsReady()
    {
        $this->update([
            'processing_status' => 'ready',
            'failure_reason' => null
        ]);
    }

    public function markAsFailed($reason = null)
    {
        $this->update([
            'processing_status' => 'failed',
            'failure_reason' => $reason
        ]);
    }

    // URL getters
    public function getImageUrlAttribute()
    {
        if ($this->image_path && Storage::disk('public')->exists($this->image_path)) {
            return Storage::url($this->image_path);
        }
        return null;
    }

    public function getThumbnailUrlAttribute()
    {
        if ($this->thumbnail_path && Storage::disk('public')->exists($this->thumbnail_path)) {
            return Storage::url($this->thumbnail_path);
        }
        return $this->image_url;
    }

    // File management
    public function deleteFiles()
    {
        if ($this->image_path && Storage::disk('public')->exists($this->image_path)) {
            Storage::disk('public')->delete($this->image_path);
        }
        if ($this->thumbnail_path && Storage::disk('public')->exists($this->thumbnail_path)) {
            Storage::disk('public')->delete($this->thumbnail_path);
        }
    }

    public function getFormattedFileSizeAttribute()
    {
        if (!$this->file_size) return 'N/A';

        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    public function getDimensionsAttribute()
    {
        if ($this->width && $this->height) {
            return $this->width . 'x' . $this->height;
        }
        return 'N/A';
    }

    // Model events
    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($image) {
            $image->deleteFiles();
        });

        static::deleted(function ($image) {
            // Update product images count and primary image if needed
            $product = $image->product;
            if ($product) {
                $product->updateImagesCount();

                // If this was the primary image, select a new one
                if ($product->primary_image_id === $image->id) {
                    $product->primary_image_id = null;
                    $product->save();
                    $product->setPrimaryImage();
                }
            }
        });

        static::created(function ($image) {
            // Update product images count
            $product = $image->product;
            if ($product) {
                $product->updateImagesCount();

                // Set as primary if it's the first image
                if ($product->images_count === 1) {
                    $product->setPrimaryImage($image->id);
                }
            }
        });
    }
}
