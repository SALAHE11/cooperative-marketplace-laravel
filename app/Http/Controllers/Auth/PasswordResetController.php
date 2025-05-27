<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\PasswordReset;
use App\Services\EmailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

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

            // Generate reset token
            $token = Str::random(64);

            // Delete existing reset tokens for this email
            PasswordReset::where('email', $request->email)->delete();

            // Create new reset token
            PasswordReset::create([
                'email' => $request->email,
                'token' => $token,
                'expires_at' => now()->addHours(1), // 1 hour expiry
            ]);

            // Send reset email
            $emailSent = EmailService::sendPasswordResetEmail(
                $request->email,
                $token,
                $user->first_name
            );

            if (!$emailSent) {
                return back()->withErrors(['email' => 'Erreur lors de l\'envoi de l\'email.']);
            }

            return back()->with('success', 'Un lien de réinitialisation a été envoyé à votre email.');

        } catch (\Exception $e) {
            Log::error('Password reset error: ' . $e->getMessage());
            return back()->with('error', 'Erreur lors de l\'envoi de l\'email.');
        }
    }

    /**
     * Show password reset form
     */
    public function showResetForm($token)
    {
        $resetRecord = PasswordReset::where('token', $token)
            ->where('expires_at', '>', now())
            ->first();

        if (!$resetRecord) {
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
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Verify reset token
        $resetRecord = PasswordReset::where('token', $request->token)
            ->where('email', $request->email)
            ->where('expires_at', '>', now())
            ->first();

        if (!$resetRecord) {
            return back()->withErrors(['email' => 'Token de réinitialisation invalide ou expiré.']);
        }

        try {
            // Update user password
            $user = User::where('email', $request->email)->first();
            $user->update([
                'password' => Hash::make($request->password)
            ]);

            // Delete used reset token
            $resetRecord->delete();

            // Delete all other reset tokens for this email
            PasswordReset::where('email', $request->email)->delete();

            return redirect()->route('login')
                ->with('success', 'Mot de passe réinitialisé avec succès! Vous pouvez maintenant vous connecter.');

        } catch (\Exception $e) {
            Log::error('Password reset update error: ' . $e->getMessage());
            return back()->with('error', 'Erreur lors de la réinitialisation du mot de passe.');
        }
    }
}
