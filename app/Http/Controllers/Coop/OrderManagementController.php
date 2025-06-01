<?php

namespace App\Http\Controllers\Coop;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderManagementController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('check.role:cooperative_admin');
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $cooperative = $user->cooperative;

        if (!$cooperative || $cooperative->status !== 'approved') {
            return redirect()->route('coop.dashboard')
                ->with('error', 'Votre coopérative doit être approuvée pour gérer les commandes.');
        }

        $status = $request->get('status', 'all');
        $search = $request->get('search', '');

        $query = Order::whereHas('orderItems', function($q) use ($cooperative) {
                $q->where('cooperative_id', $cooperative->id);
            })
            ->with(['user', 'orderItems.product', 'clientReceipt'])
            ->orderBy('created_at', 'desc');

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhereHas('user', function($sq) use ($search) {
                      $sq->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        $orders = $query->paginate(15);

        $statusCounts = [
            'all' => Order::whereHas('orderItems', function($q) use ($cooperative) {
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
            'cancelled' => Order::whereHas('orderItems', function($q) use ($cooperative) {
                $q->where('cooperative_id', $cooperative->id);
            })->where('status', 'cancelled')->count(),
        ];

        return view('coop.orders.index', compact('orders', 'status', 'search', 'statusCounts'));
    }

    public function show(Order $order)
    {
        $user = Auth::user();
        $cooperative = $user->cooperative;

        // Check if this order belongs to this cooperative
        if (!$order->orderItems()->where('cooperative_id', $cooperative->id)->exists()) {
            abort(403, 'Accès non autorisé à cette commande');
        }

        $order->load([
            'user',
            'orderItems.product.primaryImage',
            'clientReceipt.authorizationReceipts'
        ]);

        // Filter order items to only show items from this cooperative
        $order->orderItems = $order->orderItems->where('cooperative_id', $cooperative->id);

        return view('coop.orders.show', compact('order'));
    }

    public function updateStatus(Request $request, Order $order)
    {
        $user = Auth::user();
        $cooperative = $user->cooperative;

        // Check if this order belongs to this cooperative
        if (!$order->orderItems()->where('cooperative_id', $cooperative->id)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Accès non autorisé à cette commande'
            ], 403);
        }

        $request->validate([
            'status' => 'required|in:pending,ready,completed,cancelled',
            'notes' => 'nullable|string|max:500'
        ]);

        try {
            $updateData = ['status' => $request->status];

            if ($request->notes) {
                $updateData['notes'] = $request->notes;
            }

            if ($request->status === 'ready' && !$order->ready_at) {
                $updateData['ready_at'] = now();
            }

            if ($request->status === 'completed' && !$order->picked_up_at) {
                $updateData['picked_up_at'] = now();
                $updateData['picked_up_by'] = 'client'; // Default, can be updated later
            }

            $order->update($updateData);

            return response()->json([
                'success' => true,
                'message' => 'Statut de la commande mis à jour avec succès'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour du statut'
            ], 500);
        }
    }

    public function markPickedUp(Request $request, Order $order)
    {
        $user = Auth::user();
        $cooperative = $user->cooperative;

        // Check if this order belongs to this cooperative
        if (!$order->orderItems()->where('cooperative_id', $cooperative->id)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Accès non autorisé à cette commande'
            ], 403);
        }

        $request->validate([
            'picked_up_by' => 'required|in:client,authorized_person',
            'verification_code' => 'required|string',
            'notes' => 'nullable|string|max:500'
        ]);

        try {
            // Verify the receipt
            $clientReceipt = $order->clientReceipt;
            $isValid = false;

            if ($request->picked_up_by === 'client') {
                $isValid = $clientReceipt && $clientReceipt->verification_code === $request->verification_code;
            } else {
                // Check authorization receipt
                $authReceipt = $clientReceipt->authorizationReceipts()
                    ->where('unique_code', $request->verification_code)
                    ->where('is_revoked', false)
                    ->where('is_used', false)
                    ->where('validity_end', '>', now())
                    ->first();

                if ($authReceipt) {
                    $authReceipt->update(['is_used' => true, 'used_at' => now()]);
                    $isValid = true;
                }
            }

            if (!$isValid) {
                return response()->json([
                    'success' => false,
                    'message' => 'Code de vérification invalide'
                ], 400);
            }

            $order->update([
                'status' => 'completed',
                'picked_up_at' => now(),
                'picked_up_by' => $request->picked_up_by,
                'notes' => $request->notes
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Commande marquée comme récupérée avec succès'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la validation de la récupération'
            ], 500);
        }
    }
}
