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
                        @if(Auth::user()->isPrimaryAdmin())
                            <span class="badge bg-warning text-dark ms-2">
                                <i class="fas fa-crown me-1"></i>
                                Administrateur Principal
                            </span>
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

    @if(Auth::user()->cooperative && Auth::user()->cooperative->status !== 'approved')
        <div class="row mb-4">
            <div class="col-12">
                @if(Auth::user()->cooperative->status === 'pending')
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Coopérative en attente d'approbation</strong> -
                        Votre coopérative est en cours d'examen. Vous pourrez gérer vos produits et commandes une fois approuvée.
                    </div>
                @elseif(Auth::user()->cooperative->status === 'suspended')
                    <div class="alert alert-danger">
                        <i class="fas fa-ban me-2"></i>
                        <strong>Coopérative suspendue</strong> -
                        Votre coopérative est temporairement suspendue. Contactez l'administration pour plus d'informations.
                    </div>
                @endif
            </div>
        </div>
    @endif

    <!-- Urgent Orders Alert -->
    @if(Auth::user()->cooperative && Auth::user()->cooperative->status === 'approved' && isset($urgentOrders) && $urgentOrders->count() > 0)
        <div class="row mb-4">
            <div class="col-12">
                <div class="alert alert-warning border-start border-warning border-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="alert-heading mb-1">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                Commandes nécessitant votre attention
                            </h6>
                            <p class="mb-0">{{ $urgentOrders->count() }} commande(s) en attente de traitement</p>
                        </div>
                        <a href="{{ route('coop.orders.index', ['status' => 'pending']) }}" class="btn btn-warning">
                            <i class="fas fa-arrow-right me-1"></i>
                            Voir les commandes
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Stats Cards -->
    @if(Auth::user()->cooperative && Auth::user()->cooperative->status === 'approved')
        <!-- Product & Order Stats -->
        <div class="row mb-4">
            <!-- Product Stats -->
            <div class="col-xl-2 col-md-4 mb-4">
                <div class="card border-left-primary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                    Total Produits
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $productStats['total'] ?? 0 }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-box fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-2 col-md-4 mb-4">
                <div class="card border-left-success shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                    Produits Actifs
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $productStats['approved'] ?? 0 }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-2 col-md-4 mb-4">
                <div class="card border-left-info shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                    Total Commandes
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $orderStats['total'] ?? 0 }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-shopping-cart fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-2 col-md-4 mb-4">
                <div class="card border-left-warning shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                    En Préparation
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    {{ $orderStats['pending'] ?? 0 }}
                                    @if(($orderStats['pending'] ?? 0) > 0)
                                        <i class="fas fa-clock text-warning ms-1"></i>
                                    @endif
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-clock fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-2 col-md-4 mb-4">
                <div class="card border-left-secondary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-secondary text-uppercase mb-1">
                                    Prêtes
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    {{ $orderStats['ready'] ?? 0 }}
                                    @if(($orderStats['ready'] ?? 0) > 0)
                                        <i class="fas fa-bell text-info ms-1"></i>
                                    @endif
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-check fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-2 col-md-4 mb-4">
                <div class="card border-left-danger shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                    Stock Faible
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    {{ $productStats['low_stock'] ?? 0 }}
                                    @if(($productStats['low_stock'] ?? 0) > 0)
                                        <i class="fas fa-exclamation-triangle text-danger ms-1"></i>
                                    @endif
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Revenue Stats -->
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-success shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                    Chiffre d'Affaires Total
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($revenueStats['total'] ?? 0, 2) }} MAD</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
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
                                    Ce Mois
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($revenueStats['this_month'] ?? 0, 2) }} MAD</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-calendar fa-2x text-gray-300"></i>
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
                                    Commandes Aujourd'hui
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $orderStats['today'] ?? 0 }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-calendar-day fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-primary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                    Panier Moyen
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($revenueStats['avg_order_value'] ?? 0, 2) }} MAD</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-chart-line fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @else
        <!-- Basic Stats for Non-Approved Cooperatives -->
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-info shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                    Statut Coopérative
                                </div>
                                <div class="h6 mb-0 font-weight-bold text-gray-800">
                                    @if(Auth::user()->cooperative)
                                        <span class="badge bg-{{ Auth::user()->cooperative->status === 'approved' ? 'success' : (Auth::user()->cooperative->status === 'pending' ? 'warning' : 'danger') }}">
                                            {{ ucfirst(Auth::user()->cooperative->status) }}
                                        </span>
                                    @else
                                        Non défini
                                    @endif
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-building fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-primary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                    Votre Rôle
                                </div>
                                <div class="h6 mb-0 font-weight-bold text-gray-800">
                                    @if(Auth::user()->isPrimaryAdmin())
                                        Admin Principal
                                    @else
                                        Administrateur
                                    @endif
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-user-shield fa-2x text-gray-300"></i>
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
                                    Membre depuis
                                </div>
                                <div class="h6 mb-0 font-weight-bold text-gray-800">
                                    {{ Auth::user()->created_at->format('M Y') }}
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-calendar fa-2x text-gray-300"></i>
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
                                    Produits
                                </div>
                                <div class="h6 mb-0 font-weight-bold text-gray-800">
                                    @if(Auth::user()->cooperative && Auth::user()->cooperative->status === 'approved')
                                        {{ Auth::user()->cooperative->products()->count() }}
                                    @else
                                        Non disponible
                                    @endif
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-box fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- ADMIN MANAGEMENT SECTION - ONLY FOR PRIMARY ADMIN -->
    @if(Auth::user()->isPrimaryAdmin())
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
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="inactive-admins-tab" data-bs-toggle="tab" data-bs-target="#inactive-admins" type="button" role="tab">
                                <i class="fas fa-user-slash me-1"></i>
                                Administrateurs Inactifs
                                <span class="badge bg-secondary ms-1" id="inactiveAdminsCount">0</span>
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

                        <!-- Inactive Admins Tab -->
                        <div class="tab-pane fade" id="inactive-admins" role="tabpanel">
                            <div id="inactiveAdminsContent">
                                <div class="text-center py-4">
                                    <div class="spinner-border text-secondary" role="status">
                                        <span class="visually-hidden">Chargement...</span>
                                    </div>
                                    <p class="mt-2 text-muted">Chargement des administrateurs inactifs...</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Primary Admin Modals -->
    @include('dashboards.partials.admin-modals')

    @else
    <!-- MESSAGE FOR NON-PRIMARY ADMINS -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3 bg-info text-white">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-info-circle me-2"></i>
                        Statut Administrateur
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <i class="fas fa-user-shield fa-3x text-info"></i>
                        </div>
                        <div class="col">
                            <h5 class="text-info mb-2">Administrateur de Coopérative</h5>
                            <p class="mb-1">Vous êtes administrateur de <strong>{{ Auth::user()->cooperative->name }}</strong></p>
                            <p class="mb-0 text-muted">
                                <i class="fas fa-crown me-1 text-warning"></i>
                                La gestion des autres administrateurs est réservée à l'administrateur principal
                            </p>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-12">
                            <div class="alert alert-light">
                                <h6 class="alert-heading">
                                    <i class="fas fa-tasks me-2"></i>
                                    Vos responsabilités
                                </h6>
                                <ul class="mb-0">
                                    <li>Gestion des produits de la coopérative</li>
                                    <li>Traitement des commandes clients</li>
                                    <li>Suivi des stocks et inventaires</li>
                                    <li>Support client et communication</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Content Row -->
    <div class="row">
        <!-- Recent Activity -->
        <div class="col-lg-8 mb-4">
            @if(Auth::user()->cooperative && Auth::user()->cooperative->status === 'approved')
                <!-- Recent Orders -->
                @if(isset($recentOrders) && $recentOrders->count() > 0)
                <div class="card shadow mb-4">
                    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                        <h6 class="m-0 font-weight-bold text-primary">Commandes Récentes</h6>
                        <a href="{{ route('coop.orders.index') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-shopping-cart me-1"></i>
                            Voir toutes
                        </a>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th>N° Commande</th>
                                        <th>Client</th>
                                        <th>Articles</th>
                                        <th>Total</th>
                                        <th>Statut</th>
                                        <th>Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentOrders as $order)
                                    <tr>
                                        <td>
                                            <strong>#{{ $order->order_number }}</strong>
                                        </td>
                                        <td>
                                            <div>
                                                <strong>{{ $order->user->full_name }}</strong>
                                                <br>
                                                <small class="text-muted">{{ $order->user->email }}</small>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                @if($order->orderItems->first() && $order->orderItems->first()->product && $order->orderItems->first()->product->primaryImageUrl)
                                                    <img src="{{ $order->orderItems->first()->product->primaryImageUrl }}"
                                                         class="me-2 rounded" style="width: 30px; height: 30px; object-fit: cover;">
                                                @else
                                                    <div class="me-2 bg-light rounded d-flex align-items-center justify-content-center"
                                                         style="width: 30px; height: 30px;">
                                                        <i class="fas fa-image text-muted small"></i>
                                                    </div>
                                                @endif
                                                <div>
                                                    <div class="small">{{ $order->orderItems->count() }} article(s)</div>
                                                    @if($order->orderItems->count() > 1)
                                                        <small class="text-muted">+{{ $order->orderItems->count() - 1 }} autre(s)</small>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <strong class="text-success">{{ number_format($order->total_amount, 2) }} MAD</strong>
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $order->status === 'pending' ? 'warning' : ($order->status === 'ready' ? 'info' : ($order->status === 'completed' ? 'success' : 'danger')) }}">
                                                {{ ucfirst($order->status) }}
                                            </span>
                                        </td>
                                        <td>
                                            <small>{{ $order->created_at->format('d/m/Y H:i') }}</small>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="{{ route('coop.orders.show', $order) }}" class="btn btn-primary btn-sm">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                @if($order->status === 'pending')
                                                    <button class="btn btn-info btn-sm" onclick="markOrderAsReady({{ $order->id }})">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                @elseif($order->status === 'ready')
                                                    <a href="{{ route('coop.orders.show', $order) }}" class="btn btn-success btn-sm">
                                                        <i class="fas fa-handshake"></i>
                                                    </a>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Recent Products -->
                @if(isset($recentProducts) && $recentProducts->count() > 0)
                <div class="card shadow mb-4">
                    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                        <h6 class="m-0 font-weight-bold text-success">Produits Récents</h6>
                        <a href="{{ route('coop.products.index') }}" class="btn btn-success btn-sm">
                            <i class="fas fa-box me-1"></i>
                            Voir tous
                        </a>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th width="60">Image</th>
                                        <th>Produit</th>
                                        <th>Catégorie</th>
                                        <th>Prix</th>
                                        <th>Stock</th>
                                        <th>Statut</th>
                                        <th>Mis à jour</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentProducts as $product)
                                    <tr>
                                        <td>
                                            @if($product->primary_image_url)
                                                <img src="{{ $product->primary_image_url }}"
                                                     class="img-thumbnail"
                                                     style="width: 50px; height: 50px; object-fit: cover;">
                                            @else
                                                <div class="bg-light text-center d-flex align-items-center justify-content-center"
                                                     style="width: 50px; height: 50px;">
                                                    <i class="fas fa-image text-muted"></i>
                                                </div>
                                            @endif
                                        </td>
                                        <td>
                                            <strong>{{ $product->name }}</strong>
                                            <br>
                                            <small class="text-muted">{{ Str::limit($product->description, 50) }}</small>
                                            @if($product->isStockLow())
                                                <br><small class="text-danger">
                                                    <i class="fas fa-exclamation-triangle"></i>
                                                    {{ $product->stock_status_text }}
                                                </small>
                                            @endif
                                        </td>
                                        <td>{{ $product->category->name ?? 'N/A' }}</td>
                                        <td><strong>{{ number_format($product->price, 2) }} MAD</strong></td>
                                        <td>
                                            <span class="badge bg-{{ $product->stock_status_badge }}">
                                                {{ $product->stock_quantity }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $product->status_badge }}">
                                                {{ $product->status_text }}
                                            </span>
                                        </td>
                                        <td>
                                            <small>{{ $product->updated_at->format('d/m/Y') }}</small>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Low Stock Alert -->
                @if(isset($lowStockProducts) && $lowStockProducts->count() > 0)
                <div class="card shadow">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Alertes Stock Faible ({{ $lowStockProducts->count() }})
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th>Produit</th>
                                        <th>Stock Actuel</th>
                                        <th>Seuil Alerte</th>
                                        <th>Statut</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($lowStockProducts as $product)
                                    <tr>
                                        <td>
                                            <strong>{{ $product->name }}</strong>
                                            <br>
                                            <small class="text-muted">{{ $product->category->name ?? 'N/A' }}</small>
                                        </td>
                                        <td>
                                            <span class="fw-bold text-{{ $product->stock_status_badge }}">
                                                {{ $product->stock_quantity }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="text-muted">{{ $product->stock_alert_threshold }}</span>
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $product->stock_status_badge }}">
                                                {{ $product->stock_status_text }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="{{ route('coop.products.edit', $product) }}" class="btn btn-primary">
                                                    <i class="fas fa-edit me-1"></i>
                                                    Réapprovisionner
                                                </a>
                                                <button type="button" class="btn btn-outline-warning"
                                                        onclick="openStockAlertModal({{ $product->id }}, '{{ addslashes($product->name) }}', {{ $product->stock_alert_threshold }})">
                                                    <i class="fas fa-bell"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                @endif
            @else
                <!-- Placeholder for non-approved cooperatives -->
                <div class="card shadow">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-muted">Activité Récente</h6>
                    </div>
                    <div class="card-body text-center py-5">
                        <i class="fas fa-lock fa-4x text-muted mb-3"></i>
                        <h5 class="text-muted">Accès Restreint</h5>
                        <p class="text-muted">
                            La gestion des produits et commandes sera disponible une fois que votre coopérative sera approuvée par l'administration.
                        </p>
                        @if(Auth::user()->cooperative && Auth::user()->cooperative->status === 'pending')
                            <p class="small text-info">
                                <i class="fas fa-info-circle me-1"></i>
                                Votre coopérative est actuellement en attente d'approbation.
                            </p>
                        @endif
                    </div>
                </div>
            @endif
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
                        @if(Auth::user()->cooperative && Auth::user()->cooperative->status === 'approved')
                            <!-- Products -->
                            <a href="{{ route('coop.products.create') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="fas fa-plus text-success me-3"></i>
                                    Ajouter Produit
                                </div>
                                <i class="fas fa-chevron-right"></i>
                            </a>
                            <a href="{{ route('coop.products.index') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="fas fa-box text-primary me-3"></i>
                                    Mes Produits
                                </div>
                                <i class="fas fa-chevron-right"></i>
                            </a>
                            <a href="{{ route('coop.products.index', ['status' => 'pending']) }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="fas fa-clock text-warning me-3"></i>
                                    Produits en Attente
                                </div>
                                <span class="badge bg-warning">{{ $productStats['pending'] ?? 0 }}</span>
                            </a>

                            <!-- Orders -->
                            <a href="{{ route('coop.orders.index') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="fas fa-shopping-cart text-success me-3"></i>
                                    Toutes les Commandes
                                </div>
                                <span class="badge bg-info">{{ $orderStats['total'] ?? 0 }}</span>
                            </a>
                            <a href="{{ route('coop.orders.index', ['status' => 'pending']) }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="fas fa-clock text-warning me-3"></i>
                                    En Préparation
                                </div>
                                <span class="badge bg-warning">{{ $orderStats['pending'] ?? 0 }}</span>
                            </a>
                            <a href="{{ route('coop.orders.index', ['status' => 'ready']) }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="fas fa-check text-info me-3"></i>
                                    Prêtes pour Retrait
                                </div>
                                <span class="badge bg-info">{{ $orderStats['ready'] ?? 0 }}</span>
                            </a>

                            <!-- Stock Management -->
                            <button type="button" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center"
                                    onclick="openBulkStockAlertModal()">
                                <div>
                                    <i class="fas fa-bell text-warning me-3"></i>
                                    Configurer Alertes Stock
                                </div>
                                <i class="fas fa-chevron-right"></i>
                            </button>
                        @else
                            <div class="list-group-item text-center py-4">
                                <i class="fas fa-lock fa-2x text-muted mb-2"></i>
                                <p class="text-muted mb-0">Actions disponibles après approbation</p>
                            </div>
                        @endif
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
                    <p class="text-muted mb-2">
                        <i class="fas fa-calendar me-2"></i>
                        Créée le {{ Auth::user()->cooperative->date_created->format('d/m/Y') }}
                    </p>
                    <p class="text-muted mb-0">
                        <i class="fas fa-{{ Auth::user()->cooperative->status === 'approved' ? 'check-circle text-success' : (Auth::user()->cooperative->status === 'pending' ? 'clock text-warning' : 'times-circle text-danger') }} me-2"></i>
                        Statut: <strong>{{ ucfirst(Auth::user()->cooperative->status) }}</strong>
                    </p>
                    @if(Auth::user()->isPrimaryAdmin())
                        <div class="mt-3 pt-2 border-top">
                            <small class="text-warning">
                                <i class="fas fa-crown me-1"></i>
                                Vous êtes l'administrateur principal de cette coopérative
                            </small>
                        </div>
                    @endif
                </div>
            </div>
            @else
            <!-- No Cooperative Info -->
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Aucune Coopérative
                    </h6>
                </div>
                <div class="card-body text-center">
                    <i class="fas fa-building fa-3x text-muted mb-3"></i>
                    <p class="text-muted">Vous n'êtes associé à aucune coopérative.</p>
                    <a href="{{ route('coop.register') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus me-1"></i>
                        Rejoindre/Créer une Coopérative
                    </a>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Stock Alert Configuration Modals (for all cooperative admins) -->
@include('dashboards.partials.stock-modals')

<style>
.border-left-primary { border-left: 0.25rem solid #4e73df !important; }
.border-left-success { border-left: 0.25rem solid #1cc88a !important; }
.border-left-info { border-left: 0.25rem solid #36b9cc !important; }
.border-left-warning { border-left: 0.25rem solid #f6c23e !important; }
.border-left-danger { border-left: 0.25rem solid #e74a3b !important; }
.border-left-dark { border-left: 0.25rem solid #5a5c69 !important; }
.border-left-secondary { border-left: 0.25rem solid #858796 !important; }

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

.inactive-admin-card {
    border-left: 4px solid #6c757d;
    background: #f8f9fa;
}

.removal-info {
    background: #fff3cd;
    border: 1px solid #ffeaa7;
    border-radius: 0.25rem;
    padding: 0.75rem;
    margin-top: 0.5rem;
}
</style>
@endsection

@push('scripts')
<script>
// Global variables for CSRF and routes
const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
const IS_PRIMARY_ADMIN = {{ Auth::user()->isPrimaryAdmin() ? 'true' : 'false' }};

// Routes object for clean URL management
const ROUTES = {
    @if(Auth::user()->isPrimaryAdmin())
    pendingRequests: '{{ route("coop.admin-requests.pending") }}',
    currentAdmins: '{{ route("coop.admin-requests.current-admins") }}',
    inactiveAdmins: '{{ route("coop.admin-requests.inactive-admins") }}',
    approveRequest: '{{ url("coop/admin-requests") }}',
    rejectRequest: '{{ url("coop/admin-requests") }}',
    clarificationRequest: '{{ url("coop/admin-requests") }}',
    removeAdmin: '{{ url("coop/admins") }}',
    reactivateAdmin: '{{ url("coop/admins") }}',
    permanentlyRemoveAdmin: '{{ url("coop/admins") }}',
    @endif
    updateOrderStatus: '{{ url("coop/orders") }}',
    configureStockAlert: '{{ url("coop/products") }}',
    bulkConfigureStockAlerts: '{{ url("coop/products/bulk-configure-stock-alerts") }}'
};

// Main DOM Content Loaded
document.addEventListener('DOMContentLoaded', function() {
    console.log('Dashboard loaded. Primary Admin:', IS_PRIMARY_ADMIN);

    // Initialize admin management for primary admins only
    if (IS_PRIMARY_ADMIN) {
        initializeAdminManagement();
    }

    // Initialize stock alert modals for all cooperative admins
    initializeStockAlerts();
});

// ============================================================================
// ADMIN MANAGEMENT FUNCTIONS (PRIMARY ADMIN ONLY)
// ============================================================================
@if(Auth::user()->isPrimaryAdmin())
function initializeAdminManagement() {
    let currentRequestId = null;
    let currentAdminId = null;
    let currentAction = null;

    // Initialize modals
    const requestDetailsModal = new bootstrap.Modal(document.getElementById('requestDetailsModal'));
    const responseModal = new bootstrap.Modal(document.getElementById('responseModal'));
    const removeAdminModal = new bootstrap.Modal(document.getElementById('removeAdminModal'));
    const reactivateAdminModal = new bootstrap.Modal(document.getElementById('reactivateAdminModal'));
    const permanentRemovalModal = new bootstrap.Modal(document.getElementById('permanentRemovalModal'));

    // Load initial data
    loadPendingRequests();

    // Tab change handlers
    document.getElementById('current-admins-tab').addEventListener('click', loadCurrentAdmins);
    document.getElementById('inactive-admins-tab').addEventListener('click', loadInactiveAdmins);

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

    document.getElementById('sendResponseBtn').addEventListener('click', function() {
        const message = document.getElementById('responseMessage').value.trim();

        if ((currentAction === 'reject' || currentAction === 'clarification') && !message) {
            showAlert('Ce champ est requis', 'danger');
            return;
        }

        const url = `${ROUTES[currentAction + 'Request']}/${currentRequestId}/${currentAction}`;

        showLoading(this);

        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF_TOKEN
            },
            body: JSON.stringify({ response_message: message })
        })
        .then(response => response.json())
        .then(data => {
            hideLoading(document.getElementById('sendResponseBtn'));

            if (data.success) {
                responseModal.hide();
                showAlert(data.message, 'success');
                loadPendingRequests();
                if (currentAction === 'approve') {
                    loadCurrentAdmins();
                }
            } else {
                showAlert(data.message || 'Erreur lors du traitement', 'danger');
            }
        })
        .catch(error => {
            hideLoading(document.getElementById('sendResponseBtn'));
            console.error('Error:', error);
            showAlert('Erreur de connexion', 'danger');
        });
    });

    document.getElementById('confirmRemoveBtn').addEventListener('click', function() {
        const removalReason = document.getElementById('removalReason').value.trim();

        showLoading(this);

        fetch(`${ROUTES.removeAdmin}/${currentAdminId}/remove`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF_TOKEN
            },
            body: JSON.stringify({ removal_reason: removalReason })
        })
        .then(response => response.json())
        .then(data => {
            hideLoading(document.getElementById('confirmRemoveBtn'));

            if (data.success) {
                removeAdminModal.hide();
                showAlert(data.message, 'success');
                loadCurrentAdmins();
                loadInactiveAdmins();
            } else {
                showAlert(data.message || 'Erreur lors du retrait', 'danger');
            }
        })
        .catch(error => {
            hideLoading(document.getElementById('confirmRemoveBtn'));
            console.error('Error:', error);
            showAlert('Erreur de connexion', 'danger');
        });
    });

    document.getElementById('confirmReactivateBtn').addEventListener('click', function() {
        const reactivationMessage = document.getElementById('reactivationMessage').value.trim();

        showLoading(this);

        fetch(`${ROUTES.reactivateAdmin}/${currentAdminId}/reactivate`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF_TOKEN
            },
            body: JSON.stringify({ reactivation_message: reactivationMessage })
        })
        .then(response => response.json())
        .then(data => {
            hideLoading(document.getElementById('confirmReactivateBtn'));

            if (data.success) {
                reactivateAdminModal.hide();
                showAlert(data.message, 'success');
                loadCurrentAdmins();
                loadInactiveAdmins();
            } else {
                showAlert(data.message || 'Erreur lors de la réactivation', 'danger');
            }
        })
        .catch(error => {
            hideLoading(document.getElementById('confirmReactivateBtn'));
            console.error('Error:', error);
            showAlert('Erreur de connexion', 'danger');
        });
    });

    document.getElementById('confirmPermanentRemovalBtn').addEventListener('click', function() {
        showLoading(this);

        fetch(`${ROUTES.permanentlyRemoveAdmin}/${currentAdminId}/permanently-remove`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF_TOKEN
            }
        })
        .then(response => response.json())
        .then(data => {
            hideLoading(document.getElementById('confirmPermanentRemovalBtn'));

            if (data.success) {
                permanentRemovalModal.hide();
                showAlert(data.message, 'success');
                loadInactiveAdmins();
            } else {
                showAlert(data.message || 'Erreur lors du retrait définitif', 'danger');
            }
        })
        .catch(error => {
            hideLoading(document.getElementById('confirmPermanentRemovalBtn'));
            console.error('Error:', error);
            showAlert('Erreur de connexion', 'danger');
        });
    });

    // Load functions
    function loadPendingRequests() {
        fetch(ROUTES.pendingRequests)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    displayPendingRequests(data.requests);
                    updateRequestsCount(data.requests.length);
                } else {
                    showAlert('Erreur lors du chargement des demandes', 'danger');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('Erreur de connexion', 'danger');
            });
    }

    function loadCurrentAdmins() {
        fetch(ROUTES.currentAdmins)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    displayCurrentAdmins(data.admins);
                    updateAdminsCount(data.admins.length);
                } else {
                    showAlert('Erreur lors du chargement des administrateurs', 'danger');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('Erreur de connexion', 'danger');
            });
    }

    function loadInactiveAdmins() {
        fetch(ROUTES.inactiveAdmins)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    displayInactiveAdmins(data.inactive_admins);
                    updateInactiveAdminsCount(data.inactive_admins.length);
                } else {
                    showAlert('Erreur lors du chargement des administrateurs inactifs', 'danger');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('Erreur de connexion', 'danger');
            });
    }

    // Display functions
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
                                            ${admin.is_primary_admin ? '<small class="text-warning d-block"><i class="fas fa-crown me-1"></i>Admin Principal</small>' : ''}
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
                                    ${!admin.is_current_user && !admin.is_primary_admin ? `
                                        <button class="btn btn-outline-warning btn-sm" onclick="showRemoveAdminModal(${admin.id}, '${admin.full_name.replace(/'/g, "\\'")}')">
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

    function displayInactiveAdmins(inactiveAdmins) {
        const container = document.getElementById('inactiveAdminsContent');

        if (inactiveAdmins.length === 0) {
            container.innerHTML = `
                <div class="empty-state">
                    <i class="fas fa-user-slash"></i>
                    <h5>Aucun administrateur inactif</h5>
                    <p class="text-muted">Il n'y a actuellement aucun administrateur suspendu.</p>
                </div>
            `;
            return;
        }

        container.innerHTML = inactiveAdmins.map(admin => `
            <div class="card inactive-admin-card mb-3">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <div class="admin-avatar bg-secondary">
                                ${admin.full_name.charAt(0).toUpperCase()}
                            </div>
                        </div>
                        <div class="col">
                            <h6 class="mb-1">
                                ${admin.full_name}
                                ${admin.is_primary_admin ? '<small class="text-warning ms-2"><i class="fas fa-crown me-1"></i>Ex-Admin Principal</small>' : ''}
                            </h6>
                            <p class="mb-1 text-muted small">
                                <i class="fas fa-envelope me-1"></i>
                                ${admin.email}
                            </p>
                            <p class="mb-0 text-muted small">
                                <i class="fas fa-user-minus me-1"></i>
                                Retiré ${admin.removed_at_human} par ${admin.removed_by}
                            </p>
                        </div>
                        <div class="col-auto">
                            <span class="badge bg-secondary status-badge">Suspendu</span>
                            <div class="mt-2">
                                ${!admin.is_primary_admin ? `
                                    <button class="btn btn-success btn-sm me-1" onclick="showReactivateAdminModal(${admin.id}, '${admin.full_name.replace(/'/g, "\\'")}')">
                                        <i class="fas fa-user-check"></i>
                                    </button>
                                    <button class="btn btn-danger btn-sm" onclick="showPermanentRemovalModal(${admin.id}, '${admin.full_name.replace(/'/g, "\\'")}')">
                                        <i class="fas fa-user-times"></i>
                                    </button>
                                ` : `
                                    <span class="text-muted small">Ex-Admin Principal</span>
                                `}
                            </div>
                        </div>
                    </div>
                    ${admin.removal_reason ? `
                        <div class="removal-info mt-2">
                            <small><strong>Motif:</strong> ${admin.removal_reason}</small>
                        </div>
                    ` : ''}
                </div>
            </div>
        `).join('');
    }

    // Global functions for modal interactions
    window.showRequestDetails = function(requestId) {
        currentRequestId = requestId;

        fetch(ROUTES.pendingRequests)
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
                showAlert('Erreur lors du chargement des détails', 'danger');
            });
    };

    window.showRemoveAdminModal = function(adminId, adminName) {
        currentAdminId = adminId;
        document.getElementById('adminToRemoveName').textContent = adminName;
        document.getElementById('removalReason').value = '';
        removeAdminModal.show();
    };

    window.showReactivateAdminModal = function(adminId, adminName) {
        currentAdminId = adminId;
        document.getElementById('adminToReactivateName').textContent = adminName;
        document.getElementById('reactivationMessage').value = '';
        reactivateAdminModal.show();
    };

    window.showPermanentRemovalModal = function(adminId, adminName) {
        currentAdminId = adminId;
        document.getElementById('adminToPermanentlyRemoveName').textContent = adminName;
        permanentRemovalModal.show();
    };

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

    function updateRequestsCount(count) {
        const element = document.getElementById('pendingRequestsCount');
        if (element) element.textContent = count;
    }

    function updateAdminsCount(count) {
        const element = document.getElementById('currentAdminsCount');
        if (element) element.textContent = count;
    }

    function updateInactiveAdminsCount(count) {
        const element = document.getElementById('inactiveAdminsCount');
        if (element) element.textContent = count;
    }

    // Auto-refresh pending requests every 30 seconds
    setInterval(function() {
        const activeTab = document.getElementById('pending-requests-tab');
        if (activeTab && activeTab.classList.contains('active')) {
            loadPendingRequests();
        }
    }, 30000);
}
@endif

// ============================================================================
// STOCK ALERT FUNCTIONS (ALL COOPERATIVE ADMINS)
// ============================================================================
function initializeStockAlerts() {
    // Global functions for stock alerts
    window.openStockAlertModal = function(productId, productName, currentThreshold) {
        document.getElementById('productId').value = productId;
        document.getElementById('productName').value = productName;
        document.getElementById('stockAlertThreshold').value = currentThreshold;

        const stockAlertModal = new bootstrap.Modal(document.getElementById('stockAlertModal'));
        stockAlertModal.show();
    };

    window.openBulkStockAlertModal = function() {
        const bulkStockAlertModal = new bootstrap.Modal(document.getElementById('bulkStockAlertModal'));
        bulkStockAlertModal.show();
    };

    window.saveStockAlert = function() {
        const productId = document.getElementById('productId').value;
        const threshold = document.getElementById('stockAlertThreshold').value;

        if (!threshold || threshold < 0 || threshold > 1000) {
            showAlert('Seuil d\'alerte invalide (0-1000)', 'danger');
            return;
        }

        fetch(`${ROUTES.configureStockAlert}/${productId}/configure-stock-alert`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF_TOKEN
            },
            body: JSON.stringify({
                stock_alert_threshold: parseInt(threshold)
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert(data.message, 'success');
                const stockAlertModal = bootstrap.Modal.getInstance(document.getElementById('stockAlertModal'));
                stockAlertModal.hide();
                setTimeout(() => window.location.reload(), 1500);
            } else {
                showAlert(data.message, 'danger');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('Erreur de connexion au serveur', 'danger');
        });
    };

    window.saveBulkStockAlert = function() {
        const threshold = document.getElementById('bulkThreshold').value;
        const applyTo = document.getElementById('applyTo').value;

        if (!threshold || threshold < 0 || threshold > 1000) {
            showAlert('Seuil d\'alerte invalide (0-1000)', 'danger');
            return;
        }

        fetch(ROUTES.bulkConfigureStockAlerts, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF_TOKEN
            },
            body: JSON.stringify({
                threshold: parseInt(threshold),
                apply_to: applyTo
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert(data.message, 'success');
                const bulkStockAlertModal = bootstrap.Modal.getInstance(document.getElementById('bulkStockAlertModal'));
                bulkStockAlertModal.hide();
                setTimeout(() => window.location.reload(), 1500);
            } else {
                showAlert(data.message, 'danger');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('Erreur de connexion au serveur', 'danger');
        });
    };
}

// ============================================================================
// ORDER MANAGEMENT FUNCTIONS (ALL COOPERATIVE ADMINS)
// ============================================================================
function markOrderAsReady(orderId) {
    if (!confirm('Marquer cette commande comme prête pour le retrait?')) {
        return;
    }

    updateOrderStatus(orderId, 'ready', 'Commande marquée comme prête');
}

function updateOrderStatus(orderId, status, successMessage) {
    fetch(`${ROUTES.updateOrderStatus}/${orderId}/update-status`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': CSRF_TOKEN
        },
        body: JSON.stringify({ status: status })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert(successMessage || data.message, 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showAlert(data.message, 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('Erreur lors de la mise à jour', 'danger');
    });
}

// ============================================================================
// UTILITY FUNCTIONS
// ============================================================================
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
</script>
@endpush
