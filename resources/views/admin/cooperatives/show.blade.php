@extends('layouts.app')

@section('title', 'Détails Coopérative - Admin')

@section('content')
<div class="container-fluid py-4">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">{{ $cooperative->name }}</h1>
                    <p class="text-muted">
                        <span class="badge bg-{{ $cooperative->status === 'pending' ? 'warning' : ($cooperative->status === 'approved' ? 'success' : ($cooperative->status === 'suspended' ? 'danger' : 'secondary')) }}">
                            {{ ucfirst($cooperative->status) }}
                        </span>
                        • Demande soumise le {{ $cooperative->created_at->format('d/m/Y à H:i') }}
                        @if($cooperative->suspended_at)
                            • Suspendue le {{ $cooperative->suspended_at->format('d/m/Y à H:i') }}
                        @endif
                    </p>
                </div>
                <a href="{{ route('admin.cooperatives.index') }}" class="btn btn-outline-primary">
                    <i class="fas fa-arrow-left me-2"></i>
                    Retour à la liste
                </a>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>
            {{ $errors->first() }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <!-- Cooperative Information -->
        <div class="col-lg-8">
            <!-- Suspension Alert -->
            @if($cooperative->status === 'suspended')
            <div class="alert alert-danger mb-4">
                <div class="d-flex align-items-start">
                    <i class="fas fa-ban fa-2x me-3 mt-1"></i>
                    <div>
                        <h5 class="alert-heading">Coopérative Suspendue</h5>
                        <p class="mb-2">
                            <strong>Date de suspension:</strong> {{ $cooperative->suspended_at->format('d/m/Y à H:i') }}<br>
                            <strong>Suspendue par:</strong> {{ $cooperative->suspendedBy->full_name ?? 'Administrateur' }}
                        </p>
                        <p class="mb-0">
                            <strong>Raison:</strong><br>
                            <em>{{ $cooperative->suspension_reason }}</em>
                        </p>
                    </div>
                </div>
            </div>
            @endif

            <div class="card shadow mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-building me-2"></i>
                        Informations de la Coopérative
                    </h5>
                </div>
                <div class="card-body">
                    <!-- Logo Section -->
                    @if($cooperative->logo_path)
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="text-primary mb-3">
                                <i class="fas fa-image me-2"></i>
                                Logo de la Coopérative
                            </h6>
                            <div class="logo-display-container text-center">
                                <div class="logo-frame">
                                    <img src="{{ Storage::url($cooperative->logo_path) }}"
                                         alt="Logo {{ $cooperative->name }}"
                                         class="cooperative-logo"
                                         onclick="showLogoModal('{{ Storage::url($cooperative->logo_path) }}', '{{ $cooperative->name }}')">
                                </div>
                                <small class="text-muted d-block mt-2">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Cliquez pour agrandir
                                </small>
                            </div>
                        </div>
                    </div>
                    <hr>
                    @endif

                    <!-- Cooperative Details - 2 columns x 4 rows -->
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <strong>Nom:</strong>
                            <p class="mb-1">{{ $cooperative->name }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Statut Juridique:</strong>
                            <p class="mb-1">{{ $cooperative->legal_status }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Secteur d'Activité:</strong>
                            <p class="mb-1">{{ $cooperative->sector_of_activity }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Date de Création:</strong>
                            <p class="mb-1">{{ $cooperative->date_created->format('d/m/Y') }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Email:</strong>
                            <p class="mb-1">
                                {{ $cooperative->email }}
                                <br>
                                @if($cooperative->email_verified_at)
                                    <span class="badge bg-success">
                                        <i class="fas fa-check"></i>
                                        Vérifié le {{ $cooperative->email_verified_at->format('d/m/Y') }}
                                    </span>
                                @else
                                    <span class="badge bg-warning">Email non vérifié</span>
                                @endif
                            </p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Téléphone:</strong>
                            <p class="mb-1">{{ $cooperative->phone }}</p>
                        </div>
                        <div class="col-12 mb-3">
                            <strong>Adresse:</strong>
                            <p class="mb-1">{{ $cooperative->address }}</p>
                        </div>
                        @if($cooperative->description)
                        <div class="col-12 mb-3">
                            <strong>Description:</strong>
                            <p class="mb-1">{{ $cooperative->description }}</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            @if($cooperative->admin)
            <div class="card shadow">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-user-tie me-2"></i>
                        Informations de l'Administrateur
                    </h5>
                </div>
                <div class="card-body">
                    <!-- Admin Details - 2 columns x 3 rows -->
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <strong>Nom Complet:</strong>
                            <p class="mb-1">{{ $cooperative->admin->full_name }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Email Personnel:</strong>
                            <p class="mb-1">{{ $cooperative->admin->email }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Téléphone:</strong>
                            <p class="mb-1">{{ $cooperative->admin->phone ?? 'Non renseigné' }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Statut du Compte:</strong>
                            <p class="mb-1">
                                <span class="badge bg-{{ $cooperative->admin->status === 'active' ? 'success' : ($cooperative->admin->status === 'pending' ? 'warning' : 'danger') }}">
                                    {{ ucfirst($cooperative->admin->status) }}
                                </span>
                            </p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Email Vérifié:</strong>
                            <p class="mb-1">
                                @if($cooperative->admin->email_verified_at)
                                    <span class="badge bg-success">
                                        <i class="fas fa-check"></i>
                                        Vérifié le {{ $cooperative->admin->email_verified_at->format('d/m/Y') }}
                                    </span>
                                @else
                                    <span class="badge bg-warning">En attente</span>
                                @endif
                            </p>
                        </div>
                        @if($cooperative->admin->address)
                        <div class="col-md-6 mb-3">
                            <strong>Adresse Personnelle:</strong>
                            <p class="mb-1">{{ $cooperative->admin->address }}</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Actions -->
        <div class="col-lg-4">
            @if($cooperative->status === 'pending')
            <div class="card shadow mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-cogs me-2"></i>
                        Actions
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-success" onclick="approveCooperative({{ $cooperative->id }}, '{{ $cooperative->name }}')">
                            <i class="fas fa-check me-2"></i>
                            Approuver la Coopérative
                        </button>

                        <button type="button" class="btn btn-warning" onclick="requestInfo({{ $cooperative->id }}, '{{ $cooperative->name }}')">
                            <i class="fas fa-question-circle me-2"></i>
                            Demander des Infos
                        </button>

                        <button type="button" class="btn btn-danger" onclick="rejectCooperative({{ $cooperative->id }}, '{{ $cooperative->name }}')">
                            <i class="fas fa-times me-2"></i>
                            Rejeter la Demande
                        </button>
                    </div>
                </div>
            </div>
            @elseif($cooperative->status === 'approved')
            <div class="card shadow mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-check-circle me-2 text-success"></i>
                        Coopérative Approuvée
                    </h5>
                </div>
                <div class="card-body">
                    <p class="text-success">
                        <i class="fas fa-check-circle me-2"></i>
                        Cette coopérative a été approuvée le {{ $cooperative->updated_at->format('d/m/Y à H:i') }}.
                    </p>
                    <p class="text-muted mb-3">L'administrateur peut maintenant se connecter et gérer sa coopérative.</p>

                    <div class="d-grid">
                        <button type="button" class="btn btn-warning" onclick="suspendCooperative({{ $cooperative->id }}, '{{ $cooperative->name }}')">
                            <i class="fas fa-ban me-2"></i>
                            Suspendre le Compte
                        </button>
                    </div>
                </div>
            </div>
            @elseif($cooperative->status === 'suspended')
            <div class="card shadow mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-ban me-2 text-danger"></i>
                        Coopérative Suspendue
                    </h5>
                </div>
                <div class="card-body">
                    <p class="text-danger">
                        <i class="fas fa-ban me-2"></i>
                        Cette coopérative a été suspendue le {{ $cooperative->suspended_at->format('d/m/Y à H:i') }}.
                    </p>
                    <p class="text-muted mb-3">L'accès au tableau de bord est bloqué.</p>

                    <div class="d-grid">
                        <button type="button" class="btn btn-success" onclick="unsuspendCooperative({{ $cooperative->id }}, '{{ $cooperative->name }}')">
                            <i class="fas fa-unlock me-2"></i>
                            Lever la Suspension
                        </button>
                    </div>
                </div>
            </div>
            @endif

            <!-- Quick Contact -->
            <div class="card shadow">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-envelope me-2"></i>
                        Contact Rapide
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-outline-primary" onclick="showEmailModal('{{ $cooperative->email }}', 'coopérative')">
                            <i class="fas fa-envelope me-2"></i>
                            Email Coopérative
                        </button>
                        @if($cooperative->admin)
                        <button type="button" class="btn btn-outline-info" onclick="showEmailModal('{{ $cooperative->admin->email }}', 'administrateur')">
                            <i class="fas fa-user me-2"></i>
                            Email Administrateur
                        </button>
                        @endif
                        <button type="button" class="btn btn-outline-success" onclick="showPhoneModal('{{ $cooperative->phone }}')">
                            <i class="fas fa-phone me-2"></i>
                            Appeler
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Logo Modal -->
<div class="modal fade" id="logoModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="logoModalTitle">Logo de la Coopérative</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <img id="logoModalImage" src="" alt="" class="img-fluid rounded">
            </div>
        </div>
    </div>
</div>

<!-- Approve Modal -->
<div class="modal fade" id="approveModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="fas fa-check-circle me-2"></i>
                    Confirmer l'Approbation
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Attention:</strong> Cette action ne peut pas être annulée.
                </div>
                <p>Êtes-vous sûr de vouloir approuver la coopérative <strong id="approveName"></strong>?</p>
                <div class="row">
                    <div class="col-md-6">
                        <h6>Actions automatiques:</h6>
                        <ul class="list-unstyled">
                            <li><i class="fas fa-check text-success me-2"></i>Statut coopérative: <span class="badge bg-success">Approuvée</span></li>
                            <li><i class="fas fa-check text-success me-2"></i>Compte administrateur: <span class="badge bg-success">Activé</span></li>
                            <li><i class="fas fa-check text-success me-2"></i>Email de confirmation envoyé</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h6>Accès autorisés:</h6>
                        <ul class="list-unstyled">
                            <li><i class="fas fa-check text-success me-2"></i>Connexion au tableau de bord</li>
                            <li><i class="fas fa-check text-success me-2"></i>Gestion des produits</li>
                            <li><i class="fas fa-check text-success me-2"></i>Traitement des commandes</li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>
                    Annuler
                </button>
                <form id="approveForm" method="POST" style="display: inline;">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check me-1"></i>
                        Confirmer l'Approbation
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="fas fa-times-circle me-2"></i>
                    Rejeter la Demande
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="rejectForm" method="POST">
                @csrf
                @method('PATCH')
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Attention:</strong> Cette action rejettera définitivement la demande pour <strong id="rejectName"></strong>.
                    </div>
                    <div class="mb-3">
                        <label for="rejection_reason" class="form-label">
                            <i class="fas fa-comment me-1"></i>
                            Raison du rejet *
                        </label>
                        <textarea class="form-control" id="rejection_reason" name="rejection_reason"
                                  rows="4" placeholder="Expliquez clairement pourquoi cette demande est rejetée..." required></textarea>
                        <small class="form-text text-muted">
                            <i class="fas fa-info-circle me-1"></i>
                            Cette raison sera envoyée par email au demandeur.
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>
                        Annuler
                    </button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-times me-1"></i>
                        Confirmer le Rejet
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Request Info Modal -->
<div class="modal fade" id="requestInfoModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title">
                    <i class="fas fa-question-circle me-2"></i>
                    Demander des Informations
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="requestInfoForm" method="POST">
                @csrf
                <div class="modal-body">
                    <p>Demander des informations supplémentaires à <strong id="infoName"></strong>:</p>
                    <div class="mb-3">
                        <label for="info_requested" class="form-label">
                            <i class="fas fa-list me-1"></i>
                            Informations demandées *
                        </label>
                        <textarea class="form-control" id="info_requested" name="info_requested"
                                  rows="4" placeholder="Décrivez précisément les informations supplémentaires nécessaires..." required></textarea>
                        <small class="form-text text-muted">
                            <i class="fas fa-info-circle me-1"></i>
                            Cette demande sera envoyée par email à la coopérative.
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>
                        Annuler
                    </button>
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-paper-plane me-1"></i>
                        Envoyer la Demande
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Email Modal -->
<div class="modal fade" id="emailModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-envelope me-2"></i>
                    Envoyer un Email
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="emailForm">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="emailTo" class="form-label">Destinataire:</label>
                        <input type="email" class="form-control" id="emailTo" name="to" readonly>
                    </div>
                    <div class="mb-3">
                        <label for="emailSubject" class="form-label">Sujet: *</label>
                        <input type="text" class="form-control" id="emailSubject" name="subject" required
                               placeholder="Entrez le sujet de votre message">
                    </div>
                    <div class="mb-3">
                        <label for="emailMessage" class="form-label">Message: *</label>
                        <textarea class="form-control" id="emailMessage" name="message" rows="6" required
                                  placeholder="Écrivez votre message ici..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>
                        Annuler
                    </button>
                    <button type="submit" class="btn btn-primary" id="sendEmailBtn">
                        <span class="spinner-border spinner-border-sm me-2 d-none" role="status"></span>
                        <i class="fas fa-paper-plane me-1"></i>
                        Envoyer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Phone Modal -->
<div class="modal fade" id="phoneModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-phone me-2"></i>
                    Numéro de Téléphone
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <div class="phone-display">
                    <i class="fas fa-phone fa-3x text-success mb-3"></i>
                    <h3 id="phoneNumber" class="text-primary mb-3"></h3>
                    <p class="text-muted">Vous pouvez maintenant composer ce numéro</p>
                </div>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>
                    Fermer
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Suspend Modal -->
<div class="modal fade" id="suspendModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title">
                    <i class="fas fa-ban me-2"></i>
                    Suspendre la Coopérative
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="suspendForm" method="POST" action="{{ route('admin.cooperatives.suspend', $cooperative) }}">
                @csrf
                @method('PATCH')
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Attention:</strong> Cette action suspendra temporairement la coopérative <strong>{{ $cooperative->name }}</strong>.
                    </div>
                    <div class="mb-3">
                        <label for="suspension_reason" class="form-label">Raison de la suspension *</label>
                        <textarea class="form-control" id="suspension_reason" name="suspension_reason"
                                  rows="4" placeholder="Expliquez clairement pourquoi cette coopérative est suspendue..." required></textarea>
                        <small class="form-text text-muted">
                            Cette raison sera envoyée par email à la coopérative ({{ $cooperative->email }}).
                        </small>
                    </div>
                    <div class="alert alert-info">
                        <h6>Conséquences de la suspension:</h6>
                        <ul class="mb-0">
                            <li>Accès au tableau de bord bloqué</li>
                            <li>Produits non visibles sur la plateforme</li>
                            <li>Impossible de traiter les commandes</li>
                            <li>Email de notification automatique envoyé</li>
                        </ul>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-ban me-1"></i>
                        Confirmer la Suspension
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Unsuspend Modal -->
<div class="modal fade" id="unsuspendModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="fas fa-unlock me-2"></i>
                    Lever la Suspension
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    Êtes-vous sûr de vouloir lever la suspension de <strong>{{ $cooperative->name }}</strong>?
                </div>
                <p><strong>Actions qui seront effectuées:</strong></p>
                <ul>
                    <li>Statut remis à "Approuvée"</li>
                    <li>Accès au tableau de bord rétabli</li>
                    <li>Produits de nouveau visibles</li>
                    <li>Email de notification envoyé</li>
                </ul>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <form method="POST" action="{{ route('admin.cooperatives.unsuspend', $cooperative) }}" style="display: inline;">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-unlock me-1"></i>
                        Lever la Suspension
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
.logo-display-container {
    max-width: 300px;
    margin: 0 auto;
}

.logo-frame {
    width: 200px;
    height: 200px;
    margin: 0 auto;
    border: 3px solid #e9ecef;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
    cursor: pointer;
}

.logo-frame:hover {
    border-color: #007bff;
    box-shadow: 0 6px 12px rgba(0,0,0,0.15);
    transform: translateY(-2px);
}

.cooperative-logo {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.cooperative-logo:hover {
    transform: scale(1.05);
}

#logoModalImage {
    max-height: 500px;
    max-width: 100%;
}

.phone-display {
    padding: 20px;
}

#phoneNumber {
    font-family: 'Courier New', monospace;
    font-size: 2rem;
    letter-spacing: 2px;
    border: 2px solid #007bff;
    border-radius: 10px;
    padding: 15px;
    background-color: #f8f9fa;
}

/* Compact layout styles */
.card-body p {
    margin-bottom: 0.5rem;
}

.mb-3 {
    margin-bottom: 0.75rem !important;
}
</style>
@endsection

@push('scripts')
<script>
// Add CSRF token to all AJAX requests
document.addEventListener('DOMContentLoaded', function() {
    // Setup CSRF token for all requests
    const csrfToken = document.querySelector('meta[name="csrf-token"]');
    if (csrfToken) {
        window.axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken.getAttribute('content');
    }
});

function approveCooperative(cooperativeId, cooperativeName) {
    document.getElementById('approveName').textContent = cooperativeName;
    const form = document.getElementById('approveForm');
    form.action = `/admin/cooperatives/${cooperativeId}/approve`;

    const modal = new bootstrap.Modal(document.getElementById('approveModal'));
    modal.show();
}

function rejectCooperative(cooperativeId, cooperativeName) {
    document.getElementById('rejectName').textContent = cooperativeName;
    const form = document.getElementById('rejectForm');
    form.action = `/admin/cooperatives/${cooperativeId}/reject`;

    const modal = new bootstrap.Modal(document.getElementById('rejectModal'));
    modal.show();
}

function requestInfo(cooperativeId, cooperativeName) {
    document.getElementById('infoName').textContent = cooperativeName;
    const form = document.getElementById('requestInfoForm');
    form.action = `/admin/cooperatives/${cooperativeId}/request-info`;

    const modal = new bootstrap.Modal(document.getElementById('requestInfoModal'));
    modal.show();
}

function suspendCooperative(cooperativeId, cooperativeName) {
    const modal = new bootstrap.Modal(document.getElementById('suspendModal'));
    modal.show();
}

function unsuspendCooperative(cooperativeId, cooperativeName) {
    const modal = new bootstrap.Modal(document.getElementById('unsuspendModal'));
    modal.show();
}

function showLogoModal(logoUrl, cooperativeName) {
    document.getElementById('logoModalImage').src = logoUrl;
    document.getElementById('logoModalImage').alt = 'Logo ' + cooperativeName;
    document.getElementById('logoModalTitle').textContent = 'Logo de ' + cooperativeName;

    const modal = new bootstrap.Modal(document.getElementById('logoModal'));
    modal.show();
}

function showEmailModal(email, type) {
    document.getElementById('emailTo').value = email;
    document.getElementById('emailSubject').value = '';
    document.getElementById('emailMessage').value = '';

    // Update modal title
    const modalTitle = document.querySelector('#emailModal .modal-title');
    modalTitle.innerHTML = `<i class="fas fa-envelope me-2"></i>Envoyer un Email - ${type}`;

    const modal = new bootstrap.Modal(document.getElementById('emailModal'));
    modal.show();
}

function showPhoneModal(phone) {
    document.getElementById('phoneNumber').textContent = phone;

    const modal = new bootstrap.Modal(document.getElementById('phoneModal'));
    modal.show();
}

// Handle email form submission
document.addEventListener('DOMContentLoaded', function() {
    const emailForm = document.getElementById('emailForm');
    const sendBtn = document.getElementById('sendEmailBtn');

    if (emailForm && sendBtn) {
        emailForm.addEventListener('submit', function(e) {
            e.preventDefault();

            // Show loading state
            const spinner = sendBtn.querySelector('.spinner-border');
            const text = sendBtn.querySelector('i');

            spinner.classList.remove('d-none');
            sendBtn.disabled = true;

            // Prepare form data
            const formData = new FormData(emailForm);

            // Send email via AJAX
            fetch('{{ route("admin.send-email") }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success message
                    const alert = document.createElement('div');
                    alert.className = 'alert alert-success alert-dismissible fade show';
                    alert.innerHTML = `
                        <i class="fas fa-check-circle me-2"></i>
                        Email envoyé avec succès!
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    `;

                    document.querySelector('.container-fluid').insertBefore(alert, document.querySelector('.row'));

                    // Close modal
                    bootstrap.Modal.getInstance(document.getElementById('emailModal')).hide();

                    // Auto-hide alert after 5 seconds
                    setTimeout(() => alert.remove(), 5000);
                } else {
                    throw new Error(data.message || 'Erreur lors de l\'envoi');
                }
            })
            .catch(error => {
                // Show error message
                const alert = document.createElement('div');
                alert.className = 'alert alert-danger alert-dismissible fade show';
                alert.innerHTML = `
                    <i class="fas fa-exclamation-circle me-2"></i>
                    Erreur lors de l'envoi: ${error.message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                `;

                document.querySelector('.modal-body').insertBefore(alert, document.querySelector('.modal-body').firstChild);

                setTimeout(() => alert.remove(), 5000);
            })
            .finally(() => {
                // Hide loading state
                spinner.classList.add('d-none');
                sendBtn.disabled = false;
            });
        });
    }
});
</script>
@endpush
