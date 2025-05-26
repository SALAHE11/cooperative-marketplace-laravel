<?php


namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class CategoryManagementController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display categories management page
     */
    public function index()
    {
        /** @var User $user */
        $user = Auth::user();

        if (!$user->isSystemAdmin()) {
            abort(403);
        }

        // Get categories with product count
        $categories = Category::withCount('products')
                            ->orderBy('name', 'asc')
                            ->paginate(15);

        return view('admin.categories.index', compact('categories'));
    }

    /**
     * Store a new category
     */
    public function store(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        if (!$user->isSystemAdmin()) {
            return response()->json(['success' => false, 'error' => 'Accès non autorisé'], 403);
        }

        try {
            $request->validate([
                'name' => 'required|string|max:255|unique:categories,name',
                'description' => 'nullable|string|max:1000'
            ]);

            $category = Category::create([
                'name' => trim($request->name),
                'description' => trim($request->description)
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Catégorie créée avec succès!',
                'category' => [
                    'id' => $category->id,
                    'name' => $category->name,
                    'description' => $category->description,
                    'products_count' => 0,
                    'created_at' => $category->created_at->format('d/m/Y H:i')
                ]
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création de la catégorie',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update an existing category
     */
    public function update(Request $request, Category $category)
    {
        /** @var User $user */
        $user = Auth::user();

        if (!$user->isSystemAdmin()) {
            return response()->json(['success' => false, 'error' => 'Accès non autorisé'], 403);
        }

        try {
            $request->validate([
                'name' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('categories', 'name')->ignore($category->id)
                ],
                'description' => 'nullable|string|max:1000'
            ]);

            $category->update([
                'name' => trim($request->name),
                'description' => trim($request->description)
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Catégorie mise à jour avec succès!'
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour de la catégorie',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a category (only if no products associated)
     */
    public function destroy(Category $category)
    {
        /** @var User $user */
        $user = Auth::user();

        if (!$user->isSystemAdmin()) {
            return response()->json(['success' => false, 'error' => 'Accès non autorisé'], 403);
        }

        try {
            // Check if category has products
            $productCount = $category->products()->count();

            if ($productCount > 0) {
                return response()->json([
                    'success' => false,
                    'error' => "Impossible de supprimer la catégorie \"{$category->name}\" car elle contient {$productCount} produit(s). Veuillez d'abord déplacer ou supprimer les produits associés."
                ], 400);
            }

            $categoryName = $category->name;
            $category->delete();

            return response()->json([
                'success' => true,
                'message' => "Catégorie \"{$categoryName}\" supprimée avec succès!"
            ]);

        } catch (\Exception $e) {
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
        /** @var User $user */
        $user = Auth::user();

        if (!$user->isSystemAdmin()) {
            return response()->json(['success' => false, 'error' => 'Accès non autorisé'], 403);
        }

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

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Erreur lors de la récupération des catégories'
            ], 500);
        }
    }
}
