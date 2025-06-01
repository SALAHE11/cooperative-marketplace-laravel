<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Cooperative;
use App\Models\Product;
use App\Models\Category;
use App\Models\Order;
use App\Models\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * System Admin Dashboard
     */
    public function adminDashboard()
    {
        /** @var User $user */
        $user = Auth::user();

        if (!$user->isSystemAdmin()) {
            abort(403, 'Accès non autorisé.');
        }

        // Get comprehensive statistics for admin dashboard
        $stats = [
            'cooperatives' => [
                'total' => Cooperative::count(),
                'approved' => Cooperative::where('status', 'approved')->count(),
                'pending' => Cooperative::where('status', 'pending')->count(),
                'rejected' => Cooperative::where('status', 'rejected')->count(),
                'suspended' => Cooperative::where('status', 'suspended')->count(),
            ],
            'users' => [
                'total' => User::count(),
                'clients' => User::where('role', 'client')->count(),
                'coop_admins' => User::where('role', 'cooperative_admin')->count(),
                'system_admins' => User::where('role', 'system_admin')->count(),
                'verified' => User::whereNotNull('email_verified_at')->count(),
                'active_today' => User::where('last_login_at', '>=', now()->subDay())->count(),
            ],
            'products' => [
                'total' => Product::whereIn('status', ['pending', 'approved', 'rejected', 'needs_info'])->count(),
                'pending' => Product::where('status', 'pending')->count(),
                'approved' => Product::where('status', 'approved')->count(),
                'rejected' => Product::where('status', 'rejected')->count(),
                'needs_info' => Product::where('status', 'needs_info')->count(),
                'active' => Product::where('status', 'approved')->where('is_active', true)->count(),
            ],
            'categories' => [
                'total' => Category::count(),
                'with_products' => Category::whereHas('products')->count(),
                'root_categories' => Category::whereNull('parent_id')->count(),
            ],
            'orders' => [
                'total' => Order::count(),
                'today' => Order::whereDate('created_at', now()->toDateString())->count(),
                'this_week' => Order::where('created_at', '>=', now()->startOfWeek())->count(),
                'this_month' => Order::whereMonth('created_at', now()->month)->count(),
                'pending' => Order::where('status', 'pending')->count(),
                'completed' => Order::where('status', 'completed')->count(),
            ],
            'revenue' => [
                'total' => Order::where('payment_status', 'paid')->sum('total_amount'),
                'today' => Order::where('payment_status', 'paid')
                    ->whereDate('created_at', now()->toDateString())
                    ->sum('total_amount'),
                'this_week' => Order::where('payment_status', 'paid')
                    ->where('created_at', '>=', now()->startOfWeek())
                    ->sum('total_amount'),
                'this_month' => Order::where('payment_status', 'paid')
                    ->whereMonth('created_at', now()->month)
                    ->sum('total_amount'),
            ]
        ];

        // Get recent activities for dashboard
        $recentActivities = [
            'pending_cooperatives' => Cooperative::with('primaryAdmin')
                ->where('status', 'pending')
                ->latest()
                ->take(5)
                ->get(),
            'pending_products' => Product::with(['cooperative', 'category', 'images'])
                ->where('status', 'pending')
                ->latest('submitted_at')
                ->take(8)
                ->get(),
            'recent_orders' => Order::with(['user', 'orderItems.cooperative'])
                ->latest()
                ->take(5)
                ->get(),
            'recent_users' => User::whereNotNull('email_verified_at')
                ->latest()
                ->take(5)
                ->get(),
            'recent_categories' => Category::latest()->take(3)->get(),
        ];

        // Monthly statistics for charts
        $monthlyStats = [
            'cooperatives' => $this->getMonthlyStats(Cooperative::class),
            'users' => $this->getMonthlyStats(User::class, ['role' => 'client']),
            'orders' => $this->getMonthlyStats(Order::class),
            'revenue' => $this->getMonthlyRevenue(),
        ];

        return view('dashboards.admin', compact('stats', 'recentActivities', 'monthlyStats'));
    }

    /**
     * Cooperative Admin Dashboard
     */
    public function coopDashboard()
    {
        /** @var User $user */
        $user = Auth::user();

        if (!$user->isCooperativeAdmin()) {
            abort(403, 'Accès non autorisé.');
        }

        $cooperative = $user->cooperative;
        $stats = [];
        $recentProducts = collect();
        $lowStockProducts = collect();
        $outOfStockProducts = collect();
        $recentOrders = collect();
        $orderStats = [];

        if ($cooperative && $cooperative->status === 'approved') {
            // Product statistics
            $stats = [
                'products' => [
                    'total' => $cooperative->products()->count(),
                    'approved' => $cooperative->products()->where('status', 'approved')->count(),
                    'pending' => $cooperative->products()->where('status', 'pending')->count(),
                    'draft' => $cooperative->products()->where('status', 'draft')->count(),
                    'rejected' => $cooperative->products()->where('status', 'rejected')->count(),
                    'needs_info' => $cooperative->products()->where('status', 'needs_info')->count(),
                    'active' => $cooperative->products()->where('status', 'approved')->where('is_active', true)->count(),
                    'low_stock' => $cooperative->products()
                        ->where('status', 'approved')
                        ->whereRaw('stock_quantity <= stock_alert_threshold')
                        ->count(),
                    'out_of_stock' => $cooperative->products()
                        ->where('status', 'approved')
                        ->where('stock_quantity', 0)
                        ->count(),
                ]
            ];

            // Order statistics
            $orderStats = [
                'total' => Order::whereHas('orderItems', function($q) use ($cooperative) {
                    $q->where('cooperative_id', $cooperative->id);
                })->count(),
                'pending' => Order::whereHas('orderItems', function($q) use ($cooperative) {
                    $q->where('cooperative_id', $cooperative->id);
                })->where('status', 'pending')->count(),
                'ready' => Order::whereHas('orderItems', function($q) use ($cooperative) {
                    $q->where('cooperative_id', $cooperative->id);
                })->where('status', 'ready')->count(),
                'completed' => Order::whereHas('orderItems', function($q) use ($cooperative) {
                    $q->where('cooperative_id', $cooperative->id);
                })->where('status', 'completed')->count(),
                'today' => Order::whereHas('orderItems', function($q) use ($cooperative) {
                    $q->where('cooperative_id', $cooperative->id);
                })->whereDate('created_at', now()->toDateString())->count(),
                'this_week' => Order::whereHas('orderItems', function($q) use ($cooperative) {
                    $q->where('cooperative_id', $cooperative->id);
                })->where('created_at', '>=', now()->startOfWeek())->count(),
            ];

            // Revenue statistics
            $stats['revenue'] = [
                'total' => Order::whereHas('orderItems', function($q) use ($cooperative) {
                    $q->where('cooperative_id', $cooperative->id);
                })->where('payment_status', 'paid')->sum('total_amount'),
                'this_month' => Order::whereHas('orderItems', function($q) use ($cooperative) {
                    $q->where('cooperative_id', $cooperative->id);
                })->where('payment_status', 'paid')
                    ->whereMonth('created_at', now()->month)
                    ->sum('total_amount'),
                'last_month' => Order::whereHas('orderItems', function($q) use ($cooperative) {
                    $q->where('cooperative_id', $cooperative->id);
                })->where('payment_status', 'paid')
                    ->whereMonth('created_at', now()->subMonth()->month)
                    ->sum('total_amount'),
            ];

            // Get recent products
            $recentProducts = $cooperative->products()
                ->with(['category', 'images'])
                ->orderBy('updated_at', 'desc')
                ->take(10)
                ->get();

            // Get low stock products
            $lowStockProducts = $cooperative->products()
                ->where('status', 'approved')
                ->whereRaw('stock_quantity <= stock_alert_threshold')
                ->where('stock_quantity', '>', 0)
                ->orderBy('stock_quantity', 'asc')
                ->take(10)
                ->get();

            // Get out of stock products
            $outOfStockProducts = $cooperative->products()
                ->where('status', 'approved')
                ->where('stock_quantity', 0)
                ->orderBy('updated_at', 'desc')
                ->take(5)
                ->get();

            // Get recent orders
            $recentOrders = Order::whereHas('orderItems', function($q) use ($cooperative) {
                    $q->where('cooperative_id', $cooperative->id);
                })
                ->with(['user', 'orderItems' => function($q) use ($cooperative) {
                    $q->where('cooperative_id', $cooperative->id)->with('product');
                }])
                ->orderBy('created_at', 'desc')
                ->take(10)
                ->get();
        }

        return view('dashboards.coop', compact(
            'cooperative', 'stats', 'orderStats', 'recentProducts',
            'lowStockProducts', 'outOfStockProducts', 'recentOrders'
        ));
    }

    /**
     * Client Dashboard
     */
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
                'total' => $user->orders()->count(),
                'pending' => $user->orders()->where('status', 'pending')->count(),
                'ready' => $user->orders()->where('status', 'ready')->count(),
                'completed' => $user->orders()->where('status', 'completed')->count(),
                'cancelled' => $user->orders()->where('status', 'cancelled')->count(),
            ],
            'favorites' => [
                'products' => 0, // To be implemented with favorites system
                'cooperatives' => 0,
            ],
            'spending' => [
                'total' => $user->orders()->where('payment_status', 'paid')->sum('total_amount'),
                'this_month' => $user->orders()
                    ->where('payment_status', 'paid')
                    ->whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year)
                    ->sum('total_amount'),
                'last_month' => $user->orders()
                    ->where('payment_status', 'paid')
                    ->whereMonth('created_at', now()->subMonth()->month)
                    ->whereYear('created_at', now()->subMonth()->year)
                    ->sum('total_amount'),
            ],
            'cart' => [
                'items' => 0,
                'total' => 0,
            ]
        ];

        // Get cart information
        $cart = Cart::where('user_id', $user->id)->first();
        if ($cart) {
            $stats['cart']['items'] = $cart->total_items;
            $stats['cart']['total'] = $cart->total_amount;
        }

        // Get recent products (last 7 days) and featured products
        $recentProducts = Product::with(['cooperative', 'category', 'primaryImage'])
            ->where('status', 'approved')
            ->where('is_active', true)
            ->where('created_at', '>=', now()->subDays(7))
            ->whereHas('cooperative', function($query) {
                $query->where('status', 'approved');
            })
            ->orderBy('created_at', 'desc')
            ->take(8)
            ->get();

        // Get featured products if not enough recent products
        if ($recentProducts->count() < 8) {
            $additionalProducts = Product::with(['cooperative', 'category', 'primaryImage'])
                ->where('status', 'approved')
                ->where('is_active', true)
                ->whereNotIn('id', $recentProducts->pluck('id'))
                ->whereHas('cooperative', function($query) {
                    $query->where('status', 'approved');
                })
                ->inRandomOrder()
                ->take(8 - $recentProducts->count())
                ->get();

            $featuredProducts = $recentProducts->concat($additionalProducts);
        } else {
            $featuredProducts = $recentProducts;
        }

        // Get active cooperatives with product counts
        $activeCooperatives = Cooperative::where('status', 'approved')
            ->withCount(['products' => function($query) {
                $query->where('status', 'approved')->where('is_active', true);
            }])
            ->having('products_count', '>', 0)
            ->orderBy('products_count', 'desc')
            ->take(6)
            ->get();

        // Get user's recent orders
        $recentOrders = $user->orders()
            ->with(['orderItems.product.primaryImage', 'orderItems.cooperative'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // Get popular categories
        $popularCategories = Category::withCount(['products' => function($query) {
                $query->where('status', 'approved')->where('is_active', true);
            }])
            ->having('products_count', '>', 0)
            ->orderBy('products_count', 'desc')
            ->take(6)
            ->get();

        return view('dashboards.client', compact(
            'stats', 'featuredProducts', 'activeCooperatives',
            'recentOrders', 'popularCategories', 'cart'
        ));
    }

    /**
     * Get monthly statistics for charts
     */
    private function getMonthlyStats($model, $conditions = [])
    {
        $query = $model::query();

        foreach ($conditions as $field => $value) {
            $query->where($field, $value);
        }

        return $query->selectRaw('MONTH(created_at) as month, COUNT(*) as count')
            ->whereYear('created_at', now()->year)
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('count', 'month')
            ->toArray();
    }

    /**
     * Get monthly revenue statistics
     */
    private function getMonthlyRevenue()
    {
        return Order::selectRaw('MONTH(created_at) as month, SUM(total_amount) as revenue')
            ->where('payment_status', 'paid')
            ->whereYear('created_at', now()->year)
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('revenue', 'month')
            ->toArray();
    }

    /**
     * Get dashboard statistics API endpoint
     */
    public function getStats(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $type = $request->get('type', 'overview');

        if ($user->isSystemAdmin()) {
            return $this->getAdminStats($type);
        } elseif ($user->isCooperativeAdmin()) {
            return $this->getCoopStats($type);
        } elseif ($user->isClient()) {
            return $this->getClientStats($type);
        }

        return response()->json(['error' => 'Unauthorized'], 403);
    }

    /**
     * Get admin statistics for API
     */
    private function getAdminStats($type)
    {
        $stats = [];

        switch ($type) {
            case 'overview':
                $stats = [
                    'cooperatives' => Cooperative::count(),
                    'users' => User::count(),
                    'products' => Product::count(),
                    'orders' => Order::count(),
                ];
                break;

            case 'monthly':
                $stats = [
                    'orders' => $this->getMonthlyStats(Order::class),
                    'revenue' => $this->getMonthlyRevenue(),
                    'users' => $this->getMonthlyStats(User::class),
                ];
                break;
        }

        return response()->json($stats);
    }

    /**
     * Get cooperative statistics for API
     */
    private function getCoopStats($type)
    {
        $user = Auth::user();
        $cooperative = $user->cooperative;

        if (!$cooperative) {
            return response()->json(['error' => 'No cooperative found'], 404);
        }

        $stats = [];

        switch ($type) {
            case 'overview':
                $stats = [
                    'products' => $cooperative->products()->count(),
                    'orders' => Order::whereHas('orderItems', function($q) use ($cooperative) {
                        $q->where('cooperative_id', $cooperative->id);
                    })->count(),
                    'revenue' => Order::whereHas('orderItems', function($q) use ($cooperative) {
                        $q->where('cooperative_id', $cooperative->id);
                    })->where('payment_status', 'paid')->sum('total_amount'),
                ];
                break;
        }

        return response()->json($stats);
    }

    /**
     * Get client statistics for API
     */
    private function getClientStats($type)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $stats = [];

        switch ($type) {
            case 'overview':
                $stats = [
                    'orders' => $user->orders()->count(),
                    'spending' => $user->orders()->where('payment_status', 'paid')->sum('total_amount'),
                ];
                break;
        }

        return response()->json($stats);
    }
}
