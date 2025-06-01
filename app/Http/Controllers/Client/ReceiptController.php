<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\ClientReceipt;
use App\Models\AuthorizationReceipt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use PDF; // Assuming you'll use a PDF library

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

        // For now, return a view. In production, you'd generate a PDF
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

        // For now, return a view. In production, you'd generate a PDF
        return view('client.receipts.authorization-receipt', compact('authReceipt'));
    }

    public function createAuthorizationReceipt(Request $request, ClientReceipt $receipt)
    {
        if ($receipt->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Accès non autorisé'
            ], 403);
        }

        $request->validate([
            'authorized_person_name' => 'required|string|max:100',
            'authorized_person_cin' => 'required|string|max:20',
            'validity_days' => 'required|integer|min:1|max:30'
        ]);

        // Check if authorization receipt already exists for this client receipt
        if ($receipt->authorizationReceipts()->where('is_revoked', false)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Un reçu d\'autorisation existe déjà pour cette commande'
            ], 400);
        }

        try {
            $authReceipt = AuthorizationReceipt::create([
                'auth_number' => $this->generateAuthNumber(),
                'client_receipt_id' => $receipt->id,
                'authorized_person_name' => $request->authorized_person_name,
                'validity_start' => now(),
                'validity_end' => now()->addDays($request->validity_days),
                'unique_code' => $this->generateUniqueCode(),
                'qr_code_data' => $this->generateAuthQRCodeData($receipt, $request->authorized_person_name, $request->authorized_person_cin),
                'is_revoked' => false,
                'is_used' => false
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Reçu d\'autorisation créé avec succès',
                'download_url' => route('client.receipts.authorization', $authReceipt)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création du reçu d\'autorisation'
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
