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
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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

            // Logo validation
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Max 2MB
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        DB::beginTransaction();

        try {
            // Handle logo upload
            $logoPath = null;
            if ($request->hasFile('logo')) {
                $logoFile = $request->file('logo');

                // Generate unique filename
                $filename = time() . '_' . Str::slug($request->coop_name) . '.' . $logoFile->getClientOriginalExtension();

                // Store in public/storage/cooperative-logos
                $logoPath = $logoFile->storeAs('cooperative-logos', $filename, 'public');

                Log::info('Logo uploaded successfully', ['path' => $logoPath, 'filename' => $filename]);
            }

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
                'logo_path' => $logoPath,
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

            // Clean up uploaded logo if transaction failed
            if ($logoPath && Storage::disk('public')->exists($logoPath)) {
                Storage::disk('public')->delete($logoPath);
                Log::info('Cleaned up logo file after transaction failure', ['path' => $logoPath]);
            }

            Log::error('Cooperative registration failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

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

        DB::beginTransaction();

        try {
            // Mark verification records as verified
            $userVerification->update(['is_verified' => true]);
            $coopVerification->update(['is_verified' => true]);

            // Find and update user
            $user = User::find($userVerification->user_id);
            if (!$user) {
                throw new \Exception('Utilisateur introuvable');
            }

            // Update user email verification
            $user->markEmailAsVerified();

            // Find and update cooperative
            $cooperative = Cooperative::find($coopVerification->cooperative_id);
            if (!$cooperative) {
                throw new \Exception('Coopérative introuvable');
            }

            // Update cooperative email verification
            $cooperative->update(['email_verified_at' => now()]);

            DB::commit();

            return redirect()->route('login')
                            ->with('success', 'Emails vérifiés avec succès! Votre demande d\'inscription est en cours d\'examen par l\'administrateur. Vous recevrez une réponse sur l\'email de la coopérative.');

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Email verification failed:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->withErrors(['error' => 'Erreur lors de la vérification: ' . $e->getMessage()]);
        }
    }
}
