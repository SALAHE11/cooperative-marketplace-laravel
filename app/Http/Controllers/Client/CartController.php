<?php
// =====================================================================================
// FILE: app/Http/Controllers/Client/CartController.php
// =====================================================================================

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CartController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('check.role:client');
    }

    private function getOrCreateCart()
    {
        return Cart::firstOrCreate(
            ['user_id' => Auth::id()],
            ['total_amount' => 0, 'total_items' => 0, 'last_activity' => now()]
        );
    }

    public function index()
    {
        $cart = $this->getOrCreateCart();
        $itemsByCooperative = $cart->getItemsByCooperative();

        return view('client.cart.index', compact('cart', 'itemsByCooperative'));
    }

    public function add(Request $request, Product $product)
    {
        try {
            // Check product availability
            if ($product->status !== 'approved' || !$product->is_active) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ce produit n\'est plus disponible'
                ], 404);
            }

            // Validate quantity
            $request->validate([
                'quantity' => 'required|integer|min:1|max:100'
            ]);

            $quantity = (int) $request->quantity;

            // Check stock availability
            if ($product->stock_quantity < $quantity) {
                return response()->json([
                    'success' => false,
                    'message' => 'Stock insuffisant. Seulement ' . $product->stock_quantity . ' disponible(s)'
                ], 400);
            }

            DB::beginTransaction();

            $cart = $this->getOrCreateCart();

            // Check if item already exists in cart
            $existingItem = $cart->items()->where('product_id', $product->id)->first();

            if ($existingItem) {
                $newQuantity = $existingItem->quantity + $quantity;

                // Check total quantity against stock
                if ($product->stock_quantity < $newQuantity) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Stock insuffisant. Vous avez déjà ' . $existingItem->quantity . ' dans votre panier'
                    ], 400);
                }

                $existingItem->update([
                    'quantity' => $newQuantity,
                    'subtotal' => $newQuantity * $product->price
                ]);

                Log::info('Cart item updated', [
                    'user_id' => Auth::id(),
                    'product_id' => $product->id,
                    'old_quantity' => $existingItem->quantity - $quantity,
                    'new_quantity' => $newQuantity
                ]);
            } else {
                $cart->items()->create([
                    'product_id' => $product->id,
                    'cooperative_id' => $product->cooperative_id,
                    'quantity' => $quantity,
                    'unit_price' => $product->price,
                    'subtotal' => $quantity * $product->price,
                    'product_snapshot' => [
                        'name' => $product->name,
                        'description' => $product->description,
                        'image_url' => $product->primary_image_url,
                        'cooperative_name' => $product->cooperative->name
                    ]
                ]);

                Log::info('Cart item added', [
                    'user_id' => Auth::id(),
                    'product_id' => $product->id,
                    'quantity' => $quantity
                ]);
            }

            $cart->updateTotals();
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Produit ajouté au panier avec succès',
                'cart_count' => $cart->getItemCount(),
                'cart_total' => number_format($cart->getTotalAmount(), 2)
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Quantité invalide',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Failed to add item to cart', [
                'user_id' => Auth::id(),
                'product_id' => $product->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'ajout au panier: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request)
    {
        try {
            $request->validate([
                'product_id' => 'required|exists:products,id',
                'quantity' => 'required|integer|min:0|max:100'
            ]);

            $productId = $request->product_id;
            $quantity = (int) $request->quantity;

            DB::beginTransaction();

            $cart = $this->getOrCreateCart();
            $item = $cart->items()->where('product_id', $productId)->first();

            if (!$item) {
                return response()->json([
                    'success' => false,
                    'message' => 'Article non trouvé dans le panier'
                ], 404);
            }

            if ($quantity <= 0) {
                $item->delete();
                Log::info('Cart item removed', [
                    'user_id' => Auth::id(),
                    'product_id' => $productId
                ]);
            } else {
                // Check stock availability
                if ($item->product && $item->product->stock_quantity < $quantity) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Stock insuffisant. Seulement ' . $item->product->stock_quantity . ' disponible(s)'
                    ], 400);
                }

                $item->update([
                    'quantity' => $quantity,
                    'subtotal' => $quantity * $item->unit_price
                ]);

                Log::info('Cart item quantity updated', [
                    'user_id' => Auth::id(),
                    'product_id' => $productId,
                    'new_quantity' => $quantity
                ]);
            }

            $cart->updateTotals();
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Panier mis à jour avec succès',
                'cart_count' => $cart->getItemCount(),
                'cart_total' => number_format($cart->getTotalAmount(), 2)
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Données invalides',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Failed to update cart item', [
                'user_id' => Auth::id(),
                'product_id' => $request->product_id ?? null,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour: ' . $e->getMessage()
            ], 500);
        }
    }

    public function remove(Request $request)
    {
        try {
            $request->validate([
                'product_id' => 'required|exists:products,id'
            ]);

            DB::beginTransaction();

            $cart = $this->getOrCreateCart();
            $item = $cart->items()->where('product_id', $request->product_id)->first();

            if (!$item) {
                return response()->json([
                    'success' => false,
                    'message' => 'Article non trouvé dans le panier'
                ], 404);
            }

            $item->delete();
            $cart->updateTotals();

            DB::commit();

            Log::info('Cart item removed', [
                'user_id' => Auth::id(),
                'product_id' => $request->product_id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Produit retiré du panier avec succès',
                'cart_count' => $cart->getItemCount(),
                'cart_total' => number_format($cart->getTotalAmount(), 2)
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Données invalides',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Failed to remove cart item', [
                'user_id' => Auth::id(),
                'product_id' => $request->product_id ?? null,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression: ' . $e->getMessage()
            ], 500);
        }
    }

    public function clear()
    {
        try {
            DB::beginTransaction();

            $cart = $this->getOrCreateCart();
            $cart->items()->delete();
            $cart->updateTotals();

            DB::commit();

            Log::info('Cart cleared', ['user_id' => Auth::id()]);

            return response()->json([
                'success' => true,
                'message' => 'Panier vidé avec succès'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Failed to clear cart', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du vidage du panier: ' . $e->getMessage()
            ], 500);
        }
    }

    public function count()
    {
        try {
            $cart = $this->getOrCreateCart();

            return response()->json([
                'count' => $cart->getItemCount(),
                'total' => number_format($cart->getTotalAmount(), 2)
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to get cart count', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'count' => 0,
                'total' => '0.00'
            ]);
        }
    }
}
