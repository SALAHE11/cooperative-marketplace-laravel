<?php
// =====================================================================================
// FILE: app/Http/Controllers/Client/ReceiptController.php
// =====================================================================================

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\ClientReceipt;
use App\Models\AuthorizationReceipt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ReceiptController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('check.role:client');
    }

    public function downloadClientReceipt(ClientReceipt $receipt)
    {
        if ($receipt->user_id !== Auth::id()) {
            abort(403, 'Accès non autorisé à ce reçu');
        }

        $receipt->load(['order.orderItems.product', 'cooperative', 'user']);

        return view('client.receipts.client-receipt', compact('receipt'));
    }

    public function downloadAuthorizationReceipt(AuthorizationReceipt $authReceipt)
    {
        if ($authReceipt->clientReceipt->user_id !== Auth::id()) {
            abort(403, 'Accès non autorisé à ce reçu d\'autorisation');
        }

        $authReceipt->load([
            'clientReceipt.order.orderItems.product',
            'clientReceipt.cooperative',
            'clientReceipt.user'
        ]);

        return view('client.receipts.authorization-receipt', compact('authReceipt'));
    }

    public function createAuthorizationReceipt(Request $request, ClientReceipt $receipt)
    {
        try {
            // Check ownership
            if ($receipt->user_id !== Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Accès non autorisé'
                ], 403);
            }

            // Log the request for debugging
            Log::info('Authorization receipt creation request', [
                'user_id' => Auth::id(),
                'receipt_id' => $receipt->id,
                'request_data' => $request->all()
            ]);

            // Validate the request
            $validated = $request->validate([
                'authorized_person_name' => 'required|string|max:100',
                'authorized_person_cin' => 'required|string|max:20',
                'validity_days' => 'required|integer|min:1|max:30'
            ]);

            // Check if authorization receipt already exists for this client receipt
            $existingAuth = $receipt->authorizationReceipts()
                ->where('is_revoked', false)
                ->where('is_used', false)
                ->where('validity_end', '>', now())
                ->first();

            if ($existingAuth) {
                return response()->json([
                    'success' => false,
                    'message' => 'Un reçu d\'autorisation valide existe déjà pour cette commande'
                ], 400);
            }

            // Create authorization receipt
            $authReceipt = AuthorizationReceipt::create([
                'auth_number' => $this->generateAuthNumber(),
                'client_receipt_id' => $receipt->id,
                'authorized_person_name' => $validated['authorized_person_name'],
                'validity_start' => now(),
                'validity_end' => now()->addDays($validated['validity_days']),
                'unique_code' => $this->generateUniqueCode(),
                'qr_code_data' => $this->generateAuthQRCodeData(
                    $receipt,
                    $validated['authorized_person_name'],
                    $validated['authorized_person_cin']
                ),
                'is_revoked' => false,
                'is_used' => false
            ]);

            Log::info('Authorization receipt created successfully', [
                'auth_receipt_id' => $authReceipt->id,
                'auth_number' => $authReceipt->auth_number,
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Reçu d\'autorisation créé avec succès',
                'auth_receipt_id' => $authReceipt->id,
                'download_url' => route('client.receipts.authorization', $authReceipt)
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('Authorization receipt validation failed', [
                'user_id' => Auth::id(),
                'receipt_id' => $receipt->id,
                'errors' => $e->errors()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Données invalides',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            Log::error('Authorization receipt creation failed', [
                'user_id' => Auth::id(),
                'receipt_id' => $receipt->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création du reçu d\'autorisation: ' . $e->getMessage()
            ], 500);
        }
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
        do {
            $code = strtoupper(Str::random(12));
        } while (AuthorizationReceipt::where('unique_code', $code)->exists());

        return $code;
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
