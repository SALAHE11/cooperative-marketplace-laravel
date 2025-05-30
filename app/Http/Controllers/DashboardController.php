<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Cooperative;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function adminDashboard()
    {
        /** @var User $user */
        $user = Auth::user();

        if (!$user->isSystemAdmin()) {
            abort(403, 'Accès non autorisé.');
        }

        // Get statistics for dashboard
        $stats = [
            'cooperatives' => [
                'total' => Cooperative::count(),
                'approved' => Cooperative::where('status', 'approved')->count(),
                'pending' => Cooperative::where('status', 'pending')->count(),
                'suspended' => Cooperative::where('status', 'suspended')->count(),
            ],
            'users' => [
                'total' => User::count(),
                'clients' => User::where('role', 'client')->count(),
                'coop_admins' => User::where('role', 'cooperative_admin')->count(),
                'system_admins' => User::where('role', 'system_admin')->count(),
                'verified' => User::whereNotNull('email_verified_at')->count(),
            ],
            'products' => [
                'total' => Product::whereIn('status', ['pending', 'approved', 'rejected', 'needs_info'])->count(),
                'pending' => Product::where('status', 'pending')->count(),
                'approved' => Product::where('status', 'approved')->count(),
                'rejected' => Product::where('status', 'rejected')->count(),
                'needs_info' => Product::where('status', 'needs_info')->count(),
            ],
            'categories' => [
                'total' => Category::count(),
                'with_products' => Category::whereHas('products')->count(),
                'root_categories' => Category::whereNull('parent_id')->count(),
            ]
        ];

        // Get recent activities
        $recentActivities = [
            'pending_cooperatives' => Cooperative::with('admin')
                ->where('status', 'pending')
                ->latest()
                ->take(5)
                ->get(),
            'pending_products' => Product::with(['cooperative', 'category', 'images'])
                ->where('status', 'pending')
                ->latest('submitted_at')
                ->take(5)
                ->get(),
            'recent_categories' => Category::latest()->take(3)->get(),
            'recent_cooperatives' => Cooperative::latest()->take(3)->get(),
        ];

        return view('dashboards.admin', compact('stats', 'recentActivities'));
    }

    public function coopDashboard()
    {
        /** @var User $user */
        $user = Auth::user();

        if (!$user->isCooperativeAdmin()) {
            abort(403, 'Accès non autorisé.');
        }

        // Get cooperative and product statistics
        $cooperative = $user->cooperative;
        $stats = [];

        if ($cooperative && $cooperative->status === 'approved') {
            $stats = [
                'products' => [
                    'total' => $cooperative->products()->count(),
                    'approved' => $cooperative->products()->where('status', 'approved')->count(),
                    'pending' => $cooperative->products()->where('status', 'pending')->count(),
                    'draft' => $cooperative->products()->where('status', 'draft')->count(),
                    'rejected' => $cooperative->products()->where('status', 'rejected')->count(),
                    'needs_info' => $cooperative->products()->where('status', 'needs_info')->count(),
                    'low_stock' => $cooperative->products()
                        ->where('status', 'approved')
                        ->where('stock_quantity', '<=', 5)
                        ->count(),
                ],
                'revenue' => [
                    'estimated_monthly' => $cooperative->products()
                        ->where('status', 'approved')
                        ->sum('price') * 0.85, // Estimated based on price
                ],
            ];

            // Get recent products
            $recentProducts = $cooperative->products()
                ->with(['category', 'images'])
                ->orderBy('updated_at', 'desc')
                ->take(5)
                ->get();

            // Get low stock products
            $lowStockProducts = $cooperative->products()
                ->where('status', 'approved')
                ->where('stock_quantity', '<=', 5)
                ->orderBy('stock_quantity', 'asc')
                ->take(5)
                ->get();
        } else {
            $recentProducts = collect();
            $lowStockProducts = collect();
        }

        return view('dashboards.coop', compact('cooperative', 'stats', 'recentProducts', 'lowStockProducts'));
    }

    public function clientDashboard()
    {
        /** @var User $user */
        $user = Auth::user();

        if (!$user->isClient()) {
            abort(403, 'Accès non autorisé.');
        }

        // Client dashboard statistics
        $stats = [
            'orders' => [
                'total' => 0, // Will be implemented with order system
                'pending' => 0,
                'completed' => 0,
                'cancelled' => 0,
            ],
            'favorites' => [
                'products' => 0, // Will be implemented with favorites system
                'cooperatives' => 0,
            ],
            'spending' => [
                'total' => 0, // Will be implemented with order system
                'this_month' => 0,
                'last_month' => 0,
            ]
        ];

        // Get featured products (approved products from active cooperatives)
        $featuredProducts = Product::with(['cooperative', 'category', 'images'])
            ->where('status', 'approved')
            ->where('is_active', true)
            ->whereHas('cooperative', function($query) {
                $query->where('status', 'approved');
            })
            ->inRandomOrder()
            ->take(8)
            ->get();

        // Get active cooperatives
        $activeCooperatives = Cooperative::where('status', 'approved')
            ->withCount(['products' => function($query) {
                $query->where('status', 'approved')->where('is_active', true);
            }])
            ->having('products_count', '>', 0)
            ->take(6)
            ->get();

        return view('dashboards.client', compact('stats', 'featuredProducts', 'activeCooperatives'));
    }
}
