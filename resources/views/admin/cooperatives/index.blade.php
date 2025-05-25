@extends('layouts.app')

@section('title', 'Gestion des Coopératives - Admin')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-building me-2"></i>
                        Gestion des Coopératives
                    </h1>
                    <p class="text-muted">Gérer les demandes d'inscription et les coopératives actives</p>
                </div>
                <div>
                    <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-primary me-2">
                        <i class="fas fa-arrow-left me-2"></i>
                        Retour au tableau de bord
                    </a>
                    <button class="btn btn-info" onclick="refreshPage()">
                        <i class="fas fa-sync-alt me-2"></i>
                        Actualiser
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Alerts -->
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

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                En Attente
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $pendingCooperatives->total() }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Approuvées
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $approvedCooperatives->total() }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                Suspendues
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $suspendedCooperatives->total() }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-ban fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Prêtes à Approuver
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $pendingCooperatives->filter(function($coop) {
                                    return $coop->email_verified_at && $coop->admin && $coop->admin->email_verified_at;
                                })->count() }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-thumbs-up fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabs -->
    <ul class="nav nav-tabs" id="coopTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="pending-tab" data-bs-toggle="tab" data-bs-target="#pending" type="button" role="tab">
                <i class="fas fa-clock me-2"></i>
                En Attente
                <span class="badge bg-warning ms-2">{{ $pendingCooperatives->total() }}</span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="approved-tab" data-bs-toggle="tab" data-bs-target="#approved" type="button" role="tab">
                <i class="fas fa-check me-2"></i>
                Approuvées
                <span class="badge bg-success ms-2">{{ $approvedCooperatives->total() }}</span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="suspended-tab" data-bs-toggle="tab" data-bs-target="#suspended" type="button" role="tab">
                <i class="fas fa-ban me-2"></i>
                Suspendues
                <span class="badge bg-danger ms-2">{{ $suspendedCooperatives->total() }}</span>
            </button>
        </li>
    </ul>

    <div class="tab-content" id="coopTabsContent">
        <!-- Pending Cooperatives Tab -->
        <div class="tab-pane fade show active" id="pending" role="tabpanel">
            <div class="card mt-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-hourglass-half me-2"></i>
                        Demandes en Attente de Validation
                    </h5>
                    <div>
                        <button class="btn btn-sm btn-outline-info" onclick="toggleVerificationFilter()">
                            <i class="fas fa-filter me-1"></i>
                            Filtrer par vérification
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    @if($pendingCooperatives->count() > 0)
                        <!-- Legend -->
                        <div class="alert alert-info mb-3">
                            <h6><i class="fas fa-info-circle me-2"></i>Légende des Vérifications:</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <span class="badge bg-success me-2"><i class="fas fa-user"></i> Admin ✓</span> Email administrateur vérifié<br>
                                    <span class="badge bg-success me-2"><i class="fas fa-building"></i> Coop ✓</span> Email coopérative vérifié
                                </div>
                                <div class="col-md-6">
                                    <span class="badge bg-danger me-2"><i class="fas fa-user"></i> Admin ✗</span> Email administrateur non vérifié<br>
                                    <span class="badge bg-danger me-2"><i class="fas fa-building"></i> Coop ✗</span> Email coopérative non vérifié
                                </div>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover" id="pendingTable">
                                <thead class="table-dark">
                                    <tr>
                                        <th>
                                            <i class="fas fa-building me-1"></i>
                                            Coopérative
                                        </th>
                                        <th>
                                            <i class="fas fa-industry me-1"></i>
                                            Secteur
                                        </th>
                                        <th>
                                            <i class="fas fa-user-tie me-1"></i>
                                            Administrateur
                                        </th>
                                        <th>
                                            <i class="fas fa-envelope me-1"></i>
                                            Contact
                                        </th>
                                        <th>
                                            <i class="fas fa-shield-check me-1"></i>
                                            Vérification Emails
                                        </th>
                                        <th>
                                            <i class="fas fa-calendar me-1"></i>
                                            Date Demande
                                        </th>
                                        <th>
                                            <i class="fas fa-cogs me-1"></i>
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($pendingCooperatives as $cooperative)
                                    @php
                                        $adminVerified = $cooperative->admin && $cooperative->admin->email_verified_at;
                                        $coopVerified = $cooperative->email_verified_at;
                                        $fullyVerified = $adminVerified && $coopVerified;
                                    @endphp
                                    <tr class="{{ $fullyVerified ? 'table-success' : 'table-warning' }}">
                                        <td>
                                            <div>
                                                <strong class="text-primary">{{ $cooperative->name }}</strong>
                                                <br>
                                                <small class="text-muted">
                                                    <i class="fas fa-certificate me-1"></i>
                                                    {{ $cooperative->legal_status }}
                                                </small>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-info">{{ $cooperative->sector_of_activity }}</span>
                                        </td>
                                        <td>
                                            @if($cooperative->admin)
                                                <div>
                                                    <strong>{{ $cooperative->admin->full_name }}</strong>
                                                    <br>
                                                    <small class="text-muted">
                                                        <i class="fas fa-envelope me-1"></i>
                                                        {{ $cooperative->admin->email }}
                                                    </small>
                                                    <br>
                                                    <small class="text-muted">
                                                        Statut:
                                                        <span class="badge bg-{{ $cooperative->admin->status === 'active' ? 'success' : ($cooperative->admin->status === 'pending' ? 'warning' : 'danger') }}">
                                                            {{ ucfirst($cooperative->admin->status) }}
                                                        </span>
                                                    </small>
                                                </div>
                                            @else
                                                <span class="text-muted">
                                                    <i class="fas fa-exclamation-triangle me-1"></i>
                                                    Aucun administrateur
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            <div>
                                                <small class="text-muted d-block">
                                                    <i class="fas fa-envelope me-1"></i>
                                                    <a href="mailto:{{ $cooperative->email }}">{{ $cooperative->email }}</a>
                                                </small>
                                                <small class="text-muted d-block">
                                                    <i class="fas fa-phone me-1"></i>
                                                    <a href="tel:{{ $cooperative->phone }}">{{ $cooperative->phone }}</a>
                                                </small>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="verification-status">
                                                <!-- Admin Email Status -->
                                                <div class="mb-1">
                                                    @if($adminVerified)
                                                        <span class="badge bg-success" title="Email administrateur vérifié le {{ $cooperative->admin->email_verified_at->format('d/m/Y H:i') }}">
                                                            <i class="fas fa-user me-1"></i>Admin ✓
                                                        </span>
                                                    @else
                                                        <span class="badge bg-danger" title="Email administrateur non vérifié">
                                                            <i class="fas fa-user me-1"></i>Admin ✗
                                                        </span>
                                                    @endif
                                                </div>

                                                <!-- Cooperative Email Status -->
                                                <div>
                                                    @if($coopVerified)
                                                        <span class="badge bg-success" title="Email coopérative vérifié le {{ $cooperative->email_verified_at->format('d/m/Y H:i') }}">
                                                            <i class="fas fa-building me-1"></i>Coop ✓
                                                        </span>
                                                    @else
                                                        <span class="badge bg-danger" title="Email coopérative non vérifié">
                                                            <i class="fas fa-building me-1"></i>Coop ✗
                                                        </span>
                                                    @endif
                                                </div>

                                                <!-- Overall Status -->
                                                @if($fullyVerified)
                                                    <div class="mt-1">
                                                        <span class="badge bg-primary">
                                                            <i class="fas fa-thumbs-up me-1"></i>Prêt à approuver
                                                        </span>
                                                    </div>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            <div>
                                                <strong>{{ $cooperative->created_at->format('d/m/Y') }}</strong>
                                                <br>
                                                <small class="text-muted">{{ $cooperative->created_at->format('H:i') }}</small>
                                                <br>
                                                <small class="text-muted">
                                                    <i class="fas fa-clock me-1"></i>
                                                    {{ $cooperative->created_at->diffForHumans() }}
                                                </small>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="btn-group-vertical" role="group">
                                                <!-- View Details Button -->
                                                <a href="{{ route('admin.cooperatives.show', $cooperative) }}"
                                                   class="btn btn-info btn-sm mb-1" title="Voir les détails complets">
                                                    <i class="fas fa-eye me-1"></i>Détails
                                                </a>

                                                <!-- Approve Button -->
                                                @if($fullyVerified)
                                                    <button type="button" class="btn btn-success btn-sm mb-1"
                                                            onclick="approveCooperative({{ $cooperative->id }}, '{{ $cooperative->name }}')"
                                                            title="Approuver cette coopérative">
                                                        <i class="fas fa-check me-1"></i>Approuver
                                                    </button>
                                                @else
                                                    <button type="button" class="btn btn-secondary btn-sm mb-1"
                                                            title="Emails non vérifiés - Approbation impossible" disabled>
                                                        <i class="fas fa-exclamation-triangle me-1"></i>En attente
                                                    </button>
                                                @endif

                                                <!-- Request Info Button -->
                                                <button type="button" class="btn btn-warning btn-sm mb-1"
                                                        onclick="requestInfo({{ $cooperative->id }}, '{{ $cooperative->name }}')"
                                                        title="Demander des informations supplémentaires">
                                                    <i class="fas fa-question-circle me-1"></i>Info
                                                </button>

                                                <!-- Reject Button -->
                                                <button type="button" class="btn btn-danger btn-sm"
                                                        onclick="rejectCooperative({{ $cooperative->id }}, '{{ $cooperative->name }}')"
                                                        title="Rejeter cette demande">
                                                    <i class="fas fa-times me-1"></i>Rejeter
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="d-flex justify-content-center mt-3">
                            {{ $pendingCooperatives->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-check-circle fa-4x text-success mb-3"></i>
                            <h4>Aucune demande en attente</h4>
                            <p class="text-muted">Toutes les demandes d'inscription ont été traitées.</p>
                            <a href="{{ route('admin.dashboard') }}" class="btn btn-primary">
                                <i class="fas fa-arrow-left me-2"></i>
                                Retour au tableau de bord
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Approved Cooperatives Tab -->
        <div class="tab-pane fade" id="approved" role="tabpanel">
            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-check-circle me-2"></i>
                        Coopératives Approuvées et Actives
                    </h5>
                </div>
                <div class="card-body">
                    @if($approvedCooperatives->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-success">
                                    <tr>
                                        <th>Coopérative</th>
                                        <th>Secteur</th>
                                        <th>Administrateur</th>
                                        <th>Contact</th>
                                        <th>Date Approbation</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($approvedCooperatives as $cooperative)
                                    <tr>
                                        <td>
                                            <div>
                                                <strong class="text-success">{{ $cooperative->name }}</strong>
                                                <br>
                                                <small class="text-muted">{{ $cooperative->legal_status }}</small>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-success">{{ $cooperative->sector_of_activity }}</span>
                                        </td>
                                        <td>
                                            @if($cooperative->admin)
                                                <div>
                                                    {{ $cooperative->admin->full_name }}
                                                    <br>
                                                    <small class="text-muted">{{ $cooperative->admin->email }}</small>
                                                    <br>
                                                    <span class="badge bg-success">Actif</span>
                                                </div>
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div>
                                                <small>
                                                    <i class="fas fa-envelope me-1"></i>
                                                    <a href="mailto:{{ $cooperative->email }}">{{ $cooperative->email }}</a>
                                                </small>
                                                <br>
                                                <small>
                                                    <i class="fas fa-phone me-1"></i>
                                                    <a href="tel:{{ $cooperative->phone }}">{{ $cooperative->phone }}</a>
                                                </small>
                                            </div>
                                        </td>
                                        <td>
                                            {{ $cooperative->updated_at->format('d/m/Y H:i') }}
                                            <br>
                                            <small class="text-muted">
                                                {{ $cooperative->updated_at->diffForHumans() }}
                                            </small>
                                        </td>
                                        <td>
                                            <a href="{{ route('admin.cooperatives.show', $cooperative) }}"
                                               class="btn btn-info btn-sm" title="Voir détails">
                                                <i class="fas fa-eye me-1"></i>
                                                Détails
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="d-flex justify-content-center mt-3">
                            {{ $approvedCooperatives->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-building fa-4x text-muted mb-3"></i>
                            <h4>Aucune coopérative approuvée</h4>
                            <p class="text-muted">Les coopératives approuvées apparaîtront ici.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Suspended Cooperatives Tab -->
        <div class="tab-pane fade" id="suspended" role="tabpanel">
            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-ban me-2 text-danger"></i>
                        Coopératives Suspendues
                    </h5>
                </div>
                <div class="card-body">
                    @if($suspendedCooperatives->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-danger">
                                    <tr>
                                        <th>Coopérative</th>
                                        <th>Secteur</th>
                                        <th>Administrateur</th>
                                        <th>Date Suspension</th>
                                        <th>Raison</th>
                                        <th>Suspendue par</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($suspendedCooperatives as $cooperative)
                                    <tr>
                                        <td>
                                            <div>
                                                <strong class="text-danger">{{ $cooperative->name }}</strong>
                                                <br>
                                                <small class="text-muted">{{ $cooperative->legal_status }}</small>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-danger">{{ $cooperative->sector_of_activity }}</span>
                                        </td>
                                        <td>
                                            @if($cooperative->admin)
                                                <div>
                                                    {{ $cooperative->admin->full_name }}
                                                    <br>
                                                    <small class="text-muted">{{ $cooperative->admin->email }}</small>
                                                    <br>
                                                    <span class="badge bg-danger">Suspendu</span>
                                                </div>
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </td>
                                        <td>
                                            {{ $cooperative->suspended_at->format('d/m/Y H:i') }}
                                            <br>
                                            <small class="text-muted">
                                                {{ $cooperative->suspended_at->diffForHumans() }}
                                            </small>
                                        </td>
                                        <td>
                                            <button class="btn btn-outline-info btn-sm"
                                                    onclick="showSuspensionReason('{{ addslashes($cooperative->suspension_reason) }}', '{{ $cooperative->suspended_at->format('d/m/Y H:i') }}')"
                                                    title="Voir la raison">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </td>
                                        <td>
                                            {{ $cooperative->suspendedBy->full_name ?? 'Administrateur' }}
                                        </td>
                                        <td>
                                            <div class="btn-group-vertical" role="group">
                                                <a href="{{ route('admin.cooperatives.show', $cooperative) }}"
                                                   class="btn btn-info btn-sm mb-1" title="Voir détails">
                                                    <i class="fas fa-eye me-1"></i>Détails
                                                </a>
                                                <button type="button" class="btn btn-success btn-sm"
                                                        onclick="unsuspendCooperativeFromList({{ $cooperative->id }}, '{{ $cooperative->name }}')"
                                                        title="Lever la suspension">
                                                    <i class="fas fa-unlock me-1"></i>Réactiver
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="d-flex justify-content-center mt-3">
                            {{ $suspendedCooperatives->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-check-circle fa-4x text-success mb-3"></i>
                            <h4>Aucune coopérative suspendue</h4>
                            <p class="text-muted">Toutes les coopératives sont actuellement actives.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Action Modals -->
<!-- Approve Confirmation Modal -->
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

<!-- Suspension Reason Modal -->
<div class="modal fade" id="suspensionReasonModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="fas fa-ban me-2"></i>
                    Raison de la Suspension
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <strong>Date de suspension:</strong>
                    <span id="suspensionDate" class="text-muted"></span>
                </div>
                <div>
                    <strong>Raison:</strong>
                    <div id="suspensionReason" class="mt-2 p-3 bg-light border-start border-danger border-4"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
            </div>
        </div>
    </div>
</div>

<!-- Unsuspend from List Modal -->
<div class="modal fade" id="unsuspendFromListModal" tabindex="-1">
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
                    Êtes-vous sûr de vouloir lever la suspension de <strong id="unsuspendCoopName"></strong>?
                </div>
                <p>Cette action réactivera immédiatement la coopérative et enverra un email de notification.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <form id="unsuspendFromListForm" method="POST" style="display: inline;">
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
@endsection

@push('styles')
<style>
.border-left-primary { border-left: 0.25rem solid #4e73df !important; }
.border-left-success { border-left: 0.25rem solid #1cc88a !important; }
.border-left-info { border-left: 0.25rem solid #36b9cc !important; }
.border-left-warning { border-left: 0.25rem solid #f6c23e !important; }
.border-left-danger { border-left: 0.25rem solid #dc3545 !important; }

.verification-status .badge {
    font-size: 0.7rem;
    padding: 0.25rem 0.5rem;
}

.table-hover tbody tr:hover {
    background-color: rgba(0,0,0,.075);
}

.btn-group-vertical .btn {
    border-radius: 0.25rem !important;
    margin-bottom: 2px;
}

.btn-group-vertical .btn:last-child {
    margin-bottom: 0;
}

@media (max-width: 768px) {
    .btn-group-vertical {
        width: 100%;
    }

    .btn-group-vertical .btn {
        width: 100%;
    }
}

.table td {
    vertical-align: middle;
}

.modal-header.bg-success .btn-close-white,
.modal-header.bg-danger .btn-close-white {
    filter: brightness(0) invert(1);
}
</style>
@endpush

@push('scripts')
<script>
// Setup CSRF token
document.addEventListener('DOMContentLoaded', function() {
    const csrfToken = document.querySelector('meta[name="csrf-token"]');
    if (!csrfToken) {
        // Add CSRF meta tag if not present
        const meta = document.createElement('meta');
        meta.name = 'csrf-token';
        meta.content = '{{ csrf_token() }}';
        document.getElementsByTagName('head')[0].appendChild(meta);
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

function showSuspensionReason(reason, date) {
    document.getElementById('suspensionDate').textContent = date;
    document.getElementById('suspensionReason').textContent = reason;

    const modal = new bootstrap.Modal(document.getElementById('suspensionReasonModal'));
    modal.show();
}

function unsuspendCooperativeFromList(cooperativeId, cooperativeName) {
    console.log('Unsuspending cooperative:', cooperativeId, cooperativeName); // Debug log

    // Update modal content
    document.getElementById('unsuspendCoopName').textContent = cooperativeName;

    // Update form action
    const form = document.getElementById('unsuspendFromListForm');
    form.action = `/admin/cooperatives/${cooperativeId}/unsuspend`;

    console.log('Form action set to:', form.action); // Debug log

    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('unsuspendFromListModal'));
    modal.show();
}

function refreshPage() {
    window.location.reload();
}

function toggleVerificationFilter() {
    const table = document.getElementById('pendingTable');
    if (!table) return;

    const rows = table.querySelectorAll('tbody tr');

    rows.forEach(row => {
        const hasSuccess = row.classList.contains('table-success');
        if (hasSuccess) {
            row.style.display = row.style.display === 'none' ? '' : 'none';
        }
    });
}

// Auto-refresh every 30 seconds (optional)
// setInterval(() => {
//     console.log('Auto-refresh check...');
// }, 30000);
</script>
@endpush
