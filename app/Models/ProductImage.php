<?php
// app/Models/ProductImage.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
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

    // Status methods with fallback for missing column
    public function isPending()
    {
        if (!$this->hasProcessingStatusColumn()) {
            return false; // Assume ready if column doesn't exist
        }
        return $this->processing_status === 'pending';
    }

    public function isProcessing()
    {
        if (!$this->hasProcessingStatusColumn()) {
            return false;
        }
        return $this->processing_status === 'processing';
    }

    public function isReady()
    {
        if (!$this->hasProcessingStatusColumn()) {
            // If column doesn't exist, check if file exists
            return $this->image_path && Storage::disk('public')->exists($this->image_path);
        }
        return $this->processing_status === 'ready';
    }

    public function isFailed()
    {
        if (!$this->hasProcessingStatusColumn()) {
            return false;
        }
        return $this->processing_status === 'failed';
    }

    public function markAsProcessing()
    {
        if ($this->hasProcessingStatusColumn()) {
            $this->update(['processing_status' => 'processing']);
        }
    }

    public function markAsReady()
    {
        if ($this->hasProcessingStatusColumn()) {
            $this->update([
                'processing_status' => 'ready',
                'failure_reason' => null
            ]);
        }
    }

    public function markAsFailed($reason = null)
    {
        if ($this->hasProcessingStatusColumn()) {
            $this->update([
                'processing_status' => 'failed',
                'failure_reason' => $reason
            ]);
        }
    }

    // Check if processing_status column exists
    private function hasProcessingStatusColumn()
    {
        static $hasColumn = null;

        if ($hasColumn === null) {
            try {
                $hasColumn = Schema::hasColumn($this->getTable(), 'processing_status');
            } catch (\Exception $e) {
                $hasColumn = false;
            }
        }

        return $hasColumn;
    }

    // URL getters with better error handling
    public function getImageUrlAttribute()
    {
        if (!$this->image_path) {
            return null;
        }

        // Check if file exists
        if (!Storage::disk('public')->exists($this->image_path)) {
            Log::warning('Image file not found', [
                'image_id' => $this->id,
                'path' => $this->image_path,
                'full_path' => storage_path('app/public/' . $this->image_path)
            ]);
            return null;
        }

        try {
            // Use Storage::url() for public disk files
            return Storage::url($this->image_path);
        } catch (\Exception $e) {
            Log::error('Failed to generate image URL', [
                'image_id' => $this->id,
                'path' => $this->image_path,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    public function getThumbnailUrlAttribute()
    {
        if ($this->thumbnail_path && Storage::disk('public')->exists($this->thumbnail_path)) {
            try {
                return Storage::url($this->thumbnail_path);
            } catch (\Exception $e) {

            Log::warning('Failed to generate thumbnail URL, falling back to main image', [
                    'image_id' => $this->id,
                    'thumbnail_path' => $this->thumbnail_path,
                    'error' => $e->getMessage()
                ]);
            }
        }

        // Fallback to main image
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
