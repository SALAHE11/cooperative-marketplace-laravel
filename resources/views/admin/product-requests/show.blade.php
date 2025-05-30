<!-- This is the content loaded via AJAX for the product details modal -->
<div class="product-details-container">
    <!-- Product Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <h4 class="text-primary mb-2">{{ $product->name }}</h4>
                    <div class="product-status d-none" data-status="{{ $product->status }}"></div>
                    <span class="badge bg-{{ $product->status_badge }} fs-6">
                        {{ $product->status_text }}
                    </span>
                </div>
                <div class="text-end">
                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="openImageGallery({{ $product->id }})">
                        <i class="fas fa-images me-1"></i>
                        Voir Images ({{ $product->images->count() }})
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Product Information Grid -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="info-card">
                <h6 class="info-title">
                    <i class="fas fa-building me-2 text-primary"></i>
                    Informations Coopérative
                </h6>
                <div class="info-content">
                    <div class="info-row">
                        <span class="info-label">Coopérative:</span>
                        <span class="info-value">{{ $product->cooperative->name }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Secteur:</span>
                        <span class="info-value">{{ $product->cooperative->sector_of_activity }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Email:</span>
                        <span class="info-value">{{ $product->cooperative->email }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="info-card">
                <h6 class="info-title">
                    <i class="fas fa-box me-2 text-success"></i>
                    Détails du Produit
                </h6>
                <div class="info-content">
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
                        <span class="info-value fw-bold">{{ $product->stock_quantity }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Status Information -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="info-card">
                <h6 class="info-title">
                    <i class="fas fa-clock me-2 text-info"></i>
                    Historique du Produit
                </h6>
                <div class="info-content">
                    <div class="info-row">
                        <span class="info-label">Soumis le:</span>
                        <span class="info-value">{{ $product->submitted_at ? $product->submitted_at->format('d/m/Y H:i') : 'N/A' }}</span>
                    </div>
                    @if($product->reviewed_at)
                        <div class="info-row">
                            <span class="info-label">Examiné le:</span>
                            <span class="info-value">{{ $product->reviewed_at->format('d/m/Y H:i') }}</span>
                        </div>
                    @endif
                    @if($product->reviewedBy)
                        <div class="info-row">
                            <span class="info-label">Examiné par:</span>
                            <span class="info-value">{{ $product->reviewedBy->full_name }}</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="info-card">
                <h6 class="info-title">
                    <i class="fas fa-chart-bar me-2 text-warning"></i>
                    Statistiques
                </h6>
                <div class="info-content">
                    <div class="info-row">
                        <span class="info-label">Images:</span>
                        <span class="info-value">{{ $product->images->count() }} image(s)</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Taille du produit:</span>
                        <span class="info-value">{{ strlen($product->description) }} caractères</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Créé le:</span>
                        <span class="info-value">{{ $product->created_at->format('d/m/Y H:i') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Description -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="info-card">
                <h6 class="info-title">
                    <i class="fas fa-align-left me-2 text-secondary"></i>
                    Description du Produit
                </h6>
                <div class="info-content">
                    <div class="description-text">
                        {{ $product->description }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Admin Notes / Rejection Reason -->
    @if($product->rejection_reason || $product->admin_notes)
        <div class="row mb-4">
            <div class="col-12">
                @if($product->rejection_reason)
                    <div class="alert alert-danger">
                        <h6><i class="fas fa-times-circle me-2"></i>Raison du rejet</h6>
                        <p class="mb-0">{{ $product->rejection_reason }}</p>
                    </div>
                @endif

                @if($product->admin_notes)
                    <div class="alert alert-info">
                        <h6><i class="fas fa-sticky-note me-2"></i>Notes administrateur</h6>
                        <p class="mb-0">{{ $product->admin_notes }}</p>
                    </div>
                @endif
            </div>
        </div>
    @endif
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
            <div class="modal-body p-0">
                @if($product->images->count() > 0)
                    <div class="image-gallery-container">
                        <!-- Main Image Display -->
                        <div class="main-image-container">
                            <img id="mainGalleryImage"
                                 src="{{ $product->images->first()->image_url }}"
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
                                <span id="currentImageIndex">1</span> / {{ $product->images->count() }}
                            </div>
                        </div>

                        <!-- Thumbnail Strip -->
                        <div class="thumbnail-strip">
                            @foreach($product->images as $index => $image)
                                <div class="thumbnail-item {{ $index === 0 ? 'active' : '' }}"
                                     onclick="selectImage({{ $index }}, '{{ $image->image_url }}')"
                                     data-index="{{ $index }}">
                                    <img src="{{ $image->thumbnail_url ?: $image->image_url }}"
                                         alt="Miniature {{ $index + 1 }}"
                                         class="thumbnail-image">
                                    @if($image->is_primary)
                                        <div class="primary-badge">
                                            <i class="fas fa-star"></i>
                                            Principal
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @else
                    <div class="no-images-container">
                        <div class="text-center py-5">
                            <i class="fas fa-image fa-4x text-muted mb-3"></i>
                            <h5 class="text-muted">Aucune image disponible</h5>
                            <p class="text-muted">Ce produit n'a pas d'images associées.</p>
                        </div>
                    </div>
                @endif
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

<style>
/* Product Details Styling */
.product-details-container {
    padding: 1rem;
}

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

.primary-badge {
    position: absolute;
    top: 2px;
    left: 2px;
    background: rgba(255, 193, 7, 0.9);
    color: #212529;
    font-size: 0.65rem;
    padding: 0.1rem 0.3rem;
    border-radius: 0.2rem;
    font-weight: bold;
    display: flex;
    align-items: center;
    gap: 0.2rem;
}

.no-images-container {
    height: 400px;
    display: flex;
    align-items: center;
    justify-content: center;
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

<script>
let currentImageIndex = 0;
let totalImages = {{ $product->images->count() }};
let images = [
    @foreach($product->images as $index => $image)
        {
            url: '{{ $image->image_url }}',
            thumbnail: '{{ $image->thumbnail_url ?: $image->image_url }}',
            isPrimary: {{ $image->is_primary ? 'true' : 'false' }}
        }{{ $index < $product->images->count() - 1 ? ',' : '' }}
    @endforeach
];

function openImageGallery(productId) {
    const galleryModal = new bootstrap.Modal(document.getElementById('imageGalleryModal'));
    galleryModal.show();
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
    if (totalImages === 0) return;

    currentImageIndex = (currentImageIndex - 1 + totalImages) % totalImages;
    selectImage(currentImageIndex, images[currentImageIndex].url);
}

function nextImage() {
    if (totalImages === 0) return;

    currentImageIndex = (currentImageIndex + 1) % totalImages;
    selectImage(currentImageIndex, images[currentImageIndex].url);
}

function downloadCurrentImage() {
    if (totalImages === 0) return;

    const link = document.createElement('a');
    link.href = images[currentImageIndex].url;
    link.download = `product-image-${currentImageIndex + 1}.jpg`;
    link.click();
}

// Keyboard navigation
document.addEventListener('keydown', function(e) {
    const modal = document.getElementById('imageGalleryModal');
    if (modal.classList.contains('show')) {
        if (e.key === 'ArrowLeft') {
            previousImage();
        } else if (e.key === 'ArrowRight') {
            nextImage();
        } else if (e.key === 'Escape') {
            bootstrap.Modal.getInstance(modal).hide();
        }
    }
});
</script>
