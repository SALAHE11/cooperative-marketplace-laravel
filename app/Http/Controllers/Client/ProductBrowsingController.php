<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\Cooperative;
use Illuminate\Http\Request;

class ProductBrowsingController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('check.role:client');
    }

    public function index(Request $request)
    {
        $search = $request->get('search', '');
        $category_id = $request->get('category_id', '');
        $cooperative_id = $request->get('cooperative_id', '');
        $min_price = $request->get('min_price', '');
        $max_price = $request->get('max_price', '');
        $sort = $request->get('sort', 'newest');
        $per_page = $request->get('per_page', 12);

        $query = Product::with(['cooperative', 'category', 'primaryImage'])
            ->where('status', 'approved')
            ->where('is_active', true)
            ->whereHas('cooperative', function($q) {
                $q->where('status', 'approved');
            });

        // Search by name or description
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filter by category
        if ($category_id) {
            $query->where('category_id', $category_id);
        }

        // Filter by cooperative
        if ($cooperative_id) {
            $query->where('cooperative_id', $cooperative_id);
        }

        // Filter by price range
        if ($min_price) {
            $query->where('price', '>=', $min_price);
        }
        if ($max_price) {
            $query->where('price', '<=', $max_price);
        }

        // Sorting
        switch ($sort) {
            case 'price_asc':
                $query->orderBy('price', 'asc');
                break;
            case 'price_desc':
                $query->orderBy('price', 'desc');
                break;
            case 'name_asc':
                $query->orderBy('name', 'asc');
                break;
            case 'name_desc':
                $query->orderBy('name', 'desc');
                break;
            case 'oldest':
                $query->orderBy('created_at', 'asc');
                break;
            default: // newest
                $query->orderBy('created_at', 'desc');
                break;
        }

        $products = $query->paginate($per_page);

        // Get filter options
        $categories = Category::whereHas('products', function($q) {
            $q->where('status', 'approved')->where('is_active', true);
        })->orderBy('name')->get();

        $cooperatives = Cooperative::where('status', 'approved')
            ->whereHas('products', function($q) {
                $q->where('status', 'approved')->where('is_active', true);
            })
            ->orderBy('name')->get();

        $priceRange = Product::where('status', 'approved')
            ->where('is_active', true)
            ->selectRaw('MIN(price) as min_price, MAX(price) as max_price')
            ->first();

        return view('client.products.index', compact(
            'products', 'categories', 'cooperatives', 'priceRange',
            'search', 'category_id', 'cooperative_id', 'min_price', 'max_price', 'sort', 'per_page'
        ));
    }

    public function show(Product $product)
    {
        if ($product->status !== 'approved' || !$product->is_active) {
            abort(404, 'Produit non disponible');
        }

        $product->load(['cooperative', 'category', 'images']);

        // Get related products
        $relatedProducts = Product::where('status', 'approved')
            ->where('is_active', true)
            ->where('id', '!=', $product->id)
            ->where(function($q) use ($product) {
                $q->where('category_id', $product->category_id)
                  ->orWhere('cooperative_id', $product->cooperative_id);
            })
            ->with(['primaryImage', 'cooperative'])
            ->take(4)
            ->get();

        return view('client.products.show', compact('product', 'relatedProducts'));
    }

    public function search(Request $request)
    {
        $query = $request->get('q', '');

        if (strlen($query) < 2) {
            return response()->json(['products' => []]);
        }

        $products = Product::where('status', 'approved')
            ->where('is_active', true)
            ->where(function($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('description', 'like', "%{$query}%");
            })
            ->with(['cooperative', 'primaryImage'])
            ->take(10)
            ->get()
            ->map(function($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'price' => $product->price,
                    'cooperative' => $product->cooperative->name,
                    'image' => $product->primaryImageUrl,
                    'url' => route('client.products.show', $product)
                ];
            });

        return response()->json(['products' => $products]);
    }
}
