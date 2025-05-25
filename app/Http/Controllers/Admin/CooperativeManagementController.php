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

    public function sendEmail(Request $request)
{
    /** @var User $user */
    $user = Auth::user();

    if (!$user->isSystemAdmin()) {
        return response()->json(['success' => false, 'message' => 'Accès non autorisé'], 403);
    }

    $request->validate([
        'to' => 'required|email',
        'subject' => 'required|string|max:255',
        'message' => 'required|string|max:2000'
    ]);

    try {
        $emailSent = EmailService::sendNotificationEmail(
            $request->to,
            $request->subject,
            "<p>" . nl2br(e($request->message)) . "</p><br><p>Cordialement,<br>L'équipe Coopérative E-commerce</p>",
            ''
        );

        if ($emailSent) {
            return response()->json(['success' => true, 'message' => 'Email envoyé avec succès']);
        } else {
            return response()->json(['success' => false, 'message' => 'Erreur lors de l\'envoi de l\'email']);
        }
    } catch (\Exception $e) {
        return response()->json(['success' => false, 'message' => 'Erreur: ' . $e->getMessage()]);
    }
}


public function suspend(Request $request, Cooperative $cooperative)
{
    /** @var User $user */
    $user = Auth::user();

    if (!$user->isSystemAdmin()) {
        abort(403);
    }

    $request->validate([
        'suspension_reason' => 'required|string|min:10|max:1000'
    ]);

    DB::beginTransaction();

    try {
        // Update cooperative status
        $cooperative->update([
            'status' => 'suspended',
            'suspension_reason' => $request->suspension_reason,
            'suspended_at' => now(),
            'suspended_by' => $user->id,
        ]);

        // Update admin user status to suspended
        if ($cooperative->admin) {
            $cooperative->admin->update(['status' => 'suspended']);
        }

        // Send suspension email
        $this->sendSuspensionEmail($cooperative, $request->suspension_reason);

        DB::commit();

        return redirect()->route('admin.cooperatives.show', $cooperative)
                       ->with('success', "Coopérative '{$cooperative->name}' suspendue avec succès!");

    } catch (\Exception $e) {
        DB::rollback();
        return back()->withErrors(['error' => 'Erreur lors de la suspension: ' . $e->getMessage()]);
    }
}

public function unsuspend(Request $request, Cooperative $cooperative)
{
    /** @var User $user */
    $user = Auth::user();

    if (!$user->isSystemAdmin()) {
        abort(403);
    }

    DB::beginTransaction();

    try {
        // Update cooperative status back to approved
        $cooperative->update([
            'status' => 'approved',
            'suspension_reason' => null,
            'suspended_at' => null,
            'suspended_by' => null,
        ]);

        // Update admin user status back to active
        if ($cooperative->admin) {
            $cooperative->admin->update(['status' => 'active']);
        }

        // Send unsuspension email
        $this->sendUnsuspensionEmail($cooperative);

        DB::commit();

        return redirect()->route('admin.cooperatives.index')
                       ->with('success', "Suspension levée pour '{$cooperative->name}' avec succès!");

    } catch (\Exception $e) {
        DB::rollback();
        return back()->withErrors(['error' => 'Erreur lors de la levée de suspension: ' . $e->getMessage()]);
    }
}

private function sendSuspensionEmail(Cooperative $cooperative, $reason)
{
    $subject = 'Suspension de votre compte coopérative';
    $message = "
        <h2>Suspension de compte</h2>
        <p>Bonjour {$cooperative->admin->first_name},</p>
        <p>Nous vous informons que votre coopérative <strong>{$cooperative->name}</strong> a été temporairement suspendue.</p>
        <p><strong>Raison de la suspension:</strong></p>
        <p style='background: #f8f9fa; padding: 15px; border-left: 4px solid #dc3545;'>{$reason}</p>
        <p><strong>Conséquences de cette suspension:</strong></p>
        <ul>
            <li>Votre accès au tableau de bord est temporairement bloqué</li>
            <li>Vos produits ne sont plus visibles sur la plateforme</li>
            <li>Vous ne pouvez plus traiter de nouvelles commandes</li>
        </ul>
        <p>Pour contester cette décision ou demander des clarifications, vous pouvez nous contacter en répondant à cet email.</p>
        <p>Une fois les problèmes résolus, votre compte pourra être réactivé.</p>
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

private function sendUnsuspensionEmail(Cooperative $cooperative)
{
    $subject = 'Réactivation de votre compte coopérative';
    $message = "
        <h2>Compte réactivé</h2>
        <p>Bonjour {$cooperative->admin->first_name},</p>
        <p>Nous avons le plaisir de vous informer que la suspension de votre coopérative <strong>{$cooperative->name}</strong> a été levée.</p>
        <p><strong>Votre accès est maintenant rétabli:</strong></p>
        <ul>
            <li>Vous pouvez vous connecter à votre tableau de bord</li>
            <li>Vos produits sont de nouveau visibles sur la plateforme</li>
            <li>Vous pouvez traiter les commandes normalement</li>
        </ul>
        <p>Nous vous remercions de votre compréhension et vous souhaitons une excellente continuation sur notre plateforme.</p>
        <p><a href='" . route('login') . "' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Se connecter maintenant</a></p>
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

public function index(Request $request)
{
    /** @var User $user */
    $user = Auth::user();

    if (!$user->isSystemAdmin()) {
        abort(403);
    }

    // Get search parameters
    $pendingSearch = $request->get('pending_search');
    $approvedSearch = $request->get('approved_search');
    $suspendedSearch = $request->get('suspended_search');

    // Build queries with search functionality
    $pendingQuery = Cooperative::with('admin')->where('status', 'pending');
    $approvedQuery = Cooperative::with('admin')->where('status', 'approved');
    $suspendedQuery = Cooperative::with(['admin', 'suspendedBy'])->where('status', 'suspended');

    // Apply search filters if provided
    if ($pendingSearch) {
        $pendingQuery->where('name', 'LIKE', '%' . $pendingSearch . '%');
    }

    if ($approvedSearch) {
        $approvedQuery->where('name', 'LIKE', '%' . $approvedSearch . '%');
    }

    if ($suspendedSearch) {
        $suspendedQuery->where('name', 'LIKE', '%' . $suspendedSearch . '%');
    }

    // Get paginated results
    $pendingCooperatives = $pendingQuery->orderBy('created_at', 'desc')->paginate(10, ['*'], 'pending_page');
    $approvedCooperatives = $approvedQuery->orderBy('updated_at', 'desc')->paginate(10, ['*'], 'approved_page');
    $suspendedCooperatives = $suspendedQuery->orderBy('suspended_at', 'desc')->paginate(10, ['*'], 'suspended_page');

    // Preserve search parameters in pagination links
    if ($pendingSearch) {
        $pendingCooperatives->appends(['pending_search' => $pendingSearch]);
    }
    if ($approvedSearch) {
        $approvedCooperatives->appends(['approved_search' => $approvedSearch]);
    }
    if ($suspendedSearch) {
        $suspendedCooperatives->appends(['suspended_search' => $suspendedSearch]);
    }

    return view('admin.cooperatives.index', compact(
        'pendingCooperatives',
        'approvedCooperatives',
        'suspendedCooperatives'
    ));
}

/**
 * Search cooperatives via AJAX
 */
public function search(Request $request)
{
    /** @var User $user */
    $user = Auth::user();

    if (!$user->isSystemAdmin()) {
        return response()->json(['error' => 'Unauthorized'], 403);
    }

    $request->validate([
        'query' => 'required|string|min:1|max:255',
        'status' => 'required|in:pending,approved,suspended'
    ]);

    $query = $request->get('query');
    $status = $request->get('status');

    $cooperatives = Cooperative::with('admin')
        ->where('status', $status)
        ->where('name', 'LIKE', '%' . $query . '%')
        ->orderBy('created_at', 'desc')
        ->limit(20)
        ->get();

    $results = $cooperatives->map(function ($cooperative) {
        return [
            'id' => $cooperative->id,
            'name' => $cooperative->name,
            'legal_status' => $cooperative->legal_status,
            'sector_of_activity' => $cooperative->sector_of_activity,
            'email' => $cooperative->email,
            'phone' => $cooperative->phone,
            'status' => $cooperative->status,
            'created_at' => $cooperative->created_at->format('d/m/Y H:i'),
            'admin' => $cooperative->admin ? [
                'full_name' => $cooperative->admin->full_name,
                'email' => $cooperative->admin->email,
                'status' => $cooperative->admin->status,
                'email_verified_at' => $cooperative->admin->email_verified_at
            ] : null,
            'email_verified_at' => $cooperative->email_verified_at,
            'suspended_at' => $cooperative->suspended_at ? $cooperative->suspended_at->format('d/m/Y H:i') : null,
            'suspension_reason' => $cooperative->suspension_reason,
            'suspended_by' => $cooperative->suspendedBy ? $cooperative->suspendedBy->full_name : null
        ];
    });

    return response()->json([
        'success' => true,
        'results' => $results,
        'count' => $results->count()
    ]);
}
}
