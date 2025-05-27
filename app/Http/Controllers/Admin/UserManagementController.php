<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Cooperative;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Exception;

class UserManagementController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('check.role:system_admin');
    }

    /**
     * Display users management page
     */
    public function index(Request $request)
    {
        try {
            // Build query - now includes all users including system admins
            $query = User::with(['cooperative']);

            // Apply search filter
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%");
                });
            }

            // Apply status filter
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            // Apply role filter
            if ($request->filled('role')) {
                $query->where('role', $request->role);
            }

            // Apply email verification filter
            if ($request->filled('email_verified')) {
                if ($request->email_verified === 'verified') {
                    $query->whereNotNull('email_verified_at');
                } else {
                    $query->whereNull('email_verified_at');
                }
            }

            // Get users with pagination
            $users = $query->orderBy('created_at', 'desc')->paginate(15);

            // Calculate statistics - now includes all user types
            $stats = [
                'total' => User::count(),
                'active' => User::where('status', 'active')->count(),
                'pending' => User::where('status', 'pending')->count(),
                'suspended' => User::where('status', 'suspended')->count(),
                'clients' => User::where('role', 'client')->count(),
                'coop_admins' => User::where('role', 'cooperative_admin')->count(),
                'system_admins' => User::where('role', 'system_admin')->count(),
                'unverified' => User::whereNull('email_verified_at')->count(),
            ];

            return view('admin.users.index', compact('users', 'stats'));
        } catch (Exception $e) {
            Log::error('Error loading users index', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => Auth::id()
            ]);

            return redirect()->route('admin.dashboard')
                           ->with('error', 'Erreur lors du chargement des utilisateurs.');
        }
    }

    /**
     * Show user details
     */
    public function show(User $user)
    {
        try {
            // Load relationships
            $user->load(['cooperative', 'orders', 'reviews']);

            return view('admin.users.show', compact('user'));
        } catch (Exception $e) {
            Log::error('Error loading user details', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'viewed_by' => Auth::id()
            ]);

            return redirect()->route('admin.users.index')
                           ->with('error', 'Erreur lors du chargement des détails de l\'utilisateur.');
        }
    }

    /**
     * Update user status
     */
    public function updateStatus(Request $request, User $user)
    {
        try {
            // Prevent current admin from modifying their own status
            if ($user->id === Auth::id()) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Vous ne pouvez pas modifier votre propre statut.'
                    ], 403);
                }
                return back()->with('error', 'Vous ne pouvez pas modifier votre propre statut.');
            }

            $validator = Validator::make($request->all(), [
                'status' => 'required|in:active,pending,suspended'
            ], [
                'status.required' => 'Le statut est requis.',
                'status.in' => 'Le statut doit être actif, en attente ou suspendu.'
            ]);

            if ($validator->fails()) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Erreur de validation',
                        'errors' => $validator->errors()
                    ], 422);
                }
                return back()->withErrors($validator)->withInput();
            }

            $oldStatus = $user->status;
            $user->update(['status' => $request->status]);

            Log::info('User status updated', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'user_role' => $user->role,
                'old_status' => $oldStatus,
                'new_status' => $request->status,
                'updated_by' => Auth::id()
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Statut mis à jour avec succès!'
                ]);
            }

            return redirect()->route('admin.users.index')
                           ->with('success', 'Statut de l\'utilisateur mis à jour avec succès!');

        } catch (Exception $e) {
            Log::error('Error updating user status', [
                'user_id' => $user->id,
                'requested_status' => $request->status,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'updated_by' => Auth::id()
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur lors de la mise à jour du statut'
                ], 500);
            }

            return back()->with('error', 'Erreur lors de la mise à jour du statut.');
        }
    }

    /**
     * Activate all pending users
     */
    public function activateAllPending(Request $request)
    {
        try {
            $pendingUsers = User::where('status', 'pending')->get();

            if ($pendingUsers->isEmpty()) {
                return redirect()->route('admin.users.index')
                               ->with('info', 'Aucun utilisateur en attente à activer.');
            }

            $count = $pendingUsers->count();
            $userIds = $pendingUsers->pluck('id')->toArray();

            // Update all pending users to active
            User::where('status', 'pending')->update(['status' => 'active']);

            Log::info('All pending users activated', [
                'count' => $count,
                'user_ids' => $userIds,
                'activated_by' => Auth::id()
            ]);

            return redirect()->route('admin.users.index')
                           ->with('success', "{$count} utilisateur(s) activé(s) avec succès!");

        } catch (Exception $e) {
            Log::error('Error activating all pending users', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'activated_by' => Auth::id()
            ]);

            return redirect()->route('admin.users.index')
                           ->with('error', 'Erreur lors de l\'activation des utilisateurs.');
        }
    }

    /**
     * Suspend multiple users
     */
    public function suspendMultiple(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'user_ids' => 'required|array',
                'user_ids.*' => 'exists:users,id'
            ], [
                'user_ids.required' => 'Aucun utilisateur sélectionné.',
                'user_ids.array' => 'Format de données invalide.',
                'user_ids.*.exists' => 'Un ou plusieurs utilisateurs n\'existent pas.'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur de validation',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Remove current admin from the list to prevent self-suspension
            $currentAdminId = Auth::id();
            $userIds = array_filter($request->user_ids, function($id) use ($currentAdminId) {
                return $id != $currentAdminId;
            });

            if (empty($userIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aucun utilisateur valide sélectionné pour la suspension.'
                ], 400);
            }

            $users = User::whereIn('id', $userIds)->get();
            $count = $users->count();

            // Update selected users to suspended
            User::whereIn('id', $userIds)->update(['status' => 'suspended']);

            Log::info('Multiple users suspended', [
                'count' => $count,
                'user_ids' => $userIds,
                'suspended_by' => Auth::id()
            ]);

            return response()->json([
                'success' => true,
                'message' => "{$count} utilisateur(s) suspendu(s) avec succès!"
            ]);

        } catch (Exception $e) {
            Log::error('Error suspending multiple users', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'suspended_by' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suspension des utilisateurs.'
            ], 500);
        }
    }

    /**
     * Delete user account (soft delete)
     */
    public function deleteUser(Request $request, User $user)
    {
        try {
            // Prevent current admin from deleting their own account
            if ($user->id === Auth::id()) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Vous ne pouvez pas supprimer votre propre compte.'
                    ], 403);
                }
                return back()->with('error', 'Vous ne pouvez pas supprimer votre propre compte.');
            }

            // Store user info for logging before deletion
            $userInfo = [
                'id' => $user->id,
                'email' => $user->email,
                'role' => $user->role,
                'full_name' => $user->full_name
            ];

            // Soft delete the user
            $user->delete();

            Log::warning('User account deleted', [
                'deleted_user' => $userInfo,
                'deleted_by' => Auth::id()
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Utilisateur supprimé avec succès!'
                ]);
            }

            return redirect()->route('admin.users.index')
                           ->with('success', 'Utilisateur supprimé avec succès!');

        } catch (Exception $e) {
            Log::error('Error deleting user', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'deleted_by' => Auth::id()
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur lors de la suppression de l\'utilisateur.'
                ], 500);
            }

            return back()->with('error', 'Erreur lors de la suppression de l\'utilisateur.');
        }
    }

    /**
     * Export users data
     */
    public function exportUsers(Request $request)
    {
        try {
            $query = User::with(['cooperative']);

            // Apply same filters as index
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%");
                });
            }

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('role')) {
                $query->where('role', $request->role);
            }

            if ($request->filled('email_verified')) {
                if ($request->email_verified === 'verified') {
                    $query->whereNotNull('email_verified_at');
                } else {
                    $query->whereNull('email_verified_at');
                }
            }

            $users = $query->orderBy('created_at', 'desc')->get();

            // Prepare CSV data
            $csvData = [];
            $csvData[] = [
                'ID',
                'Prénom',
                'Nom',
                'Email',
                'Téléphone',
                'Rôle',
                'Statut',
                'Email Vérifié',
                'Coopérative',
                'Date d\'inscription',
                'Dernière connexion'
            ];

            foreach ($users as $user) {
                $csvData[] = [
                    $user->id,
                    $user->first_name,
                    $user->last_name,
                    $user->email,
                    $user->phone ?? '',
                    $user->role,
                    $user->status,
                    $user->email_verified_at ? 'Oui' : 'Non',
                    $user->cooperative ? $user->cooperative->name : '',
                    $user->created_at->format('d/m/Y H:i'),
                    $user->last_login_at ? $user->last_login_at->format('d/m/Y H:i') : ''
                ];
            }

            // Generate CSV content
            $filename = 'utilisateurs_' . date('Y-m-d_H-i-s') . '.csv';
            $handle = fopen('php://temp', 'r+');

            foreach ($csvData as $row) {
                fputcsv($handle, $row, ';'); // Use semicolon for French Excel compatibility
            }

            rewind($handle);
            $csvContent = stream_get_contents($handle);
            fclose($handle);

            Log::info('Users data exported', [
                'exported_by' => Auth::id(),
                'users_count' => count($users),
                'filename' => $filename
            ]);

            return response($csvContent)
                ->header('Content-Type', 'text/csv; charset=UTF-8')
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');

        } catch (Exception $e) {
            Log::error('Error exporting users', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'exported_by' => Auth::id()
            ]);

            return back()->with('error', 'Erreur lors de l\'export des données.');
        }
    }

    /**
     * Get user statistics for dashboard
     */
    public function getUserStats()
    {
        try {
            $stats = [
                'total' => User::count(),
                'active' => User::where('status', 'active')->count(),
                'pending' => User::where('status', 'pending')->count(),
                'suspended' => User::where('status', 'suspended')->count(),
                'clients' => User::where('role', 'client')->count(),
                'coop_admins' => User::where('role', 'cooperative_admin')->count(),
                'system_admins' => User::where('role', 'system_admin')->count(),
                'unverified' => User::whereNull('email_verified_at')->count(),
                'recent_registrations' => User::where('created_at', '>=', now()->subDays(7))->count(),
                'active_this_month' => User::where('last_login_at', '>=', now()->subDays(30))->count(),
            ];

            return response()->json([
                'success' => true,
                'stats' => $stats
            ]);

        } catch (Exception $e) {
            Log::error('Error getting user stats', [
                'error' => $e->getMessage(),
                'requested_by' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des statistiques.'
            ], 500);
        }
    }
}
