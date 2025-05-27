@extends('layouts.app')

@section('title', 'Gestion des Utilisateurs - Admin')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-users me-2"></i>
                Gestion des Utilisateurs
            </h1>
            <p class="text-muted">Gérer les utilisateurs clients et administrateurs de coopératives</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-primary">
                <i class="fas fa-arrow-left me-1"></i>
                Retour au tableau de bord
            </a>
            @if($stats['pending'] > 0)
                <button type="button" class="btn btn-success shadow-sm" data-bs-toggle="modal" data-bs-target="#activateAllModal">
                    <i class="fas fa-check-circle me-1"></i>
                    Activer tous ({{ $stats['pending'] }})
                </button>
            @endif
            <button type="button" class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#filterModal">
                <i class="fas fa-filter me-1"></i>
                Filtrer
            </button>
        </div>
    </div>

    <!-- Alerts -->
    <div id="alertContainer">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @if(session('info'))
            <div class="alert alert-info alert-dismissible fade show" role="alert">
                <i class="fas fa-info-circle me-2"></i>
                {{ session('info') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
    </div>

    <!-- User Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-2 col-md-4 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="stats-icon stats-primary me-3">
                        <i class="fas fa-users"></i>
                    </div>
                    <div>
                        <div class="stats-title">Total</div>
                        <div class="stats-value">{{ number_format($stats['total']) }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="stats-icon stats-success me-3">
                        <i class="fas fa-user-check"></i>
                    </div>
                    <div>
                        <div class="stats-title">Actifs</div>
                        <div class="stats-value">{{ number_format($stats['active']) }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="stats-icon stats-warning me-3">
                        <i class="fas fa-user-clock"></i>
                    </div>
                    <div>
                        <div class="stats-title">En Attente</div>
                        <div class="stats-value">{{ number_format($stats['pending']) }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="stats-icon stats-danger me-3">
                        <i class="fas fa-user-slash"></i>
                    </div>
                    <div>
                        <div class="stats-title">Suspendus</div>
                        <div class="stats-value">{{ number_format($stats['suspended']) }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="stats-icon stats-info me-3">
                        <i class="fas fa-user"></i>
                    </div>
                    <div>
                        <div class="stats-title">Clients</div>
                        <div class="stats-value">{{ number_format($stats['clients']) }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="stats-icon stats-secondary me-3">
                        <i class="fas fa-user-tie"></i>
                    </div>
                    <div>
                        <div class="stats-title">Admin Coop</div>
                        <div class="stats-value">{{ number_format($stats['coop_admins']) }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Active Filters -->
    @if(request('search') || request('status') || request('role') || request('email_verified'))
        <div class="alert alert-light border d-flex justify-content-between align-items-center mb-4">
            <div>
                <i class="fas fa-filter me-2 text-primary"></i>
                <strong>Filtres actifs: </strong>
                @if(request('search'))
                    <span class="badge bg-primary me-2">Recherche: {{ request('search') }}</span>
                @endif
                @if(request('status'))
                    <span class="badge bg-primary me-2">Statut: {{ request('status') }}</span>
                @endif
                @if(request('role'))
                    <span class="badge bg-primary me-2">Rôle: {{ request('role') }}</span>
                @endif
                @if(request('email_verified'))
                    <span class="badge bg-primary me-2">Email: {{ request('email_verified') === 'verified' ? 'Vérifié' : 'Non vérifié' }}</span>
                @endif
            </div>
            <a href="{{ route('admin.users.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-times me-1"></i> Effacer
            </a>
        </div>
    @endif

    <!-- Users Table -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-3" width="5%">ID</th>
                            <th width="30%">Utilisateur</th>
                            <th width="15%">Rôle</th>
                            <th width="15%">Statut</th>
                            <th width="15%">Email</th>
                            <th width="10%">Coopérative</th>
                            <th width="10%" class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                            <tr>
                                <td class="ps-3 fw-medium">{{ $user->id }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="flex-shrink-0 me-3">
                                            <div class="avatar-circle bg-primary text-white">
                                                {{ strtoupper(substr($user->first_name, 0, 1)) }}{{ strtoupper(substr($user->last_name, 0, 1)) }}
                                            </div>
                                        </div>
                                        <div>
                                            <h6 class="mb-0">{{ $user->full_name }}</h6>
                                            <small class="text-muted">
                                                <i class="fas fa-calendar me-1"></i>
                                                {{ $user->created_at->format('d/m/Y') }}
                                            </small>
                                        </div>
                                    </div>
                                </td>

                                <td>
                                    @if($user->role === 'client')
                                        <span class="badge bg-info">
                                            <i class="fas fa-user me-1"></i>
                                            Client
                                        </span>
                                    @elseif($user->role === 'cooperative_admin')
                                        <span class="badge bg-primary">
                                            <i class="fas fa-user-tie me-1"></i>
                                            Admin Coop
                                        </span>
                                    @endif
                                </td>

                                <td>
                                    <div class="dropdown">
                                        <button class="btn btn-sm dropdown-toggle
                                            @if($user->status === 'active') btn-light-success
                                            @elseif($user->status === 'pending') btn-light-warning
                                            @else btn-light-danger @endif"
                                            type="button" data-bs-toggle="dropdown">
                                            @if($user->status === 'active')
                                                <i class="fas fa-check-circle me-1"></i> Actif
                                            @elseif($user->status === 'pending')
                                                <i class="fas fa-clock me-1"></i> En attente
                                            @else
                                                <i class="fas fa-ban me-1"></i> Suspendu
                                            @endif
                                        </button>
                                        <ul class="dropdown-menu shadow-sm border-0">
                                            <li>
                                                <form action="{{ route('admin.users.updateStatus', $user->id) }}" method="POST">
                                                    @csrf
                                                    @method('PATCH')
                                                    <input type="hidden" name="status" value="active">
                                                    <button type="submit" class="dropdown-item d-flex align-items-center">
                                                        <i class="fas fa-check-circle me-2 text-success"></i> Activer
                                                    </button>
                                                </form>
                                            </li>
                                            <li>
                                                <form action="{{ route('admin.users.updateStatus', $user->id) }}" method="POST">
                                                    @csrf
                                                    @method('PATCH')
                                                    <input type="hidden" name="status" value="pending">
                                                    <button type="submit" class="dropdown-item d-flex align-items-center">
                                                        <i class="fas fa-clock me-2 text-warning"></i> En attente
                                                    </button>
                                                </form>
                                            </li>
                                            <li>
                                                <form action="{{ route('admin.users.updateStatus', $user->id) }}" method="POST">
                                                    @csrf
                                                    @method('PATCH')
                                                    <input type="hidden" name="status" value="suspended">
                                                    <button type="submit" class="dropdown-item d-flex align-items-center">
                                                        <i class="fas fa-ban me-2 text-danger"></i> Suspendre
                                                    </button>
                                                </form>
                                            </li>
                                        </ul>
                                    </div>
                                </td>

                                <td>
                                    <div>
                                        <small class="text-muted d-block">{{ $user->email }}</small>
                                        @if($user->email_verified_at)
                                            <span class="badge bg-success">
                                                <i class="fas fa-check me-1"></i>
                                                Vérifié
                                            </span>
                                        @else
                                            <span class="badge bg-warning">
                                                <i class="fas fa-exclamation-triangle me-1"></i>
                                                Non vérifié
                                            </span>
                                        @endif
                                    </div>
                                </td>

                                <td>
                                    @if($user->cooperative)
                                        <div>
                                            <small class="fw-medium d-block">{{ Str::limit($user->cooperative->name, 15) }}</small>
                                            <span class="badge bg-{{ $user->cooperative->status === 'approved' ? 'success' : ($user->cooperative->status === 'pending' ? 'warning' : 'danger') }}">
                                                {{ ucfirst($user->cooperative->status) }}
                                            </span>
                                        </div>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>

                                <td class="text-center">
                                    <a href="{{ route('admin.users.show', $user->id) }}" class="btn btn-sm btn-primary">
                                        <i class="fas fa-eye me-1"></i> Voir
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <i class="fas fa-users fa-3x text-muted mb-3"></i>
                                    <h5>Aucun utilisateur trouvé</h5>
                                    <p class="text-muted">Aucun utilisateur ne correspond aux critères de recherche.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Pagination -->
    <div class="d-flex justify-content-center">
        {{ $users->appends(request()->query())->links() }}
    </div>
</div>

<!-- Activate All Pending Users Modal -->
<div class="modal fade" id="activateAllModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="fas fa-check-circle me-2"></i>Activer tous les utilisateurs en attente
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center py-3">
                    <div class="mb-3">
                        <i class="fas fa-users fa-3x text-success mb-3"></i>
                    </div>
                    <h5>Confirmer l'activation</h5>
                    <p class="text-muted">
                        Êtes-vous sûr de vouloir activer tous les <strong>{{ $stats['pending'] }} utilisateur(s)</strong> en attente ?
                    </p>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Cette action changera le statut de tous les utilisateurs en attente vers "Actif".
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                <form action="{{ route('admin.users.activateAllPending') }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check-circle me-1"></i> Activer tous
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Filter Modal -->
<div class="modal fade" id="filterModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light">
                <h5 class="modal-title">
                    <i class="fas fa-filter me-2"></i>
                    Filtrer les utilisateurs
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="{{ route('admin.users.index') }}" method="GET" id="filterForm">
                    <div class="mb-3">
                        <label for="search" class="form-label">Recherche</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                            <input type="text" id="search" name="search" class="form-control"
                                   placeholder="Nom, prénom, email, téléphone..." value="{{ request('search') }}">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="status" class="form-label">Statut</label>
                            <select id="status" name="status" class="form-select">
                                <option value="">Tous les statuts</option>
                                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Actif</option>
                                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>En attente</option>
                                <option value="suspended" {{ request('status') === 'suspended' ? 'selected' : '' }}>Suspendu</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="role" class="form-label">Rôle</label>
                            <select id="role" name="role" class="form-select">
                                <option value="">Tous les rôles</option>
                                <option value="client" {{ request('role') === 'client' ? 'selected' : '' }}>Client</option>
                                <option value="cooperative_admin" {{ request('role') === 'cooperative_admin' ? 'selected' : '' }}>Admin Coopérative</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="email_verified" class="form-label">Vérification Email</label>
                        <select id="email_verified" name="email_verified" class="form-select">
                            <option value="">Tous</option>
                            <option value="verified" {{ request('email_verified') === 'verified' ? 'selected' : '' }}>Email vérifié</option>
                            <option value="unverified" {{ request('email_verified') === 'unverified' ? 'selected' : '' }}>Email non vérifié</option>
                        </select>
                    </div>

                    <div class="text-end">
                        <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary me-2">Réinitialiser</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search me-1"></i>
                            Appliquer
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    /* Custom Avatar */
    .avatar-circle {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 12px;
    }

    /* Stats Cards */
    .stats-icon {
        width: 45px;
        height: 45px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        flex-shrink: 0;
    }

    .stats-primary { background-color: #0d6efd; color: white; }
    .stats-success { background-color: #198754; color: white; }
    .stats-warning { background-color: #ffc107; color: white; }
    .stats-danger { background-color: #dc3545; color: white; }
    .stats-info { background-color: #0dcaf0; color: white; }
    .stats-secondary { background-color: #6c757d; color: white; }

    .stats-title {
        font-size: 14px;
        color: #6c757d;
        margin-bottom: 4px;
        font-weight: 500;
    }

    .stats-value {
        font-size: 24px;
        font-weight: 700;
        color: #212529;
        line-height: 1;
    }

    /* Light Button Variants */
    .btn-light-primary { background-color: rgba(13, 110, 253, 0.1); color: #0d6efd; border: none; }
    .btn-light-secondary { background-color: rgba(108, 117, 125, 0.1); color: #6c757d; border: none; }
    .btn-light-success { background-color: rgba(25, 135, 84, 0.1); color: #198754; border: none; }
    .btn-light-danger { background-color: rgba(220, 53, 69, 0.1); color: #dc3545; border: none; }
    .btn-light-warning { background-color: rgba(255, 193, 7, 0.1); color: #ffc107; border: none; }

    /* Dropdown Styling */
    .dropdown-menu { padding: 0.5rem 0; border-radius: 0.375rem; }
    .dropdown-item { padding: 0.5rem 1rem; }
    .dropdown-item:hover { background-color: #f8f9fa; }

    /* Table Styling */
    .table th {
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.5px;
    }

    .table td { vertical-align: middle; padding: 0.75rem 0.5rem; }

    /* Card hover effects */
    .card { transition: transform 0.2s ease-in-out; }
    .card:hover { transform: translateY(-2px); }
</style>
@endpush
