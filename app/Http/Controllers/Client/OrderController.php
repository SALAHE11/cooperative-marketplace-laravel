<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('check.role:client');
    }

    public function index(Request $request)
    {
        $status = $request->get('status', 'all');

        $query = Order::where('user_id', Auth::id())
            ->with(['orderItems.product', 'orderItems.cooperative', 'clientReceipt'])
            ->orderBy('created_at', 'desc');

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $orders = $query->paginate(10);

        $statusCounts = [
            'all' => Order::where('user_id', Auth::id())->count(),
            'pending' => Order::where('user_id', Auth::id())->where('status', 'pending')->count(),
            'ready' => Order::where('user_id', Auth::id())->where('status', 'ready')->count(),
            'completed' => Order::where('user_id', Auth::id())->where('status', 'completed')->count(),
            'cancelled' => Order::where('user_id', Auth::id())->where('status', 'cancelled')->count(),
        ];

        return view('client.orders.index', compact('orders', 'status', 'statusCounts'));
    }

    public function show(Order $order)
    {
        if ($order->user_id !== Auth::id()) {
            abort(403, 'Accès non autorisé à cette commande');
        }

        $order->load([
            'orderItems.product.primaryImage',
            'orderItems.cooperative',
            'clientReceipt.authorizationReceipts'
        ]);

        return view('client.orders.show', compact('order'));
    }
}
