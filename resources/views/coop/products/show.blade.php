@extends('layouts.app')

@section('title', 'Détails du Produit - Coopérative')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-box me-2"></i>
                        {{ $product->name }}
                    </h1>
                    <p class="text-muted">
                        Détails du produit - {{ Auth::user()->cooperative->name }}
                    </p>
                </div>
                <div>
                    <a href="{{ route('coop.products.index') }}" class="btn btn-outline-primary me-2">
                        <i class="fas fa-arrow-left me-2"></i>
                        Retour aux produits
                    </a>
                    <button type="button" class="btn btn-outline-info" onclick="openImageGallery()">
                        <i class="fas fa-images me-1"></i>
                        Voir Images ({{ $product->images->count() }})
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Status Alert -->
    @if($product->rejection_reason || $product->admin_notes)
        <div class="row mb-4">
            <div class="col-12">
                @if($product->rejection_reason)
                    <div class="alert alert-danger">
                        <h6><i class="fas fa-times-circle me-2"></i>Produit rejeté</h6>
                        <p class="mb-0">{{ $product->rejection_reason }}</p>
                    </div>
                @endif

                @if($product->admin_notes)
                    <div class="alert alert-info">
                        <h6><i class="fas fa-sticky-note me-2"></i>Notes de l'administrateur</h6>
                        <p class="mb-0">{{ $product->admin_notes }}</p>
                    </div>
                @endif
            </div>
        </div>
    @endif

    <!-- Product Details -->
    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8">
            <!-- Product Information -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-info-circle me-2"></i>
                        Informations du Produit
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="info-card">
                                <h6 class="info-title">
                                    <i class="fas fa-box me-2 text-success"></i>
                                    Détails Produit
                                </h6>
                                <div class="info-content">
                                    <div class="info-row">
                                        <span class="info-label">Nom:</span>
                                        <span class="info-value">{{ $product->name }}</span>
                                    </div>
                                    <div class="info-row">
                                        <span class="info-label">Catégorie:</span>
                                        <span class="info-value">{{ $product->category->name }}</span>
                                    </div>
                                    <div class="info-row">
                                        <span class="info-label">Prix:</span>
                                        <span class="info-value fw-bold text-success">{{ number_format($product->price, 2) }} MAD</span>
                                    </div>
                                    <div class="info-row">
                                        <span class="info-label">Stock:</span>
                                        <span class="info-value fw-bold {{ $product->stock_quantity <= 5 ? 'text-danger' : ($product->stock_quantity <= 10 ? 'text-warning' : 'text-success') }}">
                                            {{ $product->stock_quantity }}
                                            @if($product->stock_quantity <= 5)
                                                <i class="fas fa-exclamation-triangle text-danger ms-1"></i>
                                            @endif
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="info-card">
                                <h6 class="info-title">
                                    <i class="fas fa-clock me-2 text-info"></i>
                                    Historique
                                </h6>
                                <div class="info-content">
                                    <div class="info-row">
                                        <span class="info-label">Créé le:</span>
                                        <span class="info-value">{{ $product->created_at->format('d/m/Y H:i') }}</span>
                                    </div>
                                    <div class="info-row">
                                        <span class="info-label">Mis à jour:</span>
                                        <span class="info-value">{{ $product->updated_at->format('d/m/Y H:i') }}</span>
                                    </div>
                                    @if($product->submitted_at)
                                        <div class="info-row">
                                            <span class="info-label">Soumis le:</span>
                                            <span class="info-value">{{ $product->submitted_at->format('d/m/Y H:i') }}</span>
                                        </div>
                                    @endif
                                    @if($product->reviewed_at)
                                        <div class="info-row">
                                            <span class="info-label">Examiné le:</span>
                                            <span class="info-value">{{ $product->reviewed_at->format('d/m/Y H:i') }}</span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Description -->
                    <div class="info-card">
                        <h6 class="info-title">
                            <i class="fas fa-align-left me-2 text-secondary"></i>
                            Description
                        </h6>
                        <div class="info-content">
                            <div class="description-text">
                                {{ $product->description }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Product Images Preview -->
            @if($product->images->count() > 0)
                <div class="card shadow">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-images me-2"></i>
                            Aperçu des Images ({{ $product->images->count() }})
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @foreach($product->images->take(4) as $image)
                                <div class="col-md-3 col-6 mb-3">
                                    <div class="image-preview-item" onclick="openImageGallery({{ $loop->index }})">
                                        <img src="{{ $image->thumbnail_url ?: $image->image_url }}"
                                             alt="Image produit {{ $loop->iteration }}"
                                             class="img-fluid rounded preview-image">
                                        @if($image->is_primary)
                                            <div class="primary-badge">
                                                <i class="fas fa-star"></i>
                                                Principal
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        @if($product->images->count() > 4)
                            <div class="text-center mt-3">
                                <button type="button" class="btn btn-outline-primary" onclick="openImageGallery()">
                                    <i class="fas fa-images me-1"></i>
                                    Voir toutes les images (+{{ $product->images->count() - 4 }} autres)
                                </button>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Product Status -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Statut du Produit
                    </h6>
                </div>
                <div class="card-body text-center">
                    <span class="badge bg-{{ $product->status_badge }} fs-6 mb-3">
                        {{ $product->status_text }}
                    </span>

                    <div class="small text-muted">
                        @if($product->reviewedBy)
                            <p><strong>Examiné par:</strong><br>{{ $product->reviewedBy->full_name }}</p>
                        @endif

                        @if($product->status === 'draft')
                            <p class="text-info">
                                <i class="fas fa-info-circle me-1"></i>
                                Ce produit est en brouillon et peut être modifié librement.
                            </p>
                        @elseif($product->status === 'pending')
                            <p class="text-warning">
                                <i class="fas fa-clock me-1"></i>
                                Votre produit est en cours d'examen par l'administration.
                            </p>
                        @elseif($product->status === 'approved')
                            <p class="text-success">
                                <i class="fas fa-check-circle me-1"></i>
                                Votre produit est approuvé et visible aux clients.
                            </p>
                        @elseif($product->status === 'rejected')
                            <p class="text-danger">
                                <i class="fas fa-times-circle me-1"></i>
                                Votre produit a été rejeté. Consultez les notes ci-dessus.
                            </p>
                        @elseif($product->status === 'needs_info')
                            <p class="text-info">
                                <i class="fas fa-question-circle me-1"></i>
                                L'administration demande des clarifications.
                            </p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-success">
                        <i class="fas fa-cogs me-2"></i>
                        Actions
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        @if($product->canBeEdited())
                            <a href="{{ route('coop.products.edit', $product) }}" class="btn btn-primary">
                                <i class="fas fa-edit me-1"></i>
                                Modifier le Produit
                            </a>
                        @endif

                        @if($product->canBeSubmitted())
                            <button type="button" class="btn btn-success" onclick="submitProduct({{ $product->id }})">
                                <span class="spinner-border spinner-border-sm me-2 d-none" role="status"></span>
                                <i class="fas fa-paper-plane me-1"></i>
                                Soumettre pour Approbation
                            </button>
                        @endif

                        @if($product->isDraft())
                            <button type="button" class="btn btn-danger" onclick="deleteProduct({{ $product->id }}, '{{ addslashes($product->name) }}')">
                                <span class="spinner-border spinner-border-sm me-2 d-none" role="status"></span>
                                <i class="fas fa-trash me-1"></i>
                                Supprimer le Produit
                            </button>
                        @endif
                    </div>

                    @if(!$product->canBeEdited() && !$product->canBeSubmitted() && !$product->isDraft())
                        <div class="alert alert-info mt-3">
                            <small>
                                <i class="fas fa-info-circle me-1"></i>
                                Ce produit ne peut pas être modifié dans son état actuel.
                            </small>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-dark">
                        <i class="fas fa-chart-bar me-2"></i>
                        Statistiques Rapides
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6 border-end">
                            <div class="h5 mb-0 text-primary">{{ $product->images->count() }}</div>
                            <small class="text-muted">Images</small>
                        </div>
                        <div class="col-6">
                            <div class="h5 mb-0 text-success">{{ strlen($product->description) }}</div>
                            <small class="text-muted">Caractères</small>
                        </div>
                    </div>
                    <hr>
                    <div class="row text-center">
                        <div class="col-12">
                            <div class="h6 mb-0 text-info">{{ $product->created_at->diffForHumans() }}</div>
                            <small class="text-muted">Créé</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Image Gallery Modal -->
<div class="modal fade" id="imageGalleryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-images me-2"></i>
                    Images du Produit - {{ $product->name }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0" id="imageGalleryBody">
                <!-- Gallery content will be populated here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>
                    Fermer
                </button>
                <button type="button" class="btn btn-primary" onclick="downloadCurrentImage()">
                    <i class="fas fa-download me-1"></i>
                    Télécharger l'image
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Hidden data for image gallery -->
<script type="application/json" id="productImagesData">
    {
        "images": [
            @foreach($product->images as $index => $image)
                {
                    "url": "{{ $image->image_url }}",
                    "thumbnail": "{{ $image->thumbnail_url ?: $image->image_url }}",
                    "isPrimary": {{ $image->is_primary ? 'true' : 'false' }}
                }{{ $index < $product->images->count() - 1 ? ',' : '' }}
            @endforeach
        ],
        "productName": "{{ addslashes($product->name) }}"
    }
</script>
@endsection

@push('styles')
<style>
/* Info Cards Styling */
.info-card {
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 0.5rem;
    padding: 1.5rem;
    margin-bottom: 1rem;
    height: 100%;
    transition: all 0.3s ease;
}

.info-card:hover {
    border-color: #007bff;
    box-shadow: 0 2px 10px rgba(0, 123, 255, 0.1);
}

.info-title {
    color: #495057;
    font-weight: 600;
    margin-bottom: 1rem;
    padding-bottom: 0.5rem;
    border-bottom: 2px solid #e9ecef;
}

.info-content {
    margin: 0;
}

.info-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.5rem 0;
    border-bottom: 1px solid #e9ecef;
}

.info-row:last-child {
    border-bottom: none;
}

.info-label {
    font-weight: 500;
    color: #6c757d;
    flex: 1;
}

.info-value {
    font-weight: 500;
    color: #495057;
    text-align: right;
    flex: 1;
}

.description-text {
    line-height: 1.6;
    color: #495057;
    background: white;
    padding: 1rem;
    border-radius: 0.375rem;
    border: 1px solid #dee2e6;
    white-space: pre-wrap;
}

/* Image Preview Styling */
.image-preview-item {
    position: relative;
    cursor: pointer;
    transition: all 0.3s ease;
}

.image-preview-item:hover {
    transform: scale(1.05);
}

.preview-image {
    width: 100%;
    height: 120px;
    object-fit: cover;
    border: 2px solid #dee2e6;
    transition: border-color 0.3s ease;
}

.image-preview-item:hover .preview-image {
    border-color: #007bff;
}

.primary-badge {
    position: absolute;
    top: 5px;
    left: 5px;
    background: rgba(255, 193, 7, 0.9);
    color: #212529;
    font-size: 0.7rem;
    padding: 0.2rem 0.5rem;
    border-radius: 0.25rem;
    font-weight: bold;
    display: flex;
    align-items: center;
    gap: 0.2rem;
}

/* Image Gallery Styling */
.image-gallery-container {
    display: flex;
    flex-direction: column;
    height: 70vh;
}

.main-image-container {
    position: relative;
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #000;
    overflow: hidden;
}

.main-gallery-image {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
    transition: all 0.3s ease;
}

.image-navigation {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    width: 100%;
    display: flex;
    justify-content: space-between;
    padding: 0 1rem;
    pointer-events: none;
}

.nav-btn {
    background: rgba(0, 0, 0, 0.7);
    color: white;
    border: none;
    border-radius: 50%;
    width: 50px;
    height: 50px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.3s ease;
    pointer-events: all;
}

.nav-btn:hover {
    background: rgba(0, 0, 0, 0.9);
    transform: scale(1.1);
}

.image-counter {
    position: absolute;
    bottom: 1rem;
    right: 1rem;
    background: rgba(0, 0, 0, 0.7);
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 1rem;
    font-weight: 500;
}

.thumbnail-strip {
    display: flex;
    overflow-x: auto;
    padding: 1rem;
    background: #f8f9fa;
    border-top: 1px solid #dee2e6;
    gap: 0.5rem;
}

.thumbnail-item {
    position: relative;
    flex-shrink: 0;
    width: 80px;
    height: 80px;
    border: 2px solid transparent;
    border-radius: 0.375rem;
    overflow: hidden;
    cursor: pointer;
    transition: all 0.3s ease;
}

.thumbnail-item:hover {
    border-color: #007bff;
    transform: scale(1.05);
}

.thumbnail-item.active {
    border-color: #007bff;
    box-shadow: 0 0 0 2px rgba(0, 123, 255, 0.3);
}

.thumbnail-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

/* Responsive Design */
@media (max-width: 768px) {
    .info-row {
        flex-direction: column;
        align-items: flex-start;
    }

    .info-value {
        text-align: left;
        margin-top: 0.25rem;
    }

    .image-gallery-container {
        height: 50vh;
    }

    .nav-btn {
        width: 40px;
        height: 40px;
    }

    .thumbnail-item {
        width: 60px;
        height: 60px;
    }
}
</style>
@endpush

@push('scripts')
<script>
let imageGalleryModal = null;
let currentImageIndex = 0;
let galleryImages = [];

document.addEventListener('DOMContentLoaded', function() {
    imageGalleryModal = new bootstrap.Modal(document.getElementById('imageGalleryModal'));
});

// Image Gallery Functions
function openImageGallery(startIndex = 0) {
    const imagesDataElement = document.getElementById('productImagesData');
    if (!imagesDataElement) {
        showAlert('Aucune donnée d\'image trouvée', 'danger');
        return;
    }

    const data = JSON.parse(imagesDataElement.textContent);
    galleryImages = data.images;

    if (galleryImages.length === 0) {
        showAlert('Aucune image disponible pour ce produit', 'warning');
        return;
    }

    currentImageIndex = startIndex;
    createImageGallery();
    imageGalleryModal.show();
}

function createImageGallery() {
    const galleryBody = document.getElementById('imageGalleryBody');

    if (galleryImages.length === 0) {
        galleryBody.innerHTML = `
            <div class="text-center py-5">
                <i class="fas fa-image fa-4x text-muted mb-3"></i>
                <h5 class="text-muted">Aucune image disponible</h5>
                <p class="text-muted">Ce produit n'a pas d'images associées.</p>
            </div>
        `;
        return;
    }

    const galleryHtml = `
        <div class="image-gallery-container">
            <!-- Main Image Display -->
            <div class="main-image-container">
                <img id="mainGalleryImage"
                     src="${galleryImages[currentImageIndex].url}"
                     alt="Image principale du produit"
                     class="main-gallery-image">
                <div class="image-navigation">
                    <button type="button" class="nav-btn nav-prev" onclick="previousImage()">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <button type="button" class="nav-btn nav-next" onclick="nextImage()">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
                <div class="image-counter">
                    <span id="currentImageIndex">${currentImageIndex + 1}</span> / ${galleryImages.length}
                </div>
            </div>

            <!-- Thumbnail Strip -->
            <div class="thumbnail-strip">
                ${galleryImages.map((image, index) => `
                    <div class="thumbnail-item ${index === currentImageIndex ? 'active' : ''}"
                         onclick="selectImage(${index}, '${image.url}')"
                         data-index="${index}">
                        <img src="${image.thumbnail}"
                             alt="Miniature ${index + 1}"
                             class="thumbnail-image">
                        ${image.isPrimary ? `
                            <div class="primary-badge">
                                <i class="fas fa-star"></i>
                                Principal
                            </div>
                        ` : ''}
                    </div>
                `).join('')}
            </div>
        </div>
    `;

    galleryBody.innerHTML = galleryHtml;
}

function selectImage(index, imageUrl) {
    currentImageIndex = index;

    // Update main image
    document.getElementById('mainGalleryImage').src = imageUrl;

    // Update counter
    document.getElementById('currentImageIndex').textContent = index + 1;

    // Update active thumbnail
    document.querySelectorAll('.thumbnail-item').forEach((item, i) => {
        item.classList.toggle('active', i === index);
    });
}

function previousImage() {
    if (galleryImages.length === 0) return;

    currentImageIndex = (currentImageIndex - 1 + galleryImages.length) % galleryImages.length;
    selectImage(currentImageIndex, galleryImages[currentImageIndex].url);
}

function nextImage() {
    if (galleryImages.length === 0) return;

    currentImageIndex = (currentImageIndex + 1) % galleryImages.length;
    selectImage(currentImageIndex, galleryImages[currentImageIndex].url);
}

function downloadCurrentImage() {
    if (galleryImages.length === 0) return;

    const link = document.createElement('a');
    link.href = galleryImages[currentImageIndex].url;
    link.download = `product-image-${currentImageIndex + 1}.jpg`;
    link.click();
}

// Product Action Functions
function submitProduct(productId) {
    if (!confirm('Êtes-vous sûr de vouloir soumettre ce produit pour approbation ?')) {
        return;
    }

    const submitBtn = event.target;
    showLoading(submitBtn);

    fetch(`/coop/products/${productId}/submit`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        hideLoading(submitBtn);

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
        hideLoading(submitBtn);
        console.error('Error:', error);
        showAlert('Erreur de connexion au serveur', 'danger');
    });
}

function deleteProduct(productId, productName) {
    if (!confirm(`Êtes-vous sûr de vouloir supprimer le produit "${productName}" ?`)) {
        return;
    }

    const deleteBtn = event.target;
    showLoading(deleteBtn);

    fetch(`/coop/products/${productId}`, {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        hideLoading(deleteBtn);

        if (data.success) {
            showAlert(data.message, 'success');
            setTimeout(() => {
                window.location.href = '{{ route("coop.products.index") }}';
            }, 1500);
        } else {
            showAlert(data.message, 'danger');
        }
    })
    .catch(error => {
        hideLoading(deleteBtn);
        console.error('Error:', error);
        showAlert('Erreur de connexion au serveur', 'danger');
    });
}

// Utility Functions
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

// Keyboard navigation for image gallery
document.addEventListener('keydown', function(e) {
    const modal = document.getElementById('imageGalleryModal');
    if (modal.classList.contains('show')) {
        if (e.key === 'ArrowLeft') {
            previousImage();
        } else if (e.key === 'ArrowRight') {
            nextImage();
        } else if (e.key === 'Escape') {
            imageGalleryModal.hide();
        }
    }
});
</script>
@endpush
