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

<style>
.border-left-primary { border-left: 0.25rem solid #4e73df !important; }
.border-left-success { border-left: 0.25rem solid #1cc88a !important; }
.border-left-info { border-left: 0.25rem solid #36b9cc !important; }
.border-left-warning { border-left: 0.25rem solid #f6c23e !important; }
</style>
@endsection

