@extends('layouts.app')

@section('title', 'Tableau de Bord Coopérative - Coopérative E-commerce')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">Tableau de Bord Coopérative</h1>
                    <p class="text-muted">
                        Bienvenue, {{ Auth::user()->full_name ?? 'Test User' }}
                        @if(Auth::user()->cooperative)
                            - {{ Auth::user()->cooperative->name }}
                        @endif
                    </p>
                </div>
                <div class="text-end">
                    <small class="text-muted">Dernière connexion: {{ Auth::user()->last_login_at?->format('d/m/Y H:i') }}</small>
                </div>
            </div>
        </div>
    </div>

    @if(Auth::user()->status === 'pending')
        <div class="row mb-4">
            <div class="col-12">
                <div class="alert alert-warning">
                    <i class="fas fa-clock me-2"></i>
                    <strong>Inscription en cours d'examen</strong> -
                    Votre demande d'inscription est actuellement examinée par nos administrateurs.
                    Vous recevrez une notification par email une fois votre compte approuvé.
                </div>
            </div>
        </div>
    @endif

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Produits Actifs
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">23</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-box fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Commandes du Mois
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">47</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-shopping-cart fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Revenus du Mois
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">8,420 MAD</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-coins fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Note Moyenne
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">4.8/5</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-star fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- NEW: Admin Management Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <ul class="nav nav-tabs card-header-tabs" id="adminTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="pending-requests-tab" data-bs-toggle="tab" data-bs-target="#pending-requests" type="button" role="tab">
                                <i class="fas fa-clock me-1"></i>
                                Demandes en Attente
                                <span class="badge bg-warning text-dark ms-1" id="pendingRequestsCount">0</span>
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="current-admins-tab" data-bs-toggle="tab" data-bs-target="#current-admins" type="button" role="tab">
                                <i class="fas fa-users me-1"></i>
                                Administrateurs Actuels
                                <span class="badge bg-primary ms-1" id="currentAdminsCount">0</span>
                            </button>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content" id="adminTabsContent">
                        <!-- Pending Requests Tab -->
                        <div class="tab-pane fade show active" id="pending-requests" role="tabpanel">
                            <div id="pendingRequestsContent">
                                <div class="text-center py-4">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Chargement...</span>
                                    </div>
                                    <p class="mt-2 text-muted">Chargement des demandes...</p>
                                </div>
                            </div>
                        </div>

                        <!-- Current Admins Tab -->
                        <div class="tab-pane fade" id="current-admins" role="tabpanel">
                            <div id="currentAdminsContent">
                                <div class="text-center py-4">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Chargement...</span>
                                    </div>
                                    <p class="mt-2 text-muted">Chargement des administrateurs...</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Content Row -->
    <div class="row">
        <!-- Recent Orders -->
        <div class="col-lg-8 mb-4">
            <div class="card shadow">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Commandes Récentes</h6>
                    <a href="#" class="btn btn-primary btn-sm">Voir tout</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>N° Commande</th>
                                    <th>Client</th>
                                    <th>Produits</th>
                                    <th>Montant</th>
                                    <th>Statut</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>CMD-2025-001</td>
                                    <td>Amina Benali</td>
                                    <td>2 articles</td>
                                    <td>350 MAD</td>
                                    <td><span class="badge bg-success">Confirmée</span></td>
                                    <td>23/05/2025</td>
                                </tr>
                                <tr>
                                    <td>CMD-2025-002</td>
                                    <td>Hassan Rachid</td>
                                    <td>1 article</td>
                                    <td>180 MAD</td>
                                    <td><span class="badge bg-warning">En cours</span></td>
                                    <td>22/05/2025</td>
                                </tr>
                                <tr>
                                    <td>CMD-2025-003</td>
                                    <td>Fatima Zahra</td>
                                    <td>3 articles</td>
                                    <td>520 MAD</td>
                                    <td><span class="badge bg-info">Expédiée</span></td>
                                    <td>21/05/2025</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions & Info -->
        <div class="col-lg-4 mb-4">
            <!-- Quick Actions -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Actions Rapides</h6>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        <a href="#" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <div>
                                <i class="fas fa-plus text-success me-3"></i>
                                Ajouter Produit
                            </div>
                            <i class="fas fa-chevron-right"></i>
                        </a>
                        <a href="#" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <div>
                                <i class="fas fa-box text-primary me-3"></i>
                                Gérer Stock
                            </div>
                            <i class="fas fa-chevron-right"></i>
                        </a>
                        <a href="#" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <div>
                                <i class="fas fa-shopping-cart text-info me-3"></i>
                                Commandes
                            </div>
                            <i class="fas fa-chevron-right"></i>
                        </a>
                        <a href="#" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <div>
                                <i class="fas fa-chart-line text-warning me-3"></i>
                                Rapports
                            </div>
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Cooperative Info -->
            @if(Auth::user()->cooperative)
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-success">Informations Coopérative</h6>
                </div>
                <div class="card-body">
                    <h6 class="text-primary">{{ Auth::user()->cooperative->name }}</h6>
                    <p class="text-muted mb-2">
                        <i class="fas fa-industry me-2"></i>
                        {{ Auth::user()->cooperative->sector_of_activity }}
                    </p>
                    <p class="text-muted mb-2">
                        <i class="fas fa-envelope me-2"></i>
                        {{ Auth::user()->cooperative->email }}
                    </p>
                    <p class="text-muted mb-2">
                        <i class="fas fa-phone me-2"></i>
                        {{ Auth::user()->cooperative->phone }}
                    </p>
                    <p class="text-muted mb-0">
                        <i class="fas fa-calendar me-2"></i>
                        Créée le {{ Auth::user()->cooperative->date_created->format('d/m/Y') }}
                    </p>
                </div>
            </div>
            @endif
        </div>
    </div>

    <!-- Low Stock Alert -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-warning">Alertes Stock Faible</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Produit</th>
                                    <th>Stock Actuel</th>
                                    <th>Stock Minimum</th>
                                    <th>Statut</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Huile d'Argan Bio 250ml</td>
                                    <td>3</td>
                                    <td>10</td>
                                    <td><span class="badge bg-danger">Stock Critique</span></td>
                                    <td>
                                        <button class="btn btn-primary btn-sm">Réapprovisionner</button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Savon Naturel Lavande</td>
                                    <td>7</td>
                                    <td>15</td>
                                    <td><span class="badge bg-warning">Stock Faible</span></td>
                                    <td>
                                        <button class="btn btn-primary btn-sm">Réapprovisionner</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Request Details Modal -->
<div class="modal fade" id="requestDetailsModal" tabindex="-1" aria-labelledby="requestDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="requestDetailsModalLabel">
                    <i class="fas fa-user-plus me-2"></i>
                    Détails de la Demande d'Adhésion
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="requestDetailsContent">
                <!-- Content will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                <button type="button" class="btn btn-warning me-2" id="clarificationBtn">
                    <i class="fas fa-question-circle me-1"></i>
                    Demander Clarification
                </button>
                <button type="button" class="btn btn-danger me-2" id="rejectBtn">
                    <i class="fas fa-times me-1"></i>
                    Rejeter
                </button>
                <button type="button" class="btn btn-success" id="approveBtn">
                    <i class="fas fa-check me-1"></i>
                    Approuver
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Response Modal -->
<div class="modal fade" id="responseModal" tabindex="-1" aria-labelledby="responseModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="responseModalLabel">
                    <i class="fas fa-comment me-2"></i>
                    <span id="responseModalTitle">Message</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="responseForm">
                    <div class="mb-3">
                        <label for="responseMessage" class="form-label" id="responseMessageLabel">Message</label>
                        <textarea class="form-control" id="responseMessage" name="response_message" rows="4" placeholder="Tapez votre message..."></textarea>
                        <div class="form-text" id="responseMessageHelp">Ce message sera envoyé par email au candidat.</div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-primary" id="sendResponseBtn">
                    <i class="fas fa-paper-plane me-1"></i>
                    <span id="sendResponseText">Envoyer</span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Admin Removal Confirmation Modal -->
<div class="modal fade" id="removeAdminModal" tabindex="-1" aria-labelledby="removeAdminModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="removeAdminModalLabel">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Confirmer le Retrait
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    <i class="fas fa-warning me-2"></i>
                    <strong>Attention!</strong> Cette action est irréversible.
                </div>
                <p>Êtes-vous sûr de vouloir retirer <strong id="adminToRemoveName"></strong> de l'administration de la coopérative?</p>
                <p class="text-muted small">L'administrateur sera notifié par email et perdra immédiatement l'accès aux fonctionnalités d'administration.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-danger" id="confirmRemoveBtn">
                    <i class="fas fa-user-minus me-1"></i>
                    Confirmer le Retrait
                </button>
            </div>
        </div>
    </div>
</div>

<style>
.border-left-primary { border-left: 0.25rem solid #4e73df !important; }
.border-left-success { border-left: 0.25rem solid #1cc88a !important; }
.border-left-info { border-left: 0.25rem solid #36b9cc !important; }
.border-left-warning { border-left: 0.25rem solid #f6c23e !important; }

.request-card {
    transition: all 0.2s ease;
    cursor: pointer;
}

.request-card:hover {
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    transform: translateY(-1px);
}

.admin-avatar {
    width: 40px;
    height: 40px;
    background: linear-gradient(45deg, #007bff, #0056b3);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: bold;
    border-radius: 50%;
}

.status-badge {
    font-size: 0.75rem;
    padding: 0.25rem 0.5rem;
}

.nav-tabs .nav-link {
    border: none;
    color: #6c757d;
}

.nav-tabs .nav-link.active {
    background-color: #f8f9fa;
    border-bottom: 2px solid #007bff;
    color: #007bff;
}

.empty-state {
    text-align: center;
    padding: 3rem 1rem;
    color: #6c757d;
}

.empty-state i {
    font-size: 3rem;
    margin-bottom: 1rem;
    opacity: 0.5;
}
</style>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    let currentRequestId = null;
    let currentAdminId = null;
    let currentAction = null;

    // Modals
    const requestDetailsModal = new bootstrap.Modal(document.getElementById('requestDetailsModal'));
    const responseModal = new bootstrap.Modal(document.getElementById('responseModal'));
    const removeAdminModal = new bootstrap.Modal(document.getElementById('removeAdminModal'));

    // Load initial data
    loadPendingRequests();

    // Tab change handler
    document.getElementById('current-admins-tab').addEventListener('click', function() {
        loadCurrentAdmins();
    });

    // Load pending requests
    function loadPendingRequests() {
        fetch('{{ route("coop.admin-requests.pending") }}')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    displayPendingRequests(data.requests);
                    updateRequestsCount(data.requests.length);
                } else {
                    showError('Erreur lors du chargement des demandes');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showError('Erreur de connexion');
            });
    }

    // Load current admins
    function loadCurrentAdmins() {
        fetch('{{ route("coop.admin-requests.current-admins") }}')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    displayCurrentAdmins(data.admins);
                    updateAdminsCount(data.admins.length);
                } else {
                    showError('Erreur lors du chargement des administrateurs');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showError('Erreur de connexion');
            });
    }

    // Display pending requests
    function displayPendingRequests(requests) {
        const container = document.getElementById('pendingRequestsContent');

        if (requests.length === 0) {
            container.innerHTML = `
                <div class="empty-state">
                    <i class="fas fa-inbox"></i>
                    <h5>Aucune demande en attente</h5>
                    <p class="text-muted">Il n'y a actuellement aucune demande d'adhésion en attente d'approbation.</p>
                </div>
            `;
            return;
        }

        container.innerHTML = requests.map(request => `
            <div class="card request-card mb-3" onclick="showRequestDetails(${request.id})">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <div class="admin-avatar">
                                ${request.user.full_name.charAt(0).toUpperCase()}
                            </div>
                        </div>
                        <div class="col">
                            <h6 class="mb-1">${request.user.full_name}</h6>
                            <p class="mb-1 text-muted small">
                                <i class="fas fa-envelope me-1"></i>
                                ${request.user.email}
                            </p>
                            <p class="mb-0 text-muted small">
                                <i class="fas fa-clock me-1"></i>
                                Demandé ${request.requested_at_human}
                            </p>
                        </div>
                        <div class="col-auto">
                            <span class="badge bg-warning status-badge">En attente</span>
                            <div class="mt-1">
                                <i class="fas fa-eye text-primary"></i>
                            </div>
                        </div>
                    </div>
                    ${request.message ? `
                        <div class="mt-2 pt-2 border-top">
                            <small class="text-muted">
                                <strong>Message:</strong> ${request.message.substring(0, 100)}${request.message.length > 100 ? '...' : ''}
                            </small>
                        </div>
                    ` : ''}
                </div>
            </div>
        `).join('');
    }

    // Display current admins
    function displayCurrentAdmins(admins) {
        const container = document.getElementById('currentAdminsContent');

        if (admins.length === 0) {
            container.innerHTML = `
                <div class="empty-state">
                    <i class="fas fa-users"></i>
                    <h5>Aucun administrateur</h5>
                    <p class="text-muted">Il n'y a actuellement aucun administrateur actif.</p>
                </div>
            `;
            return;
        }

        container.innerHTML = `
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Administrateur</th>
                            <th>Email</th>
                            <th>Téléphone</th>
                            <th>Membre depuis</th>
                            <th>Dernière connexion</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${admins.map(admin => `
                            <tr ${admin.is_current_user ? 'class="table-info"' : ''}>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="admin-avatar me-2">
                                            ${admin.full_name.charAt(0).toUpperCase()}
                                        </div>
                                        <div>
                                            ${admin.full_name}
                                            ${admin.is_current_user ? '<small class="text-primary d-block">(Vous)</small>' : ''}
                                        </div>
                                    </div>
                                </td>
                                <td>${admin.email}</td>
                                <td>${admin.phone || '-'}</td>
                                <td>${admin.joined_at}</td>
                                <td>
                                    <small class="text-muted">${admin.last_login}</small>
                                </td>
                                <td>
                                    ${!admin.is_current_user ? `
                                        <button class="btn btn-outline-danger btn-sm" onclick="showRemoveAdminModal(${admin.id}, '${admin.full_name}')">
                                            <i class="fas fa-user-minus"></i>
                                        </button>
                                    ` : '<span class="text-muted">-</span>'}
                                </td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            </div>
        `;
    }

    // Show request details
    window.showRequestDetails = function(requestId) {
        currentRequestId = requestId;

        // Find request data
        fetch('{{ route("coop.admin-requests.pending") }}')
            .then(response => response.json())
            .then(data => {
                const request = data.requests.find(r => r.id === requestId);
                if (request) {
                    displayRequestDetails(request);
                    requestDetailsModal.show();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showError('Erreur lors du chargement des détails');
            });
    };

    // Display request details in modal
    function displayRequestDetails(request) {
        document.getElementById('requestDetailsContent').innerHTML = `
            <div class="row">
                <div class="col-md-8">
                    <h5 class="text-primary mb-3">Informations du Candidat</h5>
                    <div class="row mb-3">
                        <div class="col-sm-4"><strong>Nom complet:</strong></div>
                        <div class="col-sm-8">${request.user.full_name}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4"><strong>Email:</strong></div>
                        <div class="col-sm-8">${request.user.email}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4"><strong>Téléphone:</strong></div>
                        <div class="col-sm-8">${request.user.phone || 'Non renseigné'}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4"><strong>Adresse:</strong></div>
                        <div class="col-sm-8">${request.user.address || 'Non renseignée'}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4"><strong>Date de demande:</strong></div>
                        <div class="col-sm-8">${request.requested_at}</div>
                    </div>
                </div>
                <div class="col-md-4 text-center">
                    <div class="admin-avatar mx-auto mb-3" style="width: 80px; height: 80px; font-size: 2rem;">
                        ${request.user.full_name.charAt(0).toUpperCase()}
                    </div>
                    <span class="badge bg-warning">En attente d'approbation</span>
                </div>
            </div>

            ${request.message ? `
                <hr>
                <h6 class="text-success mb-3">Message du Candidat</h6>
                <div class="alert alert-light">
                    <p class="mb-0">${request.message}</p>
                </div>
            ` : ''}
        `;
    }

    // Button handlers
    document.getElementById('approveBtn').addEventListener('click', function() {
        currentAction = 'approve';
        showResponseModal('Approuver la Demande', 'Message d\'approbation (optionnel)', 'Approuver', false);
    });

    document.getElementById('rejectBtn').addEventListener('click', function() {
        currentAction = 'reject';
        showResponseModal('Rejeter la Demande', 'Motif du refus', 'Rejeter', true);
    });

    document.getElementById('clarificationBtn').addEventListener('click', function() {
        currentAction = 'clarification';
        showResponseModal('Demander Clarification', 'Questions ou clarifications demandées', 'Envoyer', true);
    });

    // Show response modal
    function showResponseModal(title, label, buttonText, required) {
        document.getElementById('responseModalTitle').textContent = title;
        document.getElementById('responseMessageLabel').textContent = label;
        document.getElementById('sendResponseText').textContent = buttonText;

        const textarea = document.getElementById('responseMessage');
        textarea.value = '';
        textarea.required = required;

        if (required) {
            document.getElementById('responseMessageHelp').textContent = 'Ce champ est requis.';
        } else {
            document.getElementById('responseMessageHelp').textContent = 'Ce message sera envoyé par email au candidat.';
        }

        requestDetailsModal.hide();
        responseModal.show();
    }

    // Send response
    document.getElementById('sendResponseBtn').addEventListener('click', function() {
        const message = document.getElementById('responseMessage').value.trim();

        if (currentAction === 'reject' && !message) {
            showError('Le motif du refus est requis');
            return;
        }

        if (currentAction === 'clarification' && !message) {
            showError('Le message de clarification est requis');
            return;
        }

        const url = currentAction === 'approve'
            ? `{{ url('/coop/admin-requests') }}/${currentRequestId}/approve`
            : currentAction === 'reject'
            ? `{{ url('/coop/admin-requests') }}/${currentRequestId}/reject`
            : `{{ url('/coop/admin-requests') }}/${currentRequestId}/clarification`;

        showLoading(document.getElementById('sendResponseBtn'));

        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                response_message: message
            })
        })
        .then(response => response.json())
        .then(data => {
            hideLoading(document.getElementById('sendResponseBtn'));

            if (data.success) {
                responseModal.hide();
                showSuccess(data.message);
                loadPendingRequests(); // Refresh the list
            } else {
                showError(data.message || 'Erreur lors du traitement');
            }
        })
        .catch(error => {
            hideLoading(document.getElementById('sendResponseBtn'));
            console.error('Error:', error);
            showError('Erreur de connexion');
        });
    });

    // Show admin removal modal
    window.showRemoveAdminModal = function(adminId, adminName) {
        currentAdminId = adminId;
        document.getElementById('adminToRemoveName').textContent = adminName;
        removeAdminModal.show();
    };

    // Confirm admin removal
    document.getElementById('confirmRemoveBtn').addEventListener('click', function() {
        showLoading(this);

        fetch(`{{ url('/coop/admins') }}/${currentAdminId}/remove`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            hideLoading(document.getElementById('confirmRemoveBtn'));

            if (data.success) {
                removeAdminModal.hide();
                showSuccess(data.message);
                loadCurrentAdmins(); // Refresh the list
            } else {
                showError(data.message || 'Erreur lors du retrait');
            }
        })
        .catch(error => {
            hideLoading(document.getElementById('confirmRemoveBtn'));
            console.error('Error:', error);
            showError('Erreur de connexion');
        });
    });

    // Update badge counts
    function updateRequestsCount(count) {
        document.getElementById('pendingRequestsCount').textContent = count;
    }

    function updateAdminsCount(count) {
        document.getElementById('currentAdminsCount').textContent = count;
    }

    // Utility functions
    function showLoading(button) {
        button.disabled = true;
        const originalText = button.innerHTML;
        button.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Traitement...';
        button.setAttribute('data-original-text', originalText);
    }

    function hideLoading(button) {
        button.disabled = false;
        button.innerHTML = button.getAttribute('data-original-text');
    }

    function showSuccess(message) {
        showAlert(message, 'success');
    }

    function showError(message) {
        showAlert(message, 'danger');
    }

    function showAlert(message, type) {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
        alertDiv.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;

        document.body.appendChild(alertDiv);

        setTimeout(() => {
            if (alertDiv.parentNode) {
                alertDiv.parentNode.removeChild(alertDiv);
            }
        }, 5000);
    }

    // Auto-refresh pending requests every 30 seconds
    setInterval(function() {
        if (document.getElementById('pending-requests-tab').classList.contains('active')) {
            loadPendingRequests();
        }
    }, 30000);
});
</script>
@endpush
