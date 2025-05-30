@extends('layouts.app')

@section('title', 'Gestion des Demandes de Produits - Admin')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-box-open me-2"></i>
                        Gestion des Demandes de Produits
                    </h1>
                    <p class="text-muted">Examiner et approuver les demandes de produits des coopératives</p>
                </div>
                <div>
                    <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-primary">
                        <i class="fas fa-arrow-left me-2"></i>
                        Retour au tableau de bord
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Status Tabs and Filters -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <ul class="nav nav-tabs card-header-tabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link {{ $status === 'all' ? 'active' : '' }}"
                               href="{{ route('admin.product-requests.index', ['status' => 'all', 'search' => $search, 'cooperative' => $cooperative]) }}">
                                <i class="fas fa-list me-1"></i>
                                Toutes
                                <span class="badge bg-secondary ms-1">{{ $counts['all'] }}</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ $status === 'pending' ? 'active' : '' }}"
                               href="{{ route('admin.product-requests.index', ['status' => 'pending', 'search' => $search, 'cooperative' => $cooperative]) }}">
                                <i class="fas fa-clock me-1"></i>
                                En Attente
                                <span class="badge bg-warning text-dark ms-1">{{ $counts['pending'] }}</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ $status === 'approved' ? 'active' : '' }}"
                               href="{{ route('admin.product-requests.index', ['status' => 'approved', 'search' => $search, 'cooperative' => $cooperative]) }}">
                                <i class="fas fa-check me-1"></i>
                                Approuvées
                                <span class="badge bg-success ms-1">{{ $counts['approved'] }}</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ $status === 'rejected' ? 'active' : '' }}"
                               href="{{ route('admin.product-requests.index', ['status' => 'rejected', 'search' => $search, 'cooperative' => $cooperative]) }}">
                                <i class="fas fa-times me-1"></i>
                                Rejetées
                                <span class="badge bg-danger ms-1">{{ $counts['rejected'] }}</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ $status === 'needs_info' ? 'active' : '' }}"
                               href="{{ route('admin.product-requests.index', ['status' => 'needs_info', 'search' => $search, 'cooperative' => $cooperative]) }}">
                                <i class="fas fa-question-circle me-1"></i>
                                Info Demandée
                                <span class="badge bg-info ms-1">{{ $counts['needs_info'] }}</span>
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <!-- Filters -->
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <form method="GET" action="{{ route('admin.product-requests.index') }}">
                                <input type="hidden" name="status" value="{{ $status }}">
                                <input type="hidden" name="cooperative" value="{{ $cooperative }}">
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-search"></i>
                                    </span>
                                    <input type="text" class="form-control" name="search"
                                           value="{{ $search }}" placeholder="Rechercher...">
                                    <button class="btn btn-primary" type="submit">Rechercher</button>
                                </div>
                            </form>
                        </div>
                        <div class="col-md-4">
                            <form method="GET" action="{{ route('admin.product-requests.index') }}">
                                <input type="hidden" name="status" value="{{ $status }}">
                                <input type="hidden" name="search" value="{{ $search }}">
                                <select class="form-select" name="cooperative" onchange="this.form.submit()">
                                    <option value="">Toutes les coopératives</option>
                                    @foreach($cooperatives as $coop)
                                        <option value="{{ $coop->id }}" {{ $cooperative == $coop->id ? 'selected' : '' }}>
                                            {{ $coop->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </form>
                        </div>
                        <div class="col-md-4">
                            @if($search || $cooperative)
                                <a href="{{ route('admin.product-requests.index', ['status' => $status]) }}"
                                   class="btn btn-outline-secondary">
                                    <i class="fas fa-times me-1"></i>
                                    Effacer les filtres
                                </a>
                            @endif
                        </div>
                    </div>

                    <!-- Products Grid -->
                    @if($products->count() > 0)
                        <div class="row">
                            @foreach($products as $product)
                                <div class="col-xl-3 col-lg-4 col-md-6 mb-4">
                                    <div class="card product-request-card h-100" onclick="showProductDetails({{ $product->id }})">
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
                                                {{ Str::limit($product->description, 60) }}
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

                                            <div class="product-meta">
                                                <small class="text-muted">
                                                    <i class="fas fa-building me-1"></i>
                                                    {{ $product->cooperative->name }}
                                                </small>
                                                <br>
                                                <small class="text-muted">
                                                    <i class="fas fa-tag me-1"></i>
                                                    {{ $product->category->name }}
                                                </small>
                                                <br>
                                                <small class="text-muted">
                                                    <i class="fas fa-calendar me-1"></i>
                                                    {{ $product->submitted_at ? $product->submitted_at->format('d/m/Y') : 'N/A' }}
                                                </small>
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
                            <i class="fas fa-box-open fa-4x text-muted mb-3"></i>
                            <h4>Aucune demande trouvée</h4>
                            <p class="text-muted">
                                @if($search)
                                    Aucune demande ne correspond à votre recherche "{{ $search }}".
                                @elseif($status !== 'all')
                                    Aucune demande avec le statut "{{ $status }}".
                                @else
                                    Aucune demande de produit pour le moment.
                                @endif
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Product Details Modal -->
<div class="modal fade" id="productDetailsModal" tabindex="-1" aria-labelledby="productDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="productDetailsModalLabel">
                    <i class="fas fa-box me-2"></i>
                    Détails du Produit
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="productDetailsContent">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Chargement...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer" id="productModalActions">
                <!-- Actions will be populated based on product status -->
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
                    <span id="responseModalTitle">Action</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="responseForm">
                    <div class="mb-3">
                        <label for="responseMessage" class="form-label" id="responseMessageLabel">Message</label>
                        <textarea class="form-control" id="responseMessage" name="response_message" rows="4" placeholder="Tapez votre message..."></textarea>
                        <div class="form-text" id="responseMessageHelp">Ce message sera envoyé par email à la coopérative.</div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-primary" id="sendResponseBtn">
                    <span class="spinner-border spinner-border-sm me-2 d-none" role="status"></span>
                    <i class="fas fa-paper-plane me-1"></i>
                    <span id="sendResponseText">Envoyer</span>
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.product-request-card {
    transition: all 0.3s ease;
    border: none;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    cursor: pointer;
}

.product-request-card:hover {
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

.nav-tabs .nav-link {
    border: none;
    color: #6c757d;
}

.nav-tabs .nav-link.active {
    background-color: #f8f9fa;
    border-bottom: 2px solid #007bff;
    color: #007bff;
}

.image-gallery {
    max-height: 400px;
    overflow-y: auto;
}

.gallery-image {
    width: 100%;
    height: 150px;
    object-fit: cover;
    border-radius: 0.375rem;
    margin-bottom: 10px;
    cursor: pointer;
    transition: transform 0.2s;
}

.gallery-image:hover {
    transform: scale(1.05);
}

.primary-image-badge {
    position: absolute;
    top: 5px;
    left: 5px;
    background: rgba(40, 167, 69, 0.9);
    color: white;
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: bold;
}
</style>
@endpush

@push('scripts')
<script>
let currentProductId = null;
let currentAction = null;
let productDetailsModal = null;
let responseModal = null;

document.addEventListener('DOMContentLoaded', function() {
    productDetailsModal = new bootstrap.Modal(document.getElementById('productDetailsModal'));
    responseModal = new bootstrap.Modal(document.getElementById('responseModal'));

    // Response form submission
    document.getElementById('sendResponseBtn').addEventListener('click', function() {
        handleResponseSubmission();
    });
});

function showProductDetails(productId) {
    currentProductId = productId;

    // Show loading
    document.getElementById('productDetailsContent').innerHTML = `
        <div class="text-center py-4">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Chargement...</span>
            </div>
        </div>
    `;

    productDetailsModal.show();

    // Load product details
    fetch(`/admin/product-requests/${productId}`)
        .then(response => response.text())
        .then(html => {
            // The response should contain the full product details HTML
            document.getElementById('productDetailsContent').innerHTML = html;
            setupModalActions(productId);
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('productDetailsContent').innerHTML = `
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Erreur lors du chargement des détails du produit.
                </div>
            `;
        });
}

function setupModalActions(productId) {
    const actionsContainer = document.getElementById('productModalActions');

    // Get product status from the loaded content
    const statusElement = document.querySelector('#productDetailsContent .product-status');
    const status = statusElement ? statusElement.dataset.status : null;

    let actionsHtml = '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>';

    if (status === 'pending' || status === 'needs_info') {
        actionsHtml += `
            <button type="button" class="btn btn-warning me-2" onclick="showResponseModal('request_info', 'Demander des Informations', 'Informations demandées', true)">
                <i class="fas fa-question-circle me-1"></i>
                Demander Info
            </button>
            <button type="button" class="btn btn-danger me-2" onclick="showResponseModal('reject', 'Rejeter le Produit', 'Motif du rejet', true)">
                <i class="fas fa-times me-1"></i>
                Rejeter
            </button>
            <button type="button" class="btn btn-success" onclick="showResponseModal('approve', 'Approuver le Produit', 'Notes d\\'approbation (optionnel)', false)">
                <i class="fas fa-check me-1"></i>
                Approuver
            </button>
        `;
    }

    actionsContainer.innerHTML = actionsHtml;
}

function showResponseModal(action, title, label, required) {
    currentAction = action;

    document.getElementById('responseModalTitle').textContent = title;
    document.getElementById('responseMessageLabel').textContent = label;

    const textarea = document.getElementById('responseMessage');
    textarea.value = '';
    textarea.required = required;

    if (required) {
        document.getElementById('responseMessageHelp').textContent = 'Ce champ est requis.';
    } else {
        document.getElementById('responseMessageHelp').textContent = 'Ce message sera envoyé par email à la coopérative.';
    }

    // Update button text
    const buttonTexts = {
        'approve': 'Approuver',
        'reject': 'Rejeter',
        'request_info': 'Demander'
    };
    document.getElementById('sendResponseText').textContent = buttonTexts[action] || 'Envoyer';

    productDetailsModal.hide();
    responseModal.show();
}

function handleResponseSubmission() {
    const message = document.getElementById('responseMessage').value.trim();

    if ((currentAction === 'reject' || currentAction === 'request_info') && !message) {
        showAlert('Ce champ est requis.', 'danger');
        return;
    }

    const urlMap = {
        'approve': `/admin/product-requests/${currentProductId}/approve`,
        'reject': `/admin/product-requests/${currentProductId}/reject`,
        'request_info': `/admin/product-requests/${currentProductId}/request-info`
    };

    const url = urlMap[currentAction];
    if (!url) return;

    const submitBtn = document.getElementById('sendResponseBtn');
    showLoading(submitBtn);

    const payload = {};
    if (currentAction === 'approve') {
        payload.admin_notes = message;
    } else if (currentAction === 'reject') {
        payload.rejection_reason = message;
    } else if (currentAction === 'request_info') {
        payload.info_requested = message;
    }

    fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        },
        body: JSON.stringify(payload)
    })
    .then(response => response.json())
    .then(data => {
        hideLoading(submitBtn);

        if (data.success) {
            responseModal.hide();
            showAlert(data.message, 'success');
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            showAlert(data.message || 'Erreur lors du traitement', 'danger');
        }
    })
    .catch(error => {
        hideLoading(submitBtn);
        console.error('Error:', error);
        showAlert('Erreur de connexion au serveur', 'danger');
    });
}

function showImageModal(imageUrl) {
    const modal = document.createElement('div');
    modal.className = 'modal fade';
    modal.innerHTML = `
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Image du Produit</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center">
                    <img src="${imageUrl}" class="img-fluid" style="max-height: 70vh;">
                </div>
            </div>
        </div>
    `;

    document.body.appendChild(modal);
    const imageModal = new bootstrap.Modal(modal);
    imageModal.show();

    modal.addEventListener('hidden.bs.modal', function() {
        document.body.removeChild(modal);
    });
}

function showLoading(button) {
    button.disabled = true;
    const spinner = button.querySelector('.spinner-border');
    const icon = button.querySelector('i.fas');

    if (spinner) spinner.classList.remove('d-none');
    if (icon) icon.style.display = 'none';
}

function hideLoading(button) {
    button.disabled = false;
    const spinner = button.querySelector('.spinner-border');
    const icon = button.querySelector('i.fas');

    if (spinner) spinner.classList.add('d-none');
    if (icon) icon.style.display = 'inline';
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
