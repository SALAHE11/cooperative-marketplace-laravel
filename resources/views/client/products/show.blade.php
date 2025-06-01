@extends('layouts.app')

@section('title', $product->name . ' - Coopérative E-commerce')

@section('content')
<div class="container py-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('client.dashboard') }}">Accueil</a></li>
            <li class="breadcrumb-item"><a href="{{ route('client.products.index') }}">Produits</a></li>
            <li class="breadcrumb-item"><a href="{{ route('client.products.index', ['category_id' => $product->category_id]) }}">{{ $product->category->name }}</a></li>
            <li class="breadcrumb-item active">{{ $product->name }}</li>
        </ol>
    </nav>

    <div class="row">
        <!-- Product Images -->
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-body">
                    @if($product->images->count() > 0)
                        <!-- Main Image -->
                        <div class="mb-3 text-center">
                            <img id="mainImage" src="{{ $product->primaryImageUrl }}"
                                 class="img-fluid product-image" alt="{{ $product->name }}"
                                 style="max-height: 400px; object-fit: cover; border-radius: 10px;">
                        </div>

                        <!-- Thumbnails -->
                        @if($product->images->count() > 1)
                            <div class="d-flex justify-content-center gap-2 flex-wrap">
                                @foreach($product->images as $index => $image)
                                    <img src="{{ $image->thumbnail_url ?: $image->image_url }}"
                                         class="product-thumbnail {{ $index === 0 ? 'active' : '' }}"
                                         alt="{{ $product->name }}"
                                         onclick="changeMainImage('{{ $image->image_url }}', this)"
                                         style="width: 80px; height: 80px; object-fit: cover; border-radius: 5px; cursor: pointer; border: 2px solid {{ $index === 0 ? '#007bff' : 'transparent' }}; transition: all 0.3s ease;">
                                @endforeach
                            </div>
                        @endif
                    @else
                        <div class="text-center py-5 bg-light rounded">
                            <i class="fas fa-image fa-4x text-muted mb-3"></i>
                            <p class="text-muted">Aucune image disponible</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Product Info -->
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-body">
                    <h1 class="h3 mb-3">{{ $product->name }}</h1>

                    <!-- Price -->
                    <div class="mb-3">
                        <span class="h4 text-success fw-bold">{{ number_format($product->price, 2) }} MAD</span>
                    </div>

                    <!-- Cooperative Info -->
                    <div class="mb-3">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-building text-primary me-2"></i>
                            <div>
                                <strong>
                                    <a href="{{ route('client.products.index', ['cooperative_id' => $product->cooperative->id]) }}"
                                       class="text-decoration-none">{{ $product->cooperative->name }}</a>
                                </strong>
                                <br>
                                <small class="text-muted">{{ $product->cooperative->sector_of_activity }}</small>
                            </div>
                        </div>
                    </div>

                    <!-- Category -->
                    <div class="mb-3">
                        <a href="{{ route('client.products.index', ['category_id' => $product->category->id]) }}"
                           class="badge bg-secondary text-decoration-none">
                            <i class="fas fa-tag me-1"></i>
                            {{ $product->category->name }}
                        </a>
                    </div>

                    <!-- Stock Status -->
                    <div class="mb-3">
                        @if($product->stock_quantity > 0)
                            @if($product->stock_quantity <= $product->stock_alert_threshold)
                                <div class="alert alert-warning py-2">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    <strong>Stock limité:</strong> Plus que {{ $product->stock_quantity }} en stock
                                </div>
                            @else
                                <div class="alert alert-success py-2">
                                    <i class="fas fa-check-circle me-2"></i>
                                    <strong>En stock</strong> ({{ $product->stock_quantity }} disponibles)
                                </div>
                            @endif
                        @else
                            <div class="alert alert-danger py-2">
                                <i class="fas fa-times-circle me-2"></i>
                                <strong>Rupture de stock</strong>
                            </div>
                        @endif
                    </div>

                    <!-- Description -->
                    <div class="mb-4">
                        <h6>Description</h6>
                        <p class="text-muted">{{ $product->description }}</p>
                    </div>

                    <!-- Add to Cart Form -->
                    @if($product->stock_quantity > 0)
                        <div class="mb-3">
                            <div class="row align-items-center">
                                <div class="col-auto">
                                    <label for="quantity" class="form-label">Quantité:</label>
                                </div>
                                <div class="col-auto">
                                    <input type="number" class="form-control"
                                           id="quantity" name="quantity" value="1"
                                           min="1" max="{{ $product->stock_quantity }}"
                                           style="max-width: 120px;">
                                </div>
                                <div class="col">
                                    <button type="button" class="btn btn-success btn-lg w-100"
                                            onclick="addToCartWithQuantity({{ $product->id }})">
                                        <i class="fas fa-cart-plus me-2"></i>
                                        Ajouter au Panier
                                    </button>
                                </div>
                            </div>
                        </div>
                    @else
                        <button class="btn btn-secondary btn-lg w-100" disabled>
                            <i class="fas fa-times me-2"></i>
                            Produit non disponible
                        </button>
                    @endif

                    <!-- Quick Actions -->
                    <div class="d-grid gap-2 d-md-flex">
                        <a href="{{ route('client.products.index', ['cooperative_id' => $product->cooperative_id]) }}"
                           class="btn btn-outline-primary">
                            <i class="fas fa-building me-1"></i>
                            Autres produits de cette coopérative
                        </a>
                        <a href="{{ route('client.products.index', ['category_id' => $product->category_id]) }}"
                           class="btn btn-outline-secondary">
                            <i class="fas fa-tag me-1"></i>
                            Produits similaires
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Related Products -->
    @if($relatedProducts->count() > 0)
        <div class="row mt-5">
            <div class="col-12">
                <h4 class="mb-4">Produits similaires</h4>
                <div class="row">
                    @foreach($relatedProducts as $relatedProduct)
                        <div class="col-lg-3 col-md-6 mb-4">
                            <div class="card related-product-card h-100">
                                <div class="position-relative">
                                    @if($relatedProduct->primaryImageUrl)
                                        <img src="{{ $relatedProduct->primaryImageUrl }}"
                                             class="card-img-top" style="height: 200px; object-fit: cover;"
                                             alt="{{ $relatedProduct->name }}">
                                    @else
                                        <div class="card-img-top d-flex align-items-center justify-content-center bg-light"
                                             style="height: 200px;">
                                            <i class="fas fa-image fa-3x text-muted"></i>
                                        </div>
                                    @endif
                                    <div class="position-absolute top-0 end-0 m-2">
                                        <span class="badge bg-success">{{ number_format($relatedProduct->price, 2) }} MAD</span>
                                    </div>
                                </div>
                                <div class="card-body d-flex flex-column">
                                    <h6 class="card-title">{{ $relatedProduct->name }}</h6>
                                    <p class="card-text text-muted small flex-grow-1">
                                        {{ Str::limit($relatedProduct->description, 60) }}
                                    </p>
                                    <div class="mt-auto">
                                        <small class="text-muted d-block mb-2">
                                            <i class="fas fa-building me-1"></i>
                                            {{ $relatedProduct->cooperative->name }}
                                        </small>
                                        <div class="d-grid gap-1">
                                            <a href="{{ route('client.products.show', $relatedProduct) }}"
                                               class="btn btn-outline-primary btn-sm">
                                                <i class="fas fa-eye me-1"></i>
                                                Voir
                                            </a>
                                            @if($relatedProduct->stock_quantity > 0)
                                                <button class="btn btn-success btn-sm"
                                                        onclick="addToCart({{ $relatedProduct->id }}, 1)">
                                                    <i class="fas fa-cart-plus me-1"></i>
                                                    Ajouter
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
</div>

<style>
.related-product-card {
    transition: all 0.3s ease;
    border: none;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.related-product-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.15);
}

.product-thumbnail:hover {
    border-color: #007bff !important;
}
</style>

<script>
function changeMainImage(src, thumbnail) {
    document.getElementById('mainImage').src = src;

    // Update active thumbnail
    document.querySelectorAll('.product-thumbnail').forEach(thumb => {
        thumb.style.borderColor = 'transparent';
    });
    thumbnail.style.borderColor = '#007bff';
}

function addToCartWithQuantity(productId) {
    const quantity = document.getElementById('quantity').value;
    addToCart(productId, quantity);
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
        body: JSON.stringify({ quantity: parseInt(quantity) })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert(data.message, 'success');

            // Success animation
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
</script>
@endsection
