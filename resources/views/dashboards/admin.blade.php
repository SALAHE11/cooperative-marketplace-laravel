@extends('layouts.app')

@section('title', 'Tableau de Bord Administrateur - Coopérative E-commerce')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">Tableau de Bord Administrateur</h1>
                    <p class="text-muted">Bienvenue, {{ Auth::user()->full_name ?? 'Test User' }}</p>
                </div>
                <div class="text-end">
                    <small class="text-muted">Dernière connexion: {{ Auth::user()->last_login_at?->format('d/m/Y H:i') }}</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Coopératives Actives
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">15</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-building fa-2x text-gray-300"></i>
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
                                Clients Actifs
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">1,245</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
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
                                Demandes en Attente
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">8</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
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
                                Revenus Totaux
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">125,450 MAD</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Content Row -->
    <div class="row">
        <!-- Pending Cooperatives -->
       <!-- Replace the existing "Pending Cooperatives" section with this -->
    <div class="col-lg-8 mb-4">
    <div class="card shadow">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">Demandes d'Inscription en Attente</h6>
            <a href="{{ route('admin.cooperatives.index') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-list me-1"></i>
                Gérer toutes
            </a>
        </div>
        <div class="card-body">
            @php
                $pendingCoops = \App\Models\Cooperative::with('admin')->where('status', 'pending')->latest()->take(5)->get();
            @endphp

            @if($pendingCoops->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Coopérative</th>
                                <th>Secteur</th>
                                <th>Administrateur</th>
                                <th>Vérification</th>
                                <th>Date Demande</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pendingCoops as $coop)
                            <tr>
                                <td>{{ $coop->name }}</td>
                                <td>{{ $coop->sector_of_activity }}</td>
                                <td>{{ $coop->admin->full_name ?? 'N/A' }}</td>
                                <td>
                                    <div>
                                        @if($coop->admin && $coop->admin->email_verified_at)
                                            <span class="badge bg-success mb-1">
                                                <i class="fas fa-user"></i> Utilisateur ✓
                                            </span>
                                        @else
                                            <span class="badge bg-warning mb-1">
                                                <i class="fas fa-user"></i> Utilisateur ?
                                            </span>
                                        @endif
                                        <br>
                                        @if($coop->email_verified_at)
                                            <span class="badge bg-success">
                                                <i class="fas fa-building"></i> Coopérative ✓
                                            </span>
                                        @else
                                            <span class="badge bg-warning">
                                                <i class="fas fa-building"></i> Coopérative ?
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td>{{ $coop->created_at->format('d/m/Y') }}</td>
                                <td>
                                    <a href="{{ route('admin.cooperatives.show', $coop) }}" class="btn btn-info btn-sm">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-4">
                    <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                    <h5>Aucune demande en attente</h5>
                    <p class="text-muted">Toutes les demandes ont été traitées.</p>
                </div>
            @endif
        </div>
    </div>
</div>

        <!-- Quick Actions -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Actions Rapides</h6>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        <a href="#" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <div>
                                <i class="fas fa-building text-primary me-3"></i>
                                Gérer Coopératives
                            </div>
                            <i class="fas fa-chevron-right"></i>
                        </a>
                        <a href="#" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <div>
                                <i class="fas fa-users text-success me-3"></i>
                                Gérer Utilisateurs
                            </div>
                            <i class="fas fa-chevron-right"></i>
                        </a>
                        <a href="#" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <div>
                                <i class="fas fa-tags text-info me-3"></i>
                                Gérer Catégories
                            </div>
                            <i class="fas fa-chevron-right"></i>
                        </a>
                        <a href="#" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <div>
                                <i class="fas fa-chart-bar text-warning me-3"></i>
                                Rapports & Statistiques
                            </div>
                            <i class="fas fa-chevron-right"></i>
                        </a>
                        <a href="#" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <div>
                                <i class="fas fa-cog text-secondary me-3"></i>
                                Paramètres Système
                            </div>
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activities -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Activités Récentes</h6>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        <div class="timeline-item">
                            <div class="timeline-marker bg-success"></div>
                            <div class="timeline-content">
                                <h6 class="timeline-title">Nouvelle coopérative approuvée</h6>
                                <p class="timeline-text">Coopérative Huile d'Olive Meknès a été approuvée et activée.</p>
                                <small class="text-muted">Il y a 2 heures</small>
                            </div>
                        </div>
                        <div class="timeline-item">
                            <div class="timeline-marker bg-info"></div>
                            <div class="timeline-content">
                                <h6 class="timeline-title">Nouveau client inscrit</h6>
                                <p class="timeline-text">25 nouveaux clients se sont inscrits aujourd'hui.</p>
                                <small class="text-muted">Il y a 4 heures</small>
                            </div>
                        </div>
                        <div class="timeline-item">
                            <div class="timeline-marker bg-warning"></div>
                            <div class="timeline-content">
                                <h6 class="timeline-title">Demande d'assistance</h6>
                                <p class="timeline-text">Coopérative Saffran Taliouine a signalé un problème technique.</p>
                                <small class="text-muted">Il y a 6 heures</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.border-left-primary { border-left: 0.25rem solid #4e73df !important; }
.border-left-success { border-left: 0.25rem solid #1cc88a !important; }
.border-left-info { border-left: 0.25rem solid #36b9cc !important; }
.border-left-warning { border-left: 0.25rem solid #f6c23e !important; }

.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline:before {
    content: '';
    position: absolute;
    left: 15px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #e3e6f0;
}

.timeline-item {
    position: relative;
    margin-bottom: 30px;
}

.timeline-marker {
    position: absolute;
    left: -37px;
    top: 5px;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    border: 2px solid #fff;
    box-shadow: 0 0 0 3px #e3e6f0;
}

.timeline-content {
    padding-left: 20px;
}

.timeline-title {
    margin-bottom: 5px;
    font-size: 14px;
    font-weight: 600;
}

.timeline-text {
    margin-bottom: 5px;
    color: #6c757d;
    font-size: 13px;
}
</style>
@endsection
