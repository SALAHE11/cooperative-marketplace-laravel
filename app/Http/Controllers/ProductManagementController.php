<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Category;
use App\Models\User;
use App\Services\EmailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Intervention\Image\Facades\Image;
use App\Http\Controllers\Admin\ProductRequestManagementController;
class ProductManagementController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('check.role:cooperative_admin');
    }

    /**
     * Display products for the cooperative
     */
    public function index(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        if (!$user->cooperative || $user->cooperative->status !== 'approved') {
            return redirect()->route('coop.dashboard')
                           ->with('error', 'Votre coopérative doit être approuvée pour gérer les produits.');
        }

        $status = $request->get('status', 'all');
        $search = $request->get('search');

        $query = Product::with(['category', 'images', 'reviewedBy'])
                       ->where('cooperative_id', $user->cooperative->id);

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('description', 'like', '%' . $search . '%');
            });
        }

        $products = $query->orderBy('updated_at', 'desc')->paginate(12);

        // Get counts for badges
        $counts = [
            'all' => Product::where('cooperative_id', $user->cooperative->id)->count(),
            'draft' => Product::where('cooperative_id', $user->cooperative->id)->where('status', 'draft')->count(),
            'pending' => Product::where('cooperative_id', $user->cooperative->id)->where('status', 'pending')->count(),
            'approved' => Product::where('cooperative_id', $user->cooperative->id)->where('status', 'approved')->count(),
            'rejected' => Product::where('cooperative_id', $user->cooperative->id)->where('status', 'rejected')->count(),
            'needs_info' => Product::where('cooperative_id', $user->cooperative->id)->where('status', 'needs_info')->count(),
        ];

        return view('coop.products.index', compact('products', 'counts', 'status', 'search'));
    }

    /**
     * Show form to create new product
     */
    public function create()
    {
        /** @var User $user */
        $user = Auth::user();

        if (!$user->cooperative || $user->cooperative->status !== 'approved') {
            return redirect()->route('coop.dashboard')
                           ->with('error', 'Votre coopérative doit être approuvée pour ajouter des produits.');
        }

        $categories = Category::orderBy('name')->get();

        return view('coop.products.create', compact('categories'));
    }

    /**
     * Store new product
     */
    public function store(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        if (!$user->cooperative || $user->cooperative->status !== 'approved') {
            return response()->json([
                'success' => false,
                'message' => 'Votre coopérative doit être approuvée pour ajouter des produits.'
            ], 403);
        }

        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'description' => 'required|string|max:2000',
            'price' => 'required|numeric|min:0|max:999999.99',
            'stock_quantity' => 'required|integer|min:0',
            'images' => 'required|array|min:1|max:5',
            'images.*' => 'image|mimes:jpeg,png,jpg,webp|max:2048',
            'action' => 'required|in:save_draft,submit'
        ]);

        DB::beginTransaction();

        try {
            // Create product
            $product = Product::create([
                'cooperative_id' => $user->cooperative->id,
                'category_id' => $validated['category_id'],
                'name' => $validated['name'],
                'description' => $validated['description'],
                'price' => $validated['price'],
                'stock_quantity' => $validated['stock_quantity'],
                'status' => $validated['action'] === 'submit' ? 'pending' : 'draft',
                'is_active' => false,
                'submitted_at' => $validated['action'] === 'submit' ? now() : null,
            ]);

            // Handle image uploads
            $this->handleImageUploads($product, $request->file('images'));

            // Send notification if submitted
            if ($validated['action'] === 'submit') {
                $this->notifyAdminsNewProduct($product);
            }

            DB::commit();

            $message = $validated['action'] === 'submit'
                ? 'Produit soumis pour approbation avec succès!'
                : 'Produit sauvegardé en brouillon avec succès!';

            return response()->json([
                'success' => true,
                'message' => $message,
                'redirect' => route('coop.products.index')
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error creating product', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
                'cooperative_id' => $user->cooperative->id
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création du produit: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show form to edit product
     */
    public function edit(Product $product)
    {
        /** @var User $user */
        $user = Auth::user();

        if ($product->cooperative_id !== $user->cooperative->id) {
            abort(403, 'Accès non autorisé à ce produit.');
        }

        if (!$product->canBeEdited()) {
            return redirect()->route('coop.products.index')
                           ->with('error', 'Ce produit ne peut pas être modifié dans son état actuel.');
        }

        $categories = Category::orderBy('name')->get();
        $product->load('images', 'category');

        return view('coop.products.edit', compact('product', 'categories'));
    }

    /**
     * Update product
     */
    public function update(Request $request, Product $product)
    {
        /** @var User $user */
        $user = Auth::user();

        if ($product->cooperative_id !== $user->cooperative->id) {
            return response()->json([
                'success' => false,
                'message' => 'Accès non autorisé à ce produit.'
            ], 403);
        }

        if (!$product->canBeEdited()) {
            return response()->json([
                'success' => false,
                'message' => 'Ce produit ne peut pas être modifié dans son état actuel.'
            ], 400);
        }

        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'description' => 'required|string|max:2000',
            'price' => 'required|numeric|min:0|max:999999.99',
            'stock_quantity' => 'required|integer|min:0',
            'new_images' => 'nullable|array|max:5',
            'new_images.*' => 'image|mimes:jpeg,png,jpg,webp|max:2048',
            'removed_images' => 'nullable|array',
            'removed_images.*' => 'exists:product_images,id',
            'action' => 'required|in:save_draft,submit'
        ]);

        // Check that we'll have at least one image after removals and additions
        $currentImagesCount = $product->images()->count();
        $removedCount = $request->get('removed_images') ? count($request->get('removed_images')) : 0;
        $addedCount = $request->hasFile('new_images') ? count($request->file('new_images')) : 0;
        $finalCount = $currentImagesCount - $removedCount + $addedCount;

        if ($finalCount < 1) {
            return response()->json([
                'success' => false,
                'message' => 'Le produit doit avoir au moins une image.'
            ], 400);
        }

        DB::beginTransaction();

        try {
            // Update product
            $product->update([
                'category_id' => $validated['category_id'],
                'name' => $validated['name'],
                'description' => $validated['description'],
                'price' => $validated['price'],
                'stock_quantity' => $validated['stock_quantity'],
                'status' => $validated['action'] === 'submit' ? 'pending' : 'draft',
                'submitted_at' => $validated['action'] === 'submit' ? now() : null,
                'rejection_reason' => null, // Clear previous rejection reason
                'admin_notes' => null, // Clear previous admin notes
                'reviewed_at' => null,
                'reviewed_by' => null,
            ]);

            // Handle image removals
            if ($request->get('removed_images')) {
                $imagesToRemove = ProductImage::whereIn('id', $request->get('removed_images'))
                                            ->where('product_id', $product->id)
                                            ->get();

                foreach ($imagesToRemove as $image) {
                    $image->delete(); // This will trigger the model's boot method to delete files
                }
            }

            // Handle new image uploads
            if ($request->hasFile('new_images')) {
                $this->handleImageUploads($product, $request->file('new_images'));
            }

            // Ensure we have a primary image
            $this->ensurePrimaryImage($product);

            // Send notification if submitted
            if ($validated['action'] === 'submit') {
                $this->notifyAdminsNewProduct($product);
            }

            DB::commit();

            $message = $validated['action'] === 'submit'
                ? 'Produit soumis pour approbation avec succès!'
                : 'Produit mis à jour avec succès!';

            return response()->json([
                'success' => true,
                'message' => $message,
                'redirect' => route('coop.products.index')
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error updating product', [
                'error' => $e->getMessage(),
                'product_id' => $product->id,
                'user_id' => $user->id
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour du produit: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Submit draft product for approval
     */
    public function submit(Product $product)
    {
        /** @var User $user */
        $user = Auth::user();

        if ($product->cooperative_id !== $user->cooperative->id) {
            return response()->json([
                'success' => false,
                'message' => 'Accès non autorisé à ce produit.'
            ], 403);
        }

        if (!$product->canBeSubmitted()) {
            return response()->json([
                'success' => false,
                'message' => 'Ce produit ne peut pas être soumis dans son état actuel.'
            ], 400);
        }

        // Check if product has at least one image
        if ($product->images()->count() === 0) {
            return response()->json([
                'success' => false,
                'message' => 'Le produit doit avoir au moins une image pour être soumis.'
            ], 400);
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

            $this->notifyAdminsNewProduct($product);

            return response()->json([
                'success' => true,
                'message' => 'Produit soumis pour approbation avec succès!'
            ]);

        } catch (\Exception $e) {
            Log::error('Error submitting product', [
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

    /**
     * Delete product (only drafts)
     */
    public function destroy(Product $product)
    {
        /** @var User $user */
        $user = Auth::user();

        if ($product->cooperative_id !== $user->cooperative->id) {
            return response()->json([
                'success' => false,
                'message' => 'Accès non autorisé à ce produit.'
            ], 403);
        }

        if (!$product->isDraft()) {
            return response()->json([
                'success' => false,
                'message' => 'Seuls les brouillons peuvent être supprimés.'
            ], 400);
        }

        try {
            // Images will be automatically deleted via model events
            $product->delete();

            return response()->json([
                'success' => true,
                'message' => 'Produit supprimé avec succès!'
            ]);

        } catch (\Exception $e) {
            Log::error('Error deleting product', [
                'error' => $e->getMessage(),
                'product_id' => $product->id,
                'user_id' => $user->id
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression du produit.'
            ], 500);
        }
    }

    /**
     * Handle image uploads for a product
     */
    private function handleImageUploads(Product $product, array $images)
    {
        $currentImageCount = $product->images()->count();
        $isFirstUpload = $currentImageCount === 0;

        foreach ($images as $index => $image) {
            $path = $image->store('products/' . $product->id, 'public');

            // Create thumbnail
            $thumbnailPath = $this->createThumbnail($image, $product->id);

            ProductImage::create([
                'product_id' => $product->id,
                'image_path' => $path,
                'thumbnail_path' => $thumbnailPath,
                'is_primary' => $isFirstUpload && $index === 0, // First image of first upload is primary
                'sort_order' => $currentImageCount + $index,
            ]);
        }
    }

    /**
     * Create thumbnail for image
     */
    private function createThumbnail($image, $productId)
    {
        try {
            $thumbnailDir = 'products/' . $productId . '/thumbnails';
            Storage::makeDirectory('public/' . $thumbnailDir);

            $filename = time() . '_thumb_' . $image->getClientOriginalName();
            $thumbnailPath = $thumbnailDir . '/' . $filename;
            $fullThumbnailPath = storage_path('app/public/' . $thumbnailPath);

            // Create thumbnail using Intervention Image (if available) or fallback
            if (class_exists('Intervention\Image\Facades\Image')) {
                $img = Image::make($image->path());
                $img->resize(300, 300, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });
                $img->save($fullThumbnailPath);
            } else {
                // Fallback: copy original image
                copy($image->path(), $fullThumbnailPath);
            }

            return $thumbnailPath;
        } catch (\Exception $e) {
            Log::warning('Could not create thumbnail', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Ensure product has a primary image
     */
    private function ensurePrimaryImage(Product $product)
    {
        $hasPrimary = $product->images()->where('is_primary', true)->exists();

        if (!$hasPrimary) {
            $firstImage = $product->images()->orderBy('sort_order')->first();
            if ($firstImage) {
                $firstImage->update(['is_primary' => true]);
            }
        }
    }

    /**
     * Notify system admins of new product submission
     */
    private function notifyAdminsNewProduct(Product $product)
    {
        try {
            $systemAdmins = User::where('role', 'system_admin')->get();

            foreach ($systemAdmins as $admin) {
                EmailService::sendNotificationEmail(
                    $admin->email,
                    'Nouvelle demande de produit - ' . $product->cooperative->name,
                    $this->buildProductNotificationHtml($product),
                    $admin->first_name
                );
            }
        } catch (\Exception $e) {
            Log::error('Failed to send product notification emails', [
                'product_id' => $product->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Build HTML for product notification email
     */
    private function buildProductNotificationHtml(Product $product)
    {
        return "
            <h2>Nouvelle Demande de Produit</h2>
            <p>Une nouvelle demande d'ajout de produit a été soumise et nécessite votre approbation.</p>

            <div style='background: #f8f9fa; padding: 20px; border-radius: 5px; margin: 20px 0;'>
                <h3>Détails du Produit</h3>
                <p><strong>Nom:</strong> {$product->name}</p>
                <p><strong>Coopérative:</strong> {$product->cooperative->name}</p>
                <p><strong>Catégorie:</strong> {$product->category->name}</p>
                <p><strong>Prix:</strong> {$product->price} MAD</p>
                <p><strong>Stock:</strong> {$product->stock_quantity}</p>
                <p><strong>Date de soumission:</strong> {$product->submitted_at->format('d/m/Y H:i')}</p>
            </div>

            <p>Connectez-vous au tableau de bord administrateur pour examiner cette demande.</p>

            <div style='text-align: center; margin: 30px 0;'>
                <a href='" . route('admin.product-requests.index') . "'
                   style='background: #007bff; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; font-weight: bold;'>
                   Examiner les Demandes
                </a>
            </div>
        ";
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
}
