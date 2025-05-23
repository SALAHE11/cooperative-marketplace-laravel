@extends('layouts.app')

@section('title', 'Tableau de Bord Client - Coopérative E-commerce')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">Mon Tableau de Bord</h1>
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
                                Commandes Totales
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">12</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-shopping-cart fa-2x text-gray-300"></i>
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
                                Commandes Livrées
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">9</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
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
                                Montant Total Dépensé
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">2,150 MAD</div>
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
                                Points Fidélité
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">215</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-star fa-2x text-gray-300"></i>
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
                    <h6 class="m-0 font-weight-bold text-primary">Mes Commandes Récentes</h6>
                    <a href="#" class="btn btn-primary btn-sm">Voir toutes</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>N° Commande</th>
                                    <th>Coopérative</th>
                                    <th>Montant</th>
                                    <th>Statut</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>CMD-2025-001</td>
                                    <td>Coopérative Argane</td>
                                    <td>350 MAD</td>
                                    <td><span class="badge bg-success">Livrée</span></td>
                                    <td>20/05/2025</td>
                                    <td>
                                        <button class="btn btn-info btn-sm">Détails</button>
                                        <button class="btn btn-warning btn-sm">Avis</button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>CMD-2025-002</td>
                                    <td>Coopérative Tapis Azrou</td>
                                    <td>180 MAD</td>
                                    <td><span class="badge bg-info">En transit</span></td>
                                    <td>22/05/2025</td>
                                    <td>
                                        <button class="btn btn-info btn-sm">Suivre</button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>CMD-2025-003</td>
                                    <td>Coopérative Miel Atlas</td>
                                    <td>520 MAD</td>
                                    <td><span class="badge bg-warning">Préparation</span></td>
                                    <td>23/05/2025</td>
                                    <td>
                                        <button class="btn btn-info btn-sm">Détails</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions & Recommendations -->
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
                                <i class="fas fa-shopping-bag text-success me-3"></i>
                                Parcourir Produits
                            </div>
                            <i class="fas fa-chevron-right"></i>
                        </a>
                        <a href="#" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <div>
                                <i class="fas fa-heart text-danger me-3"></i>
                                Mes Favoris
                            </div>
                            <i class="fas fa-chevron-right"></i>
                        </a>
                        <a href="#" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <div>
                                <i class="fas fa-user text-info me-3"></i>
                                Mon Profil
                            </div>
                            <i class="fas fa-chevron-right"></i>
                        </a>
                        <a href="#" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <div>
                                <i class="fas fa-envelope text-warning me-3"></i>
                                Messages
                            </div>
                            <span class="badge bg-warning rounded-pill">3</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Recommendations -->
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-success">Recommandations</h6>
                </div>
                <div class="card-body">
                    <div class="recommendation-item mb-3">
                        <div class="d-flex align-items-center">
                            <img src="https://via.placeholder.com/50x50" class="rounded me-3" alt="Produit">
                            <div>
                                <h6 class="mb-1">Huile d'Argan Premium</h6>
                                <small class="text-muted">Coopérative Argane Essaouira</small>
                                <div class="text-warning">
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                </div>
                                <strong class="text-primary">250 MAD</strong>
                            </div>
                        </div>
                    </div>

                    <div class="recommendation-item mb-3">
                        <div class="d-flex align-items-center">
                            <img src="https://via.placeholder.com/50x50" class="rounded me-3" alt="Produit">
                            <div>
                                <h6 class="mb-1">Tapis Berbère Authentique</h6>
                                <small class="text-muted">Coopérative Tapis Azrou</small>
                                <div class="text-warning">
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="far fa-star"></i>
                                </div>
                                <strong class="text-primary">850 MAD</strong>
                            </div>
                        </div>
                    </div>

                    <button class="btn btn-outline-success btn-sm w-100">
                        <i class="fas fa-eye me-1"></i>
                        Voir plus de recommandations
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Favorite Cooperatives -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Mes Coopératives Favorites</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <div class="card border-0 shadow-sm">
                                <div class="card-body text-center">
                                    <img src="https://via.placeholder.com/80x80" class="rounded-circle mb-3" alt="Logo">
                                    <h6 class="card-title">Coopérative Argane Essaouira</h6>
                                    <p class="card-text text-muted small">
                                        Spécialisée dans l'huile d'argan et produits cosmétiques naturels
                                    </p>
                                    <div class="text-warning mb-2">
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <small class="text-muted">(4.9/5)</small>
                                    </div>
                                    <button class="btn btn-outline-primary btn-sm">Voir Produits</button>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4 mb-3">
                            <div class="card border-0 shadow-sm">
                                <div class="card-body text-center">
                                    <img src="https://via.placeholder.com/80x80" class="rounded-circle mb-3" alt="Logo">
                                    <h6 class="card-title">Coopérative Tapis Azrou</h6>
                                    <p class="card-text text-muted small">
                                        Artisanat traditionnel marocain, tapis et produits tissés
                                    </p>
                                    <div class="text-warning mb-2">
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="far fa-star"></i>
                                        <small class="text-muted">(4.2/5)</small>
                                    </div>
                                    <button class="btn btn-outline-primary btn-sm">Voir Produits</button>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4 mb-3">
                            <div class="card border-0 shadow-sm">
                                <div class="card-body text-center">
                                    <img src="https://via.placeholder.com/80x80" class="rounded-circle mb-3" alt="Logo">
                                    <h6 class="card-title">Coopérative Miel Atlas</h6>
                                    <p class="card-text text-muted small">
                                        Miel naturel des montagnes de l'Atlas et produits de la ruche
                                    </p>
                                    <div class="text-warning mb-2">
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <small class="text-muted">(4.7/5)</small>
                                    </div>
                                    <button class="btn btn-outline-primary btn-sm">Voir Produits</button>
                                </div>
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

.recommendation-item {
    border-bottom: 1px solid #e3e6f0;
    padding-bottom: 15px;
}

.recommendation-item:last-child {
    border-bottom: none;
    padding-bottom: 0;
}
</style>
@endsection
