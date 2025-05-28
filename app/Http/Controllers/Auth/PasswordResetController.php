<?php

// File: app/Http/Controllers/Auth/PasswordResetController.php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\PasswordReset;
use App\Services\EmailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;

class PasswordResetController extends Controller
{
    /**
     * Show forgot password form
     */
    public function showForgotForm()
    {
        return view('auth.forgot-password');
    }

    /**
     * Send password reset code via email
     */
   public function sendResetCode(Request $request)
{
    $validator = Validator::make($request->all(), [
        'email' => 'required|email|exists:users,email'
    ], [
        'email.required' => 'L\'adresse email est requise.',
        'email.email' => 'Veuillez entrer une adresse email valide.',
        'email.exists' => 'Aucun compte n\'est associé à cette adresse email.'
    ]);

    if ($validator->fails()) {
        return back()->withErrors($validator)->withInput();
    }

    try {
        $user = User::where('email', $request->email)->first();

        // Check if user is active
        if ($user->status !== 'active') {
            return back()->withErrors(['email' => 'Votre compte n\'est pas activé.']);
        }

        // Generate 6-digit code
        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        Log::info('Starting password reset process', [
            'email' => $request->email,
            'user_id' => $user->id,
            'generated_code' => $code
        ]);

        // Delete existing reset codes for this email
        $deletedCount = PasswordReset::where('email', $request->email)->delete();

        Log::info('Deleted existing password reset codes', [
            'email' => $request->email,
            'deleted_count' => $deletedCount
        ]);

        // Create new reset code with enhanced debugging
        try {
            $passwordReset = PasswordReset::create([
                'email' => $request->email,
                'code' => $code,
                'expires_at' => now()->addMinutes(15), // 15 minutes expiry
                'is_used' => false,
            ]);

            Log::info('Password reset record created successfully', [
                'email' => $request->email,
                'reset_id' => $passwordReset->id,
                'code' => $code,
                'expires_at' => $passwordReset->expires_at->toDateTimeString()
            ]);

            // Verify the record was actually created
            $verifyRecord = PasswordReset::where('email', $request->email)
                ->where('code', $code)
                ->first();

            if (!$verifyRecord) {
                Log::error('Password reset record verification failed', [
                    'email' => $request->email,
                    'code' => $code
                ]);
                return back()->withErrors(['email' => 'Erreur lors de la création du code de réinitialisation.']);
            }

            Log::info('Password reset record verified', [
                'verified_id' => $verifyRecord->id,
                'email' => $verifyRecord->email,
                'code' => $verifyRecord->code
            ]);

        } catch (\Exception $createException) {
            Log::error('Failed to create password reset record', [
                'email' => $request->email,
                'code' => $code,
                'error' => $createException->getMessage(),
                'trace' => $createException->getTraceAsString()
            ]);
            return back()->withErrors(['email' => 'Erreur lors de la création du code: ' . $createException->getMessage()]);
        }

        // Send reset code via email
        $emailSent = EmailService::sendPasswordResetCode(
            $request->email,
            $code,
            $user->first_name
        );

        if (!$emailSent) {
            // Delete the created record if email fails
            $passwordReset->delete();
            Log::error('Email sending failed, deleted reset record', [
                'email' => $request->email,
                'reset_id' => $passwordReset->id
            ]);
            return back()->withErrors(['email' => 'Erreur lors de l\'envoi de l\'email.']);
        }

        Log::info('Password reset code sent successfully', [
            'email' => $request->email,
            'code' => $code,
            'reset_id' => $passwordReset->id,
            'user_first_name' => $user->first_name
        ]);

        // Store email in session for next step
        Session::put('password_reset_email', $request->email);

        return redirect()->route('password.verify-code')
            ->with('success', 'Un code de vérification a été envoyé à votre email.');

    } catch (\Exception $e) {
        Log::error('Password reset code error', [
            'email' => $request->email,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);

        return back()->with('error', 'Erreur lors de l\'envoi du code: ' . $e->getMessage());
    }
}

    /**
     * Show code verification form
     */
    public function showVerifyCodeForm()
    {
        $email = Session::get('password_reset_email');

        if (!$email) {
            return redirect()->route('password.request')
                ->with('error', 'Session expirée. Veuillez recommencer.');
        }

        return view('auth.verify-reset-code', compact('email'));
    }

    /**
     * Verify reset code
     */
    public function verifyCode(Request $request)
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

        // Verify session email matches
        $sessionEmail = Session::get('password_reset_email');
        if ($sessionEmail !== $request->email) {
            return redirect()->route('password.request')
                ->with('error', 'Session invalide. Veuillez recommencer.');
        }

        $resetRecord = PasswordReset::where('email', $request->email)
            ->where('code', $request->code)
            ->where('is_used', false)
            ->first();

        if (!$resetRecord) {
            return back()->withErrors(['code' => 'Code de vérification invalide.']);
        }

        if ($resetRecord->isExpired()) {
            return back()->withErrors(['code' => 'Code de vérification expiré.']);
        }

        // Mark code as used
        $resetRecord->markAsUsed();

        // Store verified status in session
        Session::put('password_reset_verified', true);
        Session::put('password_reset_code_id', $resetRecord->id);

        Log::info('Password reset code verified', [
            'email' => $request->email,
            'reset_id' => $resetRecord->id
        ]);

        return redirect()->route('password.new')
            ->with('success', 'Code vérifié avec succès!');
    }

    /**
     * Show new password form
     */
    public function showNewPasswordForm()
    {
        $email = Session::get('password_reset_email');
        $verified = Session::get('password_reset_verified');

        if (!$email || !$verified) {
            return redirect()->route('password.request')
                ->with('error', 'Session expirée. Veuillez recommencer.');
        }

        return view('auth.new-password', compact('email'));
    }

    /**
     * Set new password
     */
    /**
 * Set new password
 */
public function setNewPassword(Request $request)
{
    $validator = Validator::make($request->all(), [
        'email' => 'required|email|exists:users,email',
        'password' => 'required|string|min:8|confirmed',
    ], [
        'email.required' => 'L\'adresse email est requise.',
        'email.exists' => 'Aucun compte n\'est associé à cette adresse email.',
        'password.required' => 'Le mot de passe est requis.',
        'password.min' => 'Le mot de passe doit contenir au moins 8 caractères.',
        'password.confirmed' => 'La confirmation du mot de passe ne correspond pas.'
    ]);

    if ($validator->fails()) {
        return back()->withErrors($validator)->withInput();
    }

    // Verify session
    $sessionEmail = Session::get('password_reset_email');
    $verified = Session::get('password_reset_verified');
    $codeId = Session::get('password_reset_code_id');

    if (!$sessionEmail || !$verified || $sessionEmail !== $request->email) {
        return redirect()->route('password.request')
            ->with('error', 'Session invalide. Veuillez recommencer.');
    }

    try {
        // FIXED: Use direct database update instead of model update
        $hashedPassword = Hash::make($request->password);

        // Method 1: Direct database update (most reliable)
        $affectedRows = DB::table('users')
            ->where('email', $request->email)
            ->update(['password' => $hashedPassword]);

        // Log the update attempt
        Log::info('Password update attempt', [
            'email' => $request->email,
            'affected_rows' => $affectedRows,
            'hashed_password_sample' => substr($hashedPassword, 0, 20) . '...'
        ]);

        if ($affectedRows === 0) {
            Log::warning('Password update failed - no rows affected', [
                'email' => $request->email
            ]);
            return back()->with('error', 'Erreur: Aucune ligne mise à jour. Veuillez réessayer.');
        }

        // Alternative Method 2: Using Eloquent with explicit save
        /*
        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return back()->with('error', 'Utilisateur introuvable.');
        }

        $user->password = $hashedPassword;
        $saveResult = $user->save();

        Log::info('Password save result', [
            'email' => $request->email,
            'save_result' => $saveResult ? 'true' : 'false'
        ]);

        if (!$saveResult) {
            return back()->with('error', 'Erreur lors de la sauvegarde du mot de passe.');
        }
        */

        // REMOVED: Delete all reset codes for this email
        // PasswordReset::where('email', $request->email)->delete();

        // Clear session
        Session::forget(['password_reset_email', 'password_reset_verified', 'password_reset_code_id']);

        Log::info('Password reset completed successfully', [
            'email' => $request->email,
            'reset_code_id' => $codeId,
            'rows_updated' => $affectedRows
        ]);

        return redirect()->route('login')
            ->with('success', 'Mot de passe réinitialisé avec succès! Vous pouvez maintenant vous connecter.');

    } catch (\Exception $e) {
        Log::error('Password reset update error', [
            'email' => $request->email,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);

        return back()->with('error', 'Erreur lors de la réinitialisation du mot de passe: ' . $e->getMessage());
    }
}

    /**
     * Resend verification code
     */
    public function resendCode(Request $request)
    {
        $email = Session::get('password_reset_email');

        if (!$email) {
            return response()->json([
                'success' => false,
                'message' => 'Session expirée.'
            ], 400);
        }

        try {
            $user = User::where('email', $email)->first();

            if (!$user || $user->status !== 'active') {
                return response()->json([
                    'success' => false,
                    'message' => 'Utilisateur introuvable ou inactif.'
                ], 404);
            }

            // Generate new code
            $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

            // Delete existing codes
            PasswordReset::where('email', $email)->delete();

            // Create new code
            $passwordReset = PasswordReset::create([
                'email' => $email,
                'code' => $code,
                'expires_at' => now()->addMinutes(15),
                'is_used' => false,
            ]);

            // Send new code
            $emailSent = EmailService::sendPasswordResetCode(
                $email,
                $code,
                $user->first_name
            );

            if (!$emailSent) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur lors de l\'envoi de l\'email.'
                ], 500);
            }

            return response()->json([
                'success' => true,
                'message' => 'Nouveau code envoyé avec succès!'
            ]);

        } catch (\Exception $e) {
            Log::error('Resend code error', [
                'email' => $email,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du renvoi du code.'
            ], 500);
        }
    }
}

?>
