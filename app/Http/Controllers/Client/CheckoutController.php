<?php
// =====================================================================================
// FILE: app/Http/Controllers/Client/CheckoutController.php
// =====================================================================================

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
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

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
        try {
            // Log the incoming request for debugging
            Log::info('Checkout process started', [
                'user_id' => Auth::id(),
                'request_data' => $request->all()
            ]);

            // Validate the request
            $validated = $request->validate([
                'client_phone' => 'required|string|max:20',
                'pickup_instructions' => 'nullable|string|max:500',
                'payment_method' => 'required|in:cash,card,bank_transfer',
                'card_number' => 'required_if:payment_method,card|nullable|string|size:16',
                'card_expiry' => 'required_if:payment_method,card|nullable|string|size:5',
                'card_cvv' => 'required_if:payment_method,card|nullable|string|size:3',
                'cardholder_name' => 'required_if:payment_method,card|nullable|string|max:100',
                'bank_reference' => 'required_if:payment_method,bank_transfer|nullable|string|max:50',
                'create_authorization_receipt' => 'nullable|boolean',
                'authorized_person_name' => 'required_if:create_authorization_receipt,true|nullable|string|max:100',
                'authorized_person_cin' => 'required_if:create_authorization_receipt,true|nullable|string|max:20',
                'authorization_validity_days' => 'required_if:create_authorization_receipt,true|nullable|integer|min:1|max:30'
            ]);

            // Get the cart
            $cart = Cart::where('user_id', Auth::id())->first();

            if (!$cart || $cart->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Votre panier est vide'
                ], 400);
            }

            // Get cart items by cooperative
            $itemsByCooperative = $cart->getItemsByCooperative();

            // Check availability again
            foreach ($itemsByCooperative as $items) {
                foreach ($items as $item) {
                    if (!$item->isAvailable()) {
                        return response()->json([
                            'success' => false,
                            'message' => "Le produit '{$item->getProductName()}' n'est plus disponible"
                        ], 400);
                    }
                }
            }

            DB::beginTransaction();

            $user = Auth::user();
            $orders = collect();
            $clientReceipts = collect();

            // Create separate orders for each cooperative
            foreach ($itemsByCooperative as $cooperativeId => $items) {
                $cooperative = $items->first()->cooperative;
                $orderTotal = $items->sum('subtotal');

                // Simulate payment processing
                $paymentReference = $this->simulatePayment($validated['payment_method'], $orderTotal);

                Log::info('Creating order', [
                    'cooperative_id' => $cooperativeId,
                    'total' => $orderTotal,
                    'payment_reference' => $paymentReference
                ]);

                // Create order
                $order = Order::create([
                    'user_id' => $user->id,
                    'order_number' => $this->generateOrderNumber(),
                    'status' => 'pending',
                    'total_amount' => $orderTotal,
                    'shipping_address' => $user->address ?? 'Retrait en coopérative',
                    'payment_method' => $validated['payment_method'],
                    'payment_status' => 'paid',
                    'notes' => trim(($validated['pickup_instructions'] ?? '') . "\n" . "Ref: {$paymentReference}")
                ]);

                Log::info('Order created', ['order_id' => $order->id, 'order_number' => $order->order_number]);

                // Create order items
                foreach ($items as $item) {
                    // Double-check stock before creating order item
                    if ($item->product->stock_quantity < $item->quantity) {
                        throw new \Exception("Stock insuffisant pour {$item->product->name}");
                    }

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

                    Log::info('Stock updated', [
                        'product_id' => $item->product_id,
                        'quantity_removed' => $item->quantity,
                        'new_stock' => $item->product->fresh()->stock_quantity
                    ]);
                }

                // Create client receipt
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

                Log::info('Client receipt created', [
                    'receipt_id' => $clientReceipt->id,
                    'receipt_number' => $clientReceipt->receipt_number
                ]);

                $orders->push($order);
                $clientReceipts->push($clientReceipt);

                // Create authorization receipt if requested
                if ($validated['create_authorization_receipt'] ?? false) {
                    Log::info('Creating authorization receipt', [
                        'client_receipt_id' => $clientReceipt->id,
                        'authorized_person_name' => $validated['authorized_person_name'],
                        'authorized_person_cin' => $validated['authorized_person_cin']
                    ]);

                    $authReceipt = AuthorizationReceipt::create([
                        'auth_number' => $this->generateAuthNumber(),
                        'client_receipt_id' => $clientReceipt->id,
                        'authorized_person_name' => $validated['authorized_person_name'],
                        'authorized_person_cin' => $validated['authorized_person_cin'], // FIXED: Now saving this field
                        'validity_start' => now(),
                        'validity_end' => now()->addDays((int) $validated['authorization_validity_days']),
                        'unique_code' => $this->generateUniqueCode(),
                        'qr_code_data' => $this->generateAuthQRCodeData(
                            $clientReceipt,
                            $validated['authorized_person_name'],
                            $validated['authorized_person_cin']
                        ),
                        'is_revoked' => false,
                        'is_used' => false
                    ]);

                    Log::info('Authorization receipt created', [
                        'auth_receipt_id' => $authReceipt->id,
                        'auth_number' => $authReceipt->auth_number,
                        'authorized_person_cin' => $authReceipt->authorized_person_cin
                    ]);
                }
            }

            // Clear cart
            $cart->clear();
            Log::info('Cart cleared for user', ['user_id' => $user->id]);

            DB::commit();

            Log::info('Checkout completed successfully', [
                'user_id' => $user->id,
                'orders_created' => $orders->count(),
                'total_amount' => $orders->sum('total_amount')
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Commande(s) passée(s) avec succès!',
                'orders' => $orders->pluck('id')->toArray(),
                'redirect' => route('client.checkout.success', ['orders' => $orders->pluck('id')->implode(',')])
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('Checkout validation failed', [
                'user_id' => Auth::id(),
                'errors' => $e->errors()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Données invalides',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Checkout process failed', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

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
        // Simulate payment processing delay
        usleep(500000); // 0.5 second delay

        switch ($method) {
            case 'card':
                return 'CARD_' . strtoupper(Str::random(8)) . '_' . time();
            case 'bank_transfer':
                return 'BANK_' . strtoupper(Str::random(8)) . '_' . time();
            default:
                return 'CASH_' . strtoupper(Str::random(8)) . '_' . time();
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

?>
