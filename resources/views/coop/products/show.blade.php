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
                        @if($product->isStockLow())
                            <i class="fas fa-exclamation-triangle text-{{ $product->stock_status_badge }} ms-2"
                               title="{{ $product->stock_status_text }}"></i>
                        @endif
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
                    @if($product->hasImages())
                        <button type="button" class="btn btn-outline-info" onclick="openImageGallery()">
                            <i class="fas fa-images me-1"></i>
                            Voir Images ({{ $product->images_count }})
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Stock Alert Status -->
    @if($product->isStockLow())
        <div class="row mb-4">
            <div class="col-12">
                <div class="alert alert-{{ $product->stock_status_badge }}">
                    <h6>
                        <i class="fas fa-{{ $product->isOutOfStock() ? 'times-circle' : 'exclamation-triangle' }} me-2"></i>
                        {{ $product->stock_status_text }}
                    </h6>
                    <p class="mb-0">
                        <strong>Stock actuel:</strong> {{ $product->stock_quantity }} unités •
                        <strong>Seuil d'alerte:</strong> {{ $product->stock_alert_threshold }} unités
                        @if($product->isOutOfStock())
                            • Ce produit est en rupture de stock et n'est plus visible aux clients!
                        @else
                            • Il est recommandé de réapprovisionner ce produit.
                        @endif
                    </p>
                </div>
            </div>
        </div>
    @endif

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
                                        <span class="info-value fw-bold text-{{ $product->stock_status_badge }}">
                                            {{ $product->stock_quantity }}
                                            @if($product->isStockLow())
                                                <i class="fas fa-exclamation-triangle ms-1"></i>
                                            @endif
                                        </span>
                                    </div>
                                    <div class="info-row">
                                        <span class="info-label">Seuil d'alerte:</span>
                                        <span class="info-value fw-bold">
                                            {{ $product->stock_alert_threshold }}
                                            <small class="text-muted">(alerte si ≤)</small>
                                        </span>
                                    </div>
                                    <div class="info-row">
                                        <span class="info-label">Images:</span>
                                        <span class="info-value fw-bold">{{ $product->images_count }}</span>
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

            <!-- Enhanced Product Images Preview -->
            @if($product->hasImages())
                <div class="card shadow">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-images me-2"></i>
                            Images du Produit ({{ $product->images_count }})
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @foreach($product->images->take(6) as $image)
                                <div class="col-md-4 col-lg-3 mb-3">
                                    <div class="image-preview-item {{ $product->primary_image_id === $image->id ? 'primary-image' : '' }}"
                                         onclick="openImageGallery({{ $loop->index }})">
                                        <img src="{{ $image->thumbnail_url ?: $image->image_url }}"
                                             alt="Image produit {{ $loop->iteration }}"
                                             class="img-fluid rounded preview-image">
                                        @if($product->primary_image_id === $image->id)
                                            <div class="primary-badge">
                                                <i class="fas fa-star"></i>
                                                Principal
                                            </div>
                                        @endif
                                        @if($image->processing_status !== 'ready')
                                            <div class="processing-badge">
                                                @if($image->processing_status === 'processing')
                                                    <i class="fas fa-spinner fa-spin"></i>
                                                    Traitement...
                                                @elseif($image->processing_status === 'failed')
                                                    <i class="fas fa-exclamation-triangle"></i>
                                                    Erreur
                                                @else
                                                    <i class="fas fa-clock"></i>
                                                    En attente
                                                @endif
                                            </div>
                                        @endif
                                        <div class="image-info">
                                            <small>{{ $image->formatted_file_size }} • {{ $image->dimensions }}</small>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        @if($product->images_count > 6)
                            <div class="text-center mt-3">
                                <button type="button" class="btn btn-outline-primary" onclick="openImageGallery()">
                                    <i class="fas fa-images me-1"></i>
                                    Voir toutes les images (+{{ $product->images_count - 6 }} autres)
                                </button>
                            </div>
                        @endif
                    </div>
                </div>
            @else
                <div class="card shadow">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-image fa-4x text-muted mb-3"></i>
                        <h5 class="text-muted">Aucune image</h5>
                        <p class="text-muted">Ce produit n'a pas d'images associées.</p>
                        @if($product->canBeEdited())
                            <a href="{{ route('coop.products.edit', $product) }}" class="btn btn-primary">
                                <i class="fas fa-plus me-1"></i>
                                Ajouter des images
                            </a>
                        @endif
                    </div>
                </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Stock Status Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-{{ $product->stock_status_badge }}">
                        <i class="fas fa-{{ $product->isOutOfStock() ? 'times-circle' : ($product->isStockLow() ? 'exclamation-triangle' : 'check-circle') }} me-2"></i>
                        État du Stock
                    </h6>
                </div>
                <div class="card-body text-center">
                    <div class="row mb-3">
                        <div class="col-6 border-end">
                            <div class="h4 mb-0 text-{{ $product->stock_status_badge }}">{{ $product->stock_quantity }}</div>
                            <small class="text-muted">En Stock</small>
                        </div>
                        <div class="col-6">
                            <div class="h5 mb-0 text-warning">{{ $product->stock_alert_threshold }}</div>
                            <small class="text-muted">Seuil d'Alerte</small>
                        </div>
                    </div>

                    <div class="progress mb-3" style="height: 15px;">
                        @php
                            $maxStock = max($product->stock_quantity, $product->stock_alert_threshold * 2);
                            $percentage = $maxStock > 0 ? ($product->stock_quantity / $maxStock) * 100 : 0;
                            $percentage = min(100, max(5, $percentage)); // Minimum 5% for visibility
                        @endphp
                        <div class="progress-bar bg-{{ $product->stock_status_badge }}"
                             style="width: {{ $percentage }}%">
                            {{ $product->stock_quantity }}
                        </div>
                    </div>

                    <span class="badge bg-{{ $product->stock_status_badge }} fs-6 mb-3">
                        {{ $product->stock_status_text }}
                    </span>

                    @if($product->isStockLow())
                        <div class="alert alert-{{ $product->stock_status_badge }} py-2">
                            <small>
                                <i class="fas fa-{{ $product->isOutOfStock() ? 'times-circle' : 'exclamation-triangle' }} me-1"></i>
                                @if($product->isOutOfStock())
                                    Produit indisponible pour les clients
                                @else
                                    Réapprovisionnement recommandé
                                @endif
                            </small>
                        </div>
                    @endif

                    <button type="button" class="btn btn-outline-warning btn-sm" onclick="openStockAlertModal()">
                        <i class="fas fa-cog me-1"></i>
                        Configurer Seuil
                    </button>
                </div>
            </div>

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

                    @if($product->isUpdatedVersion())
                        <div class="alert alert-warning mb-3">
                            <i class="fas fa-sync-alt me-1"></i>
                            <strong>Version mise à jour</strong><br>
                            <small>Ce produit a été modifié après approbation</small>
                        </div>
                    @endif

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

                        @if($product->isStockLow())
                            <a href="{{ route('coop.products.edit', $product) }}" class="btn btn-warning">
                                <i class="fas fa-warehouse me-1"></i>
                                Réapprovisionner
                            </a>
                        @endif

                        <button type="button" class="btn btn-outline-warning" onclick="openStockAlertModal()">
                            <i class="fas fa-bell me-1"></i>
                            Configurer Alerte Stock
                        </button>

                        <button type="button" class="btn btn-danger" onclick="deleteProduct({{ $product->id }}, '{{ addslashes($product->name) }}', '{{ $product->status }}')">
                            <span class="spinner-border spinner-border-sm me-2 d-none" role="status"></span>
                            <i class="fas fa-trash me-1"></i>
                            Supprimer le Produit
                        </button>
                    </div>

                    @if(!$product->canBeEdited() && !$product->canBeSubmitted())
                        <div class="alert alert-info mt-3">
                            <small>
                                <i class="fas fa-info-circle me-1"></i>
                                Certaines actions peuvent être limitées selon le statut du produit.
                            </small>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Enhanced Quick Stats -->
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
                            <div class="h5 mb-0 text-primary">{{ $product->images_count }}</div>
                            <small class="text-muted">Images</small>
                        </div>
                        <div class="col-6">
                            <div class="h5 mb-0 text-success">{{ strlen($product->description) }}</div>
                            <small class="text-muted">Caractères</small>
                        </div>
                    </div>
                    <hr>
                    <div class="row text-center">
                        <div class="col-6 border-end">
                            <div class="h6 mb-0 text-info">{{ $product->created_at->diffForHumans() }}</div>
                            <small class="text-muted">Créé</small>
                        </div>
                        <div class="col-6">
                            <div class="h6 mb-0 text-warning">{{ $product->updated_at->diffForHumans() }}</div>
                            <small class="text-muted">Modifié</small>
                        </div>
                    </div>
                    @if($product->hasImages())
                        <hr>
                        <div class="row text-center">
                            <div class="col-12">
                                @php
                                    $readyImages = $product->images()->where('processing_status', 'ready')->count();
                                @endphp
                                <div class="h6 mb-0 text-{{ $readyImages === $product->images_count ? 'success' : 'warning' }}">
                                    {{ $readyImages }}/{{ $product->images_count }}
                                </div>
                                <small class="text-muted">Images prêtes</small>
                            </div>
                        </div>
                    @endif

                    <!-- Stock Alert Progress -->
                    <hr>
                    <div class="text-center">
                        <div class="small text-muted mb-1">Niveau de Stock</div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-{{ $product->stock_status_badge }}"
                                 style="width: {{ min(100, max(5, ($product->stock_quantity / max($product->stock_alert_threshold * 3, $product->stock_quantity)) * 100)) }}%"></div>
                        </div>
                        <small class="text-{{ $product->stock_status_badge }}">{{ $product->stock_status_text }}</small>
                    </div>
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
                <div class="alert alert-info">
                    <strong>Stock actuel:</strong> {{ $product->stock_quantity }} unités<br>
                    <strong>Seuil actuel:</strong> {{ $product->stock_alert_threshold }} unités
                </div>

                <form id="stockAlertForm">
                    <div class="mb-3">
                        <label for="stockAlertThreshold" class="form-label">Nouveau Seuil d'Alerte</label>
                        <div class="input-group">
                            <input type="number" class="form-control" id="stockAlertThreshold"
                                   min="0" max="1000" value="{{ $product->stock_alert_threshold }}" required>
                            <span class="input-group-text">unités</span>
                        </div>
                        <div class="form-text">
                            Vous serez alerté quand le stock descend à ce niveau ou en dessous.
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Suggestions</label>
                        <div class="d-grid gap-2">
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="setThreshold(5)">
                                5 unités (Standard)
                            </button>
                            <button type="button" class="btn btn-outline-warning btn-sm" onclick="setThreshold(10)">
                                10 unités (Produits populaires)
                            </button>
                            @if($product->stock_quantity > 10)
                                <button type="button" class="btn btn-outline-info btn-sm" onclick="setThreshold({{ Math.floor($product->stock_quantity * 0.1) }})">
                                    {{ Math.floor($product->stock_quantity * 0.1) }} unités (10% du stock actuel)
                                </button>
                            @endif
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

<!-- Enhanced Image Gallery Modal -->
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
                    "isPrimary": {{ $product->primary_image_id === $image->id ? 'true' : 'false' }},
                    "processingStatus": "{{ $image->processing_status }}",
                    "fileSize": "{{ $image->formatted_file_size }}",
                    "dimensions": "{{ $image->dimensions }}"
                }{{ $index < $product->images->count() - 1 ? ',' : '' }}
            @endforeach
        ],
        "productName": "{{ addslashes($product->name) }}"
    }
</script>
@endsection

@push('styles')
<style>
/* Enhanced info cards and image styling */
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

/* Enhanced image preview styling */
.image-preview-item {
    position: relative;
    cursor: pointer;
    transition: all 0.3s ease;
    border-radius: 0.375rem;
    overflow: hidden;
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

.primary-image .preview-image {
    border-color: #ffc107;
    border-width: 3px;
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

.processing-badge {
    position: absolute;
    top: 5px;
    right: 5px;
    background: rgba(0, 0, 0, 0.8);
    color: white;
    font-size: 0.7rem;
    padding: 0.2rem 0.5rem;
    border-radius: 0.25rem;
    font-weight: bold;
}

.image-info {
    position: absolute;
    bottom: 5px;
    left: 5px;
    right: 5px;
    background: rgba(0, 0, 0, 0.7);
    color: white;
    padding: 0.2rem 0.5rem;
    border-radius: 0.25rem;
    font-size: 0.7rem;
    text-align: center;
}

/* Enhanced image gallery styling */
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

.progress {
    background-color: #e9ecef;
}

.progress-bar {
    transition: width 0.3s ease;
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
let stockAlertModal = null;
let currentImageIndex = 0;
let galleryImages = [];

document.addEventListener('DOMContentLoaded', function() {
    imageGalleryModal = new bootstrap.Modal(document.getElementById('imageGalleryModal'));
    stockAlertModal = new bootstrap.Modal(document.getElementById('stockAlertModal'));
});

// Stock Alert Configuration Functions
function openStockAlertModal() {
    stockAlertModal.show();
}

function setThreshold(value) {
    document.getElementById('stockAlertThreshold').value = value;
}

function saveStockAlert() {
    const threshold = document.getElementById('stockAlertThreshold').value;

    if (!threshold || threshold < 0 || threshold > 1000) {
        showAlert('Seuil d\'alerte invalide (0-1000)', 'danger');
        return;
    }

    fetch(`{{ route('coop.products.configure-stock-alert', $product) }}`, {
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
            // Reload page to update display
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

// Enhanced Image Gallery Functions
function openImageGallery(startIndex = 0) {
    const imagesDataElement = document.getElementById('productImagesData');
    if (!imagesDataElement) {
        showAlert('Aucune donnée d\'image trouvée', 'danger');
        return;
    }

    const data = JSON.parse(imagesDataElement.textContent);
    galleryImages = data.images.filter(img => img.processingStatus === 'ready');

    if (galleryImages.length === 0) {
        showAlert('Aucune image prête disponible pour ce produit', 'warning');
        return;
    }

    currentImageIndex = Math.min(startIndex, galleryImages.length - 1);
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
                <p class="text-muted">Ce produit n'a pas d'images prêtes.</p>
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
                ${galleryImages.length > 1 ? `
                    <div class="image-navigation">
                        <button type="button" class="nav-btn nav-prev" onclick="previousImage()">
                            <i class="fas fa-chevron-left"></i>
                        </button>
                        <button type="button" class="nav-btn nav-next" onclick="nextImage()">
                            <i class="fas fa-chevron-right"></i>
                        </button>
                    </div>
                ` : ''}
                <div class="image-counter">
                    <span id="currentImageIndex">${currentImageIndex + 1}</span> / ${galleryImages.length}
                    ${galleryImages[currentImageIndex].isPrimary ? ' • <i class="fas fa-star text-warning"></i> Principal' : ''}
                </div>
                ${galleryImages[currentImageIndex].fileSize || galleryImages[currentImageIndex].dimensions ? `
                    <div class="image-counter" style="left: 1rem; right: auto;">
                        ${galleryImages[currentImageIndex].fileSize || ''} ${galleryImages[currentImageIndex].dimensions ? '• ' + galleryImages[currentImageIndex].dimensions : ''}
                    </div>
                ` : ''}
            </div>

            ${galleryImages.length > 1 ? `
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
                                </div>
                            ` : ''}
                        </div>
                    `).join('')}
                </div>
            ` : ''}
        </div>
    `;

    galleryBody.innerHTML = galleryHtml;
}

function selectImage(index, imageUrl) {
    currentImageIndex = index;

    // Update main image
    document.getElementById('mainGalleryImage').src = imageUrl;

    // Update counter and info
    const counterElement = document.getElementById('currentImageIndex');
    if (counterElement) {
        counterElement.textContent = index + 1;
    }

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
