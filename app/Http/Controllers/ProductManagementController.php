<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\ProductImage;
use App\Services\ImageProcessingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
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

        $query = $cooperative->products()->with(['category', 'images']);

        // Apply status filter
        if ($status !== 'all') {
            $query->where('status', $status);
        }

        // Apply search filter
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $products = $query->orderBy('updated_at', 'desc')->paginate(12);

        // Get counts for tabs
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

        // Base validation rules
        $rules = [
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'description' => 'required|string|max:2000',
            'price' => 'required|numeric|min:0|max:999999.99',
            'stock_quantity' => 'required|integer|min:0',
        ];

        // For submission, images are required
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

            // Create product
            $product = new Product();
            $product->cooperative_id = $cooperative->id;
            $product->category_id = $validatedData['category_id'];
            $product->name = $validatedData['name'];
            $product->description = $validatedData['description'];
            $product->price = $validatedData['price'];
            $product->stock_quantity = $validatedData['stock_quantity'];
            $product->status = $action === 'submit' ? 'pending' : 'draft';

            if ($action === 'submit') {
                $product->submitted_at = now();
            }

            $product->save();

            // Handle images
            if ($request->hasFile('images')) {
                $this->processAndStoreImages($product, $request->file('images'));
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
                'user_id' => $user->id,
                'cooperative_id' => $cooperative->id
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création du produit.'
            ], 500);
        }
    }

    public function show(Product $product)
    {
        $user = Auth::user();

        // Check if the product belongs to the user's cooperative
        if ($product->cooperative_id !== $user->cooperative_id) {
            abort(403, 'Accès non autorisé à ce produit.');
        }

        $product->load(['category', 'images', 'cooperative', 'reviewedBy']);

        return view('coop.products.show', compact('product'));
    }

    public function edit(Product $product)
    {
        $user = Auth::user();

        // Check if the product belongs to the user's cooperative
        if ($product->cooperative_id !== $user->cooperative_id) {
            abort(403, 'Accès non autorisé à ce produit.');
        }

        // Check if product can be edited
        if (!$product->canBeEdited()) {
            return redirect()->route('coop.products.index')->with('error', 'Ce produit ne peut pas être modifié dans son état actuel.');
        }

        $categories = Category::orderBy('name')->get();
        $product->load(['images']);

        return view('coop.products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product)
    {
        $user = Auth::user();

        // Check if the product belongs to the user's cooperative
        if ($product->cooperative_id !== $user->cooperative_id) {
            return response()->json(['success' => false, 'message' => 'Accès non autorisé.'], 403);
        }

        // Check if product can be edited
        if (!$product->canBeEdited()) {
            return response()->json(['success' => false, 'message' => 'Ce produit ne peut pas être modifié.'], 403);
        }

        $action = $request->input('action', 'save_draft');

        // Base validation rules
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
        ];

        try {
            $validatedData = $request->validate($rules);

            DB::beginTransaction();

            // NEW: Store original data if this is an approved product being updated
            if ($product->isApproved() && !$product->isUpdatedVersion()) {
                $product->load('category'); // Ensure category is loaded
                $product->storeOriginalData();
            }

            // Handle removed images
            if ($request->has('removed_images') && is_array($request->removed_images)) {
                ProductImage::whereIn('id', $request->removed_images)
                    ->where('product_id', $product->id)
                    ->delete();
            }

            // Handle new images
            if ($request->hasFile('new_images')) {
                $this->processAndStoreImages($product, $request->file('new_images'));
            }

            // Check if we have at least one image
            $remainingImageCount = $product->images()->count();
            if ($remainingImageCount === 0) {
                throw new \Exception('Le produit doit avoir au moins une image.');
            }

            // Update product
            $product->category_id = $validatedData['category_id'];
            $product->name = $validatedData['name'];
            $product->description = $validatedData['description'];
            $product->price = $validatedData['price'];
            $product->stock_quantity = $validatedData['stock_quantity'];

            // NEW: Update status based on current status and action
            if ($action === 'submit') {
                $product->status = 'pending';
                $product->submitted_at = now();
                $product->rejection_reason = null;
                $product->admin_notes = null;
                $product->reviewed_at = null;
                $product->reviewed_by = null;
            } else {
                // If it was approved and we're saving as draft, change status to pending for re-review
                if ($product->isApproved()) {
                    $product->status = 'pending';
                    $product->submitted_at = now();
                    $product->rejection_reason = null;
                    $product->admin_notes = null;
                    $product->reviewed_at = null;
                    $product->reviewed_by = null;
                } else {
                    $product->status = 'draft';
                }
            }

            $product->save();

            DB::commit();

            $message = ($action === 'submit' || $product->wasChanged('status'))
                ? 'Produit mis à jour et soumis pour ré-approbation!'
                : 'Produit mis à jour!';

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
                'product_id' => $product->id,
                'user_id' => $user->id
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

        // Check if the product belongs to the user's cooperative
        if ($product->cooperative_id !== $user->cooperative_id) {
            return response()->json(['success' => false, 'message' => 'Accès non autorisé.'], 403);
        }

        // Check if product can be submitted
        if (!$product->canBeSubmitted()) {
            return response()->json(['success' => false, 'message' => 'Ce produit ne peut pas être soumis.'], 400);
        }

        // Check if product has at least one image
        if ($product->images()->count() === 0) {
            return response()->json(['success' => false, 'message' => 'Le produit doit avoir au moins une image.'], 400);
        }

        try {
            $product->status = 'pending';
            $product->submitted_at = now();
            $product->rejection_reason = null;
            $product->admin_notes = null;
            $product->reviewed_at = null;
            $product->reviewed_by = null;
            $product->save();

            return response()->json([
                'success' => true,
                'message' => 'Produit soumis avec succès pour approbation!'
            ]);

        } catch (\Exception $e) {
            Log::error('Product submission error', [
                'error' => $e->getMessage(),
                'product_id' => $product->id,
                'user_id' => $user->id
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

        // Check if the product belongs to the user's cooperative
        if ($product->cooperative_id !== $user->cooperative_id) {
            return response()->json(['success' => false, 'message' => 'Accès non autorisé.'], 403);
        }

        // NEW: Allow deletion of all product types, but with different confirmations
        $statusMessages = [
            'draft' => 'Brouillon supprimé avec succès!',
            'pending' => 'Produit en attente supprimé avec succès!',
            'approved' => 'Produit approuvé supprimé avec succès!',
            'rejected' => 'Produit rejeté supprimé avec succès!',
            'needs_info' => 'Produit supprimé avec succès!'
        ];

        try {
            DB::beginTransaction();

            // Store product name for message
            $productName = $product->name;
            $statusMessage = $statusMessages[$product->status] ?? 'Produit supprimé avec succès!';

            // Delete all images and their files
            foreach ($product->images as $image) {
                $image->delete(); // This will trigger the model's boot method to delete files
            }

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
                'product_id' => $product->id,
                'user_id' => $user->id,
                'product_status' => $product->status
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression du produit.'
            ], 500);
        }
    }

    /**
     * Process and store images for a product
     */
    private function processAndStoreImages(Product $product, array $images)
    {
        $existingImageCount = $product->images()->count();
        $isFirstImage = $existingImageCount === 0;

        foreach ($images as $index => $image) {
            try {
                $paths = ImageProcessingService::processProductImage($image, $product->id, $index + $existingImageCount);

                ProductImage::create([
                    'product_id' => $product->id,
                    'image_path' => $paths['original_path'],
                    'thumbnail_path' => $paths['thumbnail_path'],
                    'is_primary' => $isFirstImage && $index === 0,
                    'sort_order' => $existingImageCount + $index,
                    'alt_text' => $product->name . ' - Image ' . ($index + 1)
                ]);
            } catch (\Exception $e) {
                Log::error('Image processing failed', [
                    'product_id' => $product->id,
                    'image_index' => $index,
                    'error' => $e->getMessage()
                ]);
                // Continue with other images even if one fails
            }
        }

        // Ensure we have at least one primary image
        if ($isFirstImage && $product->images()->count() > 0) {
            $firstImage = $product->images()->orderBy('sort_order')->first();
            if ($firstImage && !$firstImage->is_primary) {
                $firstImage->update(['is_primary' => true]);
            }
        }
    }
}
