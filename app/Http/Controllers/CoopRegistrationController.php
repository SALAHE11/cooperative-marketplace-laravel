<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Cooperative;
use App\Models\EmailVerification;
use App\Services\EmailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class CoopRegistrationController extends Controller
{
    public function showRegistrationForm()
    {
        return view('auth.coop-register');
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            // User fields
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'password' => 'required|string|min:8|confirmed',

            // Cooperative fields
            'coop_name' => 'required|string|max:255',
            'coop_email' => 'required|string|email|max:255|different:email',
            'coop_phone' => 'required|string|max:255',
            'coop_address' => 'required|string',
            'legal_status' => 'required|string|max:255',
            'date_created' => 'required|date',
            'sector_of_activity' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        DB::beginTransaction();

        try {
            // Create cooperative
            $cooperative = Cooperative::create([
                'name' => $request->coop_name,
                'email' => $request->coop_email,
                'phone' => $request->coop_phone,
                'address' => $request->coop_address,
                'legal_status' => $request->legal_status,
                'date_created' => $request->date_created,
                'sector_of_activity' => $request->sector_of_activity,
                'description' => $request->description,
                'status' => 'pending',
            ]);

            // Create user
            $user = User::create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'phone' => $request->phone,
                'address' => $request->address,
                'password' => Hash::make($request->password),
                'role' => 'cooperative_admin',
                'status' => 'pending',
                'cooperative_id' => $cooperative->id,
            ]);

            // Generate verification codes
            $userCode = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            $coopCode = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

            // Create verification records
            EmailVerification::create([
                'email' => $user->email,
                'code' => $userCode,
                'type' => 'user',
                'user_id' => $user->id,
                'cooperative_id' => $cooperative->id,
                'expires_at' => now()->addMinutes(15),
            ]);

            EmailVerification::create([
                'email' => $cooperative->email,
                'code' => $coopCode,
                'type' => 'cooperative',
                'user_id' => $user->id,
                'cooperative_id' => $cooperative->id,
                'expires_at' => now()->addMinutes(15),
            ]);

            // Send verification emails
            $userEmailSent = EmailService::sendVerificationCode(
                $user->email,
                $userCode,
                $user->first_name,
                'user'
            );

            $coopEmailSent = EmailService::sendVerificationCode(
                $cooperative->email,
                $coopCode,
                '',
                'cooperative'
            );

            if (!$userEmailSent || !$coopEmailSent) {
                throw new \Exception('Erreur lors de l\'envoi des emails de vérification.');
            }

            DB::commit();

            return redirect()->route('coop.verify-emails', [
                'user_email' => $user->email,
                'coop_email' => $cooperative->email
            ])->with('success', 'Codes de vérification envoyés aux deux adresses email.');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }

    public function showVerifyEmailsForm(Request $request)
    {
        $userEmail = $request->get('user_email');
        $coopEmail = $request->get('coop_email');
        return view('auth.verify-coop-emails', compact('userEmail', 'coopEmail'));
    }

    public function verifyEmails(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_email' => 'required|email',
            'coop_email' => 'required|email',
            'user_code' => 'required|string|size:6',
            'coop_code' => 'required|string|size:6',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Verify user email code
        $userVerification = EmailVerification::where('email', $request->user_email)
                                            ->where('code', $request->user_code)
                                            ->where('type', 'user')
                                            ->where('is_verified', false)
                                            ->first();

        // Verify cooperative email code
        $coopVerification = EmailVerification::where('email', $request->coop_email)
                                            ->where('code', $request->coop_code)
                                            ->where('type', 'cooperative')
                                            ->where('is_verified', false)
                                            ->first();

        if (!$userVerification || !$coopVerification) {
            return back()->withErrors(['error' => 'Un ou plusieurs codes de vérification sont invalides.']);
        }

        if ($userVerification->isExpired() || $coopVerification->isExpired()) {
            return back()->withErrors(['error' => 'Un ou plusieurs codes de vérification ont expiré.']);
        }

        // Mark emails as verified
        $user = User::find($userVerification->user_id);
        $user->update(['email_verified_at' => now()]);

        $userVerification->update(['is_verified' => true]);
        $coopVerification->update(['is_verified' => true]);

        return redirect()->route('login')
                        ->with('success', 'Emails vérifiés avec succès! Votre demande d\'inscription est en cours d\'examen par l\'administrateur. Vous recevrez une réponse sur l\'email de la coopérative.');
    }
}
