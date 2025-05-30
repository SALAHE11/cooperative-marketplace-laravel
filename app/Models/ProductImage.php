<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class ProductImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'image_path',
        'thumbnail_path',
        'is_primary',
        'alt_text',
        'sort_order',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function getImageUrlAttribute()
    {
        return Storage::url($this->image_path);
    }

    public function getThumbnailUrlAttribute()
    {
        if ($this->thumbnail_path) {
            return Storage::url($this->thumbnail_path);
        }
        return $this->image_url;
    }

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($image) {
            // Delete image files when model is deleted
            if (Storage::disk('public')->exists($image->image_path)) {
                Storage::disk('public')->delete($image->image_path);
            }
            if ($image->thumbnail_path && Storage::disk('public')->exists($image->thumbnail_path)) {
                Storage::disk('public')->delete($image->thumbnail_path);
            }
        });
    }
}
