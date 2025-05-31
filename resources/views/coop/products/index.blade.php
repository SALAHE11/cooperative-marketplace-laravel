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
                    <div class="btn-group me-2">
                        <button type="button" class="btn btn-outline-warning" onclick="openBulkStockAlertModal()">
                            <i class="fas fa-bell me-1"></i>
                            Configurer Alertes
                        </button>
                        <button type="button" class="btn btn-outline-warning dropdown-toggle dropdown-toggle-split"
                                data-bs-toggle="dropdown" aria-expanded="false">
                            <span class="visually-hidden">Toggle Dropdown</span>
                        </button>
                        <ul class="dropdown-menu">
                            <li>
                                <a class="dropdown-item" href="#" onclick="showLowStockOnly()">
                                    <i class="fas fa-exclamation-triangle text-warning me-2"></i>
                                    Voir Stock Faible Uniquement
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="#" onclick="showOutOfStockOnly()">
                                    <i class="fas fa-times-circle text-danger me-2"></i>
                                    Voir Ruptures de Stock
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item" href="#" onclick="exportStockReport()">
                                    <i class="fas fa-download text-info me-2"></i>
                                    Exporter Rapport Stock
                                </a>
                            </li>
                        </ul>
                    </div>
                    <a href="{{ route('coop.products.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>
                        Ajouter un Produit
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Stock Alert Summary -->
    @if(Auth::user()->cooperative && Auth::user()->cooperative->status === 'approved')
        @php
            $lowStockCount = Auth::user()->cooperative->products()
                ->where('status', 'approved')
                ->whereRaw('stock_quantity <= stock_alert_threshold')
                ->count();
            $outOfStockCount = Auth::user()->cooperative->products()
                ->where('status', 'approved')
                ->where('stock_quantity', 0)
                ->count();
        @endphp

        @if($lowStockCount > 0 || $outOfStockCount > 0)
            <div class="row mb-4">
                <div class="col-12">
                    <div class="alert alert-{{ $outOfStockCount > 0 ? 'danger' : 'warning' }} alert-dismissible fade show">
                        <h6>
                            <i class="fas fa-{{ $outOfStockCount > 0 ? 'times-circle' : 'exclamation-triangle' }} me-2"></i>
                            Alertes Stock Actives
                        </h6>
                        <div class="row">
                            @if($outOfStockCount > 0)
                                <div class="col-md-6">
                                    <strong>{{ $outOfStockCount }}</strong> produit(s) en rupture de stock
                                    <br><small>Ces produits ne sont plus visibles aux clients</small>
                                </div>
                            @endif
                            @if($lowStockCount > $outOfStockCount)
                                <div class="col-md-6">
                                    <strong>{{ $lowStockCount - $outOfStockCount }}</strong> produit(s) en stock faible
                                    <br><small>Réapprovisionnement recommandé</small>
                                </div>
                            @endif
                        </div>
                        <div class="mt-2">
                            <button type="button" class="btn btn-sm btn-outline-{{ $outOfStockCount > 0 ? 'danger' : 'warning' }}"
                                    onclick="filterByStockStatus('low')">
                                Voir les produits concernés
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-info ms-2"
                                    onclick="openBulkStockAlertModal()">
                                Configurer les seuils
                            </button>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                </div>
            </div>
        @endif
    @endif

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
                    <!-- Search and Filter Bar -->
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
                        <div class="col-md-6">
                            <div class="d-flex gap-2">
                                <select class="form-select" id="stockFilter" onchange="applyStockFilter()">
                                    <option value="all">Tous les niveaux de stock</option>
                                    <option value="normal">Stock normal</option>
                                    <option value="low">Stock faible</option>
                                    <option value="out">Rupture de stock</option>
                                </select>
                                <select class="form-select" id="sortBy" onchange="applySorting()">
                                    <option value="updated_at">Tri par date de modification</option>
                                    <option value="name">Tri par nom</option>
                                    <option value="price">Tri par prix</option>
                                    <option value="stock_quantity">Tri par stock</option>
                                    <option value="stock_alert">Tri par alerte stock</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Products Grid -->
                    @if($products->count() > 0)
                        <div class="row" id="productsContainer">
                            @foreach($products as $product)
                                <div class="col-xl-3 col-lg-4 col-md-6 mb-4 product-card-container"
                                     data-stock-status="{{ $product->stock_status }}"
                                     data-name="{{ strtolower($product->name) }}"
                                     data-price="{{ $product->price }}"
                                     data-stock="{{ $product->stock_quantity }}"
                                     data-alert="{{ $product->stock_alert_threshold }}">
                                    <div class="card product-card h-100 {{ $product->isStockLow() ? 'border-' . $product->stock_status_badge : '' }}">
                                        <!-- Product Image -->
                                        <div class="product-image-container">
                                            @if($product->primary_thumbnail_url)
                                                <img src="{{ $product->primary_thumbnail_url }}"
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
                                                @if($product->isUpdatedVersion())
                                                    <span class="badge bg-warning text-dark ms-1">
                                                        <i class="fas fa-sync-alt"></i>
                                                        Mis à jour
                                                    </span>
                                                @endif
                                            </div>

                                            <!-- Stock Alert Badge -->
                                            @if($product->isStockLow())
                                                <div class="stock-alert-badge">
                                                    <span class="badge bg-{{ $product->stock_status_badge }}">
                                                        <i class="fas fa-{{ $product->isOutOfStock() ? 'times-circle' : 'exclamation-triangle' }}"></i>
                                                        {{ $product->stock_status_text }}
                                                    </span>
                                                </div>
                                            @endif

                                            <!-- Image Count Badge -->
                                            @if($product->images_count > 0)
                                                <div class="product-image-count">
                                                    <span class="badge bg-dark">
                                                        <i class="fas fa-images me-1"></i>
                                                        {{ $product->images_count }}
                                                    </span>
                                                </div>
                                            @endif
                                        </div>

                                        <div class="card-body d-flex flex-column">
                                            <h6 class="card-title">
                                                {{ $product->name }}
                                                @if($product->isStockLow())
                                                    <i class="fas fa-exclamation-triangle text-{{ $product->stock_status_badge }} ms-1"
                                                       title="{{ $product->stock_status_text }}"></i>
                                                @endif
                                            </h6>
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
                                                        <div class="fw-bold text-{{ $product->stock_status_badge }}">
                                                            {{ $product->stock_quantity }}
                                                            @if($product->isStockLow())
                                                                <i class="fas fa-exclamation-triangle ms-1"></i>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row mt-2">
                                                    <div class="col-12">
                                                        <small class="text-muted">Seuil d'alerte: {{ $product->stock_alert_threshold }}</small>
                                                        @if($product->isStockLow())
                                                            <div class="progress mt-1" style="height: 4px;">
                                                                @php
                                                                    $percentage = $product->stock_alert_threshold > 0 ?
                                                                        min(100, ($product->stock_quantity / $product->stock_alert_threshold) * 100) : 100;
                                                                @endphp
                                                                <div class="progress-bar bg-{{ $product->stock_status_badge }}"
                                                                     style="width: {{ max(5, $percentage) }}%"></div>
                                                            </div>
                                                        @endif
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
                                                <div class="btn-group w-100 mb-2" role="group">
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
                                                </div>

                                                <div class="btn-group w-100 mb-2" role="group">
                                                    @if($product->canBeSubmitted())
                                                        <button class="btn btn-success btn-sm"
                                                                onclick="submitProduct({{ $product->id }})">
                                                            <i class="fas fa-paper-plane me-1"></i>
                                                            Soumettre
                                                        </button>
                                                    @endif

                                                    @if($product->isStockLow())
                                                        <a href="{{ route('coop.products.edit', $product) }}"
                                                           class="btn btn-warning btn-sm">
                                                            <i class="fas fa-warehouse me-1"></i>
                                                            Réapprovisionner
                                                        </a>
                                                    @endif

                                                    <button class="btn btn-outline-warning btn-sm"
                                                            onclick="openStockAlertModal({{ $product->id }}, '{{ addslashes($product->name) }}', {{ $product->stock_alert_threshold }})">
                                                        <i class="fas fa-bell me-1"></i>
                                                        Alerte
                                                    </button>
                                                </div>

                                                <div class="btn-group w-100" role="group">
                                                    <button class="btn btn-danger btn-sm"
                                                            onclick="deleteProduct({{ $product->id }}, '{{ addslashes($product->name) }}', '{{ $product->status }}')">
                                                        <i class="fas fa-trash me-1"></i>
                                                        Supprimer
                                                    </button>
                                                </div>

                                                @if($product->rejection_reason || $product->admin_notes)
                                                    <button class="btn btn-info btn-sm w-100 mt-2"
                                                            onclick="showProductNotes({{ $product->id }}, '{{ addslashes($product->name) }}',
                                                                                   '{{ addslashes($product->rejection_reason ?? '') }}',
                                                                                   '{{ addslashes($product->admin_notes ?? '') }}')">
                                                        <i class="fas fa-comment me-1"></i>
                                                        Voir les Notes
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

.stock-alert-badge {
    position: absolute;
    top: 10px;
    left: 10px;
}

.product-image-count {
    position: absolute;
    bottom: 10px;
    left: 10px;
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

/* Stock alert styling */
.border-warning {
    border-color: #ffc107 !important;
    border-width: 2px !important;
}

.border-danger {
    border-color: #dc3545 !important;
    border-width: 2px !important;
}

.progress {
    background-color: #e9ecef;
}

.progress-bar {
    transition: width 0.3s ease;
}

/* Filter highlight */
.product-card-container.filtered-out {
    display: none !important;
}

.product-card.stock-alert-highlight {
    animation: pulse-alert 2s infinite;
}

@keyframes pulse-alert {
    0% { box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
    50% { box-shadow: 0 5px 20px rgba(255, 193, 7, 0.3); }
    100% { box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
}
</style>
@endpush

@push('scripts')
<script>
// Global variables
let stockAlertModal, bulkStockAlertModal;

document.addEventListener('DOMContentLoaded', function() {
    // Initialize modals
    stockAlertModal = new bootstrap.Modal(document.getElementById('stockAlertModal'));
    bulkStockAlertModal = new bootstrap.Modal(document.getElementById('bulkStockAlertModal'));
});

// Stock Filter Functions
function applyStockFilter() {
    const filter = document.getElementById('stockFilter').value;
    const containers = document.querySelectorAll('.product-card-container');

    containers.forEach(container => {
        const stockStatus = container.dataset.stockStatus;
        let show = true;

        switch(filter) {
            case 'normal':
                show = stockStatus === 'normal';
                break;
            case 'low':
                show = stockStatus === 'low_stock';
                break;
            case 'out':
                show = stockStatus === 'out_of_stock';
                break;
            default:
                show = true;
        }

        if (show) {
            container.classList.remove('filtered-out');
        } else {
            container.classList.add('filtered-out');
        }
    });

    updateVisibleCount();
}

function applySorting() {
    const sortBy = document.getElementById('sortBy').value;
    const container = document.getElementById('productsContainer');
    const items = Array.from(container.children);

    items.sort((a, b) => {
        const aVal = getSortValue(a, sortBy);
        const bVal = getSortValue(b, sortBy);

        if (sortBy === 'name') {
            return aVal.localeCompare(bVal);
        }

        return parseFloat(bVal) - parseFloat(aVal); // Descending for numbers
    });

    items.forEach(item => container.appendChild(item));
}

function getSortValue(element, sortBy) {
    switch(sortBy) {
        case 'name':
            return element.dataset.name;
        case 'price':
            return element.dataset.price;
        case 'stock_quantity':
            return element.dataset.stock;
        case 'stock_alert':
            return element.dataset.alert;
        default:
            return 0;
    }
}

function showLowStockOnly() {
    document.getElementById('stockFilter').value = 'low';
    applyStockFilter();

    // Highlight the filtered products
    document.querySelectorAll('.product-card').forEach(card => {
        card.classList.remove('stock-alert-highlight');
    });

    setTimeout(() => {
        document.querySelectorAll('.product-card-container:not(.filtered-out) .product-card').forEach(card => {
            card.classList.add('stock-alert-highlight');
        });
    }, 100);
}

function showOutOfStockOnly() {
    document.getElementById('stockFilter').value = 'out';
    applyStockFilter();
}

function filterByStockStatus(status) {
    if (status === 'low') {
        showLowStockOnly();
    } else if (status === 'out') {
        showOutOfStockOnly();
    }
}

function updateVisibleCount() {
    const total = document.querySelectorAll('.product-card-container').length;
    const visible = document.querySelectorAll('.product-card-container:not(.filtered-out)').length;

    if (total !== visible) {
        showAlert(`${visible} produit(s) affiché(s) sur ${total}`, 'info');
    }
}

// Stock Alert Modal Functions
function openStockAlertModal(productId, productName, currentThreshold) {
    document.getElementById('productId').value = productId;
    document.getElementById('productName').value = productName;
    document.getElementById('stockAlertThreshold').value = currentThreshold;

    stockAlertModal.show();
}

function openBulkStockAlertModal() {
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

// Export Functions
function exportStockReport() {
    // This would implement CSV/Excel export functionality
    showAlert('Fonctionnalité d\'export en développement', 'info');
}

// Product Action Functions
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
            showAlert(data.message, 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('Erreur de connexion au serveur', 'danger');
    });
}

function deleteProduct(productId, productName, productStatus) {
    let confirmMessage = '';
    let warningMessage = '';

    switch(productStatus) {
        case 'draft':
            confirmMessage = `Êtes-vous sûr de vouloir supprimer le brouillon "${productName}" ?`;
            break;
        case 'pending':
            confirmMessage = `Êtes-vous sûr de vouloir supprimer le produit "${productName}" en attente d'approbation ?`;
            warningMessage = 'Ce produit est en cours d\'examen par l\'administration.';
            break;
        case 'approved':
            confirmMessage = `Êtes-vous sûr de vouloir supprimer le produit approuvé "${productName}" ?`;
            warningMessage = 'ATTENTION: Ce produit est approuvé et visible aux clients!';
            break;
        case 'rejected':
            confirmMessage = `Êtes-vous sûr de vouloir supprimer le produit rejeté "${productName}" ?`;
            break;
        case 'needs_info':
            confirmMessage = `Êtes-vous sûr de vouloir supprimer le produit "${productName}" ?`;
            warningMessage = 'L\'administration attend des clarifications sur ce produit.';
            break;
        default:
            confirmMessage = `Êtes-vous sûr de vouloir supprimer le produit "${productName}" ?`;
    }

    if (warningMessage) {
        confirmMessage = warningMessage + '\n\n' + confirmMessage;
    }

    if (!confirm(confirmMessage)) {
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
            showAlert(data.message, 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('Erreur de connexion au serveur', 'danger');
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
