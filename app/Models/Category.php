<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'parent_id',
        'level',
        'path',
        'children_count',
        'sort_order',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'level' => 'integer',
        'children_count' => 'integer',
        'sort_order' => 'integer',
    ];

    /**
     * Boot the model and set up event listeners
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($category) {
            $category->updateHierarchyData();
        });

        static::updating(function ($category) {
            if ($category->isDirty('parent_id')) {
                $category->updateHierarchyData();
            }
        });

        static::saved(function ($category) {
            $category->updateChildrenCount();
        });

        static::deleted(function ($category) {
            if ($category->parent) {
                $category->parent->updateChildrenCount();
            }
        });
    }

    /**
     * Get the parent category
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    /**
     * Get the child categories
     */
    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id')->orderBy('sort_order');
    }

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
     * Scope to get root categories (no parent)
     */
    public function scopeRoots($query)
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Scope to get categories by level
     */
    public function scopeByLevel($query, $level)
    {
        return $query->where('level', $level);
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
     * Get ancestors (parent, grandparent, etc.)
     */
    public function ancestors()
    {
        $ancestors = collect();
        $current = $this->parent;

        while ($current) {
            $ancestors->prepend($current);
            $current = $current->parent;
        }

        return $ancestors;
    }

    /**
     * Get descendants (children, grandchildren, etc.)
     */
    public function descendants()
    {
        $descendants = collect();

        foreach ($this->children as $child) {
            $descendants->push($child);
            $descendants = $descendants->merge($child->descendants());
        }

        return $descendants;
    }

    /**
     * Get the full hierarchy path as array
     */
    public function getHierarchyPath()
    {
        if (!$this->path) {
            return [$this->id];
        }

        return array_map('intval', explode('/', $this->path));
    }

    /**
     * Get the breadcrumb path
     */
    /**
 * Get the breadcrumb path
 */
public function getBreadcrumb()
{
    $breadcrumb = collect(); // Use collect() instead of array
    $pathIds = $this->getHierarchyPath();

    if (count($pathIds) > 1) {
        $ancestors = Category::whereIn('id', array_slice($pathIds, 0, -1))->get()->keyBy('id');

        foreach (array_slice($pathIds, 0, -1) as $id) {
            if (isset($ancestors[$id])) {
                $breadcrumb->push($ancestors[$id]); // Use push() instead of []
            }
        }
    }

    $breadcrumb->push($this); // Use push() instead of []
    return $breadcrumb; // Now returns a Collection
}

    /**
     * Check if this category can be moved to another parent
     */
    public function canMoveTo($newParentId)
    {
        if ($newParentId === null) {
            return true; // Can always move to root
        }

        if ($newParentId == $this->id) {
            return false; // Cannot be parent of itself
        }

        // Check if new parent is a descendant (would create circular reference)
        $descendants = $this->descendants();
        return !$descendants->contains('id', $newParentId);
    }

    /**
     * Update hierarchy data (level and path)
     */
    public function updateHierarchyData()
    {
        if ($this->parent_id) {
            $parent = Category::find($this->parent_id);
            $this->level = $parent->level + 1;
            $this->path = $parent->path ? $parent->path . '/' . $this->id : $this->id;
        } else {
            $this->level = 0;
            $this->path = $this->id;
        }
    }

    /**
     * Update children count for this category
     */
    public function updateChildrenCount()
    {
        $this->children_count = $this->children()->count();
        $this->saveQuietly(); // Save without triggering events

        // Also update parent's children count
        if ($this->parent) {
            $this->parent->updateChildrenCount();
        }
    }

    /**
     * Get the category name with hierarchy indicator
     */
    public function getNameWithHierarchyAttribute()
    {
        $indent = str_repeat('└─ ', $this->level);
        return $indent . $this->name;
    }

    /**
     * Get the category name with product count
     */
    public function getNameWithCountAttribute()
    {
        return $this->name . ' (' . $this->products_count . ')';
    }

    /**
     * Check if category is root (has no parent)
     */
    public function isRoot()
    {
        return $this->parent_id === null;
    }

    /**
     * Check if category has children
     */
    public function hasChildren()
    {
        return $this->children_count > 0;
    }

    /**
     * Get next sort order for siblings
     */
    public function getNextSortOrder()
    {
        return Category::where('parent_id', $this->parent_id)->max('sort_order') + 1;
    }

    public function getMaxDescendantLevel()
{
    $maxLevel = 0;

    foreach ($this->descendants() as $descendant) {
        $relativeLevel = $descendant->level - $this->level;
        if ($relativeLevel > $maxLevel) {
            $maxLevel = $relativeLevel;
        }
    }

    return $maxLevel;
}
}
