@extends('layouts.app')

@section('title', 'Détails Coopérative - Admin')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">{{ $cooperative->name }}</h1>
                    <p class="text-muted">
                        <span class="badge bg-{{ $cooperative->status === 'pending' ? 'warning' : ($cooperative->status === 'approved' ? 'success' : 'danger') }}">
                            {{ ucfirst($cooperative->status) }}
                        </span>
                        • Demande soumise le {{ $cooperative->created_at->format('d/m/Y à H:i') }}
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

    <div class="row">
        <!-- Cooperative Information -->
        <div class="col-lg-8">
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

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <strong>Nom:</strong>
                            <p>{{ $cooperative->name }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Statut Juridique:</strong>
                            <p>{{ $cooperative->legal_status }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Secteur d'Activité:</strong>
                            <p>{{ $cooperative->sector_of_activity }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Date de Création:</strong>
                            <p>{{ $cooperative->date_created->format('d/m/Y') }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Email:</strong>
                            <p>
                                <a href="mailto:{{ $cooperative->email }}">{{ $cooperative->email }}</a>
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
                            <p>
                                <a href="tel:{{ $cooperative->phone }}">{{ $cooperative->phone }}</a>
                            </p>
                        </div>
                        <div class="col-12 mb-3">
                            <strong>Adresse:</strong>
                            <p>{{ $cooperative->address }}</p>
                        </div>
                        @if($cooperative->description)
                        <div class="col-12 mb-3">
                            <strong>Description:</strong>
                            <p>{{ $cooperative->description }}</p>
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
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <strong>Nom Complet:</strong>
                            <p>{{ $cooperative->admin->full_name }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Email Personnel:</strong>
                            <p>
                                <a href="mailto:{{ $cooperative->admin->email }}">{{ $cooperative->admin->email }}</a>
                            </p>
                        </div>
                        @if($cooperative->admin->phone)
                        <div class="col-md-6 mb-3">
                            <strong>Téléphone:</strong>
                            <p>
                                <a href="tel:{{ $cooperative->admin->phone }}">{{ $cooperative->admin->phone }}</a>
                            </p>
                        </div>
                        @endif
                        @if($cooperative->admin->address)
                        <div class="col-12 mb-3">
                            <strong>Adresse Personnelle:</strong>
                            <p>{{ $cooperative->admin->address }}</p>
                        </div>
                        @endif
                        <div class="col-md-6 mb-3">
                            <strong>Statut du Compte:</strong>
                            <p>
                                <span class="badge bg-{{ $cooperative->admin->status === 'active' ? 'success' : ($cooperative->admin->status === 'pending' ? 'warning' : 'danger') }}">
                                    {{ ucfirst($cooperative->admin->status) }}
                                </span>
                            </p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Email Vérifié:</strong>
                            <p>
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
                        <button type="button" class="btn btn-success" onclick="approveCooperative({{ $cooperative->id }})">
                            <i class="fas fa-check me-2"></i>
                            Approuver la Coopérative
                        </button>

                        <button type="button" class="btn btn-warning" onclick="requestInfo()">
                            <i class="fas fa-question-circle me-2"></i>
                            Demander des Infos
                        </button>

                        <button type="button" class="btn btn-danger" onclick="rejectCooperative({{ $cooperative->id }})">
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
                    <p class="text-muted">L'administrateur peut maintenant se connecter et gérer sa coopérative.</p>
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
                        <a href="mailto:{{ $cooperative->email }}" class="btn btn-outline-primary">
                            <i class="fas fa-envelope me-2"></i>
                            Email Coopérative
                        </a>
                        @if($cooperative->admin)
                        <a href="mailto:{{ $cooperative->admin->email }}" class="btn btn-outline-info">
                            <i class="fas fa-user me-2"></i>
                            Email Administrateur
                        </a>
                        @endif
                        <a href="tel:{{ $cooperative->phone }}" class="btn btn-outline-success">
                            <i class="fas fa-phone me-2"></i>
                            Appeler
                        </a>
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

<!-- Action Modals (unchanged, keeping existing modals for approve/reject/request info) -->
<!-- Approve Modal -->
<div class="modal fade" id="approveModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmer l'Approbation</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Attention:</strong> Cette action ne peut pas être annulée.
                </div>
                <p>Êtes-vous sûr de vouloir approuver la coopérative <strong>{{ $cooperative->name }}</strong>?</p>
                <p><strong>Actions qui seront effectuées:</strong></p>
                <ul>
                    <li>Statut de la coopérative: <span class="badge bg-success">Approuvée</span></li>
                    <li>Compte administrateur: <span class="badge bg-success">Activé</span></li>
                    <li>Email de confirmation envoyé à: {{ $cooperative->email }}</li>
                </ul>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <form method="POST" action="{{ route('admin.cooperatives.approve', $cooperative) }}" style="display: inline;">
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
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Rejeter la Demande</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('admin.cooperatives.reject', $cooperative) }}">
                @csrf
                @method('PATCH')
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Attention:</strong> Cette action rejettera définitivement la demande.
                    </div>
                    <div class="mb-3">
                        <label for="rejection_reason" class="form-label">Raison du rejet *</label>
                        <textarea class="form-control" id="rejection_reason" name="rejection_reason"
                                  rows="4" placeholder="Expliquez clairement pourquoi cette demande est rejetée..." required></textarea>
                        <small class="form-text text-muted">
                            Cette raison sera envoyée par email au demandeur ({{ $cooperative->email }}).
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
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
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Demander des Informations Supplémentaires</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('admin.cooperatives.request-info', $cooperative) }}">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="info_requested" class="form-label">Informations demandées *</label>
                        <textarea class="form-control" id="info_requested" name="info_requested"
                                  rows="4" placeholder="Décrivez précisément les informations supplémentaires nécessaires..." required></textarea>
                        <small class="form-text text-muted">
                            Cette demande sera envoyée par email à: {{ $cooperative->email }}
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-paper-plane me-1"></i>
                        Envoyer la Demande
                    </button>
                </div>
            </form>
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
</style>
@endsection

@push('scripts')
<script>
function approveCooperative(cooperativeId) {
    const modal = new bootstrap.Modal(document.getElementById('approveModal'));
    modal.show();
}

function rejectCooperative(cooperativeId) {
    const modal = new bootstrap.Modal(document.getElementById('rejectModal'));
    modal.show();
}

function requestInfo() {
    const modal = new bootstrap.Modal(document.getElementById('requestInfoModal'));
    modal.show();
}

function showLogoModal(logoUrl, cooperativeName) {
    document.getElementById('logoModalImage').src = logoUrl;
    document.getElementById('logoModalImage').alt = 'Logo ' + cooperativeName;
    document.getElementById('logoModalTitle').textContent = 'Logo de ' + cooperativeName;

    const modal = new bootstrap.Modal(document.getElementById('logoModal'));
    modal.show();
}
</script>
@endpush
