@extends('layouts.app')

@section('title', 'Parcourir les Produits - Coopérative E-commerce')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">Parcourir les Produits</h1>
                    <p class="text-muted">Découvrez les produits de nos coopératives partenaires</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('client.dashboard') }}" class="btn btn-outline-primary">
                        <i class="fas fa-home me-1"></i>
                        Accueil
                    </a>
                    <a href="{{ route('client.cart.index') }}" class="btn btn-success">
                        <i class="fas fa-shopping-cart me-1"></i>
                        Mon Panier
                        <span class="badge bg-light text-dark ms-1" id="cartBadge" style="display: none;">0</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Filters Sidebar -->
        <div class="col-lg-3 mb-4">
            <div class="card shadow">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-filter me-2"></i>
                        Filtres
                    </h5>
                </div>
                <div class="card-body">
                    <form id="filtersForm" method="GET">
                        <!-- Search -->
                        <div class="mb-3">
                            <label for="search" class="form-label">Rechercher</label>
                            <div class="position-relative">
                                <input type="text" class="form-control" id="search" name="search"
                                       value="{{ $search }}" placeholder="Nom du produit...">
                                <div id="searchSuggestions" class="search-suggestions d-none"></div>
                            </div>
                        </div>

                        <!-- Category -->
                        <div class="mb-3">
                            <label for="category_id" class="form-label">Catégorie</label>
                            <select class="form-select" id="category_id" name="category_id">
                                <option value="">Toutes les catégories</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ $category_id == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Cooperative -->
                        <div class="mb-3">
                            <label for="cooperative_id" class="form-label">Coopérative</label>
                            <select class="form-select" id="cooperative_id" name="cooperative_id">
                                <option value="">Toutes les coopératives</option>
                                @foreach($cooperatives as $cooperative)
                                    <option value="{{ $cooperative->id }}" {{ $cooperative_id == $cooperative->id ? 'selected' : '' }}>
                                        {{ $cooperative->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Price Range -->
                        <div class="mb-3">
                            <label class="form-label">Fourchette de prix</label>
                            <div class="row">
                                <div class="col-6">
                                    <input type="number" class="form-control" name="min_price"
                                           value="{{ $min_price }}" placeholder="Min" min="0" step="0.01">
                                </div>
                                <div class="col-6">
                                    <input type="number" class="form-control" name="max_price"
                                           value="{{ $max_price }}" placeholder="Max" min="0" step="0.01">
                                </div>
                            </div>
                            @if($priceRange)
                            <small class="text-muted">
                                Prix disponibles: {{ number_format($priceRange->min_price, 2) }} - {{ number_format($priceRange->max_price, 2) }} MAD
                            </small>
                            @endif
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search me-1"></i>
                                Appliquer les filtres
                            </button>
                            <a href="{{ route('client.products.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-1"></i>
                                Effacer les filtres
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Products Grid -->
        <div class="col-lg-9">
            <!-- Sort and View Options -->
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <span class="text-muted">{{ $products->total() }} produit(s) trouvé(s)</span>
                </div>
                <div class="d-flex gap-3">
                    <select class="form-select form-select-sm" style="width: 200px;" onchange="updateSort(this.value)">
                        <option value="newest" {{ $sort == 'newest' ? 'selected' : '' }}>Plus récents</option>
                        <option value="oldest" {{ $sort == 'oldest' ? 'selected' : '' }}>Plus anciens</option>
                        <option value="price_asc" {{ $sort == 'price_asc' ? 'selected' : '' }}>Prix croissant</option>
                        <option value="price_desc" {{ $sort == 'price_desc' ? 'selected' : '' }}>Prix décroissant</option>
                        <option value="name_asc" {{ $sort == 'name_asc' ? 'selected' : '' }}>Nom A-Z</option>
                        <option value="name_desc" {{ $sort == 'name_desc' ? 'selected' : '' }}>Nom Z-A</option>
                    </select>

                    <select class="form-select form-select-sm" style="width: 100px;" onchange="updatePerPage(this.value)">
                        <option value="12" {{ $per_page == 12 ? 'selected' : '' }}>12</option>
                        <option value="24" {{ $per_page == 24 ? 'selected' : '' }}>24</option>
                        <option value="48" {{ $per_page == 48 ? 'selected' : '' }}>48</option>
                    </select>
                </div>
            </div>

            @if($products->count() > 0)
                <div class="row">
                    @foreach($products as $product)
                        <div class="col-xl-4 col-lg-6 col-md-6 mb-4">
                            <div class="card product-card h-100">
                                <div class="position-relative">
                                    @if($product->primaryImageUrl)
                                        <img src="{{ $product->primaryImageUrl }}" class="card-img-top"
                                             style="height: 200px; object-fit: cover; cursor: pointer;"
                                             alt="{{ $product->name }}"
                                             onclick="window.location.href='{{ route('client.products.show', $product) }}'">
                                    @else
                                        <div class="card-img-top d-flex align-items-center justify-content-center"
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

                                    <div class="mb-3">
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
                                                Stock limité ({{ $product->stock_quantity }})
                                            </small>
                                        @endif
                                    </div>

                                    <div class="d-grid gap-2">
                                        <a href="{{ route('client.products.show', $product) }}" class="btn btn-outline-primary btn-sm">
                                            <i class="fas fa-eye me-1"></i>
                                            Voir les détails
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

                <!-- Pagination -->
                <div class="d-flex justify-content-center">
                    {{ $products->appends(request()->query())->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-search fa-4x text-muted mb-3"></i>
                    <h4>Aucun produit trouvé</h4>
                    <p class="text-muted">Essayez de modifier vos critères de recherche ou de supprimer certains filtres.</p>
                    <a href="{{ route('client.products.index') }}" class="btn btn-primary">
                        <i class="fas fa-arrow-left me-1"></i>
                        Voir tous les produits
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>

<style>
.product-card {
    transition: all 0.3s ease;
    border: none;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.product-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 5px 20px rgba(0,0,0,0.15);
}

.search-suggestions {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: white;
    border: 1px solid #ddd;
    border-top: none;
    max-height: 300px;
    overflow-y: auto;
    z-index: 1000;
}

.search-suggestion {
    padding: 10px;
    cursor: pointer;
    border-bottom: 1px solid #eee;
}

.search-suggestion:hover {
    background: #f8f9fa;
}
</style>

<script>
let searchTimeout;

document.addEventListener('DOMContentLoaded', function() {
    updateCartBadge();
});

document.getElementById('search').addEventListener('input', function() {
    clearTimeout(searchTimeout);
    const query = this.value;

    if (query.length >= 2) {
        searchTimeout = setTimeout(() => {
            fetchSearchSuggestions(query);
        }, 300);
    } else {
        document.getElementById('searchSuggestions').classList.add('d-none');
    }
});

function fetchSearchSuggestions(query) {
    fetch(`/client/products/search/ajax?q=${encodeURIComponent(query)}`)
        .then(response => response.json())
        .then(data => {
            const suggestionsContainer = document.getElementById('searchSuggestions');

            if (data.products.length > 0) {
                suggestionsContainer.innerHTML = data.products.map(product => `
                    <div class="search-suggestion" onclick="selectProduct('${product.name}')">
                        <div class="d-flex align-items-center">
                            ${product.image ? `<img src="${product.image}" style="width: 40px; height: 40px; object-fit: cover;" class="me-2 rounded">` : '<div class="me-2" style="width: 40px; height: 40px; background: #f8f9fa; display: flex; align-items: center; justify-content: center; border-radius: 5px;"><i class="fas fa-image text-muted"></i></div>'}
                            <div>
                                <div class="fw-bold">${product.name}</div>
                                <small class="text-muted">${product.cooperative} - ${product.price} MAD</small>
                            </div>
                        </div>
                    </div>
                `).join('');
                suggestionsContainer.classList.remove('d-none');
            } else {
                suggestionsContainer.classList.add('d-none');
            }
        })
        .catch(error => {
            console.error('Search error:', error);
        });
}

function selectProduct(productName) {
    document.getElementById('search').value = productName;
    document.getElementById('searchSuggestions').classList.add('d-none');
    document.getElementById('filtersForm').submit();
}

function updateSort(sort) {
    const url = new URL(window.location);
    url.searchParams.set('sort', sort);
    window.location = url.toString();
}

function updatePerPage(perPage) {
    const url = new URL(window.location);
    url.searchParams.set('per_page', perPage);
    window.location = url.toString();
}

function updateCartBadge() {
    fetch('/client/cart/count')
        .then(response => response.json())
        .then(data => {
            const badge = document.getElementById('cartBadge');
            if (badge) {
                badge.textContent = data.count;
                badge.style.display = data.count > 0 ? 'inline' : 'none';
            }
        })
        .catch(error => console.error('Error updating cart badge:', error));
}

function addToCart(productId, quantity = 1) {
    const button = event.target;
    const originalText = button.innerHTML;

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

function showAlert(message, type) {
    const existingAlerts = document.querySelectorAll('.dynamic-alert');
    existingAlerts.forEach(alert => alert.remove());

    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show position-fixed dynamic-alert`;
    alertDiv.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    alertDiv.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-triangle'} me-2"></i>
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

// Close suggestions when clicking outside
document.addEventListener('click', function(event) {
    if (!event.target.closest('#search') && !event.target.closest('#searchSuggestions')) {
        document.getElementById('searchSuggestions').classList.add('d-none');
    }
});
</script>
@endsection
