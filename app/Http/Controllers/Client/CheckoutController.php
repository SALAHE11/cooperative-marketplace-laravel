<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ClientReceipt;
use App\Models\AuthorizationReceipt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CheckoutController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('check.role:client');
    }

    public function show()
    {
        $cart = Cart::where('user_id', Auth::id())->first();

        if (!$cart || $cart->isEmpty()) {
            return redirect()->route('client.cart.index')
                ->with('error', 'Votre panier est vide');
        }

        $itemsByCooperative = $cart->getItemsByCooperative();

        // Check for any unavailable items
        $unavailableItems = [];
        foreach ($itemsByCooperative as $items) {
            foreach ($items as $item) {
                if (!$item->isAvailable()) {
                    $unavailableItems[] = $item;
                }
            }
        }

        if (!empty($unavailableItems)) {
            return redirect()->route('client.cart.index')
                ->with('error', 'Certains articles de votre panier ne sont plus disponibles');
        }

        return view('client.checkout.index', compact('cart', 'itemsByCooperative'));
    }

    public function process(Request $request)
    {
        $request->validate([
            'client_phone' => 'required|string|max:20',
            'pickup_instructions' => 'nullable|string|max:500',
            'payment_method' => 'required|in:cash,card,bank_transfer',
            'card_number' => 'required_if:payment_method,card|nullable|string|size:16',
            'card_expiry' => 'required_if:payment_method,card|nullable|string|size:5',
            'card_cvv' => 'required_if:payment_method,card|nullable|string|size:3',
            'cardholder_name' => 'required_if:payment_method,card|nullable|string|max:100',
            'bank_reference' => 'required_if:payment_method,bank_transfer|nullable|string|max:50',
            'create_authorization_receipt' => 'nullable|boolean',
            'authorized_person_name' => 'required_if:create_authorization_receipt,1|nullable|string|max:100',
            'authorized_person_cin' => 'required_if:create_authorization_receipt,1|nullable|string|max:20',
            'authorization_validity_days' => 'required_if:create_authorization_receipt,1|nullable|integer|min:1|max:30'
        ]);

        $cart = Cart::where('user_id', Auth::id())->first();

        if (!$cart || $cart->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Votre panier est vide'
            ], 400);
        }

        try {
            DB::beginTransaction();

            $user = Auth::user();
            $itemsByCooperative = $cart->getItemsByCooperative();
            $orders = collect();
            $clientReceipts = collect();

            // Create separate orders for each cooperative
            foreach ($itemsByCooperative as $cooperativeId => $items) {
                $cooperative = $items->first()->cooperative;
                $orderTotal = $items->sum('subtotal');

                // Simulate payment processing
                $paymentReference = $this->simulatePayment($request->payment_method, $orderTotal);

                // Create order using only existing fields
                $order = Order::create([
                    'user_id' => $user->id,
                    'order_number' => $this->generateOrderNumber(),
                    'status' => 'pending',
                    'total_amount' => $orderTotal,
                    'shipping_address' => $user->address ?? '',
                    'payment_method' => $request->payment_method,
                    'payment_status' => 'paid',
                    'notes' => $request->pickup_instructions ?: 'Commande passée via la plateforme'
                ]);

                // Create order items
                foreach ($items as $item) {
                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $item->product_id,
                        'cooperative_id' => $cooperativeId,
                        'quantity' => $item->quantity,
                        'unit_price' => $item->unit_price,
                        'subtotal' => $item->subtotal
                    ]);

                    // Update product stock
                    $item->product->decrement('stock_quantity', $item->quantity);
                }

                // Create client receipt using only existing fields
                $clientReceipt = ClientReceipt::create([
                    'receipt_number' => $this->generateReceiptNumber(),
                    'order_id' => $order->id,
                    'user_id' => $user->id,
                    'cooperative_id' => $cooperativeId,
                    'total_amount' => $orderTotal,
                    'verification_code' => $this->generateVerificationCode(),
                    'qr_code_data' => $this->generateQRCodeData($order, $user),
                    'is_void' => false
                ]);

                $orders->push($order);
                $clientReceipts->push($clientReceipt);

                // Create authorization receipt if requested
                if ($request->create_authorization_receipt) {
                    AuthorizationReceipt::create([
                        'auth_number' => $this->generateAuthNumber(),
                        'client_receipt_id' => $clientReceipt->id,
                        'authorized_person_name' => $request->authorized_person_name,
                        'validity_start' => now(),
                        'validity_end' => now()->addDays($request->authorization_validity_days),
                        'unique_code' => $this->generateUniqueCode(),
                        'qr_code_data' => $this->generateAuthQRCodeData($clientReceipt, $request->authorized_person_name, $request->authorized_person_cin),
                        'is_revoked' => false,
                        'is_used' => false
                    ]);
                }
            }

            // Clear cart
            $cart->clear();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Commande(s) passée(s) avec succès!',
                'orders' => $orders->pluck('id'),
                'redirect' => route('client.checkout.success', ['orders' => $orders->pluck('id')->implode(',')])
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du traitement de la commande: ' . $e->getMessage()
            ], 500);
        }
    }

    public function success(Request $request)
    {
        $orderIds = explode(',', $request->get('orders', ''));
        $orders = Order::whereIn('id', $orderIds)
            ->where('user_id', Auth::id())
            ->with(['orderItems.product', 'orderItems.cooperative', 'clientReceipt'])
            ->get();

        if ($orders->isEmpty()) {
            return redirect()->route('client.dashboard')
                ->with('error', 'Commandes introuvables');
        }

        return view('client.checkout.success', compact('orders'));
    }

    private function simulatePayment($method, $amount)
    {
        switch ($method) {
            case 'card':
                return 'CARD_' . strtoupper(Str::random(10));
            case 'bank_transfer':
                return 'BANK_' . strtoupper(Str::random(10));
            default:
                return 'CASH_' . strtoupper(Str::random(10));
        }
    }

    private function generateOrderNumber()
    {
        do {
            $number = 'ORD' . date('Ymd') . strtoupper(Str::random(6));
        } while (Order::where('order_number', $number)->exists());

        return $number;
    }

    private function generateReceiptNumber()
    {
        do {
            $number = 'REC' . date('Ymd') . strtoupper(Str::random(6));
        } while (ClientReceipt::where('receipt_number', $number)->exists());

        return $number;
    }

    private function generateVerificationCode()
    {
        return strtoupper(Str::random(8));
    }

    private function generateQRCodeData($order, $user)
    {
        return json_encode([
            'type' => 'client_receipt',
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'user_id' => $user->id,
            'user_name' => $user->full_name,
            'amount' => $order->total_amount,
            'timestamp' => now()->timestamp
        ]);
    }

    private function generateAuthNumber()
    {
        do {
            $number = 'AUTH' . date('Ymd') . strtoupper(Str::random(6));
        } while (AuthorizationReceipt::where('auth_number', $number)->exists());

        return $number;
    }

    private function generateUniqueCode()
    {
        return strtoupper(Str::random(12));
    }

    private function generateAuthQRCodeData($clientReceipt, $authorizedPersonName, $authorizedPersonCin)
    {
        return json_encode([
            'type' => 'authorization_receipt',
            'client_receipt_id' => $clientReceipt->id,
            'receipt_number' => $clientReceipt->receipt_number,
            'authorized_person_name' => $authorizedPersonName,
            'authorized_person_cin' => $authorizedPersonCin,
            'amount' => $clientReceipt->total_amount,
            'timestamp' => now()->timestamp
        ]);
    }
}
