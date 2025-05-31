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
                        Votre coopérative est en cours d'examen. Vous pourrez gérer vos produits une fois approuvée.
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

    <!-- Product Stats Cards -->
    @if(Auth::user()->cooperative && Auth::user()->cooperative->status === 'approved')
        @php
            $productStats = [
                'total' => Auth::user()->cooperative->products()->count(),
                'approved' => Auth::user()->cooperative->products()->where('status', 'approved')->count(),
                'pending' => Auth::user()->cooperative->products()->where('status', 'pending')->count(),
                'draft' => Auth::user()->cooperative->products()->where('status', 'draft')->count(),
                'revenue' => Auth::user()->cooperative->products()->where('status', 'approved')->sum('price') * 0.85,
                'low_stock' => Auth::user()->cooperative->products()->where('status', 'approved')->whereRaw('stock_quantity <= stock_alert_threshold')->count(),
                'out_of_stock' => Auth::user()->cooperative->products()->where('status', 'approved')->where('stock_quantity', 0)->count()
            ];
        @endphp

        <div class="row mb-4">
            <div class="col-xl-2 col-md-4 mb-4">
                <div class="card border-left-primary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                    Total Produits
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $productStats['total'] }}</div>
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
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $productStats['approved'] }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-check-circle fa-2x text-gray-300"></i>
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
                                    En Attente
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $productStats['pending'] }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-clock fa-2x text-gray-300"></i>
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
                                    Brouillons
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $productStats['draft'] }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-edit fa-2x text-gray-300"></i>
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
                                    {{ $productStats['low_stock'] }}
                                    @if($productStats['low_stock'] > 0)
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

            <div class="col-xl-2 col-md-4 mb-4">
                <div class="card border-left-dark shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-dark text-uppercase mb-1">
                                    Rupture Stock
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    {{ $productStats['out_of_stock'] }}
                                    @if($productStats['out_of_stock'] > 0)
                                        <i class="fas fa-times-circle text-danger ms-1"></i>
                                    @endif
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-times-circle fa-2x text-gray-300"></i>
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
        <!-- Recent Products / Orders -->
        <div class="col-lg-8 mb-4">
            @if(Auth::user()->cooperative && Auth::user()->cooperative->status === 'approved')
                <!-- Recent Products -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                        <h6 class="m-0 font-weight-bold text-primary">Produits Récents</h6>
                        <a href="{{ route('coop.products.index') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-box me-1"></i>
                            Voir tous
                        </a>
                    </div>
                    <div class="card-body">
                        @php
                            $recentProducts = Auth::user()->cooperative->products()
                                ->with(['category', 'images'])
                                ->orderBy('updated_at', 'desc')
                                ->take(5)
                                ->get();
                        @endphp

                        @if($recentProducts->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-bordered" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th width="60">Image</th>
                                            <th>Produit</th>
                                            <th>Catégorie</th>
                                            <th>Prix</th>
                                            <th>Stock</th>
                                            <th>Seuil Alerte</th>
                                            <th>Statut</th>
                                            <th>Mis à jour</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($recentProducts as $product)
                                        <tr>
                                            <td>
                                                @if($product->primaryImageUrl)
                                                    <img src="{{ $product->primaryImageUrl }}"
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
                                            <td>{{ $product->category->name }}</td>
                                            <td><strong>{{ number_format($product->price, 2) }} MAD</strong></td>
                                            <td>
                                                <span class="badge bg-{{ $product->stock_status_badge }}">
                                                    {{ $product->stock_quantity }}
                                                </span>
                                            </td>
                                            <td>
                                                <small class="text-muted">{{ $product->stock_alert_threshold }}</small>
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
                        @else
                            <div class="text-center py-4">
                                <i class="fas fa-box fa-3x text-muted mb-3"></i>
                                <h5>Aucun produit</h5>
                                <p class="text-muted">Commencez par ajouter votre premier produit.</p>
                                <a href="{{ route('coop.products.create') }}" class="btn btn-primary">
                                    <i class="fas fa-plus me-2"></i>
                                    Ajouter un produit
                                </a>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Low Stock Alert -->
                @php
                    $lowStockProducts = Auth::user()->cooperative->products()
                        ->where('status', 'approved')
                        ->whereRaw('stock_quantity <= stock_alert_threshold')
                        ->orderBy('stock_quantity', 'asc')
                        ->take(5)
                        ->get();
                @endphp

                @if($lowStockProducts->count() > 0)
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
                                            <small class="text-muted">{{ $product->category->name }}</small>
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
                        <h6 class="m-0 font-weight-bold text-muted">Gestion des Produits</h6>
                    </div>
                    <div class="card-body text-center py-5">
                        <i class="fas fa-lock fa-4x text-muted mb-3"></i>
                        <h5 class="text-muted">Accès Restreint</h5>
                        <p class="text-muted">
                            La gestion des produits sera disponible une fois que votre coopérative sera approuvée par l'administration.
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
                            <a href="{{ route('coop.products.index', ['status' => 'draft']) }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="fas fa-edit text-info me-3"></i>
                                    Brouillons
                                </div>
                                <span class="badge bg-info">{{ $productStats['draft'] ?? 0 }}</span>
                            </a>
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
                        <a href="#" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <div>
                                <i class="fas fa-shopping-cart text-success me-3"></i>
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

<!-- Stock Alert Configuration Modal -->
<div class="modal fade" id="stockAlertModal" tabindex="-1" aria-labelledby="stockAlertModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="stockAlertModalLabel">
                    <i class="fas fa-bell me-2"></i>
                    Configurer Seuil d'Alerte Stock
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="stockAlertForm">
                    <div class="mb-3">
                        <label for="productName" class="form-label">Produit</label>
                        <input type="text" class="form-control" id="productName" readonly>
                        <input type="hidden" id="productId">
                    </div>
                    <div class="mb-3">
                        <label for="stockAlertThreshold" class="form-label">Seuil d'Alerte Stock</label>
                        <div class="input-group">
                            <input type="number" class="form-control" id="stockAlertThreshold"
                                   min="0" max="1000" required>
                            <span class="input-group-text">unités</span>
                        </div>
                        <div class="form-text">
                            Vous serez alerté quand le stock descend à ce niveau ou en dessous.
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-warning" onclick="saveStockAlert()">
                    <i class="fas fa-save me-1"></i>
                    Sauvegarder
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Bulk Stock Alert Configuration Modal -->
<div class="modal fade" id="bulkStockAlertModal" tabindex="-1" aria-labelledby="bulkStockAlertModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="bulkStockAlertModalLabel">
                    <i class="fas fa-bell me-2"></i>
                    Configuration Groupée des Alertes Stock
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="bulkStockAlertForm">
                    <div class="mb-3">
                        <label for="bulkThreshold" class="form-label">Nouveau Seuil d'Alerte</label>
                        <div class="input-group">
                            <input type="number" class="form-control" id="bulkThreshold"
                                   min="0" max="1000" value="5" required>
                            <span class="input-group-text">unités</span>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="applyTo" class="form-label">Appliquer à</label>
                        <select class="form-select" id="applyTo" required>
                            <option value="all">Tous les produits</option>
                            <option value="approved">Produits approuvés uniquement</option>
                            <option value="low_stock">Produits actuellement en stock faible</option>
                        </select>
                    </div>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Information:</strong> Cette action modifiera le seuil d'alerte pour plusieurs produits à la fois.
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-warning" onclick="saveBulkStockAlert()">
                    <i class="fas fa-save me-1"></i>
                    Appliquer la Configuration
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Include existing modals for primary admin -->
@if(Auth::user()->isPrimaryAdmin())
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
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title" id="removeAdminModalLabel">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Confirmer le Retrait Temporaire
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Retrait temporaire:</strong> Cette action peut être annulée depuis l'onglet "Administrateurs Inactifs".
                </div>
                <p>Êtes-vous sûr de vouloir retirer temporairement <strong id="adminToRemoveName"></strong> de l'administration de la coopérative?</p>

                <div class="mb-3">
                    <label for="removalReason" class="form-label">Motif du retrait (optionnel)</label>
                    <textarea class="form-control" id="removalReason" rows="3" placeholder="Expliquez la raison du retrait..."></textarea>
                    <small class="form-text text-muted">Ce motif sera inclus dans l'email de notification.</small>
                </div>

                <p class="text-muted small">L'administrateur sera notifié par email et pourra être réactivé ultérieurement.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-warning" id="confirmRemoveBtn">
                    <i class="fas fa-user-minus me-1"></i>
                    Confirmer le Retrait
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Admin Reactivation Modal -->
<div class="modal fade" id="reactivateAdminModal" tabindex="-1" aria-labelledby="reactivateAdminModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="reactivateAdminModalLabel">
                    <i class="fas fa-user-check me-2"></i>
                    Confirmer la Réactivation
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-success">
                    <i class="fas fa-check-circle me-2"></i>
                    <strong>Réactivation:</strong> L'administrateur retrouvera tous ses droits d'accès.
                </div>
                <p>Êtes-vous sûr de vouloir réactiver <strong id="adminToReactivateName"></strong> comme administrateur de la coopérative?</p>

                <div class="mb-3">
                    <label for="reactivationMessage" class="form-label">Message de bienvenue (optionnel)</label>
                    <textarea class="form-control" id="reactivationMessage" rows="3" placeholder="Message de bienvenue pour le retour de l'administrateur..."></textarea>
                    <small class="form-text text-muted">Ce message sera inclus dans l'email de réactivation.</small>
                </div>

                <p class="text-muted small">L'administrateur sera notifié par email et pourra immédiatement accéder au tableau de bord.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-success" id="confirmReactivateBtn">
                    <i class="fas fa-user-check me-1"></i>
                    Confirmer la Réactivation
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Permanent Removal Confirmation Modal -->
<div class="modal fade" id="permanentRemovalModal" tabindex="-1" aria-labelledby="permanentRemovalModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="permanentRemovalModalLabel">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Confirmer le Retrait Définitif
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger">
                    <i class="fas fa-warning me-2"></i>
                    <strong>Action irréversible!</strong> Cette action ne peut pas être annulée.
                </div>
                <p>Êtes-vous sûr de vouloir définitivement retirer <strong id="adminToPermanentlyRemoveName"></strong> de la coopérative?</p>

                <div class="alert alert-info">
                    <h6>Conséquences:</h6>
                    <ul class="mb-0">
                        <li>L'administrateur sera converti en client régulier</li>
                        <li>Tous les liens avec la coopérative seront supprimés</li>
                        <li>Cette action ne peut pas être annulée</li>
                        <li>Le compte utilisateur reste actif (pas supprimé)</li>
                    </ul>
                </div>

                <p class="text-muted small"><strong>Note:</strong> L'utilisateur pourra toujours utiliser son compte comme client ou rejoindre d'autres coopératives.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-danger" id="confirmPermanentRemovalBtn">
                    <i class="fas fa-user-times me-1"></i>
                    Retirer Définitivement
                </button>
            </div>
        </div>
    </div>
</div>
@endif

<style>
.border-left-primary { border-left: 0.25rem solid #4e73df !important; }
.border-left-success { border-left: 0.25rem solid #1cc88a !important; }
.border-left-info { border-left: 0.25rem solid #36b9cc !important; }
.border-left-warning { border-left: 0.25rem solid #f6c23e !important; }
.border-left-danger { border-left: 0.25rem solid #e74a3b !important; }
.border-left-dark { border-left: 0.25rem solid #5a5c69 !important; }

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
document.addEventListener('DOMContentLoaded', function() {
    // Check if user is primary admin - exit early if not
    const isPrimaryAdmin = {{ Auth::user()->isPrimaryAdmin() ? 'true' : 'false' }};

    // Stock Alert Modals
    const stockAlertModal = new bootstrap.Modal(document.getElementById('stockAlertModal'));
    const bulkStockAlertModal = new bootstrap.Modal(document.getElementById('bulkStockAlertModal'));

    if (!isPrimaryAdmin) {
        // For non-primary admins, exit early - no admin management features
        console.log('Non-primary admin: Admin management features are disabled');
        return;
    }

    // === PRIMARY ADMIN ONLY CODE BELOW ===
    let currentRequestId = null;
    let currentAdminId = null;
    let currentAction = null;

    // Initialize modals (only exist for primary admin)
    const requestDetailsModal = new bootstrap.Modal(document.getElementById('requestDetailsModal'));
    const responseModal = new bootstrap.Modal(document.getElementById('responseModal'));
    const removeAdminModal = new bootstrap.Modal(document.getElementById('removeAdminModal'));
    const reactivateAdminModal = new bootstrap.Modal(document.getElementById('reactivateAdminModal'));
    const permanentRemovalModal = new bootstrap.Modal(document.getElementById('permanentRemovalModal'));

    // Load initial data
    loadPendingRequests();

    // Tab change handlers
    document.getElementById('current-admins-tab').addEventListener('click', function() {
        loadCurrentAdmins();
    });

    document.getElementById('inactive-admins-tab').addEventListener('click', function() {
        loadInactiveAdmins();
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

    // Load inactive admins
    function loadInactiveAdmins() {
        fetch('{{ route("coop.admin-requests.inactive-admins") }}')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    displayInactiveAdmins(data.inactive_admins);
                    updateInactiveAdminsCount(data.inactive_admins.length);
                } else {
                    showError('Erreur lors du chargement des administrateurs inactifs');
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
                                        <button class="btn btn-outline-warning btn-sm" onclick="showRemoveAdminModal(${admin.id}, '${admin.full_name}')">
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

    // Display inactive admins
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
                                    <button class="btn btn-success btn-sm me-1" onclick="showReactivateAdminModal(${admin.id}, '${admin.full_name}')">
                                        <i class="fas fa-user-check"></i>
                                    </button>
                                    <button class="btn btn-danger btn-sm" onclick="showPermanentRemovalModal(${admin.id}, '${admin.full_name}')">
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
                if (currentAction === 'approve') {
                    loadCurrentAdmins(); // Also refresh current admins if approved
                }
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
        document.getElementById('removalReason').value = '';
        removeAdminModal.show();
    };

    // Confirm admin removal
    document.getElementById('confirmRemoveBtn').addEventListener('click', function() {
        const removalReason = document.getElementById('removalReason').value.trim();

        showLoading(this);

        fetch(`{{ url('/coop/admins') }}/${currentAdminId}/remove`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                removal_reason: removalReason
            })
        })
        .then(response => response.json())
        .then(data => {
            hideLoading(document.getElementById('confirmRemoveBtn'));

            if (data.success) {
                removeAdminModal.hide();
                showSuccess(data.message);
                loadCurrentAdmins(); // Refresh current admins
                loadInactiveAdmins(); // Refresh inactive admins
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

    // Show reactivate admin modal
    window.showReactivateAdminModal = function(adminId, adminName) {
        currentAdminId = adminId;
        document.getElementById('adminToReactivateName').textContent = adminName;
        document.getElementById('reactivationMessage').value = '';
        reactivateAdminModal.show();
    };

    // Confirm admin reactivation
    document.getElementById('confirmReactivateBtn').addEventListener('click', function() {
        const reactivationMessage = document.getElementById('reactivationMessage').value.trim();

        showLoading(this);

        fetch(`{{ url('/coop/admins') }}/${currentAdminId}/reactivate`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                reactivation_message: reactivationMessage
            })
        })
        .then(response => response.json())
        .then(data => {
            hideLoading(document.getElementById('confirmReactivateBtn'));

            if (data.success) {
                reactivateAdminModal.hide();
                showSuccess(data.message);
                loadCurrentAdmins(); // Refresh current admins
                loadInactiveAdmins(); // Refresh inactive admins
            } else {
                showError(data.message || 'Erreur lors de la réactivation');
            }
        })
        .catch(error => {
            hideLoading(document.getElementById('confirmReactivateBtn'));
            console.error('Error:', error);
            showError('Erreur de connexion');
        });
    });

    // Show permanent removal modal
    window.showPermanentRemovalModal = function(adminId, adminName) {
        currentAdminId = adminId;
        document.getElementById('adminToPermanentlyRemoveName').textContent = adminName;
        permanentRemovalModal.show();
    };

    // Confirm permanent removal
    document.getElementById('confirmPermanentRemovalBtn').addEventListener('click', function() {
        showLoading(this);

        fetch(`{{ url('/coop/admins') }}/${currentAdminId}/permanently-remove`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            hideLoading(document.getElementById('confirmPermanentRemovalBtn'));

            if (data.success) {
                permanentRemovalModal.hide();
                showSuccess(data.message);
                loadInactiveAdmins(); // Refresh inactive admins
            } else {
                showError(data.message || 'Erreur lors du retrait définitif');
            }
        })
        .catch(error => {
            hideLoading(document.getElementById('confirmPermanentRemovalBtn'));
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

    function updateInactiveAdminsCount(count) {
        document.getElementById('inactiveAdminsCount').textContent = count;
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

    // === END PRIMARY ADMIN ONLY CODE ===
});

// Stock Alert Configuration Functions (Available for all cooperative admins)
function openStockAlertModal(productId, productName, currentThreshold) {
    document.getElementById('productId').value = productId;
    document.getElementById('productName').value = productName;
    document.getElementById('stockAlertThreshold').value = currentThreshold;

    const stockAlertModal = new bootstrap.Modal(document.getElementById('stockAlertModal'));
    stockAlertModal.show();
}

function openBulkStockAlertModal() {
    const bulkStockAlertModal = new bootstrap.Modal(document.getElementById('bulkStockAlertModal'));
    bulkStockAlertModal.show();
}

function saveStockAlert() {
    const productId = document.getElementById('productId').value;
    const threshold = document.getElementById('stockAlertThreshold').value;

    if (!threshold || threshold < 0 || threshold > 1000) {
        showAlert('Seuil d\'alerte invalide (0-1000)', 'danger');
        return;
    }

    fetch(`/coop/products/${productId}/configure-stock-alert`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
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
            // Reload page to update dashboard
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            showAlert(data.message, 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('Erreur de connexion au serveur', 'danger');
    });
}

function saveBulkStockAlert() {
    const threshold = document.getElementById('bulkThreshold').value;
    const applyTo = document.getElementById('applyTo').value;

    if (!threshold || threshold < 0 || threshold > 1000) {
        showAlert('Seuil d\'alerte invalide (0-1000)', 'danger');
        return;
    }

    fetch('/coop/products/bulk-configure-stock-alerts', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
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
            // Reload page to update dashboard
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            showAlert(data.message, 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('Erreur de connexion au serveur', 'danger');
    });
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
