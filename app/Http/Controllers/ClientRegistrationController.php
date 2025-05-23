<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\EmailVerification;
use App\Services\EmailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ClientRegistrationController extends Controller
{
    public function showRegistrationForm()
    {
        return view('auth.client-register');
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Create user
        $user = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
            'password' => Hash::make($request->password),
            'role' => 'client',
            'status' => 'pending',
        ]);

        // Generate verification code
        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        EmailVerification::create([
            'email' => $user->email,
            'code' => $code,
            'type' => 'user',
            'user_id' => $user->id,
            'expires_at' => now()->addMinutes(15),
        ]);

        // Send verification email
        $emailSent = EmailService::sendVerificationCode(
            $user->email,
            $code,
            $user->first_name,
            'user'
        );

        if (!$emailSent) {
            $user->delete();
            return back()->withErrors(['email' => 'Erreur lors de l\'envoi de l\'email de vérification.']);
        }

        return redirect()->route('client.verify-email', ['email' => $user->email])
                        ->with('success', 'Un code de vérification a été envoyé à votre email.');
    }

    public function showVerifyEmailForm(Request $request)
    {
        $email = $request->get('email');
        return view('auth.verify-email', compact('email'));
    }

    public function verifyEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'code' => 'required|string|size:6',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $verification = EmailVerification::where('email', $request->email)
                                        ->where('code', $request->code)
                                        ->where('type', 'user')
                                        ->where('is_verified', false)
                                        ->first();

        if (!$verification) {
            return back()->withErrors(['code' => 'Code de vérification invalide.']);
        }

        if ($verification->isExpired()) {
            return back()->withErrors(['code' => 'Code de vérification expiré.']);
        }

        // Verify email and activate user
        $user = User::find($verification->user_id);
        $user->update([
            'email_verified_at' => now(),
            'status' => 'active',
        ]);

        $verification->update(['is_verified' => true]);

        return redirect()->route('login')
                        ->with('success', 'Email vérifié avec succès! Vous pouvez maintenant vous connecter.');
    }
}
