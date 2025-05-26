<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
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
            // Get categories with product count
            $categories = Category::withCount('products')
                                ->orderBy('name', 'asc')
                                ->paginate(15);

            return view('admin.categories.index', compact('categories'));
        } catch (Exception $e) {
            Log::error('Error loading categories index: ' . $e->getMessage());
            return redirect()->route('admin.dashboard')
                           ->with('error', 'Erreur lors du chargement des catégories.');
        }
    }

    /**
     * Store a new category
     */
    public function store(Request $request)
    {
        try {
            // Validate the request
            $validated = $request->validate([
                'name' => 'required|string|max:255|unique:categories,name',
                'description' => 'nullable|string|max:1000'
            ]);

            // Create the category
            $category = Category::create([
                'name' => trim($validated['name']),
                'description' => !empty($validated['description']) ? trim($validated['description']) : null
            ]);

            Log::info('Category created successfully', [
                'category_id' => $category->id,
                'name' => $category->name,
                'created_by' => Auth::id()
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
            // Validate the request
            $validated = $request->validate([
                'name' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('categories', 'name')->ignore($category->id)
                ],
                'description' => 'nullable|string|max:1000'
            ]);

            // Update the category
            $category->update([
                'name' => trim($validated['name']),
                'description' => !empty($validated['description']) ? trim($validated['description']) : null
            ]);

            Log::info('Category updated successfully', [
                'category_id' => $category->id,
                'name' => $category->name,
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

            $category->delete();

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
}
