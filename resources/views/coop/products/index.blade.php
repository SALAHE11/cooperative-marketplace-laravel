@extends('layouts.app')

@section('title', 'Mes Produits - Coopérative')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-box me-2"></i>
                        Mes Produits
                    </h1>
                    <p class="text-muted">Gérer les produits de {{ Auth::user()->cooperative->name }}</p>
                </div>
                <div>
                    <a href="{{ route('coop.dashboard') }}" class="btn btn-outline-primary me-2">
                        <i class="fas fa-arrow-left me-2"></i>
                        Retour au tableau de bord
                    </a>
                    <a href="{{ route('coop.products.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>
                        Ajouter un Produit
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Status Tabs -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <ul class="nav nav-tabs card-header-tabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link {{ $status === 'all' ? 'active' : '' }}"
                               href="{{ route('coop.products.index', ['status' => 'all', 'search' => $search]) }}">
                                <i class="fas fa-list me-1"></i>
                                Tous
                                <span class="badge bg-secondary ms-1">{{ $counts['all'] }}</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ $status === 'draft' ? 'active' : '' }}"
                               href="{{ route('coop.products.index', ['status' => 'draft', 'search' => $search]) }}">
                                <i class="fas fa-edit me-1"></i>
                                Brouillons
                                <span class="badge bg-secondary ms-1">{{ $counts['draft'] }}</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ $status === 'pending' ? 'active' : '' }}"
                               href="{{ route('coop.products.index', ['status' => 'pending', 'search' => $search]) }}">
                                <i class="fas fa-clock me-1"></i>
                                En Attente
                                <span class="badge bg-warning text-dark ms-1">{{ $counts['pending'] }}</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ $status === 'approved' ? 'active' : '' }}"
                               href="{{ route('coop.products.index', ['status' => 'approved', 'search' => $search]) }}">
                                <i class="fas fa-check me-1"></i>
                                Approuvés
                                <span class="badge bg-success ms-1">{{ $counts['approved'] }}</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ $status === 'rejected' ? 'active' : '' }}"
                               href="{{ route('coop.products.index', ['status' => 'rejected', 'search' => $search]) }}">
                                <i class="fas fa-times me-1"></i>
                                Rejetés
                                <span class="badge bg-danger ms-1">{{ $counts['rejected'] }}</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ $status === 'needs_info' ? 'active' : '' }}"
                               href="{{ route('coop.products.index', ['status' => 'needs_info', 'search' => $search]) }}">
                                <i class="fas fa-question-circle me-1"></i>
                                Info Demandée
                                <span class="badge bg-info ms-1">{{ $counts['needs_info'] }}</span>
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <!-- Search Bar -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <form method="GET" action="{{ route('coop.products.index') }}">
                                <input type="hidden" name="status" value="{{ $status }}">
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-search"></i>
                                    </span>
                                    <input type="text" class="form-control" name="search"
                                           value="{{ $search }}" placeholder="Rechercher un produit...">
                                    <button class="btn btn-primary" type="submit">Rechercher</button>
                                    @if($search)
                                        <a href="{{ route('coop.products.index', ['status' => $status]) }}"
                                           class="btn btn-outline-secondary">
                                            <i class="fas fa-times"></i>
                                        </a>
                                    @endif
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Products Grid -->
                    @if($products->count() > 0)
                        <div class="row">
                            @foreach($products as $product)
                                <div class="col-xl-3 col-lg-4 col-md-6 mb-4">
                                    <div class="card product-card h-100">
                                        <!-- Product Image -->
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

                                            <!-- Status Badge -->
                                            <div class="product-status-badge">
                                                <span class="badge bg-{{ $product->status_badge }}">
                                                    {{ $product->status_text }}
                                                </span>
                                            </div>
                                        </div>

                                        <div class="card-body d-flex flex-column">
                                            <h6 class="card-title">{{ $product->name }}</h6>
                                            <p class="card-text text-muted small flex-grow-1">
                                                {{ Str::limit($product->description, 80) }}
                                            </p>

                                            <div class="product-info mb-3">
                                                <div class="row">
                                                    <div class="col-6">
                                                        <small class="text-muted">Prix</small>
                                                        <div class="fw-bold">{{ number_format($product->price, 2) }} MAD</div>
                                                    </div>
                                                    <div class="col-6">
                                                        <small class="text-muted">Stock</small>
                                                        <div class="fw-bold">{{ $product->stock_quantity }}</div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="product-meta mb-3">
                                                <small class="text-muted">
                                                    <i class="fas fa-tag me-1"></i>
                                                    {{ $product->category->name }}
                                                </small>
                                                <br>
                                                <small class="text-muted">
                                                    <i class="fas fa-calendar me-1"></i>
                                                    {{ $product->updated_at->format('d/m/Y') }}
                                                </small>
                                            </div>

                                            <!-- Action Buttons -->
                                            <div class="product-actions mt-auto">
                                                <a href="{{ route('coop.products.show', $product) }}"
                                                   class="btn btn-outline-primary btn-sm">
                                                    <i class="fas fa-eye me-1"></i>
                                                    Détails
                                                </a>

                                                @if($product->canBeEdited())
                                                    <a href="{{ route('coop.products.edit', $product) }}"
                                                       class="btn btn-primary btn-sm">
                                                        <i class="fas fa-edit me-1"></i>
                                                        Modifier
                                                    </a>
                                                @endif

                                                @if($product->canBeSubmitted())
                                                    <button class="btn btn-success btn-sm"
                                                            onclick="submitProduct({{ $product->id }})">
                                                        <i class="fas fa-paper-plane me-1"></i>
                                                        Soumettre
                                                    </button>
                                                @endif

                                                @if($product->isDraft())
                                                    <button class="btn btn-danger btn-sm"
                                                            onclick="deleteProduct({{ $product->id }}, '{{ addslashes($product->name) }}')">
                                                        <i class="fas fa-trash me-1"></i>
                                                        Supprimer
                                                    </button>
                                                @endif

                                                @if($product->rejection_reason || $product->admin_notes)
                                                    <button class="btn btn-info btn-sm"
                                                            onclick="showProductNotes({{ $product->id }}, '{{ addslashes($product->name) }}',
                                                                                   '{{ addslashes($product->rejection_reason ?? '') }}',
                                                                                   '{{ addslashes($product->admin_notes ?? '') }}')">
                                                        <i class="fas fa-comment me-1"></i>
                                                        Notes
                                                    </button>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Pagination -->
                        <div class="d-flex justify-content-center mt-4">
                            {{ $products->appends(request()->input())->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-box fa-4x text-muted mb-3"></i>
                            <h4>Aucun produit trouvé</h4>
                            <p class="text-muted">
                                @if($search)
                                    Aucun produit ne correspond à votre recherche "{{ $search }}".
                                @elseif($status !== 'all')
                                    Aucun produit avec le statut "{{ $status }}".
                                @else
                                    Vous n'avez pas encore ajouté de produits.
                                @endif
                            </p>
                            @if(!$search && $status === 'all')
                                <a href="{{ route('coop.products.create') }}" class="btn btn-primary">
                                    <i class="fas fa-plus me-2"></i>
                                    Ajouter votre premier produit
                                </a>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Product Notes Modal -->
<div class="modal fade" id="productNotesModal" tabindex="-1" aria-labelledby="productNotesModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="productNotesModalLabel">
                    <i class="fas fa-comment me-2"></i>
                    Notes du Produit
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="productNotesContent">
                <!-- Content will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
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

.product-image-container {
    position: relative;
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

.product-status-badge {
    position: absolute;
    top: 10px;
    right: 10px;
}

.product-actions .btn {
    margin-right: 5px;
    margin-bottom: 5px;
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
</style>
@endpush

@push('scripts')
<script>
function submitProduct(productId) {
    if (!confirm('Êtes-vous sûr de vouloir soumettre ce produit pour approbation ?')) {
        return;
    }

    fetch(`/coop/products/${productId}/submit`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert(data.message, 'success');
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            showAlert(data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('Erreur de connexion au serveur', 'error');
    });
}

function deleteProduct(productId, productName) {
    if (!confirm(`Êtes-vous sûr de vouloir supprimer le produit "${productName}" ?`)) {
        return;
    }

    fetch(`/coop/products/${productId}`, {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert(data.message, 'success');
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            showAlert(data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('Erreur de connexion au serveur', 'error');
    });
}

function showProductNotes(productId, productName, rejectionReason, adminNotes) {
    document.getElementById('productNotesModalLabel').innerHTML = `
        <i class="fas fa-comment me-2"></i>
        Notes - ${productName}
    `;

    let content = '';

    if (rejectionReason) {
        content += `
            <div class="alert alert-danger">
                <h6><i class="fas fa-times-circle me-2"></i>Raison du rejet:</h6>
                <p class="mb-0">${rejectionReason}</p>
            </div>
        `;
    }

    if (adminNotes) {
        content += `
            <div class="alert alert-info">
                <h6><i class="fas fa-sticky-note me-2"></i>Notes de l'administrateur:</h6>
                <p class="mb-0">${adminNotes}</p>
            </div>
        `;
    }

    if (!content) {
        content = '<p class="text-muted">Aucune note disponible.</p>';
    }

    document.getElementById('productNotesContent').innerHTML = content;

    const modal = new bootstrap.Modal(document.getElementById('productNotesModal'));
    modal.show();
}

function showAlert(message, type) {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type === 'error' ? 'danger' : 'success'} alert-dismissible fade show position-fixed`;
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
