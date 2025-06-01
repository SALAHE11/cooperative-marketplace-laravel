<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'cart_id',
        'product_id',
        'cooperative_id',
        'quantity',
        'unit_price',
        'subtotal',
        'product_snapshot'
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'product_snapshot' => 'array'
    ];

    public function cart()
    {
        return $this->belongsTo(Cart::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function cooperative()
    {
        return $this->belongsTo(Cooperative::class);
    }

    public function isAvailable()
    {
        return $this->product &&
               $this->product->status === 'approved' &&
               $this->product->is_active &&
               $this->product->stock_quantity >= $this->quantity;
    }

    public function getProductName()
    {
        return $this->product ? $this->product->name : ($this->product_snapshot['name'] ?? 'Produit supprimé');
    }

    public function getProductImage()
    {
        return $this->product ? $this->product->primaryImageUrl : ($this->product_snapshot['image_url'] ?? null);
    }

    public function getCooperativeName()
    {
        return $this->cooperative ? $this->cooperative->name : ($this->product_snapshot['cooperative_name'] ?? 'Coopérative');
    }
}
