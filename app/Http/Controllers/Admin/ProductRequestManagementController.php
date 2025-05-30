<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\User;
use App\Services\EmailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProductRequestManagementController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('check.role:system_admin');
    }

    /**
     * Display product requests management page
     */
    public function index(Request $request)
    {
        $status = $request->get('status', 'pending');
        $search = $request->get('search');
        $cooperative = $request->get('cooperative');

        $query = Product::with(['cooperative', 'category', 'images', 'reviewedBy'])
                       ->whereIn('status', ['pending', 'approved', 'rejected', 'needs_info']);

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('description', 'like', '%' . $search . '%')
                  ->orWhereHas('cooperative', function($cq) use ($search) {
                      $cq->where('name', 'like', '%' . $search . '%');
                  });
            });
        }

        if ($cooperative) {
            $query->where('cooperative_id', $cooperative);
        }

        $products = $query->orderBy('submitted_at', 'desc')->paginate(12);

        // Get counts for badges
        $counts = [
            'all' => Product::whereIn('status', ['pending', 'approved', 'rejected', 'needs_info'])->count(),
            'pending' => Product::where('status', 'pending')->count(),
            'approved' => Product::where('status', 'approved')->count(),
            'rejected' => Product::where('status', 'rejected')->count(),
            'needs_info' => Product::where('status', 'needs_info')->count(),
        ];

        // Get cooperatives for filter
        $cooperatives = \App\Models\Cooperative::where('status', 'approved')->orderBy('name')->get();

        return view('admin.product-requests.index', compact('products', 'counts', 'status', 'search', 'cooperative', 'cooperatives'));
    }

    /**
     * Show product request details
     */
    public function show(Product $product)
    {
        if (!in_array($product->status, ['pending', 'approved', 'rejected', 'needs_info'])) {
            abort(404, 'Demande de produit non trouvée.');
        }

        $product->load(['cooperative', 'category', 'images', 'reviewedBy']);

        return view('admin.product-requests.show', compact('product'));
    }

   

    /**
     * Reject product request
     */
    public function reject(Request $request, Product $product)
    {
        /** @var User $user */
        $user = Auth::user();

        if ($product->status !== 'pending' && $product->status !== 'needs_info') {
            return response()->json([
                'success' => false,
                'message' => 'Ce produit ne peut pas être rejeté dans son état actuel.'
            ], 400);
        }

        $request->validate([
            'rejection_reason' => 'required|string|min:10|max:1000'
        ]);

        DB::beginTransaction();

        try {
            $product->update([
                'status' => 'rejected',
                'is_active' => false,
                'reviewed_at' => now(),
                'reviewed_by' => $user->id,
                'rejection_reason' => $request->rejection_reason,
                'admin_notes' => $request->admin_notes,
            ]);

            // Send rejection email
            $this->sendRejectionEmail($product, $request->rejection_reason);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Produit '{$product->name}' rejeté."
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error rejecting product', [
                'product_id' => $product->id,
                'error' => $e->getMessage(),
                'user_id' => $user->id
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du rejet: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Request more information about product
     */
    public function requestInfo(Request $request, Product $product)
    {
        /** @var User $user */
        $user = Auth::user();

        if ($product->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Des informations ne peuvent être demandées que pour les produits en attente.'
            ], 400);
        }

        $request->validate([
            'info_requested' => 'required|string|min:10|max:1000'
        ]);

        DB::beginTransaction();

        try {
            $product->update([
                'status' => 'needs_info',
                'reviewed_at' => now(),
                'reviewed_by' => $user->id,
                'admin_notes' => $request->info_requested,
            ]);

            // Send info request email
            $this->sendInfoRequestEmail($product, $request->info_requested);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Demande d\'informations envoyée avec succès!'
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error requesting product info', [
                'product_id' => $product->id,
                'error' => $e->getMessage(),
                'user_id' => $user->id
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'envoi: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get product images for modal display
     */
    public function getImages(Product $product)
    {
        $images = $product->images()->orderBy('sort_order')->get()->map(function($image) {
            return [
                'id' => $image->id,
                'url' => $image->image_url,
                'thumbnail' => $image->thumbnail_url,
                'is_primary' => $image->is_primary,
                'alt_text' => $image->alt_text,
            ];
        });

        return response()->json([
            'success' => true,
            'images' => $images
        ]);
    }

    /**
     * Send product approval email
     */
    private function sendApprovalEmail(Product $product, $adminNotes = null)
    {
        $cooperative = $product->cooperative;
        $adminEmails = $cooperative->activeAdmins()->pluck('email');

        $notesHtml = $adminNotes ? "
            <div style='background: #d4edda; border: 1px solid #c3e6cb; padding: 15px; border-radius: 5px; margin: 20px 0;'>
                <h4>Notes de l'administrateur:</h4>
                <p>{$adminNotes}</p>
            </div>
        " : '';

        $subject = 'Produit approuvé - ' . $product->name;
        $message = "
            <h2>Produit Approuvé!</h2>
            <p>Félicitations! Votre produit a été approuvé et est maintenant visible sur la plateforme.</p>

            <div style='background: #f8f9fa; padding: 20px; border-radius: 5px; margin: 20px 0;'>
                <h3>Détails du Produit</h3>
                <p><strong>Nom:</strong> {$product->name}</p>
                <p><strong>Catégorie:</strong> {$product->category->name}</p>
                <p><strong>Prix:</strong> {$product->price} MAD</p>
                <p><strong>Stock:</strong> {$product->stock_quantity}</p>
                <p><strong>Date d'approbation:</strong> {$product->reviewed_at->format('d/m/Y H:i')}</p>
            </div>

            {$notesHtml}

            <p>Votre produit est maintenant actif et visible par les clients sur la plateforme.</p>

            <div style='text-align: center; margin: 30px 0;'>
                <a href='" . route('coop.products.index') . "'
                   style='background: #28a745; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; font-weight: bold;'>
                   Voir Mes Produits
                </a>
            </div>
        ";

        foreach ($adminEmails as $email) {
            EmailService::sendNotificationEmail($email, $subject, $message);
        }
    }

    /**
     * Send product rejection email
     */
    private function sendRejectionEmail(Product $product, $rejectionReason)
    {
        $cooperative = $product->cooperative;
        $adminEmails = $cooperative->activeAdmins()->pluck('email');

        $subject = 'Produit rejeté - ' . $product->name;
        $message = "
            <h2>Produit Rejeté</h2>
            <p>Votre demande d'ajout de produit n'a pas pu être approuvée.</p>

            <div style='background: #f8f9fa; padding: 20px; border-radius: 5px; margin: 20px 0;'>
                <h3>Détails du Produit</h3>
                <p><strong>Nom:</strong> {$product->name}</p>
                <p><strong>Catégorie:</strong> {$product->category->name}</p>
                <p><strong>Prix:</strong> {$product->price} MAD</p>
                <p><strong>Date de rejet:</strong> {$product->reviewed_at->format('d/m/Y H:i')}</p>
            </div>

            <div style='background: #f8d7da; border: 1px solid #f5c6cb; padding: 15px; border-radius: 5px; margin: 20px 0;'>
                <h4>Raison du rejet:</h4>
                <p>{$rejectionReason}</p>
            </div>

            <p>Vous pouvez modifier votre produit en tenant compte des points mentionnés ci-dessus et le soumettre à nouveau.</p>

            <div style='text-align: center; margin: 30px 0;'>
                <a href='" . route('coop.products.edit', $product) . "'
                   style='background: #007bff; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; font-weight: bold;'>
                   Modifier le Produit
                </a>
            </div>
        ";

        foreach ($adminEmails as $email) {
            EmailService::sendNotificationEmail($email, $subject, $message);
        }
    }

    /**
     * Send info request email
     */
    private function sendInfoRequestEmail(Product $product, $infoRequested)
    {
        $cooperative = $product->cooperative;
        $adminEmails = $cooperative->activeAdmins()->pluck('email');

        $subject = 'Informations supplémentaires demandées - ' . $product->name;
        $message = "
            <h2>Informations Supplémentaires Demandées</h2>
            <p>Votre demande d'ajout de produit est en cours d'examen. Nous avons besoin d'informations supplémentaires.</p>

            <div style='background: #f8f9fa; padding: 20px; border-radius: 5px; margin: 20px 0;'>
                <h3>Détails du Produit</h3>
                <p><strong>Nom:</strong> {$product->name}</p>
                <p><strong>Catégorie:</strong> {$product->category->name}</p>
                <p><strong>Prix:</strong> {$product->price} MAD</p>
            </div>

            <div style='background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 5px; margin: 20px 0;'>
                <h4>Informations demandées:</h4>
                <p>{$infoRequested}</p>
            </div>

            <p>Veuillez modifier votre produit pour fournir les informations demandées, puis le soumettre à nouveau.</p>

            <div style='text-align: center; margin: 30px 0;'>
                <a href='" . route('coop.products.edit', $product) . "'
                   style='background: #ffc107; color: #212529; padding: 15px 30px; text-decoration: none; border-radius: 5px; font-weight: bold;'>
                   Modifier le Produit
                </a>
            </div>
        ";

        foreach ($adminEmails as $email) {
            EmailService::sendNotificationEmail($email, $subject, $message);
        }
    }

    public function approve(Request $request, Product $product)
{
    $request->validate([
        'admin_notes' => 'nullable|string|max:2000'
    ]);

    try {
        DB::beginTransaction();

        $product->status = 'approved';
        $product->admin_notes = $request->admin_notes;
        $product->reviewed_at = now();
        $product->reviewed_by = Auth::id();
        $product->rejection_reason = null;

        // NEW: Clear original_data when product is approved
        $product->original_data = null;

        $product->save();

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Produit approuvé avec succès!'
        ]);

    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Product approval error', [
            'error' => $e->getMessage(),
            'product_id' => $product->id,
            'admin_id' => Auth::id()
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Erreur lors de l\'approbation du produit.'
        ], 500);
    }
}
}
