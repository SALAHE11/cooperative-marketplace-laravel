@extends('layouts.app')

@section('title', 'Tableau de Bord Client - Coopérative E-commerce')

@section('content')
<div class="container-fluid py-4">
    <!-- Header with Navigation -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">Tableau de Bord Client</h1>
                    <p class="text-muted">Bienvenue, {{ Auth::user()->full_name ?? 'Test User' }}</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('client.products.index') }}" class="btn btn-primary">
                        <i class="fas fa-search me-1"></i>
                        Parcourir Produits
                    </a>
                    <a href="{{ route('client.cart.index') }}" class="btn btn-success position-relative">
                        <i class="fas fa-shopping-cart me-1"></i>
                        Mon Panier
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
                              id="cartBadge" style="display: none;">0</span>
                    </a>
                    <a href="{{ route('client.orders.index') }}" class="btn btn-info">
                        <i class="fas fa-shopping-bag me-1"></i>
                        Mes Commandes
                    </a>
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
                                Articles dans le Panier
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="cartItemsCount">{{ $stats['cart']['items'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-shopping-basket fa-2x text-gray-300"></i>
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
                                Commandes en Cours
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['orders']['pending'] + $stats['orders']['ready'] }}</div>
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

    <!-- Quick Actions -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-rocket me-2"></i>
                        Actions Rapides
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-3 col-md-6 mb-3">
                            <a href="{{ route('client.products.index') }}" class="btn btn-outline-primary w-100 h-100 d-flex flex-column align-items-center justify-content-center py-4">
                                <i class="fas fa-search fa-3x mb-2"></i>
                                <h6 class="mb-1">Parcourir les Produits</h6>
                                <small class="text-muted">Découvrir nos coopératives</small>
                            </a>
                        </div>
                        <div class="col-lg-3 col-md-6 mb-3">
                            <a href="{{ route('client.cart.index') }}" class="btn btn-outline-success w-100 h-100 d-flex flex-column align-items-center justify-content-center py-4">
                                <i class="fas fa-shopping-cart fa-3x mb-2"></i>
                                <h6 class="mb-1">Mon Panier</h6>
                                <small class="text-muted" id="cartSummary">
                                    @if($stats['cart']['items'] > 0)
                                        {{ $stats['cart']['items'] }} article(s) - {{ number_format($stats['cart']['total'], 2) }} MAD
                                    @else
                                        Panier vide
                                    @endif
                                </small>
                            </a>
                        </div>
                        <div class="col-lg-3 col-md-6 mb-3">
                            <a href="{{ route('client.orders.index') }}" class="btn btn-outline-info w-100 h-100 d-flex flex-column align-items-center justify-content-center py-4">
                                <i class="fas fa-shopping-bag fa-3x mb-2"></i>
                                <h6 class="mb-1">Mes Commandes</h6>
                                <small class="text-muted">{{ $stats['orders']['total'] }} commande(s)</small>
                            </a>
                        </div>
                        <div class="col-lg-3 col-md-6 mb-3">
                            <a href="{{ route('client.orders.index', ['status' => 'ready']) }}" class="btn btn-outline-warning w-100 h-100 d-flex flex-column align-items-center justify-content-center py-4">
                                <i class="fas fa-bell fa-3x mb-2"></i>
                                <h6 class="mb-1">Commandes Prêtes</h6>
                                <small class="text-muted">{{ $stats['orders']['ready'] }} prête(s)</small>
                            </a>
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
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-star me-2"></i>
                        Produits en Vedette
                    </h6>
                    <a href="{{ route('client.products.index') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-arrow-right me-1"></i>
                        Voir tous les produits
                    </a>
                </div>
                <div class="card-body">
                    @if($featuredProducts->count() > 0)
                        <div class="row">
                            @foreach($featuredProducts as $product)
                                <div class="col-xl-3 col-lg-4 col-md-6 mb-4">
                                    <div class="card product-card h-100">
                                        <div class="product-image-container position-relative">
                                            @if($product->primaryImageUrl)
                                                <img src="{{ $product->primaryImageUrl }}"
                                                     class="card-img-top product-image"
                                                     alt="{{ $product->name }}"
                                                     style="height: 200px; object-fit: cover; cursor: pointer;"
                                                     onclick="window.location.href='{{ route('client.products.show', $product) }}'">
                                            @else
                                                <div class="card-img-top product-image-placeholder d-flex align-items-center justify-content-center"
                                                     style="height: 200px; background: #f8f9fa; cursor: pointer;"
                                                     onclick="window.location.href='{{ route('client.products.show', $product) }}'">
                                                    <i class="fas fa-image fa-3x text-muted"></i>
                                                </div>
                                            @endif
                                            <div class="position-absolute top-0 end-0 m-2">
                                                <span class="badge bg-success">{{ number_format($product->price, 2) }} MAD</span>
                                            </div>
                                            @if($product->stock_quantity <= $product->stock_alert_threshold && $product->stock_quantity > 0)
                                                <div class="position-absolute top-0 start-0 m-2">
                                                    <span class="badge bg-warning text-dark">Stock limité</span>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="card-body d-flex flex-column">
                                            <h6 class="card-title">{{ $product->name }}</h6>
                                            <p class="card-text text-muted small flex-grow-1">
                                                {{ Str::limit($product->description, 80) }}
                                            </p>
                                            <div class="product-info mb-3">
                                                <small class="text-muted">
                                                    <i class="fas fa-building me-1"></i>
                                                    <a href="{{ route('client.products.index', ['cooperative_id' => $product->cooperative->id]) }}"
                                                       class="text-decoration-none">{{ $product->cooperative->name }}</a>
                                                </small>
                                                <br>
                                                <small class="text-muted">
                                                    <i class="fas fa-tag me-1"></i>
                                                    <a href="{{ route('client.products.index', ['category_id' => $product->category->id]) }}"
                                                       class="text-decoration-none">{{ $product->category->name }}</a>
                                                </small>
                                                @if($product->stock_quantity <= $product->stock_alert_threshold && $product->stock_quantity > 0)
                                                    <br>
                                                    <small class="text-warning">
                                                        <i class="fas fa-exclamation-triangle me-1"></i>
                                                        Plus que {{ $product->stock_quantity }} en stock
                                                    </small>
                                                @endif
                                            </div>
                                            <div class="d-grid gap-2">
                                                <a href="{{ route('client.products.show', $product) }}" class="btn btn-outline-primary btn-sm">
                                                    <i class="fas fa-eye me-1"></i>
                                                    Voir détails
                                                </a>
                                                @if($product->stock_quantity > 0)
                                                    <button class="btn btn-success btn-sm" onclick="addToCart({{ $product->id }}, 1)">
                                                        <i class="fas fa-cart-plus me-1"></i>
                                                        Ajouter au Panier
                                                    </button>
                                                @else
                                                    <button class="btn btn-secondary btn-sm" disabled>
                                                        <i class="fas fa-times me-1"></i>
                                                        Rupture de stock
                                                    </button>
                                                @endif
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
                            <a href="{{ route('client.products.index') }}" class="btn btn-primary">
                                <i class="fas fa-search me-1"></i>
                                Explorer les produits
                            </a>
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
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-success">
                        <i class="fas fa-building me-2"></i>
                        Coopératives Actives
                    </h6>
                    <a href="{{ route('client.products.index') }}" class="btn btn-success btn-sm">
                        <i class="fas fa-search me-1"></i>
                        Explorer toutes
                    </a>
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
                                                    @if($cooperative->logo_path)
                                                        <img src="{{ Storage::url($cooperative->logo_path) }}"
                                                             alt="{{ $cooperative->name }}"
                                                             class="rounded-circle" style="width: 50px; height: 50px; object-fit: cover;">
                                                    @else
                                                        <i class="fas fa-building"></i>
                                                    @endif
                                                </div>
                                                <div>
                                                    <h6 class="mb-1">{{ $cooperative->name }}</h6>
                                                    <small class="text-muted">{{ $cooperative->sector_of_activity }}</small>
                                                </div>
                                            </div>
                                            <p class="card-text small text-muted">
                                                {{ Str::limit($cooperative->description, 100) }}
                                            </p>
                                            <div class="mb-3">
                                                <small class="text-muted">
                                                    <i class="fas fa-map-marker-alt me-1"></i>
                                                    {{ Str::limit($cooperative->address, 50) }}
                                                </small>
                                                <br>
                                                <small class="text-muted">
                                                    <i class="fas fa-phone me-1"></i>
                                                    {{ $cooperative->phone }}
                                                </small>
                                            </div>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span class="badge bg-primary">{{ $cooperative->products_count }} produits</span>
                                                <a href="{{ route('client.products.index', ['cooperative_id' => $cooperative->id]) }}"
                                                   class="btn btn-outline-primary btn-sm">
                                                    <i class="fas fa-eye me-1"></i>
                                                    Voir produits
                                                </a>
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

    <!-- Recent Orders (if any) -->
    @if(isset($recentOrders) && $recentOrders->count() > 0)
    <div class="row mt-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-info">
                        <i class="fas fa-history me-2"></i>
                        Mes Dernières Commandes
                    </h6>
                    <a href="{{ route('client.orders.index') }}" class="btn btn-info btn-sm">
                        <i class="fas fa-list me-1"></i>
                        Voir toutes
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Commande</th>
                                    <th>Date</th>
                                    <th>Statut</th>
                                    <th>Total</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentOrders->take(5) as $order)
                                <tr>
                                    <td>
                                        <strong>#{{ $order->order_number }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $order->orderItems->count() }} article(s)</small>
                                    </td>
                                    <td>{{ $order->created_at->format('d/m/Y H:i') }}</td>
                                    <td>
                                        <span class="badge bg-{{ $order->status === 'pending' ? 'warning' : ($order->status === 'ready' ? 'info' : ($order->status === 'completed' ? 'success' : 'danger')) }}">
                                            {{ ucfirst($order->status) }}
                                        </span>
                                    </td>
                                    <td><strong>{{ number_format($order->total_amount, 2) }} MAD</strong></td>
                                    <td>
                                        <a href="{{ route('client.orders.show', $order) }}" class="btn btn-primary btn-sm">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
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
    cursor: pointer;
}

.product-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 5px 20px rgba(0,0,0,0.15);
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

.btn:focus, .btn:active {
    box-shadow: none;
}

.product-image-container:hover .product-image {
    transform: scale(1.05);
    transition: transform 0.3s ease;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    updateCartBadge();

    // Update cart badge every 30 seconds
    setInterval(updateCartBadge, 30000);
});

// Update cart badge function
function updateCartBadge() {
    fetch('/client/cart/count')
        .then(response => response.json())
        .then(data => {
            const badge = document.getElementById('cartBadge');
            const itemsCount = document.getElementById('cartItemsCount');
            const cartSummary = document.getElementById('cartSummary');

            if (badge) {
                badge.textContent = data.count;
                badge.style.display = data.count > 0 ? 'inline' : 'none';
            }

            if (itemsCount) {
                itemsCount.textContent = data.count;
            }

            if (cartSummary) {
                if (data.count > 0) {
                    cartSummary.textContent = `${data.count} article(s) - ${data.total} MAD`;
                } else {
                    cartSummary.textContent = 'Panier vide';
                }
            }
        })
        .catch(error => console.error('Error updating cart badge:', error));
}

// Add to cart function
function addToCart(productId, quantity = 1) {
    const button = event.target;
    const originalText = button.innerHTML;

    // Show loading state
    button.disabled = true;
    button.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Ajout...';

    fetch(`/client/cart/add/${productId}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ quantity: quantity })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert(data.message, 'success');
            updateCartBadge();

            // Animate button success
            button.innerHTML = '<i class="fas fa-check me-1"></i>Ajouté!';
            button.classList.remove('btn-success');
            button.classList.add('btn-outline-success');

            setTimeout(() => {
                button.innerHTML = originalText;
                button.classList.remove('btn-outline-success');
                button.classList.add('btn-success');
                button.disabled = false;
            }, 2000);
        } else {
            showAlert(data.message, 'danger');
            button.innerHTML = originalText;
            button.disabled = false;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('Erreur lors de l\'ajout au panier', 'danger');
        button.innerHTML = originalText;
        button.disabled = false;
    });
}

// Show alert function
function showAlert(message, type) {
    // Remove existing alerts
    const existingAlerts = document.querySelectorAll('.dynamic-alert');
    existingAlerts.forEach(alert => alert.remove());

    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show position-fixed dynamic-alert`;
    alertDiv.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px; max-width: 400px;';
    alertDiv.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-triangle'} me-2"></i>
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;

    document.body.appendChild(alertDiv);

    // Auto remove after 5 seconds
    setTimeout(() => {
        if (alertDiv.parentNode) {
            alertDiv.parentNode.removeChild(alertDiv);
        }
    }, 5000);
}
</script>
@endsection
