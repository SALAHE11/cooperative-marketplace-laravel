<?php
// app/Http/Controllers/ProductManagementController.php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\ProductImage;
use App\Services\ImageProcessingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class ProductManagementController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('check.role:cooperative_admin');
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $cooperative = $user->cooperative;

        if (!$cooperative || $cooperative->status !== 'approved') {
            return redirect()->route('coop.dashboard')->with('error', 'Votre coopérative doit être approuvée pour gérer les produits.');
        }

        $search = $request->get('search', '');
        $status = $request->get('status', 'all');

        $query = $cooperative->products()->with(['category', 'primaryImage']);

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $products = $query->orderBy('updated_at', 'desc')->paginate(12);

        $counts = [
            'all' => $cooperative->products()->count(),
            'draft' => $cooperative->products()->where('status', 'draft')->count(),
            'pending' => $cooperative->products()->where('status', 'pending')->count(),
            'approved' => $cooperative->products()->where('status', 'approved')->count(),
            'rejected' => $cooperative->products()->where('status', 'rejected')->count(),
            'needs_info' => $cooperative->products()->where('status', 'needs_info')->count(),
        ];

        return view('coop.products.index', compact('products', 'search', 'status', 'counts'));
    }

    public function create()
    {
        $user = Auth::user();
        $cooperative = $user->cooperative;

        if (!$cooperative || $cooperative->status !== 'approved') {
            return redirect()->route('coop.dashboard')->with('error', 'Votre coopérative doit être approuvée pour ajouter des produits.');
        }

        $categories = Category::orderBy('name')->get();
        return view('coop.products.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $cooperative = $user->cooperative;

        if (!$cooperative || $cooperative->status !== 'approved') {
            return response()->json(['success' => false, 'message' => 'Coopérative non approuvée.'], 403);
        }

        $action = $request->input('action', 'save_draft');

        $rules = [
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'description' => 'required|string|max:2000',
            'price' => 'required|numeric|min:0|max:999999.99',
            'stock_quantity' => 'required|integer|min:0',
        ];

        if ($action === 'submit') {
            $rules['images'] = 'required|array|min:1|max:5';
            $rules['images.*'] = 'image|mimes:jpeg,png,jpg,webp|max:2048';
        } else {
            $rules['images'] = 'nullable|array|max:5';
            $rules['images.*'] = 'image|mimes:jpeg,png,jpg,webp|max:2048';
        }

        try {
            $validatedData = $request->validate($rules);

            DB::beginTransaction();

            $product = Product::create([
                'cooperative_id' => $cooperative->id,
                'category_id' => $validatedData['category_id'],
                'name' => $validatedData['name'],
                'description' => $validatedData['description'],
                'price' => $validatedData['price'],
                'stock_quantity' => $validatedData['stock_quantity'],
                'status' => $action === 'submit' ? 'pending' : 'draft',
                'submitted_at' => $action === 'submit' ? now() : null,
            ]);

            if ($request->hasFile('images')) {
                $results = ImageProcessingService::processMultipleImages(
                    $request->file('images'),
                    $product->id
                );

                if (!empty($results['errors'])) {
                    $errorMessages = array_map(function($error) {
                        return $error['file'] . ': ' . $error['error'];
                    }, $results['errors']);

                    Log::warning('Some images failed to process', $results['errors']);
                }
            }

            DB::commit();

            $message = $action === 'submit'
                ? 'Produit soumis avec succès pour approbation!'
                : 'Produit sauvegardé en brouillon!';

            return response()->json([
                'success' => true,
                'message' => $message,
                'redirect' => route('coop.products.index')
            ]);

        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erreurs de validation',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Product creation error', [
                'error' => $e->getMessage(),
                'user_id' => $user->id
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création du produit: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show(Product $product)
    {
        $user = Auth::user();

        if ($product->cooperative_id !== $user->cooperative_id) {
            abort(403, 'Accès non autorisé à ce produit.');
        }

        $product->load(['category', 'images', 'cooperative', 'reviewedBy', 'primaryImage']);

        return view('coop.products.show', compact('product'));
    }

    public function edit(Product $product)
    {
        $user = Auth::user();

        if ($product->cooperative_id !== $user->cooperative_id) {
            abort(403, 'Accès non autorisé à ce produit.');
        }

        if (!$product->canBeEdited()) {
            return redirect()->route('coop.products.index')
                ->with('error', 'Ce produit ne peut pas être modifié dans son état actuel.');
        }

        $categories = Category::orderBy('name')->get();
        $product->load(['images']);

        return view('coop.products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product)
    {
        $user = Auth::user();

        if ($product->cooperative_id !== $user->cooperative_id) {
            return response()->json(['success' => false, 'message' => 'Accès non autorisé.'], 403);
        }

        if (!$product->canBeEdited()) {
            return response()->json(['success' => false, 'message' => 'Ce produit ne peut pas être modifié.'], 403);
        }

        $action = $request->input('action', 'save_draft');

        $rules = [
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'description' => 'required|string|max:2000',
            'price' => 'required|numeric|min:0|max:999999.99',
            'stock_quantity' => 'required|integer|min:0',
            'new_images' => 'nullable|array|max:5',
            'new_images.*' => 'image|mimes:jpeg,png,jpg,webp|max:2048',
            'removed_images' => 'nullable|array',
            'removed_images.*' => 'integer|exists:product_images,id',
            'image_order' => 'nullable|array',
            'primary_image_id' => 'nullable|integer|exists:product_images,id',
        ];

        try {
            $validatedData = $request->validate($rules);

            DB::beginTransaction();

            // Check if only stock_quantity is being updated (for approved products)
            $isStockOnlyUpdate = false;
            if ($product->isApproved() && $action !== 'submit') {
                $currentData = [
                    'category_id' => $product->category_id,
                    'name' => $product->name,
                    'description' => $product->description,
                    'price' => floatval($product->price),
                ];

                $newData = [
                    'category_id' => intval($validatedData['category_id']),
                    'name' => $validatedData['name'],
                    'description' => $validatedData['description'],
                    'price' => floatval($validatedData['price']),
                ];

                $hasImageChanges = $request->hasFile('new_images') ||
                                 ($request->has('removed_images') && !empty($request->removed_images));

                // Check if only stock_quantity changed and no image changes
                $isStockOnlyUpdate = ($currentData === $newData) && !$hasImageChanges;
            }

            // Store original data only if it's not a stock-only update
            if ($product->isApproved() && !$product->isUpdatedVersion() && !$isStockOnlyUpdate) {
                $product->load('category');
                $product->storeOriginalData();
            }

            // Handle removed images
            if ($request->has('removed_images') && is_array($request->removed_images)) {
                ProductImage::whereIn('id', $request->removed_images)
                    ->where('product_id', $product->id)
                    ->delete(); // This will soft delete
            }

            // Handle new images
            if ($request->hasFile('new_images')) {
                $currentMaxOrder = $product->images()->max('sort_order') ?? -1;
                $results = ImageProcessingService::processMultipleImages(
                    $request->file('new_images'),
                    $product->id
                );
            }

            // Handle image reordering
            if ($request->has('image_order') && is_array($request->image_order)) {
                ImageProcessingService::reorderImages($product->id, $request->image_order);
            }

            // Update primary image
            if ($request->has('primary_image_id')) {
                $product->setPrimaryImage($request->primary_image_id);
            }

            // Check if we have at least one image (only if not stock-only update)
            if (!$isStockOnlyUpdate) {
                $product->updateImagesCount();
                if ($product->images_count === 0) {
                    throw new \Exception('Le produit doit avoir au moins une image.');
                }
            }

            // Update product
            $product->update([
                'category_id' => $validatedData['category_id'],
                'name' => $validatedData['name'],
                'description' => $validatedData['description'],
                'price' => $validatedData['price'],
                'stock_quantity' => $validatedData['stock_quantity'],
            ]);

            // Update status based on action - BUT skip if it's a stock-only update
            if ($action === 'submit') {
                $product->update([
                    'status' => 'pending',
                    'submitted_at' => now(),
                    'rejection_reason' => null,
                    'admin_notes' => null,
                    'reviewed_at' => null,
                    'reviewed_by' => null,
                ]);
            } else if ($product->isApproved() && !$isStockOnlyUpdate) {
                // Only change status if it's NOT a stock-only update
                $product->update([
                    'status' => 'pending',
                    'submitted_at' => now(),
                    'rejection_reason' => null,
                    'admin_notes' => null,
                    'reviewed_at' => null,
                    'reviewed_by' => null,
                ]);
            } else if (!$product->isApproved()) {
                $product->update(['status' => 'draft']);
            }
            // If it's a stock-only update for an approved product, we don't change the status at all

            DB::commit();

            $message = '';
            if ($isStockOnlyUpdate) {
                $message = 'Stock mis à jour avec succès!';
            } else if ($action === 'submit' || ($product->isApproved() && !$isStockOnlyUpdate)) {
                $message = 'Produit mis à jour et soumis pour ré-approbation!';
            } else {
                $message = 'Produit mis à jour!';
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'redirect' => route('coop.products.index')
            ]);

        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erreurs de validation',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Product update error', [
                'error' => $e->getMessage(),
                'product_id' => $product->id
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function submit(Request $request, Product $product)
    {
        $user = Auth::user();

        if ($product->cooperative_id !== $user->cooperative_id) {
            return response()->json(['success' => false, 'message' => 'Accès non autorisé.'], 403);
        }

        if (!$product->canBeSubmitted()) {
            return response()->json(['success' => false, 'message' => 'Ce produit ne peut pas être soumis.'], 400);
        }

        if (!$product->hasImages()) {
            return response()->json(['success' => false, 'message' => 'Le produit doit avoir au moins une image.'], 400);
        }

        try {
            $product->update([
                'status' => 'pending',
                'submitted_at' => now(),
                'rejection_reason' => null,
                'admin_notes' => null,
                'reviewed_at' => null,
                'reviewed_by' => null,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Produit soumis avec succès pour approbation!'
            ]);

        } catch (\Exception $e) {
            Log::error('Product submission error', [
                'error' => $e->getMessage(),
                'product_id' => $product->id
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la soumission du produit.'
            ], 500);
        }
    }

    public function destroy(Product $product)
    {
        $user = Auth::user();

        if ($product->cooperative_id !== $user->cooperative_id) {
            return response()->json(['success' => false, 'message' => 'Accès non autorisé.'], 403);
        }

        $statusMessages = [
            'draft' => 'Brouillon supprimé avec succès!',
            'pending' => 'Produit en attente supprimé avec succès!',
            'approved' => 'Produit approuvé supprimé avec succès!',
            'rejected' => 'Produit rejeté supprimé avec succès!',
            'needs_info' => 'Produit supprimé avec succès!'
        ];

        try {
            DB::beginTransaction();

            $statusMessage = $statusMessages[$product->status] ?? 'Produit supprimé avec succès!';

            // Soft delete all images (this will trigger the model events)
            $product->images()->delete();

            // Delete the product
            $product->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $statusMessage
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Product deletion error', [
                'error' => $e->getMessage(),
                'product_id' => $product->id
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression du produit.'
            ], 500);
        }
    }

    // New method for managing images via AJAX
    public function manageImages(Request $request, Product $product)
    {
        $user = Auth::user();

        if ($product->cooperative_id !== $user->cooperative_id) {
            return response()->json(['success' => false, 'message' => 'Accès non autorisé.'], 403);
        }

        $action = $request->input('action');

        try {
            DB::beginTransaction();

            switch ($action) {
                case 'reorder':
                    $imageIds = $request->input('image_ids', []);
                    ImageProcessingService::reorderImages($product->id, $imageIds);
                    $message = 'Images réorganisées avec succès!';
                    break;

                case 'set_primary':
                    $imageId = $request->input('image_id');
                    $product->setPrimaryImage($imageId);
                    $message = 'Image principale mise à jour!';
                    break;

                case 'delete':
                    $imageId = $request->input('image_id');
                    $image = $product->images()->find($imageId);
                    if ($image) {
                        $image->delete();
                        $message = 'Image supprimée avec succès!';
                    } else {
                        throw new \Exception('Image non trouvée.');
                    }
                    break;

                default:
                    throw new \Exception('Action non supportée.');
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $message
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
