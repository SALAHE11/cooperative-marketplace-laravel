<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cooperative;
use App\Models\User;
use App\Services\EmailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CooperativeManagementController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        /** @var User $user */
        $user = Auth::user();

        if (!$user->isSystemAdmin()) {
            abort(403);
        }

        $pendingCooperatives = Cooperative::with('admin')
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $approvedCooperatives = Cooperative::with('admin')
            ->where('status', 'approved')
            ->orderBy('updated_at', 'desc')
            ->paginate(10);

        return view('admin.cooperatives.index', compact('pendingCooperatives', 'approvedCooperatives'));
    }

    public function show(Cooperative $cooperative)
    {
        /** @var User $user */
        $user = Auth::user();

        if (!$user->isSystemAdmin()) {
            abort(403);
        }

        $cooperative->load('admin');

        return view('admin.cooperatives.show', compact('cooperative'));
    }

    public function approve(Request $request, Cooperative $cooperative)
    {
        /** @var User $user */
        $user = Auth::user();

        if (!$user->isSystemAdmin()) {
            abort(403);
        }

        DB::beginTransaction();

        try {
            // Update cooperative status
            $cooperative->update(['status' => 'approved']);

            // Update admin user status
            $cooperative->admin->update(['status' => 'active']);

            // Send approval email
            $this->sendApprovalEmail($cooperative);

            DB::commit();

            return redirect()->route('admin.cooperatives.index')
                           ->with('success', "Coopérative '{$cooperative->name}' approuvée avec succès!");

        } catch (\Exception $e) {
            DB::rollback();
            return back()->withErrors(['error' => 'Erreur lors de l\'approbation: ' . $e->getMessage()]);
        }
    }

    public function reject(Request $request, Cooperative $cooperative)
    {
        /** @var User $user */
        $user = Auth::user();

        if (!$user->isSystemAdmin()) {
            abort(403);
        }

        $request->validate([
            'rejection_reason' => 'required|string|min:10|max:1000'
        ]);

        DB::beginTransaction();

        try {
            // Update cooperative status
            $cooperative->update([
                'status' => 'rejected',
                'rejection_reason' => $request->rejection_reason
            ]);

            // Update admin user status
            $cooperative->admin->update(['status' => 'suspended']);

            // Send rejection email
            $this->sendRejectionEmail($cooperative, $request->rejection_reason);

            DB::commit();

            return redirect()->route('admin.cooperatives.index')
                           ->with('success', "Coopérative '{$cooperative->name}' rejetée.");

        } catch (\Exception $e) {
            DB::rollback();
            return back()->withErrors(['error' => 'Erreur lors du rejet: ' . $e->getMessage()]);
        }
    }

    public function requestInfo(Request $request, Cooperative $cooperative)
    {
        /** @var User $user */
        $user = Auth::user();

        if (!$user->isSystemAdmin()) {
            abort(403);
        }

        $request->validate([
            'info_requested' => 'required|string|min:10|max:1000'
        ]);

        try {
            // Send info request email
            $this->sendInfoRequestEmail($cooperative, $request->info_requested);

            return redirect()->route('admin.cooperatives.show', $cooperative)
                           ->with('success', 'Demande d\'informations envoyée avec succès!');

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Erreur lors de l\'envoi: ' . $e->getMessage()]);
        }
    }

    private function sendApprovalEmail(Cooperative $cooperative)
    {
        $subject = 'Votre coopérative a été approuvée!';
        $message = "
            <h2>Félicitations!</h2>
            <p>Bonjour,</p>
            <p>Nous avons le plaisir de vous informer que votre demande d'inscription pour la coopérative <strong>{$cooperative->name}</strong> a été approuvée.</p>
            <p>Votre compte est maintenant actif et vous pouvez vous connecter à la plateforme.</p>
            <p><strong>Informations de connexion:</strong></p>
            <ul>
                <li>Email: {$cooperative->admin->email}</li>
                <li>Mot de passe: Celui que vous avez choisi lors de l'inscription</li>
            </ul>
            <p><a href='" . route('login') . "' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Se connecter maintenant</a></p>
            <p>Merci de rejoindre notre plateforme coopérative!</p>
            <br>
            <p>Cordialement,<br>L'équipe Coopérative E-commerce</p>
        ";

        return EmailService::sendNotificationEmail(
            $cooperative->email,
            $subject,
            $message,
            $cooperative->admin->first_name
        );
    }

    private function sendRejectionEmail(Cooperative $cooperative, $reason)
    {
        $subject = 'Mise à jour de votre demande d\'inscription';
        $message = "
            <h2>Mise à jour de votre demande</h2>
            <p>Bonjour {$cooperative->admin->first_name},</p>
            <p>Nous vous informons que votre demande d'inscription pour la coopérative <strong>{$cooperative->name}</strong> n'a pas pu être approuvée.</p>
            <p><strong>Raison:</strong></p>
            <p style='background: #f8f9fa; padding: 15px; border-left: 4px solid #dc3545;'>{$reason}</p>
            <p>Vous pouvez soumettre une nouvelle demande en tenant compte des points mentionnés ci-dessus.</p>
            <p>N'hésitez pas à nous contacter si vous avez des questions.</p>
            <br>
            <p>Cordialement,<br>L'équipe Coopérative E-commerce</p>
        ";

        return EmailService::sendNotificationEmail(
            $cooperative->email,
            $subject,
            $message,
            $cooperative->admin->first_name
        );
    }

    private function sendInfoRequestEmail(Cooperative $cooperative, $infoRequested)
    {
        $subject = 'Informations supplémentaires requises';
        $message = "
            <h2>Informations supplémentaires requises</h2>
            <p>Bonjour {$cooperative->admin->first_name},</p>
            <p>Votre demande d'inscription pour la coopérative <strong>{$cooperative->name}</strong> est en cours d'examen.</p>
            <p>Pour finaliser le processus, nous avons besoin des informations supplémentaires suivantes:</p>
            <p style='background: #f8f9fa; padding: 15px; border-left: 4px solid #ffc107;'>{$infoRequested}</p>
            <p>Merci de nous fournir ces informations en répondant à cet email.</p>
            <p>Une fois ces informations reçues, nous procéderons à l'examen final de votre dossier.</p>
            <br>
            <p>Cordialement,<br>L'équipe Coopérative E-commerce</p>
        ";

        return EmailService::sendNotificationEmail(
            $cooperative->email,
            $subject,
            $message,
            $cooperative->admin->first_name
        );
    }
}
