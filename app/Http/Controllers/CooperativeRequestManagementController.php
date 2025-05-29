<?php
// ===== 1. UPDATED CONTROLLER WITH SIMPLIFIED QUERIES =====
// File: app/Http/Controllers/CooperativeRequestManagementController.php

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

        // Get active admins for this cooperative
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
     * FIXED: Get inactive cooperative admins - Simplified query
     */
    public function getInactiveAdmins()
    {
        /** @var User $user */
        $user = Auth::user();

        if (!$user->isCooperativeAdmin() || !$user->cooperative) {
            return response()->json(['success' => false, 'message' => 'Accès non autorisé'], 403);
        }

        // First, get all suspended users who are cooperative admins
        $baseQuery = User::where('role', 'cooperative_admin')
            ->where('status', 'suspended');

        // Check if the removal tracking columns exist
        try {
            // Test if the columns exist by running a small query
            DB::select('SELECT removed_from_coop_at FROM users LIMIT 1');

            // If no exception, columns exist - use the full query
            $inactiveAdmins = $baseQuery
                ->where(function($query) use ($user) {
                    $query->where('cooperative_id', $user->cooperative_id)
                          ->orWhere('removed_from_coop_at', '!=', null);
                })
                ->with(['removedBy'])
                ->orderBy('updated_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'inactive_admins' => $inactiveAdmins->map(function($admin) {
                    return [
                        'id' => $admin->id,
                        'full_name' => $admin->getFullNameAttribute(),
                        'email' => $admin->email,
                        'phone' => $admin->phone,
                        'joined_at' => $admin->created_at->format('d/m/Y'),
                        'removed_at' => isset($admin->removed_from_coop_at) ? $admin->removed_from_coop_at->format('d/m/Y à H:i') : 'Non défini',
                        'removed_at_human' => isset($admin->removed_from_coop_at) ? $admin->removed_from_coop_at->diffForHumans() : 'Non défini',
                        'removed_by' => isset($admin->removedBy) ? $admin->removedBy->getFullNameAttribute() : 'Système',
                        'removal_reason' => $admin->removal_reason ?? null,
                        'last_login' => $admin->last_login_at ? $admin->last_login_at->format('d/m/Y H:i') : 'Jamais connecté',
                    ];
                })
            ]);

        } catch (\Exception $e) {
            // Columns don't exist yet - use simple query
            Log::info('Removal tracking columns not found, using simple query', ['error' => $e->getMessage()]);

            $inactiveAdmins = $baseQuery
                ->where('cooperative_id', $user->cooperative_id)
                ->orderBy('updated_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'inactive_admins' => $inactiveAdmins->map(function($admin) {
                    return [
                        'id' => $admin->id,
                        'full_name' => $admin->getFullNameAttribute(),
                        'email' => $admin->email,
                        'phone' => $admin->phone,
                        'joined_at' => $admin->created_at->format('d/m/Y'),
                        'removed_at' => 'Migration requise',
                        'removed_at_human' => 'Migration requise',
                        'removed_by' => 'Système',
                        'removal_reason' => null,
                        'last_login' => $admin->last_login_at ? $admin->last_login_at->format('d/m/Y H:i') : 'Jamais connecté',
                    ];
                })
            ]);
        }
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

            // Update the user - clear any suspension fields
            $updateData = [
                'cooperative_id' => $currentUser->cooperative_id,
                'status' => 'active',
            ];

            // Only set removal fields if columns exist
            try {
                DB::select('SELECT removed_from_coop_at FROM users LIMIT 1');
                $updateData['removed_from_coop_at'] = null;
                $updateData['removed_by'] = null;
                $updateData['removal_reason'] = null;
            } catch (\Exception $e) {
                // Columns don't exist, skip them
            }

            $newAdmin = $joinRequest->user;
            $newAdmin->update($updateData);

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
     * FIXED: Remove an admin from cooperative - Uses 'suspended' status
     */
    public function removeAdmin(Request $request, $adminId)
    {
        $validator = Validator::make($request->all(), [
            'removal_reason' => 'nullable|string|max:500',
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
            // Prepare update data
            $updateData = [
                'status' => 'suspended', // FIXED: Use 'suspended' not 'inactive'
            ];

            // Only set removal tracking fields if columns exist
            try {
                DB::select('SELECT removed_from_coop_at FROM users LIMIT 1');
                $updateData['removed_from_coop_at'] = now();
                $updateData['removed_by'] = $currentUser->id;
                $updateData['removal_reason'] = $request->removal_reason;
            } catch (\Exception $e) {
                Log::info('Removal tracking columns not found, using basic suspension');
            }

            // Update the admin
            $adminToRemove->update($updateData);

            // Send notification email
            EmailService::sendAdminRemovedNotification(
                $adminToRemove->email,
                $adminToRemove->first_name,
                $currentUser->cooperative->name,
                $currentUser->getFullNameAttribute(),
                $request->removal_reason
            );

            Log::info('Admin removed from cooperative', [
                'removed_admin_id' => $adminId,
                'removed_by' => $currentUser->id,
                'cooperative_id' => $currentUser->cooperative_id,
                'removal_reason' => $request->removal_reason
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

    /**
     * FIXED: Reactivate an admin
     */
    public function reactivateAdmin(Request $request, $adminId)
    {
        $validator = Validator::make($request->all(), [
            'reactivation_message' => 'nullable|string|max:500',
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

        $adminToReactivate = User::where('id', $adminId)
            ->where('role', 'cooperative_admin')
            ->where('status', 'suspended')
            ->first();

        if (!$adminToReactivate) {
            return response()->json(['success' => false, 'message' => 'Administrateur introuvable'], 404);
        }

        try {
            // Prepare reactivation data
            $updateData = [
                'status' => 'active',
                'cooperative_id' => $currentUser->cooperative_id,
            ];

            // Only clear removal tracking fields if columns exist
            try {
                DB::select('SELECT removed_from_coop_at FROM users LIMIT 1');
                $updateData['removed_from_coop_at'] = null;
                $updateData['removed_by'] = null;
                $updateData['removal_reason'] = null;
            } catch (\Exception $e) {
                Log::info('Removal tracking columns not found');
            }

            // Reactivate the admin
            $adminToReactivate->update($updateData);

            // Send reactivation email
            EmailService::sendAdminReactivatedNotification(
                $adminToReactivate->email,
                $adminToReactivate->first_name,
                $currentUser->cooperative->name,
                $currentUser->getFullNameAttribute(),
                $request->reactivation_message
            );

            Log::info('Admin reactivated', [
                'reactivated_admin_id' => $adminId,
                'reactivated_by' => $currentUser->id,
                'cooperative_id' => $currentUser->cooperative_id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Administrateur réactivé avec succès'
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to reactivate admin', [
                'admin_id' => $adminId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la réactivation: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * NEW: Permanently remove an admin
     */
    public function permanentlyRemoveAdmin(Request $request, $adminId)
    {
        /** @var User $currentUser */
        $currentUser = Auth::user();

        if (!$currentUser->isCooperativeAdmin() || !$currentUser->cooperative) {
            return response()->json(['success' => false, 'message' => 'Accès non autorisé'], 403);
        }

        $adminToDelete = User::where('id', $adminId)
            ->where('role', 'cooperative_admin')
            ->where('status', 'suspended')
            ->first();

        if (!$adminToDelete) {
            return response()->json(['success' => false, 'message' => 'Administrateur introuvable'], 404);
        }

        try {
            // Prepare permanent removal data
            $updateData = [
                'role' => 'client', // Convert back to regular client
                'cooperative_id' => null,
                'status' => 'active', // Reactivate as regular client
            ];

            // Only clear removal tracking fields if columns exist
            try {
                DB::select('SELECT removed_from_coop_at FROM users LIMIT 1');
                $updateData['removed_from_coop_at'] = null;
                $updateData['removed_by'] = null;
                $updateData['removal_reason'] = null;
            } catch (\Exception $e) {
                Log::info('Removal tracking columns not found');
            }

            // Keep user account but completely remove from cooperative system
            $adminToDelete->update($updateData);

            // Delete related cooperative admin requests
            CooperativeAdminRequest::where('user_id', $adminId)
                ->where('cooperative_id', $currentUser->cooperative_id)
                ->delete();

            Log::info('Admin permanently removed', [
                'removed_admin_id' => $adminId,
                'removed_by' => $currentUser->id,
                'cooperative_id' => $currentUser->cooperative_id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Administrateur définitivement retiré de la coopérative'
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to permanently remove admin', [
                'admin_id' => $adminId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du retrait définitif: ' . $e->getMessage()
            ], 500);
        }
    }
}
