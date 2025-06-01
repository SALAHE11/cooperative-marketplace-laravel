<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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
        if ($product->status !== 'approved' || !$product->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Ce produit n\'est plus disponible'
            ], 404);
        }

        $request->validate([
            'quantity' => 'required|integer|min:1|max:100'
        ]);

        try {
            $cart = $this->getOrCreateCart();
            $cart->addItem($product->id, $request->quantity);

            return response()->json([
                'success' => true,
                'message' => 'Produit ajouté au panier',
                'cart_count' => $cart->getItemCount(),
                'cart_total' => number_format($cart->getTotalAmount(), 2)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function update(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:0|max:100'
        ]);

        try {
            $cart = $this->getOrCreateCart();
            $cart->updateItemQuantity($request->product_id, $request->quantity);

            return response()->json([
                'success' => true,
                'message' => 'Panier mis à jour',
                'cart_count' => $cart->getItemCount(),
                'cart_total' => number_format($cart->getTotalAmount(), 2)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function remove(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id'
        ]);

        try {
            $cart = $this->getOrCreateCart();
            $cart->removeItem($request->product_id);

            return response()->json([
                'success' => true,
                'message' => 'Produit retiré du panier',
                'cart_count' => $cart->getItemCount(),
                'cart_total' => number_format($cart->getTotalAmount(), 2)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function clear()
    {
        try {
            $cart = $this->getOrCreateCart();
            $cart->clear();

            return response()->json([
                'success' => true,
                'message' => 'Panier vidé'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function count()
    {
        $cart = $this->getOrCreateCart();

        return response()->json([
            'count' => $cart->getItemCount(),
            'total' => number_format($cart->getTotalAmount(), 2)
        ]);
    }
}
