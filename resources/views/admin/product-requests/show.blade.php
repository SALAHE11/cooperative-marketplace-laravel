<!-- This is the content loaded via AJAX for the product details modal -->
<div class="product-details-container">
    <!-- Product Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <h4 class="text-primary mb-2">
                        {{ $product->name }}
                        @if($product->isUpdatedVersion())
                            <span class="badge bg-warning text-dark ms-2">
                                <i class="fas fa-sync-alt me-1"></i>
                                Produit Mis à Jour
                            </span>
                        @endif
                    </h4>
                    <div class="product-status d-none" data-status="{{ $product->status }}"></div>
                    <span class="badge bg-{{ $product->status_badge }} fs-6">
                        {{ $product->status_text }}
                    </span>
                </div>
                <div class="text-end">
                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="openImageGallery()">
                        <i class="fas fa-images me-1"></i>
                        Voir Images ({{ $product->images->count() }})
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- NEW: Comparison Section for Updated Products -->
    @if($product->isUpdatedVersion() && $product->original_data)
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-warning">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0">
                            <i class="fas fa-balance-scale me-2"></i>
                            Comparaison: Ancienne vs Nouvelle Version
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <!-- Original Version -->
                            <div class="col-md-6">
                                <h6 class="text-success mb-3">
                                    <i class="fas fa-check-circle me-1"></i>
                                    Version Précédente (Approuvée)
                                </h6>
                                <div class="comparison-section bg-light p-3 rounded">
                                    <div class="comparison-row">
                                        <strong>Nom:</strong> {{ $product->original_data['name'] ?? 'N/A' }}
                                    </div>
                                    <div class="comparison-row">
                                        <strong>Catégorie:</strong> {{ $product->original_data['category_name'] ?? 'N/A' }}
                                    </div>
                                    <div class="comparison-row">
                                        <strong>Prix:</strong> {{ number_format($product->original_data['price'] ?? 0, 2) }} MAD
                                    </div>
                                    <div class="comparison-row">
                                        <strong>Stock:</strong> {{ $product->original_data['stock_quantity'] ?? 'N/A' }}
                                    </div>
                                    <div class="comparison-row">
                                        <strong>Images:</strong> {{ $product->original_data['images_count'] ?? 0 }} image(s)
                                    </div>
                                    <div class="comparison-row">
                                        <strong>Description:</strong>
                                        <div class="bg-white p-2 mt-1 rounded border">
                                            {{ Str::limit($product->original_data['description'] ?? 'N/A', 200) }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Current Version -->
                            <div class="col-md-6">
                                <h6 class="text-warning mb-3">
                                    <i class="fas fa-sync-alt me-1"></i>
                                    Version Actuelle (En Attente)
                                </h6>
                                <div class="comparison-section bg-light p-3 rounded">
                                    <div class="comparison-row {{ ($product->original_data['name'] ?? '') !== $product->name ? 'bg-warning-subtle' : '' }}">
                                        <strong>Nom:</strong> {{ $product->name }}
                                        @if(($product->original_data['name'] ?? '') !== $product->name)
                                            <i class="fas fa-edit text-warning ms-1" title="Modifié"></i>
                                        @endif
                                    </div>
                                    <div class="comparison-row {{ ($product->original_data['category_name'] ?? '') !== $product->category->name ? 'bg-warning-subtle' : '' }}">
                                        <strong>Catégorie:</strong> {{ $product->category->name }}
                                        @if(($product->original_data['category_name'] ?? '') !== $product->category->name)
                                            <i class="fas fa-edit text-warning ms-1" title="Modifié"></i>
                                        @endif
                                    </div>
                                    <div class="comparison-row {{ ($product->original_data['price'] ?? 0) != $product->price ? 'bg-warning-subtle' : '' }}">
                                        <strong>Prix:</strong> {{ number_format($product->price, 2) }} MAD
                                        @if(($product->original_data['price'] ?? 0) != $product->price)
                                            <i class="fas fa-edit text-warning ms-1" title="Modifié"></i>
                                        @endif
                                    </div>
                                    <div class="comparison-row {{ ($product->original_data['stock_quantity'] ?? 0) != $product->stock_quantity ? 'bg-warning-subtle' : '' }}">
                                        <strong>Stock:</strong> {{ $product->stock_quantity }}
                                        @if(($product->original_data['stock_quantity'] ?? 0) != $product->stock_quantity)
                                            <i class="fas fa-edit text-warning ms-1" title="Modifié"></i>
                                        @endif
                                    </div>
                                    <div class="comparison-row {{ ($product->original_data['images_count'] ?? 0) != $product->images->count() ? 'bg-warning-subtle' : '' }}">
                                        <strong>Images:</strong> {{ $product->images->count() }} image(s)
                                        @if(($product->original_data['images_count'] ?? 0) != $product->images->count())
                                            <i class="fas fa-edit text-warning ms-1" title="Modifié"></i>
                                        @endif
                                    </div>
                                    <div class="comparison-row {{ ($product->original_data['description'] ?? '') !== $product->description ? 'bg-warning-subtle' : '' }}">
                                        <strong>Description:</strong>
                                        @if(($product->original_data['description'] ?? '') !== $product->description)
                                            <i class="fas fa-edit text-warning ms-1" title="Modifié"></i>
                                        @endif
                                        <div class="bg-white p-2 mt-1 rounded border">
                                            {{ Str::limit($product->description, 200) }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-3 text-center">
                            <small class="text-muted">
                                <i class="fas fa-info-circle me-1"></i>
                                Les champs modifiés sont surlignés en jaune
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

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
                    @if($product->isUpdatedVersion() && $product->original_data)
                        <div class="info-row">
                            <span class="info-label">Version précédente:</span>
                            <span class="info-value">{{ \Carbon\Carbon::parse($product->original_data['updated_at'])->format('d/m/Y H:i') }}</span>
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

/* NEW: Comparison Styling */
.comparison-section {
    border: 1px solid #dee2e6;
}

.comparison-row {
    padding: 0.5rem;
    margin-bottom: 0.5rem;
    border-radius: 0.25rem;
}

.comparison-row:last-child {
    margin-bottom: 0;
}

.bg-warning-subtle {
    background-color: rgba(255, 193, 7, 0.15) !important;
    border: 1px solid rgba(255, 193, 7, 0.3);
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
}
</style>
