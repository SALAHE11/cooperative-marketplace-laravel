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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;

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
     * Send password reset email
     */
    public function sendResetEmail(Request $request)
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

        DB::beginTransaction();

        try {
            $user = User::where('email', $request->email)->first();

            // Check if user is active
            if ($user->status !== 'active') {
                return back()->withErrors(['email' => 'Votre compte n\'est pas activé.']);
            }

            // Generate reset token
            $token = Str::random(64);

            // Delete existing reset tokens for this email
            PasswordReset::where('email', $request->email)->delete();

            // Create new reset token with explicit model save
            $passwordReset = new PasswordReset();
            $passwordReset->email = $request->email;
            $passwordReset->token = $token;
            $passwordReset->expires_at = now()->addHours(1);
            $passwordReset->save();

            // Alternative using create method
            // $passwordReset = PasswordReset::create([
            //     'email' => $request->email,
            //     'token' => $token,
            //     'expires_at' => now()->addHours(1),
            // ]);

            // Verify the record was created
            if (!$passwordReset->id) {
                throw new \Exception('Failed to create password reset record');
            }

            // Send reset email
            $emailSent = EmailService::sendPasswordResetEmail(
                $request->email,
                $token,
                $user->first_name
            );

            if (!$emailSent) {
                throw new \Exception('Failed to send reset email');
            }

            DB::commit();

            Log::info('Password reset email sent', [
                'email' => $request->email,
                'token' => $token,
                'reset_id' => $passwordReset->id
            ]);

            return back()->with('success', 'Un lien de réinitialisation a été envoyé à votre email.');

        } catch (\Exception $e) {
            DB::rollback();

            Log::error('Password reset error', [
                'email' => $request->email,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()->with('error', 'Erreur lors de l\'envoi de l\'email: ' . $e->getMessage());
        }
    }

    /**
     * Show password reset form
     */
    public function showResetForm($token)
    {
        Log::info('Showing reset form', ['token' => $token]);

        $resetRecord = PasswordReset::where('token', $token)
            ->where('expires_at', '>', now())
            ->first();

        if (!$resetRecord) {
            Log::warning('Invalid or expired reset token', ['token' => $token]);
            return redirect()->route('login')
                ->with('error', 'Lien de réinitialisation invalide ou expiré.');
        }

        return view('auth.reset-password', compact('token'));
    }

    /**
     * Reset password
     */
    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required',
            'email' => 'required|email|exists:users,email',
            'password' => 'required|string|min:8|confirmed',
        ], [
            'token.required' => 'Token de réinitialisation manquant.',
            'email.required' => 'L\'adresse email est requise.',
            'email.exists' => 'Aucun compte n\'est associé à cette adresse email.',
            'password.required' => 'Le mot de passe est requis.',
            'password.min' => 'Le mot de passe doit contenir au moins 8 caractères.',
            'password.confirmed' => 'La confirmation du mot de passe ne correspond pas.'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        DB::beginTransaction();

        try {
            // Verify reset token
            $resetRecord = PasswordReset::where('token', $request->token)
                ->where('email', $request->email)
                ->where('expires_at', '>', now())
                ->first();

            if (!$resetRecord) {
                return back()->withErrors(['email' => 'Token de réinitialisation invalide ou expiré.']);
            }

            // Update user password
            $user = User::where('email', $request->email)->first();
            $user->update([
                'password' => Hash::make($request->password)
            ]);

            // Delete used reset token
            $resetRecord->delete();

            // Delete all other reset tokens for this email
            PasswordReset::where('email', $request->email)->delete();

            DB::commit();

            Log::info('Password reset completed', [
                'email' => $request->email,
                'user_id' => $user->id
            ]);

            return redirect()->route('login')
                ->with('success', 'Mot de passe réinitialisé avec succès! Vous pouvez maintenant vous connecter.');

        } catch (\Exception $e) {
            DB::rollback();

            Log::error('Password reset update error', [
                'email' => $request->email,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()->with('error', 'Erreur lors de la réinitialisation du mot de passe: ' . $e->getMessage());
        }
    }

    /**
     * Debug method to check table contents (remove in production)
     */
    public function debug()
    {
        if (config('app.debug')) {
            $resets = PasswordReset::all();
            dd([
                'table_exists' => Schema::hasTable('password_resets'),
                'records_count' => $resets->count(),
                'records' => $resets->toArray()
            ]);
        }

        abort(404);
    }
}
