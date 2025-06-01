<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

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
            $query->where('status', 'approved')->where('is_active', true);
        });
    }

    public function updateTotals()
    {
        $totals = $this->activeItems()
            ->selectRaw('SUM(quantity) as total_items, SUM(subtotal) as total_amount')
            ->first();

        $this->update([
            'total_items' => $totals->total_items ?? 0,
            'total_amount' => $totals->total_amount ?? 0,
            'last_activity' => now()
        ]);
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
                    'image_url' => $product->primaryImageUrl,
                    'cooperative_name' => $product->cooperative->name
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
            if ($item->product->stock_quantity < $quantity) {
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
            ->with(['product', 'cooperative'])
            ->get()
            ->groupBy('cooperative_id');
    }

    public function isEmpty()
    {
        return $this->total_items == 0;
    }

    public function getItemCount()
    {
        return $this->total_items;
    }

    public function getTotalAmount()
    {
        return $this->total_amount;
    }
}
