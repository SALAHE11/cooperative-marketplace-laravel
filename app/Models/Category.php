<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get all products for this category
     */
    public function products()
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Get active products for this category
     */
    public function activeProducts()
    {
        return $this->hasMany(Product::class)->where('is_active', true);
    }

    /**
     * Scope to search categories by name
     */
    public function scopeSearch($query, $search)
    {
        return $query->where('name', 'like', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%');
    }

    /**
     * Get the category name with product count
     */
    public function getNameWithCountAttribute()
    {
        return $this->name . ' (' . $this->products_count . ')';
    }
}
