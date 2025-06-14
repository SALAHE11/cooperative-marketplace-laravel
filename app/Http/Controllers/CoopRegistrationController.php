<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Cooperative;
use App\Models\CooperativeAdminRequest;
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

    // ===== Search cooperatives for joining =====
    public function searchCooperatives(Request $request)
    {
        $search = $request->get('search', '');
        $limit = $request->get('limit', 10);

        $cooperatives = Cooperative::where('status', 'approved')
            ->where(function($query) use ($search) {
                if ($search) {
                    $query->where('name', 'LIKE', "%{$search}%")
                          ->orWhere('sector_of_activity', 'LIKE', "%{$search}%")
                          ->orWhere('address', 'LIKE', "%{$search}%");
                }
            })
            ->select('id', 'name', 'sector_of_activity', 'address', 'phone', 'email', 'date_created', 'logo_path')
            ->limit($limit)
            ->get();

        return response()->json([
            'success' => true,
            'cooperatives' => $cooperatives->map(function($coop) {
                return [
                    'id' => $coop->id,
                    'name' => $coop->name,
                    'sector_of_activity' => $coop->sector_of_activity,
                    'address' => $coop->address,
                    'phone' => $coop->phone,
                    'email' => $coop->email,
                    'date_created' => $coop->date_created->format('d/m/Y'),
                    'logo_url' => $coop->logo_path ? Storage::url($coop->logo_path) : null,
                ];
            })
        ]);
    }

    

    // ===== Handle both new registration and join requests =====
    public function register(Request $request)
    {
        $registrationType = $request->input('registration_type', 'new');

        if ($registrationType === 'join') {
            return $this->handleJoinRequest($request);
        } else {
            return $this->handleNewCooperativeRegistration($request);
        }
    }

    // ===== Handle join existing cooperative request =====
    private function handleJoinRequest(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'password' => 'required|string|min:8|confirmed',
            'cooperative_id' => 'required|exists:cooperatives,id',
            'message' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Check if cooperative is approved
        $cooperative = Cooperative::find($request->cooperative_id);
        if ($cooperative->status !== 'approved') {
            return back()->withErrors(['cooperative_id' => 'Cette coopérative n\'est pas encore approuvée.']);
        }

        DB::beginTransaction();

        try {
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
                'cooperative_id' => null, // Will be set after approval
            ]);

            // Create join request
            $joinRequest = CooperativeAdminRequest::create([
                'user_id' => $user->id,
                'cooperative_id' => $request->cooperative_id,
                'message' => $request->message,
                'status' => 'pending',
                'requested_at' => now(),
            ]);

            // Generate verification code for user email only
            $userCode = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

            EmailVerification::create([
                'email' => $user->email,
                'code' => $userCode,
                'type' => 'user',
                'user_id' => $user->id,
                'cooperative_id' => $cooperative->id,
                'expires_at' => now()->addMinutes(15),
            ]);

            // Send verification email to new user
            $userEmailSent = EmailService::sendVerificationCode(
                $user->email,
                $userCode,
                $user->first_name,
                'user'
            );

            if (!$userEmailSent) {
                throw new \Exception('Erreur lors de l\'envoi de l\'email de vérification.');
            }

            // Send notification to existing cooperative admin (primary admin)
            $existingAdmin = $cooperative->primaryAdmin;
            if ($existingAdmin) {
                EmailService::sendJoinRequestNotification(
                    $existingAdmin->email,
                    $existingAdmin->first_name,
                    $user->getFullNameAttribute(),
                    $cooperative->name,
                    $request->message
                );
            }

            DB::commit();

            Log::info('Join request created', [
                'user_id' => $user->id,
                'cooperative_id' => $cooperative->id,
                'cooperative_name' => $cooperative->name,
                'user_email' => $user->email,
                'primary_admin_notified' => $existingAdmin ? $existingAdmin->id : null
            ]);

            return redirect()->route('coop.verify-join-request', [
                'email' => $user->email,
                'cooperative_name' => $cooperative->name
            ])->with('success', 'Demande d\'adhésion envoyée avec succès! Vérifiez votre email.');

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Join request failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }

    // ===== Handle new cooperative registration =====
    private function handleNewCooperativeRegistration(Request $request)
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
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
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
                $filename = time() . '_' . Str::slug($request->coop_name) . '.' . $logoFile->getClientOriginalExtension();
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
                // PRIMARY_ADMIN_ID will be set after user verification
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

            Log::info('New cooperative registration initiated', [
                'cooperative_id' => $cooperative->id,
                'cooperative_name' => $cooperative->name,
                'user_id' => $user->id,
                'user_email' => $user->email,
                'will_be_primary_admin' => true
            ]);

            return redirect()->route('coop.verify-emails', [
                'user_email' => $user->email,
                'coop_email' => $cooperative->email
            ])->with('success', 'Codes de vérification envoyés aux deux adresses email.');

        } catch (\Exception $e) {
            DB::rollback();
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

    // ===== Show join request verification form =====
    public function showVerifyJoinRequestForm(Request $request)
    {
        $email = $request->get('email');
        $cooperativeName = $request->get('cooperative_name');
        return view('auth.verify-join-request', compact('email', 'cooperativeName'));
    }

    // ===== Verify join request email =====
    public function verifyJoinRequest(Request $request)
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

        DB::beginTransaction();

        try {
            // Verify email and activate user
            $user = User::find($verification->user_id);
            $user->markEmailAsVerified();
            // Note: Don't activate user yet - wait for admin approval
            // Note: Don't set as primary admin - this is for join requests only

            $verification->update(['is_verified' => true]);

            DB::commit();

            Log::info('Join request email verified', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'cooperative_id' => $verification->cooperative_id,
                'verification_id' => $verification->id
            ]);

            return redirect()->route('coop.join-request-sent')
                            ->with('success', 'Email vérifié avec succès! Votre demande d\'adhésion est en attente d\'approbation.');

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Join request email verification failed:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->withErrors(['error' => 'Erreur lors de la vérification: ' . $e->getMessage()]);
        }
    }

    // ===== Show join request sent confirmation =====
    public function showJoinRequestSent()
    {
        return view('auth.join-request-sent');
    }

    // ===== Show email verification form for new cooperative =====
    public function showVerifyEmailsForm(Request $request)
    {
        $userEmail = $request->get('user_email');
        $coopEmail = $request->get('coop_email');
        return view('auth.verify-coop-emails', compact('userEmail', 'coopEmail'));
    }

    // ===== Verify emails for new cooperative registration =====
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

            // ===== NEW: Set the user as primary admin of the cooperative =====
            if (!$cooperative->primary_admin_id) {
                $cooperative->setPrimaryAdmin($user->id);
                Log::info('Primary admin set for cooperative', [
                    'cooperative_id' => $cooperative->id,
                    'primary_admin_id' => $user->id,
                    'admin_name' => $user->getFullNameAttribute(),
                    'cooperative_name' => $cooperative->name
                ]);
            }

            DB::commit();

            Log::info('Cooperative email verification completed', [
                'cooperative_id' => $cooperative->id,
                'cooperative_name' => $cooperative->name,
                'user_id' => $user->id,
                'user_email' => $user->email,
                'primary_admin_set' => true
            ]);

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

    public function getCooperativeDetails($id)
{
    try {
        // Use raw DB query to avoid any model relationship issues
        $cooperative = DB::table('cooperatives')
            ->where('id', $id)
            ->where('status', 'approved')
            ->select([
                'id',
                'name',
                'sector_of_activity',
                'address',
                'phone',
                'email',
                'date_created',
                'logo_path',
                'description',
                'legal_status'
            ])
            ->first();

        if (!$cooperative) {
            return response()->json([
                'success' => false,
                'message' => 'Coopérative introuvable'
            ], 404);
        }

        // Format date safely
        $dateCreated = 'Non spécifiée';
        if ($cooperative->date_created) {
            try {
                $dateCreated = \Carbon\Carbon::parse($cooperative->date_created)->format('d/m/Y');
            } catch (\Exception $e) {
                $dateCreated = 'Non spécifiée';
            }
        }

        // Handle logo URL safely
        $logoUrl = null;
        if ($cooperative->logo_path) {
            try {
                if (Storage::disk('public')->exists($cooperative->logo_path)) {
                    $logoUrl = Storage::url($cooperative->logo_path);
                }
            } catch (\Exception $e) {
                // Ignore logo errors
            }
        }

        return response()->json([
            'success' => true,
            'cooperative' => [
                'id' => $cooperative->id,
                'name' => $cooperative->name,
                'sector_of_activity' => $cooperative->sector_of_activity ?? 'Non spécifié',
                'address' => $cooperative->address ?? 'Non spécifiée',
                'phone' => $cooperative->phone ?? 'Non spécifié',
                'email' => $cooperative->email,
                'date_created' => $dateCreated,
                'description' => $cooperative->description ?? '',
                'legal_status' => $cooperative->legal_status ?? 'Non spécifié',
                'logo_url' => $logoUrl,
            ]
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Erreur lors du chargement des détails: ' . $e->getMessage()
        ], 500);
    }
}

}
