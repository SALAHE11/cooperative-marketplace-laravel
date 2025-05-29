<?php
namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Cooperative;
use App\Models\CooperativeAdminRequest;
use App\Services\EmailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CooperativeRequestManagementController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('check.role:cooperative_admin');
    }

    /**
     * Get pending requests for the cooperative
     */
    public function getPendingRequests()
    {
        /** @var User $user */
        $user = Auth::user();

        if (!$user->isCooperativeAdmin() || !$user->cooperative) {
            return response()->json(['success' => false, 'message' => 'Accès non autorisé'], 403);
        }

        $requests = CooperativeAdminRequest::with(['user'])
            ->where('cooperative_id', $user->cooperative_id)
            ->where('status', 'pending')
            ->orderBy('requested_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'requests' => $requests->map(function($request) {
                return [
                    'id' => $request->id,
                    'user' => [
                        'id' => $request->user->id,
                        'full_name' => $request->user->getFullNameAttribute(),
                        'email' => $request->user->email,
                        'phone' => $request->user->phone,
                        'address' => $request->user->address,
                    ],
                    'message' => $request->message,
                    'requested_at' => $request->requested_at->format('d/m/Y à H:i'),
                    'requested_at_human' => $request->requested_at->diffForHumans(),
                ];
            })
        ]);
    }

    /**
     * Get current cooperative admins
     */
    public function getCurrentAdmins()
    {
        /** @var User $user */
        $user = Auth::user();

        if (!$user->isCooperativeAdmin() || !$user->cooperative) {
            return response()->json(['success' => false, 'message' => 'Accès non autorisé'], 403);
        }

        $admins = User::where('cooperative_id', $user->cooperative_id)
            ->where('role', 'cooperative_admin')
            ->where('status', 'active')
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'admins' => $admins->map(function($admin) {
                return [
                    'id' => $admin->id,
                    'full_name' => $admin->getFullNameAttribute(),
                    'email' => $admin->email,
                    'phone' => $admin->phone,
                    'joined_at' => $admin->created_at->format('d/m/Y'),
                    'last_login' => $admin->last_login_at ? $admin->last_login_at->format('d/m/Y H:i') : 'Jamais connecté',
                    'is_current_user' => $admin->id === Auth::id(),
                ];
            })
        ]);
    }

    /**
     * Approve a join request
     */
    public function approveRequest(Request $request, $requestId)
    {
        $validator = Validator::make($request->all(), [
            'response_message' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Données invalides',
                'errors' => $validator->errors()
            ], 422);
        }

        /** @var User $currentUser */
        $currentUser = Auth::user();

        if (!$currentUser->isCooperativeAdmin() || !$currentUser->cooperative) {
            return response()->json(['success' => false, 'message' => 'Accès non autorisé'], 403);
        }

        $joinRequest = CooperativeAdminRequest::with(['user'])
            ->where('id', $requestId)
            ->where('cooperative_id', $currentUser->cooperative_id)
            ->where('status', 'pending')
            ->first();

        if (!$joinRequest) {
            return response()->json(['success' => false, 'message' => 'Demande introuvable'], 404);
        }

        DB::beginTransaction();

        try {
            // Approve the request
            $joinRequest->approve($currentUser->id, $request->response_message);

            // Update the user
            $newAdmin = $joinRequest->user;
            $newAdmin->update([
                'cooperative_id' => $currentUser->cooperative_id,
                'status' => 'active',
            ]);

            // Send approval email
            $emailSent = EmailService::sendJoinRequestResponse(
                $newAdmin->email,
                $newAdmin->first_name,
                $currentUser->cooperative->name,
                'approved',
                $request->response_message
            );

            if (!$emailSent) {
                Log::warning('Failed to send approval email', [
                    'request_id' => $requestId,
                    'user_email' => $newAdmin->email
                ]);
            }

            DB::commit();

            Log::info('Join request approved', [
                'request_id' => $requestId,
                'approved_by' => $currentUser->id,
                'new_admin' => $newAdmin->id,
                'cooperative_id' => $currentUser->cooperative_id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Demande approuvée avec succès'
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Failed to approve join request', [
                'request_id' => $requestId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'approbation: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reject a join request
     */
    public function rejectRequest(Request $request, $requestId)
    {
        $validator = Validator::make($request->all(), [
            'response_message' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Le message de refus est requis',
                'errors' => $validator->errors()
            ], 422);
        }

        /** @var User $currentUser */
        $currentUser = Auth::user();

        if (!$currentUser->isCooperativeAdmin() || !$currentUser->cooperative) {
            return response()->json(['success' => false, 'message' => 'Accès non autorisé'], 403);
        }

        $joinRequest = CooperativeAdminRequest::with(['user'])
            ->where('id', $requestId)
            ->where('cooperative_id', $currentUser->cooperative_id)
            ->where('status', 'pending')
            ->first();

        if (!$joinRequest) {
            return response()->json(['success' => false, 'message' => 'Demande introuvable'], 404);
        }

        DB::beginTransaction();

        try {
            // Reject the request
            $joinRequest->reject($currentUser->id, $request->response_message);

            // Send rejection email
            $emailSent = EmailService::sendJoinRequestResponse(
                $joinRequest->user->email,
                $joinRequest->user->first_name,
                $currentUser->cooperative->name,
                'rejected',
                $request->response_message
            );

            if (!$emailSent) {
                Log::warning('Failed to send rejection email', [
                    'request_id' => $requestId,
                    'user_email' => $joinRequest->user->email
                ]);
            }

            DB::commit();

            Log::info('Join request rejected', [
                'request_id' => $requestId,
                'rejected_by' => $currentUser->id,
                'user_id' => $joinRequest->user->id,
                'cooperative_id' => $currentUser->cooperative_id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Demande rejetée avec succès'
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Failed to reject join request', [
                'request_id' => $requestId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du rejet: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Send clarification request
     */
    public function requestClarification(Request $request, $requestId)
    {
        $validator = Validator::make($request->all(), [
            'message' => 'required|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Le message est requis',
                'errors' => $validator->errors()
            ], 422);
        }

        /** @var User $currentUser */
        $currentUser = Auth::user();

        if (!$currentUser->isCooperativeAdmin() || !$currentUser->cooperative) {
            return response()->json(['success' => false, 'message' => 'Accès non autorisé'], 403);
        }

        $joinRequest = CooperativeAdminRequest::with(['user'])
            ->where('id', $requestId)
            ->where('cooperative_id', $currentUser->cooperative_id)
            ->where('status', 'pending')
            ->first();

        if (!$joinRequest) {
            return response()->json(['success' => false, 'message' => 'Demande introuvable'], 404);
        }

        try {
            // Send clarification email
            $emailSent = EmailService::sendClarificationRequest(
                $joinRequest->user->email,
                $joinRequest->user->first_name,
                $currentUser->cooperative->name,
                $currentUser->getFullNameAttribute(),
                $request->message
            );

            if (!$emailSent) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur lors de l\'envoi de l\'email'
                ], 500);
            }

            Log::info('Clarification requested', [
                'request_id' => $requestId,
                'requested_by' => $currentUser->id,
                'user_id' => $joinRequest->user->id,
                'cooperative_id' => $currentUser->cooperative_id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Demande de clarification envoyée avec succès'
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send clarification request', [
                'request_id' => $requestId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'envoi: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove an admin from cooperative (deactivate)
     */
    public function removeAdmin(Request $request, $adminId)
    {
        /** @var User $currentUser */
        $currentUser = Auth::user();

        if (!$currentUser->isCooperativeAdmin() || !$currentUser->cooperative) {
            return response()->json(['success' => false, 'message' => 'Accès non autorisé'], 403);
        }

        // Prevent self-removal
        if ($adminId == $currentUser->id) {
            return response()->json([
                'success' => false,
                'message' => 'Vous ne pouvez pas vous retirer vous-même'
            ], 400);
        }

        $adminToRemove = User::where('id', $adminId)
            ->where('cooperative_id', $currentUser->cooperative_id)
            ->where('role', 'cooperative_admin')
            ->where('status', 'active')
            ->first();

        if (!$adminToRemove) {
            return response()->json(['success' => false, 'message' => 'Administrateur introuvable'], 404);
        }

        try {
            // Deactivate the admin
            $adminToRemove->update([
                'status' => 'inactive',
                'cooperative_id' => null,
            ]);

            // Send notification email
            EmailService::sendAdminRemovedNotification(
                $adminToRemove->email,
                $adminToRemove->first_name,
                $currentUser->cooperative->name,
                $currentUser->getFullNameAttribute()
            );

            Log::info('Admin removed from cooperative', [
                'removed_admin_id' => $adminId,
                'removed_by' => $currentUser->id,
                'cooperative_id' => $currentUser->cooperative_id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Administrateur retiré avec succès'
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to remove admin', [
                'admin_id' => $adminId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du retrait: ' . $e->getMessage()
            ], 500);
        }
    }
}
