<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Cooperative;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
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
            // Build query
            $query = User::with(['cooperative'])
                        ->where('role', '!=', 'system_admin'); // Don't show system admins

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

            // Calculate statistics
            $stats = [
                'total' => User::where('role', '!=', 'system_admin')->count(),
                'active' => User::where('role', '!=', 'system_admin')->where('status', 'active')->count(),
                'pending' => User::where('role', '!=', 'system_admin')->where('status', 'pending')->count(),
                'suspended' => User::where('role', '!=', 'system_admin')->where('status', 'suspended')->count(),
                'clients' => User::where('role', 'client')->count(),
                'coop_admins' => User::where('role', 'cooperative_admin')->count(),
                'unverified' => User::where('role', '!=', 'system_admin')->whereNull('email_verified_at')->count(),
            ];

            return view('admin.users.index', compact('users', 'stats'));
        } catch (Exception $e) {
            Log::error('Error loading users index: ' . $e->getMessage());
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
            // Don't allow viewing system admin details
            if ($user->role === 'system_admin') {
                abort(403, 'Accès non autorisé.');
            }

            $user->load(['cooperative', 'orders', 'reviews']);

            return view('admin.users.show', compact('user'));
        } catch (Exception $e) {
            Log::error('Error loading user details: ' . $e->getMessage());
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
            // Don't allow modifying system admin
            if ($user->role === 'system_admin') {
                return response()->json([
                    'success' => false,
                    'message' => 'Impossible de modifier un administrateur système.'
                ], 403);
            }

            $validated = $request->validate([
                'status' => 'required|in:active,pending,suspended'
            ]);

            $oldStatus = $user->status;
            $user->update(['status' => $validated['status']]);

            Log::info('User status updated', [
                'user_id' => $user->id,
                'old_status' => $oldStatus,
                'new_status' => $validated['status'],
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

        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur de validation',
                    'errors' => $e->errors()
                ], 422);
            }
            return back()->withErrors($e->errors())->withInput();
        } catch (Exception $e) {
            Log::error('Error updating user status', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
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
            $pendingUsers = User::where('role', '!=', 'system_admin')
                              ->where('status', 'pending')
                              ->get();

            if ($pendingUsers->isEmpty()) {
                return redirect()->route('admin.users.index')
                               ->with('info', 'Aucun utilisateur en attente à activer.');
            }

            $count = $pendingUsers->count();

            User::where('role', '!=', 'system_admin')
                ->where('status', 'pending')
                ->update(['status' => 'active']);

            Log::info('All pending users activated', [
                'count' => $count,
                'activated_by' => Auth::id()
            ]);

            return redirect()->route('admin.users.index')
                           ->with('success', "{$count} utilisateur(s) activé(s) avec succès!");

        } catch (Exception $e) {
            Log::error('Error activating all pending users', [
                'error' => $e->getMessage(),
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
            $validated = $request->validate([
                'user_ids' => 'required|array',
                'user_ids.*' => 'exists:users,id'
            ]);

            $users = User::whereIn('id', $validated['user_ids'])
                        ->where('role', '!=', 'system_admin')
                        ->get();

            if ($users->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aucun utilisateur valide sélectionné.'
                ], 400);
            }

            $count = $users->count();

            User::whereIn('id', $validated['user_ids'])
                ->where('role', '!=', 'system_admin')
                ->update(['status' => 'suspended']);

            Log::info('Multiple users suspended', [
                'count' => $count,
                'user_ids' => $validated['user_ids'],
                'suspended_by' => Auth::id()
            ]);

            return response()->json([
                'success' => true,
                'message' => "{$count} utilisateur(s) suspendu(s) avec succès!"
            ]);

        } catch (Exception $e) {
            Log::error('Error suspending multiple users', [
                'error' => $e->getMessage(),
                'suspended_by' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suspension des utilisateurs.'
            ], 500);
        }
    }
}
