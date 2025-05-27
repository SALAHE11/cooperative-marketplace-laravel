<?php

// File: app/Http/Controllers/Admin/AdminInvitationController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminInvitation;
use App\Models\User;
use App\Models\EmailVerification;
use App\Services\EmailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class AdminInvitationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth')->except(['showRegistrationForm', 'register', 'showVerifyEmailForm', 'verifyEmail']);
        $this->middleware('check.role:system_admin')->except(['showRegistrationForm', 'register', 'showVerifyEmailForm', 'verifyEmail']);
    }

    /**
     * Send admin invitation email
     */
    public function sendInvitation(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|unique:users,email|unique:admin_invitations,email'
        ], [
            'email.unique' => 'Cette adresse email est déjà utilisée ou a déjà reçu une invitation.',
            'email.required' => 'L\'adresse email est requise.',
            'email.email' => 'Veuillez entrer une adresse email valide.'
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }
            return back()->withErrors($validator)->withInput();
        }

        try {
            // Clean up expired invitations first
            AdminInvitation::cleanupExpired();

            // Generate unique token
            $token = Str::random(64);

            // Create invitation record
            $invitation = AdminInvitation::create([
                'email' => $request->email,
                'token' => $token,
                'invited_by' => Auth::id(),
                'expires_at' => Carbon::now()->addDays(7), // 7 days to accept
            ]);

            // Send invitation email
            $emailSent = EmailService::sendAdminInvitation(
                $request->email,
                $token,
                Auth::user()->full_name
            );

            if (!$emailSent) {
                // Delete the invitation if email failed
                $invitation->delete();

                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Erreur lors de l\'envoi de l\'email d\'invitation.'
                    ], 500);
                }
                return back()->withErrors(['email' => 'Erreur lors de l\'envoi de l\'email d\'invitation.']);
            }

            Log::info('Admin invitation sent', [
                'email' => $request->email,
                'invited_by' => Auth::id(),
                'token' => $token
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Invitation envoyée avec succès!'
                ]);
            }

            return back()->with('success', 'Invitation d\'administrateur envoyée avec succès à ' . $request->email);

        } catch (\Exception $e) {
            Log::error('Admin invitation error', [
                'email' => $request->email,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur lors de l\'envoi de l\'invitation.'
                ], 500);
            }

            return back()->with('error', 'Erreur lors de l\'envoi de l\'invitation.');
        }
    }

    /**
     * Show admin registration form
     */
    public function showRegistrationForm($token)
    {
        $invitation = AdminInvitation::where('token', $token)
            ->where('is_used', false)
            ->where('expires_at', '>', now())
            ->first();

        if (!$invitation) {
            return redirect()->route('login')
                ->with('error', 'Lien d\'invitation invalide ou expiré.');
        }

        return view('auth.admin-register', compact('invitation'));
    }

    /**
     * Process admin registration
     */
    public function register(Request $request, $token)
    {
        $invitation = AdminInvitation::where('token', $token)
            ->where('is_used', false)
            ->where('expires_at', '>', now())
            ->first();

        if (!$invitation) {
            return redirect()->route('login')
                ->with('error', 'Lien d\'invitation invalide ou expiré.');
        }

        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'nullable|string|max:20',
            'password' => 'required|string|min:8|confirmed',
        ], [
            'first_name.required' => 'Le prénom est requis.',
            'last_name.required' => 'Le nom est requis.',
            'email.required' => 'L\'adresse email est requise.',
            'email.unique' => 'Cette adresse email est déjà utilisée.',
            'password.required' => 'Le mot de passe est requis.',
            'password.min' => 'Le mot de passe doit contenir au moins 8 caractères.',
            'password.confirmed' => 'La confirmation du mot de passe ne correspond pas.'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        DB::beginTransaction();

        try {
            // Create admin user
            $user = User::create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => Hash::make($request->password),
                'role' => 'system_admin',
                'status' => 'pending', // Will be activated after email verification
            ]);

            // Generate verification code
            $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

            EmailVerification::create([
                'email' => $user->email,
                'code' => $code,
                'type' => 'admin',
                'user_id' => $user->id,
                'expires_at' => now()->addMinutes(15),
            ]);

            // Send verification email
            $emailSent = EmailService::sendVerificationCode(
                $user->email,
                $code,
                $user->first_name,
                'admin'
            );

            if (!$emailSent) {
                throw new \Exception('Erreur lors de l\'envoi de l\'email de vérification.');
            }

            // Mark invitation as used
            $invitation->markAsUsed();

            DB::commit();

            Log::info('Admin registration completed', [
                'user_id' => $user->id,
                'email' => $user->email,
                'invitation_token' => $token
            ]);

            return redirect()->route('admin.verify-email', ['email' => $user->email])
                ->with('success', 'Un code de vérification a été envoyé à votre email.');

        } catch (\Exception $e) {
            DB::rollback();

            Log::error('Admin registration error', [
                'email' => $request->email,
                'invitation_token' => $token,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()->with('error', 'Erreur lors de l\'inscription: ' . $e->getMessage());
        }
    }

    /**
     * Show admin email verification form
     */
    public function showVerifyEmailForm(Request $request)
    {
        $email = $request->get('email');

        if (!$email) {
            return redirect()->route('login')
                ->with('error', 'Email de vérification manquant.');
        }

        // Check if user exists and is an admin
        $user = User::where('email', $email)
            ->where('role', 'system_admin')
            ->where('status', 'pending')
            ->first();

        if (!$user) {
            return redirect()->route('login')
                ->with('error', 'Utilisateur administrateur introuvable.');
        }

        return view('auth.verify-admin-email', compact('email'));
    }

    /**
     * Verify admin email
     */
    public function verifyEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'code' => 'required|string|size:6',
        ], [
            'email.required' => 'L\'adresse email est requise.',
            'code.required' => 'Le code de vérification est requis.',
            'code.size' => 'Le code de vérification doit contenir 6 chiffres.'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $verification = EmailVerification::where('email', $request->email)
            ->where('code', $request->code)
            ->where('type', 'admin')
            ->where('is_verified', false)
            ->first();

        if (!$verification) {
            return back()->withErrors(['code' => 'Code de vérification invalide.']);
        }

        if ($verification->isExpired()) {
            return back()->withErrors(['code' => 'Code de vérification expiré.']);
        }

        DB::beginTransaction();

        try {
            // Verify email and activate admin
            $user = User::find($verification->user_id);

            if (!$user || $user->role !== 'system_admin') {
                throw new \Exception('Utilisateur administrateur introuvable.');
            }

            $user->markEmailAsVerified();
            $user->update(['status' => 'active']);

            $verification->update(['is_verified' => true]);

            DB::commit();

            Log::info('Admin email verified and activated', [
                'user_id' => $user->id,
                'email' => $user->email
            ]);

            return redirect()->route('login')
                ->with('success', 'Email vérifié avec succès! Vous pouvez maintenant vous connecter en tant qu\'administrateur.');

        } catch (\Exception $e) {
            DB::rollback();

            Log::error('Admin email verification error', [
                'email' => $request->email,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()->with('error', 'Erreur lors de la vérification: ' . $e->getMessage());
        }
    }

    /**
     * Resend verification code (AJAX)
     */
    public function resendVerificationCode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Email invalide.'
            ], 422);
        }

        try {
            $user = User::where('email', $request->email)
                ->where('role', 'system_admin')
                ->where('status', 'pending')
                ->first();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Utilisateur introuvable.'
                ], 404);
            }

            // Delete existing verification codes
            EmailVerification::where('email', $request->email)
                ->where('type', 'admin')
                ->delete();

            // Generate new code
            $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

            EmailVerification::create([
                'email' => $user->email,
                'code' => $code,
                'type' => 'admin',
                'user_id' => $user->id,
                'expires_at' => now()->addMinutes(15),
            ]);

            // Send verification email
            $emailSent = EmailService::sendVerificationCode(
                $user->email,
                $code,
                $user->first_name,
                'admin'
            );

            if (!$emailSent) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur lors de l\'envoi de l\'email.'
                ], 500);
            }

            return response()->json([
                'success' => true,
                'message' => 'Code de vérification renvoyé avec succès!'
            ]);

        } catch (\Exception $e) {
            Log::error('Admin verification code resend error', [
                'email' => $request->email,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du renvoi du code.'
            ], 500);
        }
    }
}
