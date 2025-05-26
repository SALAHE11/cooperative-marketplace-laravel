<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use Exception;

class CategoryManagementController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('check.role:system_admin');
    }

    /**
     * Display categories management page
     */
    public function index()
{
    try {
        // Get all categories in hierarchical order (parents first, then children)
        $allCategories = Category::with(['parent', 'children'])
                              ->withCount('products')
                              ->orderBy('level', 'asc')
                              ->orderBy('parent_id', 'asc')
                              ->orderBy('sort_order', 'asc')
                              ->orderBy('name', 'asc')
                              ->get();

        // Build hierarchical structure for proper display
        $hierarchicalCategories = $this->buildHierarchicalList($allCategories);

        // Convert to paginated collection for compatibility
        $perPage = 50;
        $currentPage = request()->get('page', 1);
        $pagedData = array_slice($hierarchicalCategories, ($currentPage - 1) * $perPage, $perPage);

        $categories = new \Illuminate\Pagination\LengthAwarePaginator(
            $pagedData,
            count($hierarchicalCategories),
            $perPage,
            $currentPage,
            ['path' => request()->url(), 'pageName' => 'page']
        );

        // Add statistics
        $stats = [
            'total' => $allCategories->count(),
            'roots' => $allCategories->where('level', 0)->count(),
            'subcategories' => $allCategories->where('level', '>', 0)->count(),
            'with_products' => $allCategories->where('products_count', '>', 0)->count(),
            'max_level' => $allCategories->max('level'),
            'total_products' => $allCategories->sum('products_count')
        ];

        return view('admin.categories.index', compact('categories', 'allCategories', 'stats'));
    } catch (Exception $e) {
        Log::error('Error loading categories index: ' . $e->getMessage());
        return redirect()->route('admin.dashboard')
                       ->with('error', 'Erreur lors du chargement des catégories.');
    }
}

private function buildHierarchicalList($categories, $parentId = null, $level = 0)
{
    $result = [];

    $categoryCollection = $categories->where('parent_id', $parentId)
                                   ->sortBy('sort_order')
                                   ->sortBy('name');

    foreach ($categoryCollection as $category) {
        $result[] = $category;

        // Recursively add children
        $children = $this->buildHierarchicalList($categories, $category->id, $level + 1);
        $result = array_merge($result, $children);
    }

    return $result;
}

    /**
     * Store a new category
     */
    public function store(Request $request)
{
    try {
        // Validate the request including parent_id
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:categories,name',
            'description' => 'nullable|string|max:1000',
            'parent_id' => 'nullable|exists:categories,id'
        ]);

        // Additional validation for parent_id to prevent deep nesting
        if ($validated['parent_id']) {
            $parent = Category::find($validated['parent_id']);
            if ($parent && $parent->level >= 4) { // Max 5 levels (0-4)
                return response()->json([
                    'success' => false,
                    'message' => 'Profondeur maximale de hiérarchie atteinte (5 niveaux maximum)',
                    'errors' => ['parent_id' => ['Profondeur maximale atteinte']]
                ], 422);
            }
        }

        // Create the category
        $category = Category::create([
            'name' => trim($validated['name']),
            'description' => !empty($validated['description']) ? trim($validated['description']) : null,
            'parent_id' => $validated['parent_id']
        ]);

        // Set sort order if it has a parent
        if ($validated['parent_id']) {
            $category->sort_order = $category->getNextSortOrder();
            $category->save();
        }

        Log::info('Category created successfully', [
            'category_id' => $category->id,
            'name' => $category->name,
            'parent_id' => $category->parent_id,
            'level' => $category->level,
            'created_by' => Auth::id()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Catégorie créée avec succès!',
            'category' => [
                'id' => $category->id,
                'name' => $category->name,
                'description' => $category->description,
                'parent_id' => $category->parent_id,
                'level' => $category->level,
                'products_count' => 0,
                'children_count' => 0,
                'created_at' => $category->created_at->format('d/m/Y H:i')
            ]
        ]);

    } catch (\Illuminate\Validation\ValidationException $e) {
        Log::warning('Category creation validation failed', [
            'errors' => $e->errors(),
            'request_data' => $request->all()
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Erreur de validation',
            'errors' => $e->errors()
        ], 422);

    } catch (Exception $e) {
        Log::error('Error creating category', [
            'error' => $e->getMessage(),
            'request_data' => $request->all(),
            'user_id' => Auth::id()
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Erreur lors de la création de la catégorie',
            'error' => config('app.debug') ? $e->getMessage() : 'Erreur interne du serveur'
        ], 500);
    }
}

    /**
     * Update an existing category
     */
    public function update(Request $request, Category $category)
{
    try {
        // Validate the request including parent_id with circular reference check
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('categories', 'name')->ignore($category->id)
            ],
            'description' => 'nullable|string|max:1000',
            'parent_id' => [
                'nullable',
                'exists:categories,id',
                function ($attribute, $value, $fail) use ($category) {
                    if ($value && !$category->canMoveTo($value)) {
                        $fail('Impossible de déplacer la catégorie : cela créerait une référence circulaire.');
                    }
                }
            ]
        ]);

        // Additional validation for hierarchy depth
        if ($validated['parent_id']) {
            $newParent = Category::find($validated['parent_id']);
            $maxAllowedLevel = 4 - $category->getMaxDescendantLevel(); // Ensure no descendant exceeds level 4

            if ($newParent->level >= $maxAllowedLevel) {
                return response()->json([
                    'success' => false,
                    'message' => 'Profondeur maximale de hiérarchie dépassée',
                    'errors' => ['parent_id' => ['Déplacement impossible : profondeur maximale dépassée']]
                ], 422);
            }
        }

        // Check if parent is changing
        $parentChanged = $category->parent_id != $validated['parent_id'];

        // Update the category
        $category->update([
            'name' => trim($validated['name']),
            'description' => !empty($validated['description']) ? trim($validated['description']) : null,
            'parent_id' => $validated['parent_id']
        ]);

        // If parent changed, update sort order and hierarchy data for descendants
        if ($parentChanged) {
            if ($validated['parent_id']) {
                $category->sort_order = $category->getNextSortOrder();
            } else {
                $category->sort_order = 0;
            }
            $category->save();

            // Update all descendants' hierarchy data
            $this->updateDescendantsHierarchy($category);
        }

        Log::info('Category updated successfully', [
            'category_id' => $category->id,
            'name' => $category->name,
            'parent_changed' => $parentChanged,
            'new_parent_id' => $category->parent_id,
            'updated_by' => Auth::id()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Catégorie mise à jour avec succès!'
        ]);

    } catch (\Illuminate\Validation\ValidationException $e) {
        Log::warning('Category update validation failed', [
            'category_id' => $category->id,
            'errors' => $e->errors(),
            'request_data' => $request->all()
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Erreur de validation',
            'errors' => $e->errors()
        ], 422);

    } catch (Exception $e) {
        Log::error('Error updating category', [
            'category_id' => $category->id,
            'error' => $e->getMessage(),
            'request_data' => $request->all(),
            'user_id' => Auth::id()
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Erreur lors de la mise à jour de la catégorie',
            'error' => config('app.debug') ? $e->getMessage() : 'Erreur interne du serveur'
        ], 500);
    }
}

    /**
     * Delete a category (only if no products associated)
     */
   public function destroy(Category $category)
{
    try {
        // Check if category has children
        $childrenCount = $category->children()->count();
        if ($childrenCount > 0) {
            Log::warning('Attempt to delete category with children', [
                'category_id' => $category->id,
                'category_name' => $category->name,
                'children_count' => $childrenCount,
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'error' => "Impossible de supprimer la catégorie \"{$category->name}\" car elle contient {$childrenCount} sous-catégorie(s). Veuillez d'abord déplacer ou supprimer les sous-catégories."
            ], 400);
        }

        // Check if category has products
        $productCount = $category->products()->count();
        if ($productCount > 0) {
            Log::warning('Attempt to delete category with products', [
                'category_id' => $category->id,
                'category_name' => $category->name,
                'products_count' => $productCount,
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'error' => "Impossible de supprimer la catégorie \"{$category->name}\" car elle contient {$productCount} produit(s). Veuillez d'abord déplacer ou supprimer les produits associés."
            ], 400);
        }

        $categoryName = $category->name;
        $categoryId = $category->id;
        $parentCategory = $category->parent;

        $category->delete();

        // Update parent's children count if it had a parent
        if ($parentCategory) {
            $parentCategory->updateChildrenCount();
        }

        Log::info('Category deleted successfully', [
            'category_id' => $categoryId,
            'category_name' => $categoryName,
            'deleted_by' => Auth::id()
        ]);

        return response()->json([
            'success' => true,
            'message' => "Catégorie \"{$categoryName}\" supprimée avec succès!"
        ]);

    } catch (Exception $e) {
        Log::error('Error deleting category', [
            'category_id' => $category->id,
            'error' => $e->getMessage(),
            'user_id' => Auth::id()
        ]);

        return response()->json([
            'success' => false,
            'error' => 'Erreur lors de la suppression de la catégorie'
        ], 500);
    }
}

    /**
     * Get categories via AJAX
     */
    public function getCategoriesAjax()
    {
        try {
            $categories = Category::withCount('products')
                                ->orderBy('name', 'asc')
                                ->get()
                                ->map(function ($category) {
                                    return [
                                        'id' => $category->id,
                                        'name' => $category->name,
                                        'description' => $category->description,
                                        'products_count' => $category->products_count,
                                        'created_at' => $category->created_at->format('d/m/Y H:i')
                                    ];
                                });

            return response()->json([
                'success' => true,
                'categories' => $categories
            ]);

        } catch (Exception $e) {
            Log::error('Error fetching categories via AJAX', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Erreur lors de la récupération des catégories'
            ], 500);
        }
    }

    /**
 * NEW METHOD: Get hierarchical tree data for AJAX tree view
 */
public function getTreeData()
{
    try {
        // Get root categories with their children (recursive loading)
        $categories = Category::with(['children' => function($query) {
            $query->orderBy('sort_order', 'asc')->orderBy('name', 'asc');
        }])
        ->whereNull('parent_id')
        ->withCount('products')
        ->orderBy('sort_order', 'asc')
        ->orderBy('name', 'asc')
        ->get();

        Log::info('Tree data requested', [
            'root_categories_count' => $categories->count(),
            'user_id' => Auth::id()
        ]);

        return response()->json([
            'success' => true,
            'tree' => $this->buildTreeArray($categories)
        ]);

    } catch (Exception $e) {
        Log::error('Error fetching tree data', [
            'error' => $e->getMessage(),
            'user_id' => Auth::id()
        ]);

        return response()->json([
            'success' => false,
            'error' => 'Erreur lors de la récupération des données de l\'arbre'
        ], 500);
    }
}

/**
 * NEW METHOD: Move category to a new parent
 */
public function moveCategory(Request $request, Category $category)
{
    try {
        $validated = $request->validate([
            'parent_id' => 'nullable|exists:categories,id',
            'position' => 'integer|min:0'
        ]);

        // Check if move is valid (no circular reference)
        if (!$category->canMoveTo($validated['parent_id'])) {
            return response()->json([
                'success' => false,
                'error' => 'Impossible de déplacer la catégorie : cela créerait une référence circulaire'
            ], 400);
        }

        // Check hierarchy depth
        if ($validated['parent_id']) {
            $newParent = Category::find($validated['parent_id']);
            $maxDescendantLevel = $category->getMaxDescendantLevel();

            if (($newParent->level + 1 + $maxDescendantLevel) > 4) {
                return response()->json([
                    'success' => false,
                    'error' => 'Déplacement impossible : profondeur maximale de hiérarchie dépassée'
                ], 400);
            }
        }

        $oldParent = $category->parent;

        // Update category
        $category->parent_id = $validated['parent_id'];
        $category->sort_order = $validated['position'] ?? $category->getNextSortOrder();
        $category->save();

        // Update hierarchy data for this category and all its descendants
        $this->updateDescendantsHierarchy($category);

        // Update children count for old and new parents
        if ($oldParent) {
            $oldParent->updateChildrenCount();
        }
        if ($category->parent) {
            $category->parent->updateChildrenCount();
        }

        Log::info('Category moved successfully', [
            'category_id' => $category->id,
            'old_parent_id' => $oldParent?->id,
            'new_parent_id' => $category->parent_id,
            'new_position' => $category->sort_order,
            'moved_by' => Auth::id()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Catégorie déplacée avec succès'
        ]);

    } catch (\Illuminate\Validation\ValidationException $e) {
        return response()->json([
            'success' => false,
            'message' => 'Erreur de validation',
            'errors' => $e->errors()
        ], 422);

    } catch (Exception $e) {
        Log::error('Error moving category', [
            'category_id' => $category->id,
            'error' => $e->getMessage(),
            'user_id' => Auth::id()
        ]);

        return response()->json([
            'success' => false,
            'error' => 'Erreur lors du déplacement de la catégorie'
        ], 500);
    }
}

/**
 * NEW METHOD: Reorder categories (for drag & drop functionality)
 */
public function reorderCategories(Request $request)
{
    try {
        $validated = $request->validate([
            'orders' => 'required|array',
            'orders.*.id' => 'required|exists:categories,id',
            'orders.*.sort_order' => 'required|integer|min:0',
            'parent_id' => 'nullable|exists:categories,id'
        ]);

        $updatedCount = 0;

        foreach ($validated['orders'] as $order) {
            $updated = Category::where('id', $order['id'])
                             ->where('parent_id', $validated['parent_id'])
                             ->update(['sort_order' => $order['sort_order']]);

            if ($updated) {
                $updatedCount++;
            }
        }

        Log::info('Categories reordered', [
            'parent_id' => $validated['parent_id'],
            'categories_updated' => $updatedCount,
            'total_categories' => count($validated['orders']),
            'reordered_by' => Auth::id()
        ]);

        return response()->json([
            'success' => true,
            'message' => "{$updatedCount} catégorie(s) réorganisée(s) avec succès"
        ]);

    } catch (\Illuminate\Validation\ValidationException $e) {
        return response()->json([
            'success' => false,
            'message' => 'Erreur de validation',
            'errors' => $e->errors()
        ], 422);

    } catch (Exception $e) {
        Log::error('Error reordering categories', [
            'error' => $e->getMessage(),
            'user_id' => Auth::id()
        ]);

        return response()->json([
            'success' => false,
            'error' => 'Erreur lors de la réorganisation des catégories'
        ], 500);
    }
}

/**
 * NEW METHOD: Get breadcrumb path for a category
 */
public function getBreadcrumb(Category $category)
{
    try {
        $breadcrumb = $category->getBreadcrumb();

        Log::info('Breadcrumb requested', [
            'category_id' => $category->id,
            'breadcrumb_length' => $breadcrumb->count(),
            'user_id' => Auth::id()
        ]);

        return response()->json([
            'success' => true,
            'breadcrumb' => $breadcrumb->map(function ($cat) {
                return [
                    'id' => $cat->id,
                    'name' => $cat->name,
                    'level' => $cat->level,
                    'slug' => Str::slug($cat->name)
                ];
            })
        ]);

    } catch (Exception $e) {
        Log::error('Error getting breadcrumb', [
            'category_id' => $category->id,
            'error' => $e->getMessage(),
            'user_id' => Auth::id()
        ]);

        return response()->json([
            'success' => false,
            'error' => 'Erreur lors de la récupération du chemin de navigation'
        ], 500);
    }
}

/**
 * HELPER METHOD: Build tree array for JSON response
 */
private function buildTreeArray($categories)
{
    return $categories->map(function ($category) {
        return [
            'id' => $category->id,
            'name' => $category->name,
            'description' => $category->description,
            'products_count' => $category->products_count,
            'level' => $category->level,
            'sort_order' => $category->sort_order,
            'has_children' => $category->hasChildren(),
            'children_count' => $category->children_count,
            'created_at' => $category->created_at->format('d/m/Y H:i'),
            'children' => $category->children->isNotEmpty() ? $this->buildTreeArray($category->children) : []
        ];
    });
}

/**
 * HELPER METHOD: Update hierarchy data for category and all its descendants
 */
private function updateDescendantsHierarchy(Category $category)
{
    // Update the category itself
    $category->updateHierarchyData();
    $category->saveQuietly();

    // Recursively update all descendants
    foreach ($category->children as $child) {
        $this->updateDescendantsHierarchy($child);
    }
}

}
