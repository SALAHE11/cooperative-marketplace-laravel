@extends('layouts.app')

@section('title', 'Mon Panier - Coopérative E-commerce')

@section('content')
<div class="container py-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">
                    <i class="fas fa-shopping-cart me-2"></i>
                    Mon Panier
                </h1>
                <div class="d-flex gap-2">
                    <a href="{{ route('client.dashboard') }}" class="btn btn-outline-primary">
                        <i class="fas fa-home me-1"></i>
                        Accueil
                    </a>
                    <a href="{{ route('client.products.index') }}" class="btn btn-primary">
                        <i class="fas fa-search me-1"></i>
                        Continuer mes achats
                    </a>
                </div>
            </div>
        </div>
    </div>

    @if($cart && !$cart->isEmpty())
        <div class="row">
            <div class="col-lg-8">
                @foreach($itemsByCooperative as $cooperativeId => $items)
                    @php $cooperative = $items->first()->cooperative; @endphp
                    <div class="card mb-4 shadow">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">
                                <i class="fas fa-building me-2"></i>
                                {{ $cooperative->name }}
                            </h5>
                        </div>
                        <div class="card-body">
                            @foreach($items as $item)
                                <div class="row align-items-center border-bottom py-3" data-product-id="{{ $item->product_id }}">
                                    <div class="col-auto">
                                        @if($item->getProductImage())
                                            <img src="{{ $item->getProductImage() }}" alt="{{ $item->getProductName() }}"
                                                 class="rounded" style="width: 80px; height: 80px; object-fit: cover;">
                                        @else
                                            <div class="bg-light rounded d-flex align-items-center justify-content-center"
                                                 style="width: 80px; height: 80px;">
                                                <i class="fas fa-image text-muted"></i>
                                            </div>
                                        @endif
                                    </div>

                                    <div class="col">
                                        <h6 class="mb-1">{{ $item->getProductName() }}</h6>
                                        <p class="text-muted small mb-2">
                                            {{ $item->product ? Str::limit($item->product->description, 100) : '' }}
                                        </p>
                                        <div class="text-success fw-bold">{{ number_format($item->unit_price, 2) }} MAD</div>

                                        @if(!$item->isAvailable())
                                            <div class="alert alert-warning alert-sm mt-2 py-1">
                                                <i class="fas fa-exclamation-triangle me-1"></i>
                                                Ce produit n'est plus disponible
                                            </div>
                                        @elseif($item->product && $item->product->stock_quantity < $item->quantity)
                                            <div class="alert alert-warning alert-sm mt-2 py-1">
                                                <i class="fas fa-exclamation-triangle me-1"></i>
                                                Stock insuffisant ({{ $item->product->stock_quantity }} disponible(s))
                                            </div>
                                        @endif
                                    </div>

                                    <div class="col-auto">
                                        <div class="d-flex align-items-center border rounded">
                                            <button class="btn btn-sm" type="button"
                                                    onclick="updateQuantity({{ $item->product_id }}, {{ $item->quantity - 1 }})">
                                                <i class="fas fa-minus"></i>
                                            </button>
                                            <input type="number" class="form-control text-center border-0"
                                                   value="{{ $item->quantity }}" min="1" max="100"
                                                   style="width: 60px;"
                                                   onchange="updateQuantity({{ $item->product_id }}, this.value)">
                                            <button class="btn btn-sm" type="button"
                                                    onclick="updateQuantity({{ $item->product_id }}, {{ $item->quantity + 1 }})">
                                                <i class="fas fa-plus"></i>
                                            </button>
                                        </div>
                                    </div>

                                    <div class="col-auto">
                                        <div class="fw-bold text-success">{{ number_format($item->subtotal, 2) }} MAD</div>
                                    </div>

                                    <div class="col-auto">
                                        <button class="btn btn-outline-danger btn-sm" onclick="removeItem({{ $item->product_id }})">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            @endforeach

                            <div class="text-end mt-3 pt-3 border-top">
                                <h6>
                                    Sous-total {{ $cooperative->name }}:
                                    <span class="text-success">{{ number_format($items->sum('subtotal'), 2) }} MAD</span>
                                </h6>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="col-lg-4">
                <div class="card shadow">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-calculator me-2"></i>
                            Résumé de la commande
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Articles ({{ $cart->total_items }})</span>
                            <span>{{ number_format($cart->total_amount, 2) }} MAD</span>
                        </div>

                        @if($itemsByCooperative->count() > 1)
                            <hr>
                            <h6 class="text-muted">Détail par coopérative:</h6>
                            @foreach($itemsByCooperative as $cooperativeId => $items)
                                @php $cooperative = $items->first()->cooperative; @endphp
                                <div class="d-flex justify-content-between mb-1">
                                    <small>{{ $cooperative->name }}</small>
                                    <small>{{ number_format($items->sum('subtotal'), 2) }} MAD</small>
                                </div>
                            @endforeach
                        @endif

                        <hr>
                        <div class="d-flex justify-content-between mb-3">
                            <strong>Total</strong>
                            <strong class="text-success">{{ number_format($cart->total_amount, 2) }} MAD</strong>
                        </div>

                        <div class="d-grid gap-2">
                            <a href="{{ route('client.checkout.show') }}" class="btn btn-success btn-lg">
                                <i class="fas fa-credit-card me-1"></i>
                                Procéder au paiement
                            </a>
                            <button class="btn btn-outline-danger" onclick="clearCart()">
                                <i class="fas fa-trash me-1"></i>
                                Vider le panier
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Info Card -->
                <div class="card mt-3 shadow">
                    <div class="card-body">
                        <h6 class="card-title">
                            <i class="fas fa-info-circle me-2 text-info"></i>
                            Informations importantes
                        </h6>
                        <ul class="list-unstyled small mb-0">
                            <li class="mb-2">
                                <i class="fas fa-check text-success me-1"></i>
                                Paiement sécurisé simulé
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-store text-primary me-1"></i>
                                Retrait en coopérative uniquement
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-receipt text-warning me-1"></i>
                                Reçu client généré automatiquement
                            </li>
                            <li>
                                <i class="fas fa-user-friends text-info me-1"></i>
                                Possibilité de déléguer le retrait
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="text-center py-5">
            <i class="fas fa-shopping-cart fa-4x text-muted mb-3"></i>
            <h4>Votre panier est vide</h4>
            <p class="text-muted mb-4">Découvrez nos produits et ajoutez-les à votre panier.</p>
            <div class="d-flex gap-2 justify-content-center">
                <a href="{{ route('client.products.index') }}" class="btn btn-primary">
                    <i class="fas fa-search me-1"></i>
                    Parcourir les produits
                </a>
                <a href="{{ route('client.dashboard') }}" class="btn btn-outline-primary">
                    <i class="fas fa-home me-1"></i>
                    Retour à l'accueil
                </a>
            </div>
        </div>
    @endif
</div>

<script>
function updateQuantity(productId, quantity) {
    if (quantity < 0) return;

    fetch('/client/cart/update', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            product_id: productId,
            quantity: quantity
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (quantity === 0) {
                document.querySelector(`[data-product-id="${productId}"]`).remove();
            }
            location.reload(); // Reload to update totals
        } else {
            showAlert(data.message, 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('Erreur lors de la mise à jour', 'danger');
    });
}

function removeItem(productId) {
    if (!confirm('Êtes-vous sûr de vouloir retirer cet article du panier?')) {
        return;
    }

    fetch('/client/cart/remove', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            product_id: productId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            showAlert(data.message, 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('Erreur lors de la suppression', 'danger');
    });
}

function clearCart() {
    if (!confirm('Êtes-vous sûr de vouloir vider complètement votre panier?')) {
        return;
    }

    fetch('/client/cart/clear', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            showAlert(data.message, 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('Erreur lors du vidage du panier', 'danger');
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
@endsection
