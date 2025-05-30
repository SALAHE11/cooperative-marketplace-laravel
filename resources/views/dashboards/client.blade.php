@extends('layouts.app')

@section('title', 'Tableau de Bord Client - Coopérative E-commerce')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">Tableau de Bord Client</h1>
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
                                Mes Commandes
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['orders']['total'] }}</div>
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
                                Produits Favoris
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['favorites']['products'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-heart fa-2x text-gray-300"></i>
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
                                Coopératives Suivies
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['favorites']['cooperatives'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-building fa-2x text-gray-300"></i>
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
                                Total Dépensé
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($stats['spending']['total'], 2) }} MAD</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-coins fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Featured Products -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-star me-2"></i>
                        Produits en Vedette
                    </h6>
                </div>
                <div class="card-body">
                    @if($featuredProducts->count() > 0)
                        <div class="row">
                            @foreach($featuredProducts as $product)
                                <div class="col-xl-3 col-lg-4 col-md-6 mb-4">
                                    <div class="card product-card h-100">
                                        <div class="product-image-container">
                                            @if($product->primaryImageUrl)
                                                <img src="{{ $product->primaryImageUrl }}"
                                                     class="card-img-top product-image"
                                                     alt="{{ $product->name }}">
                                            @else
                                                <div class="card-img-top product-image-placeholder d-flex align-items-center justify-content-center">
                                                    <i class="fas fa-image fa-3x text-muted"></i>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="card-body d-flex flex-column">
                                            <h6 class="card-title">{{ $product->name }}</h6>
                                            <p class="card-text text-muted small flex-grow-1">
                                                {{ Str::limit($product->description, 80) }}
                                            </p>
                                            <div class="product-info mb-3">
                                                <div class="fw-bold text-success">{{ number_format($product->price, 2) }} MAD</div>
                                                <small class="text-muted">
                                                    <i class="fas fa-building me-1"></i>
                                                    {{ $product->cooperative->name }}
                                                </small>
                                            </div>
                                            <div class="d-grid">
                                                <button class="btn btn-primary btn-sm">
                                                    <i class="fas fa-cart-plus me-1"></i>
                                                    Ajouter au Panier
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-box fa-4x text-muted mb-3"></i>
                            <h4>Aucun produit disponible</h4>
                            <p class="text-muted">Les coopératives n'ont pas encore ajouté de produits.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Active Cooperatives -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-success">
                        <i class="fas fa-building me-2"></i>
                        Coopératives Actives
                    </h6>
                </div>
                <div class="card-body">
                    @if($activeCooperatives->count() > 0)
                        <div class="row">
                            @foreach($activeCooperatives as $cooperative)
                                <div class="col-xl-4 col-lg-6 mb-4">
                                    <div class="card cooperative-card h-100">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center mb-3">
                                                <div class="cooperative-avatar me-3">
                                                    <i class="fas fa-building"></i>
                                                </div>
                                                <div>
                                                    <h6 class="mb-1">{{ $cooperative->name }}</h6>
                                                    <small class="text-muted">{{ $cooperative->sector_of_activity }}</small>
                                                </div>
                                            </div>
                                            <p class="card-text small text-muted">
                                                {{ Str::limit($cooperative->description, 100) }}
                                            </p>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span class="badge bg-primary">{{ $cooperative->products_count }} produits</span>
                                                <button class="btn btn-outline-primary btn-sm">
                                                    <i class="fas fa-eye me-1"></i>
                                                    Voir
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-building fa-3x text-muted mb-3"></i>
                            <h5>Aucune coopérative active</h5>
                            <p class="text-muted">Il n'y a actuellement aucune coopérative avec des produits disponibles.</p>
                        </div>
                    @endif
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

.product-card {
    transition: all 0.3s ease;
    border: none;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.product-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 5px 20px rgba(0,0,0,0.15);
}

.product-image-container {
    height: 200px;
    overflow: hidden;
}

.product-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.product-image-placeholder {
    width: 100%;
    height: 100%;
    background: #f8f9fa;
}

.cooperative-card {
    transition: all 0.3s ease;
    border: none;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.cooperative-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.15);
}

.cooperative-avatar {
    width: 50px;
    height: 50px;
    background: linear-gradient(45deg, #28a745, #20c997);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.2rem;
}
</style>
@endsection
