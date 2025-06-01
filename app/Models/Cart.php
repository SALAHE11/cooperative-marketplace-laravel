<?php
// =====================================================================================
// FILE: app/Models/Cart.php
// =====================================================================================

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Cart extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'session_data',
        'total_amount',
        'total_items',
        'last_activity'
    ];

    protected $casts = [
        'session_data' => 'array',
        'total_amount' => 'decimal:2',
        'total_items' => 'integer',
        'last_activity' => 'datetime'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(CartItem::class);
    }

    public function activeItems()
    {
        return $this->hasMany(CartItem::class)->whereHas('product', function($query) {
            $query->where('status', 'approved')
                  ->where('is_active', true)
                  ->where('stock_quantity', '>', 0);
        });
    }

    public function updateTotals()
    {
        try {
            $totals = $this->activeItems()
                ->selectRaw('SUM(quantity) as total_items, SUM(subtotal) as total_amount')
                ->first();

            $this->update([
                'total_items' => $totals->total_items ?? 0,
                'total_amount' => $totals->total_amount ?? 0.00,
                'last_activity' => now()
            ]);

            Log::debug('Cart totals updated', [
                'cart_id' => $this->id,
                'total_items' => $this->total_items,
                'total_amount' => $this->total_amount
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to update cart totals', [
                'cart_id' => $this->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function addItem($productId, $quantity = 1)
    {
        $product = Product::where('id', $productId)
            ->where('status', 'approved')
            ->where('is_active', true)
            ->first();

        if (!$product) {
            throw new \Exception('Produit non disponible');
        }

        if ($product->stock_quantity < $quantity) {
            throw new \Exception('Stock insuffisant');
        }

        $existingItem = $this->items()->where('product_id', $productId)->first();

        if ($existingItem) {
            $newQuantity = $existingItem->quantity + $quantity;
            if ($product->stock_quantity < $newQuantity) {
                throw new \Exception('Stock insuffisant pour cette quantité');
            }

            $existingItem->update([
                'quantity' => $newQuantity,
                'subtotal' => $newQuantity * $product->price
            ]);
        } else {
            $this->items()->create([
                'product_id' => $productId,
                'cooperative_id' => $product->cooperative_id,
                'quantity' => $quantity,
                'unit_price' => $product->price,
                'subtotal' => $quantity * $product->price,
                'product_snapshot' => [
                    'name' => $product->name,
                    'description' => $product->description,
                    'image_url' => $product->primary_image_url,
                    'cooperative_name' => $product->cooperative->name ?? 'N/A'
                ]
            ]);
        }

        $this->updateTotals();
        return true;
    }

    public function updateItemQuantity($productId, $quantity)
    {
        $item = $this->items()->where('product_id', $productId)->first();

        if (!$item) {
            throw new \Exception('Article non trouvé dans le panier');
        }

        if ($quantity <= 0) {
            $item->delete();
        } else {
            if ($item->product && $item->product->stock_quantity < $quantity) {
                throw new \Exception('Stock insuffisant');
            }

            $item->update([
                'quantity' => $quantity,
                'subtotal' => $quantity * $item->unit_price
            ]);
        }

        $this->updateTotals();
        return true;
    }

    public function removeItem($productId)
    {
        $this->items()->where('product_id', $productId)->delete();
        $this->updateTotals();
        return true;
    }

    public function clear()
    {
        $this->items()->delete();
        $this->updateTotals();
        return true;
    }

    public function getItemsByCooperative()
    {
        return $this->activeItems()
            ->with(['product.primaryImage', 'cooperative'])
            ->get()
            ->groupBy('cooperative_id');
    }

    public function isEmpty()
    {
        return $this->total_items == 0;
    }

    public function getItemCount()
    {
        return $this->total_items ?? 0;
    }

    public function getTotalAmount()
    {
        return $this->total_amount ?? 0.00;
    }

    /**
     * Get all unavailable items in the cart
     */
    public function getUnavailableItems()
    {
        return $this->items()->whereHas('product', function($query) {
            $query->where(function($q) {
                $q->where('status', '!=', 'approved')
                  ->orWhere('is_active', false);
            });
        })->orWhereDoesntHave('product')->get();
    }

    /**
     * Clean up unavailable items from cart
     */
    public function cleanupUnavailableItems()
    {
        $unavailableItems = $this->getUnavailableItems();

        foreach ($unavailableItems as $item) {
            $item->delete();
        }

        if ($unavailableItems->count() > 0) {
            $this->updateTotals();
        }

        return $unavailableItems->count();
    }

    /**
     * Check if cart has any items with stock issues
     */
    public function hasStockIssues()
    {
        return $this->items()->whereHas('product', function($query) {
            $query->whereRaw('products.stock_quantity < cart_items.quantity');
        })->exists();
    }

    /**
     * Get items with stock issues
     */
    public function getItemsWithStockIssues()
    {
        return $this->items()->whereHas('product', function($query) {
            $query->whereRaw('products.stock_quantity < cart_items.quantity');
        })->with('product')->get();
    }

    /**
     * Fix stock issues by adjusting quantities
     */
    public function fixStockIssues()
    {
        $itemsFixed = 0;
        $itemsWithIssues = $this->getItemsWithStockIssues();

        foreach ($itemsWithIssues as $item) {
            if ($item->product->stock_quantity > 0) {
                $item->update([
                    'quantity' => $item->product->stock_quantity,
                    'subtotal' => $item->product->stock_quantity * $item->unit_price
                ]);
                $itemsFixed++;
            } else {
                $item->delete();
                $itemsFixed++;
            }
        }

        if ($itemsFixed > 0) {
            $this->updateTotals();
        }

        return $itemsFixed;
    }

    /**
     * Boot method to handle model events
     */
    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($cart) {
            // Delete all cart items when cart is deleted
            $cart->items()->delete();
        });
    }
}
