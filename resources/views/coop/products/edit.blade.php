@extends('layouts.app')

@section('title', 'Modifier le Produit - Coopérative')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-edit me-2"></i>
                        Modifier le Produit
                    </h1>
                    <p class="text-muted">{{ $product->name }}</p>
                </div>
                <div>
                    <a href="{{ route('coop.products.index') }}" class="btn btn-outline-primary">
                        <i class="fas fa-arrow-left me-2"></i>
                        Retour aux produits
                    </a>
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

    <!-- Stock Alert Status -->
    @if($product->isStockLow())
        <div class="row mb-4">
            <div class="col-12">
                <div class="alert alert-{{ $product->isOutOfStock() ? 'danger' : 'warning' }}">
                    <h6>
                        <i class="fas fa-{{ $product->isOutOfStock() ? 'times-circle' : 'exclamation-triangle' }} me-2"></i>
                        {{ $product->stock_status_text }}
                    </h6>
                    <p class="mb-0">
                        Stock actuel: <strong>{{ $product->stock_quantity }}</strong> •
                        Seuil d'alerte: <strong>{{ $product->stock_alert_threshold }}</strong>
                        @if($product->isOutOfStock())
                            • Ce produit est en rupture de stock!
                        @else
                            • Ce produit nécessite un réapprovisionnement.
                        @endif
                    </p>
                </div>
            </div>
        </div>
    @endif

    <!-- Product Form -->
    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-info-circle me-2"></i>
                        Informations du Produit
                    </h6>
                </div>
                <div class="card-body">
                    <form id="productForm" enctype="multipart/form-data">
                        @csrf

                        <!-- Category -->
                        <div class="mb-3">
                            <label for="category_id" class="form-label">
                                <i class="fas fa-tag me-1"></i>
                                Catégorie *
                            </label>
                            <select class="form-select" id="category_id" name="category_id" required>
                                <option value="">Sélectionnez une catégorie</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ $category->id == $product->category_id ? 'selected' : '' }}>
                                        {{ str_repeat('└─ ', $category->level) }}{{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback" id="category_id_error"></div>
                        </div>

                        <!-- Product Name -->
                        <div class="mb-3">
                            <label for="name" class="form-label">
                                <i class="fas fa-box me-1"></i>
                                Nom du Produit *
                            </label>
                            <input type="text" class="form-control" id="name" name="name" required
                                   value="{{ $product->name }}" placeholder="Nom du produit" maxlength="255">
                            <div class="invalid-feedback" id="name_error"></div>
                        </div>

                        <!-- Description -->
                        <div class="mb-3">
                            <label for="description" class="form-label">
                                <i class="fas fa-align-left me-1"></i>
                                Description *
                            </label>
                            <textarea class="form-control" id="description" name="description" rows="4" required
                                      placeholder="Description détaillée du produit" maxlength="2000">{{ $product->description }}</textarea>
                            <div class="invalid-feedback" id="description_error"></div>
                            <div class="form-text">
                                <span id="description_count">{{ strlen($product->description) }}</span>/2000 caractères
                            </div>
                        </div>

                        <!-- Price, Stock and Alert Threshold Row -->
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="price" class="form-label">
                                        <i class="fas fa-coins me-1"></i>
                                        Prix (MAD) *
                                    </label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" id="price" name="price"
                                               step="0.01" min="0" max="999999.99" required
                                               value="{{ $product->price }}" placeholder="0.00">
                                        <span class="input-group-text">MAD</span>
                                    </div>
                                    <div class="invalid-feedback" id="price_error"></div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="stock_quantity" class="form-label">
                                        <i class="fas fa-warehouse me-1"></i>
                                        Quantité en Stock *
                                        @if($product->isStockLow())
                                            <i class="fas fa-exclamation-triangle text-{{ $product->stock_status_badge }} ms-1"></i>
                                        @endif
                                    </label>
                                    <input type="number" class="form-control {{ $product->isStockLow() ? 'border-' . $product->stock_status_badge : '' }}"
                                           id="stock_quantity" name="stock_quantity"
                                           min="0" required value="{{ $product->stock_quantity }}" placeholder="0">
                                    <div class="invalid-feedback" id="stock_quantity_error"></div>
                                    @if($product->isStockLow())
                                        <div class="form-text text-{{ $product->stock_status_badge }}">
                                            {{ $product->stock_status_text }} (Seuil: {{ $product->stock_alert_threshold }})
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="stock_alert_threshold" class="form-label">
                                        <i class="fas fa-bell me-1"></i>
                                        Seuil d'Alerte Stock *
                                    </label>
                                    <input type="number" class="form-control" id="stock_alert_threshold" name="stock_alert_threshold"
                                           min="0" max="1000" value="{{ $product->stock_alert_threshold }}" required placeholder="5">
                                    <div class="invalid-feedback" id="stock_alert_threshold_error"></div>
                                    <div class="form-text">
                                        Alerte si stock ≤ cette valeur
                                        <button type="button" class="btn btn-link btn-sm p-0 ms-2" onclick="showStockAlertInfo()">
                                            <i class="fas fa-question-circle"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Enhanced Current Images Management -->
                        <div class="mb-4">
                            <label class="form-label">
                                <i class="fas fa-images me-1"></i>
                                Images Actuelles ({{ $product->images_count }})
                            </label>

                            <div id="currentImagesContainer" class="row">
                                @foreach($product->images as $image)
                                    <div class="col-md-6 col-lg-4 mb-3" data-image-id="{{ $image->id }}">
                                        <div class="image-preview current-image">
                                            <img src="{{ $image->thumbnail_url ?: $image->image_url }}" alt="Image produit">
                                            @if($product->primary_image_id === $image->id)
                                                <div class="primary-badge">Principal</div>
                                            @endif
                                            <div class="image-controls">
                                                <button type="button" class="btn btn-sm btn-warning set-primary-btn"
                                                        data-image-id="{{ $image->id }}"
                                                        @if($product->primary_image_id === $image->id) style="display:none;" @endif
                                                        onclick="setPrimaryImage({{ $image->id }})">
                                                    <i class="fas fa-star"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-danger remove-current-image"
                                                        onclick="removeCurrentImage({{ $image->id }})">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </div>
                                            <div class="image-info">
                                                <small>{{ $image->formatted_file_size }} • {{ $image->dimensions }}</small>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- New Images Upload -->
                        <div class="mb-4">
                            <label class="form-label">
                                <i class="fas fa-plus me-1"></i>
                                Ajouter de Nouvelles Images (optionnel)
                            </label>

                            <!-- File Input -->
                            <div class="mb-3">
                                <input type="file" class="form-control" id="new_images" name="new_images[]"
                                       multiple accept="image/jpeg,image/png,image/jpg,image/webp">
                                <div class="invalid-feedback" id="new_images_error"></div>
                                <div class="form-text">
                                    Formats acceptés: JPEG, PNG, JPG, WEBP. Taille max: 2MB par image.
                                    <br>Maximum 5 images au total (actuelles + nouvelles).
                                </div>
                            </div>

                            <!-- New Image Preview Container -->
                            <div id="newImagePreviewContainer" class="row" style="display: none;">
                                <!-- Previews will be added here -->
                            </div>
                        </div>

                        <!-- Hidden fields for tracking changes -->
                        <input type="hidden" id="removed_images" name="removed_images" value="">
                        <input type="hidden" id="primary_image_id" name="primary_image_id" value="{{ $product->primary_image_id }}">
                    </form>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Actions Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-success">
                        <i class="fas fa-save me-2"></i>
                        Actions
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-secondary" id="saveDraftBtn">
                            <span class="spinner-border spinner-border-sm me-2 d-none" role="status"></span>
                            <i class="fas fa-save me-1"></i>
                            Sauvegarder en Brouillon
                        </button>
                        @if($product->canBeSubmitted())
                            <button type="button" class="btn btn-success" id="submitBtn">
                                <span class="spinner-border spinner-border-sm me-2 d-none" role="status"></span>
                                <i class="fas fa-paper-plane me-1"></i>
                                Soumettre pour Approbation
                            </button>
                        @endif
                    </div>

                    <hr>

                    <div class="alert alert-warning">
                        <h6><i class="fas fa-exclamation-triangle me-2"></i>Attention</h6>
                        <ul class="mb-0 small">
                            <li>Le produit doit avoir au moins une image</li>
                            <li>Les modifications effacent les notes précédentes</li>
                            @if($product->needsInfo())
                                <li><strong>Informations demandées:</strong> Veuillez répondre aux questions de l'administrateur</li>
                            @endif
                            @if($product->isApproved())
                                <li><strong>Produit approuvé:</strong> Seuls les changements de stock et seuil d'alerte ne nécessitent pas de ré-approbation</li>
                            @endif
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Stock Alert Configuration Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-warning">
                        <i class="fas fa-bell me-2"></i>
                        Configuration Alerte Stock
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row text-center mb-3">
                        <div class="col-6">
                            <div class="border-end">
                                <div class="h5 mb-0 text-{{ $product->stock_status_badge }}">{{ $product->stock_quantity }}</div>
                                <small class="text-muted">Stock Actuel</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="h5 mb-0 text-warning">{{ $product->stock_alert_threshold }}</div>
                            <small class="text-muted">Seuil d'Alerte</small>
                        </div>
                    </div>

                    <div class="progress mb-3" style="height: 10px;">
                        @php
                            $percentage = $product->stock_alert_threshold > 0 ? ($product->stock_quantity / max($product->stock_alert_threshold * 2, $product->stock_quantity)) * 100 : 100;
                            $percentage = min(100, $percentage);
                        @endphp
                        <div class="progress-bar bg-{{ $product->stock_status_badge }}"
                             style="width: {{ $percentage }}%"></div>
                    </div>

                    @if($product->isStockLow())
                        <div class="alert alert-{{ $product->stock_status_badge }} py-2">
                            <small>
                                <i class="fas fa-{{ $product->isOutOfStock() ? 'times-circle' : 'exclamation-triangle' }} me-1"></i>
                                {{ $product->stock_status_text }}
                            </small>
                        </div>
                    @endif

                    <div class="small text-muted">
                        <p><strong>Fonctionnement:</strong></p>
                        <ul class="mb-2">
                            <li>Alerte quand stock ≤ seuil</li>
                            <li>Visible dans le tableau de bord</li>
                            <li>Modifiable à tout moment</li>
                        </ul>

                        <button type="button" class="btn btn-outline-warning btn-sm w-100" onclick="showStockAlertModal()">
                            <i class="fas fa-cog me-1"></i>
                            Configuration Rapide
                        </button>
                    </div>
                </div>
            </div>

            <!-- Product Status -->
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Statut Actuel
                    </h6>
                </div>
                <div class="card-body text-center">
                    <span class="badge bg-{{ $product->status_badge }} fs-6 mb-3">
                        {{ $product->status_text }}
                    </span>

                    <div class="small text-muted">
                        @if($product->submitted_at)
                            <p><strong>Soumis le:</strong><br>{{ $product->submitted_at->format('d/m/Y H:i') }}</p>
                        @endif

                        @if($product->reviewed_at)
                            <p><strong>Examiné le:</strong><br>{{ $product->reviewed_at->format('d/m/Y H:i') }}</p>
                        @endif

                        @if($product->reviewedBy)
                            <p><strong>Par:</strong><br>{{ $product->reviewedBy->full_name }}</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Stock Alert Quick Configuration Modal -->
<div class="modal fade" id="stockAlertQuickModal" tabindex="-1" aria-labelledby="stockAlertQuickModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="stockAlertQuickModalLabel">
                    <i class="fas fa-bell me-2"></i>
                    Configuration Rapide - Seuil d'Alerte
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Seuils Suggérés</label>
                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-outline-secondary" onclick="setQuickThreshold(5)">
                            <i class="fas fa-star me-1"></i>
                            5 unités (Recommandé pour la plupart des produits)
                        </button>
                        <button type="button" class="btn btn-outline-warning" onclick="setQuickThreshold(10)">
                            <i class="fas fa-exclamation-triangle me-1"></i>
                            10 unités (Produits populaires)
                        </button>
                        <button type="button" class="btn btn-outline-info" onclick="setQuickThreshold(Math.max(5, Math.floor({{ $product->stock_quantity }} * 0.1)))">
                            <i class="fas fa-percentage me-1"></i>
                            {{ Math.max(5, Math.floor($product->stock_quantity * 0.1)) }} unités (10% du stock actuel)
                        </button>
                        <button type="button" class="btn btn-outline-danger" onclick="setQuickThreshold(1)">
                            <i class="fas fa-times-circle me-1"></i>
                            1 unité (Alerte de dernière minute)
                        </button>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="customThreshold" class="form-label">Ou définir un seuil personnalisé</label>
                    <div class="input-group">
                        <input type="number" class="form-control" id="customThreshold"
                               min="0" max="1000" value="{{ $product->stock_alert_threshold }}">
                        <span class="input-group-text">unités</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-warning" onclick="applyQuickThreshold()">
                    <i class="fas fa-check me-1"></i>
                    Appliquer
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Stock Alert Info Modal -->
<div class="modal fade" id="stockAlertInfoModal" tabindex="-1" aria-labelledby="stockAlertInfoModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="stockAlertInfoModalLabel">
                    <i class="fas fa-info-circle me-2"></i>
                    Comment fonctionnent les Alertes Stock
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <h6>Principe</h6>
                    <p class="mb-0">Vous recevez une alerte quand le stock atteint ou descend en dessous du seuil configuré.</p>
                </div>

                <h6>Exemples pratiques:</h6>
                <ul>
                    <li><strong>Seuil à 5:</strong> Alerte quand il reste 5 unités ou moins</li>
                    <li><strong>Seuil à 10:</strong> Alerte quand il reste 10 unités ou moins</li>
                    <li><strong>Seuil à 0:</strong> Alerte uniquement en rupture de stock</li>
                </ul>

                <h6>Recommandations:</h6>
                <div class="row">
                    <div class="col-6">
                        <div class="card bg-light">
                            <div class="card-body py-2">
                                <h6 class="card-title text-success">Produits lents</h6>
                                <p class="card-text small">Seuil: 1-5 unités</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="card bg-light">
                            <div class="card-body py-2">
                                <h6 class="card-title text-warning">Produits populaires</h6>
                                <p class="card-text small">Seuil: 10-20 unités</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="alert alert-warning mt-3">
                    <h6>Important</h6>
                    <p class="mb-0">Ajustez le seuil selon votre fréquence de réapprovisionnement et la popularité du produit.</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Compris</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.image-preview {
    position: relative;
    margin-bottom: 15px;
    border-radius: 0.375rem;
    overflow: hidden;
}

.image-preview img {
    width: 100%;
    height: 150px;
    object-fit: cover;
    border: 2px solid #dee2e6;
    transition: all 0.3s ease;
}

.current-image {
    border: 2px solid #28a745;
}

.current-image img {
    border-color: #28a745;
}

.removed-image {
    opacity: 0.5;
    border: 2px solid #dc3545;
}

.removed-image img {
    border-color: #dc3545;
}

.image-controls {
    position: absolute;
    top: 5px;
    right: 5px;
    display: flex;
    gap: 5px;
}

.image-controls .btn {
    width: 28px;
    height: 28px;
    padding: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
}

.primary-badge {
    position: absolute;
    top: 5px;
    left: 5px;
    background: rgba(255, 193, 7, 0.9);
    color: #212529;
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: bold;
}

.image-info {
    position: absolute;
    bottom: 5px;
    left: 5px;
    right: 5px;
    background: rgba(0, 0, 0, 0.7);
    color: white;
    padding: 2px 5px;
    border-radius: 3px;
    font-size: 0.7rem;
    text-align: center;
}

.new-image {
    border: 2px solid #007bff;
}

.new-image img {
    border-color: #007bff;
}

.form-control:focus {
    border-color: #007bff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

.current-image:hover img {
    transform: scale(1.05);
}

/* Stock alert specific styling */
.border-warning {
    border-color: #ffc107 !important;
}

.border-danger {
    border-color: #dc3545 !important;
}

#stock_alert_threshold {
    border-left: 3px solid #ffc107;
}

#stock_alert_threshold:focus {
    border-left-color: #ff8f00;
    box-shadow: 0 0 0 0.2rem rgba(255, 193, 7, 0.25);
}

.progress {
    background-color: #e9ecef;
}

.progress-bar {
    transition: width 0.3s ease;
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    let selectedNewFiles = [];
    let removedImageIds = [];
    let primaryImageId = {{ $product->primary_image_id ?? 'null' }};

    // Initialize modals
    const stockAlertQuickModal = new bootstrap.Modal(document.getElementById('stockAlertQuickModal'));
    const stockAlertInfoModal = new bootstrap.Modal(document.getElementById('stockAlertInfoModal'));

    // Character counter for description
    const descriptionTextarea = document.getElementById('description');
    const descriptionCount = document.getElementById('description_count');

    descriptionTextarea.addEventListener('input', function() {
        descriptionCount.textContent = this.value.length;
    });

    // Stock alert threshold and quantity validation
    const stockQuantityInput = document.getElementById('stock_quantity');
    const stockAlertInput = document.getElementById('stock_alert_threshold');

    stockQuantityInput.addEventListener('input', updateStockStatus);
    stockAlertInput.addEventListener('input', updateStockStatus);

    function updateStockStatus() {
        const stockQuantity = parseInt(stockQuantityInput.value) || 0;
        const alertThreshold = parseInt(stockAlertInput.value) || 0;

        // Update visual indicators
        stockQuantityInput.classList.remove('border-warning', 'border-danger', 'border-success');

        if (stockQuantity === 0) {
            stockQuantityInput.classList.add('border-danger');
        } else if (stockQuantity <= alertThreshold) {
            stockQuantityInput.classList.add('border-warning');
        } else {
            stockQuantityInput.classList.add('border-success');
        }
    }

    // File input change handler
    document.getElementById('new_images').addEventListener('change', function(e) {
        handleNewFileSelection(e.target.files);
    });

    // Form submission handlers
    document.getElementById('saveDraftBtn').addEventListener('click', function() {
        submitForm('save_draft');
    });

    const submitBtn = document.getElementById('submitBtn');
    if (submitBtn) {
        submitBtn.addEventListener('click', function() {
            submitForm('submit');
        });
    }

    function handleNewFileSelection(files) {
        const fileArray = Array.from(files);

        // Validate total image count
        const currentImageCount = getCurrentImageCount();
        const totalImages = currentImageCount + fileArray.length;

        if (totalImages > 5) {
            showError(`Nombre total d'images dépassé. Maximum: 5 images. Actuellement: ${currentImageCount}`);
            return;
        }

        // Validate file types and sizes
        const validTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp'];
        const maxSize = 2 * 1024 * 1024; // 2MB

        for (let file of fileArray) {
            if (!validTypes.includes(file.type)) {
                showError(`Format non supporté: ${file.name}. Utilisez JPEG, PNG, JPG ou WEBP.`);
                return;
            }
            if (file.size > maxSize) {
                showError(`Fichier trop volumineux: ${file.name}. Taille max: 2MB.`);
                return;
            }
        }

        selectedNewFiles = fileArray;
        displayNewImagePreviews();
    }

    function displayNewImagePreviews() {
        const container = document.getElementById('newImagePreviewContainer');
        container.innerHTML = '';

        if (selectedNewFiles.length === 0) {
            container.style.display = 'none';
            return;
        }

        container.style.display = 'block';

        selectedNewFiles.forEach((file, index) => {
            const reader = new FileReader();
            reader.onload = function(e) {
                const col = document.createElement('div');
                col.className = 'col-md-6 col-lg-4';

                const fileSize = (file.size / 1024).toFixed(1) + ' KB';

                col.innerHTML = `
                    <div class="image-preview new-image">
                        <img src="${e.target.result}" alt="Nouvelle image ${index + 1}">
                        <div class="image-controls">
                            <button type="button" class="btn btn-sm btn-danger" onclick="removeNewImage(${index})">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <div class="image-info">${file.name} (${fileSize})</div>
                    </div>
                `;

                container.appendChild(col);
            };
            reader.readAsDataURL(file);
        });
    }

    window.removeCurrentImage = function(imageId) {
        const imageElement = document.querySelector(`[data-image-id="${imageId}"]`);
        if (!imageElement) return;

        if (removedImageIds.includes(imageId)) {
            // Restore image
            removedImageIds = removedImageIds.filter(id => id !== imageId);
            imageElement.querySelector('.image-preview').classList.remove('removed-image');
            imageElement.querySelector('.remove-current-image i').className = 'fas fa-times';
        } else {
            // Check if removing this image would leave us with no images
            const remainingImages = getCurrentImageCount() - 1 + selectedNewFiles.length;
            if (remainingImages < 1) {
                showError('Le produit doit avoir au moins une image.');
                return;
            }

            // Check if this is the primary image
            if (primaryImageId === imageId) {
                // Find another image to set as primary
                const otherImages = document.querySelectorAll('[data-image-id]');
                let newPrimaryId = null;

                for (let img of otherImages) {
                    const imgId = parseInt(img.dataset.imageId);
                    if (imgId !== imageId && !removedImageIds.includes(imgId)) {
                        newPrimaryId = imgId;
                        break;
                    }
                }

                if (newPrimaryId) {
                    setPrimaryImage(newPrimaryId);
                }
            }

            // Mark for removal
            removedImageIds.push(imageId);
            imageElement.querySelector('.image-preview').classList.add('removed-image');
            imageElement.querySelector('.remove-current-image i').className = 'fas fa-undo';
        }

        updateRemovedImagesInput();
    };

    window.setPrimaryImage = function(imageId) {
        // Remove primary badge from all images
        document.querySelectorAll('.primary-badge').forEach(badge => badge.remove());
        document.querySelectorAll('.set-primary-btn').forEach(btn => btn.style.display = 'block');

        // Add primary badge to selected image
        const imageElement = document.querySelector(`[data-image-id="${imageId}"]`);
        if (imageElement && !removedImageIds.includes(imageId)) {
            const primaryBadge = document.createElement('div');
            primaryBadge.className = 'primary-badge';
            primaryBadge.textContent = 'Principal';
            imageElement.querySelector('.image-preview').appendChild(primaryBadge);

            // Hide set primary button for this image
            const setPrimaryBtn = imageElement.querySelector('.set-primary-btn');
            if (setPrimaryBtn) {
                setPrimaryBtn.style.display = 'none';
            }

            primaryImageId = imageId;
            document.getElementById('primary_image_id').value = imageId;
        }
    };

    window.removeNewImage = function(index) {
        selectedNewFiles.splice(index, 1);
        displayNewImagePreviews();

        // Update file input
        const dt = new DataTransfer();
        selectedNewFiles.forEach(file => dt.items.add(file));
        document.getElementById('new_images').files = dt.files;
    };

    function getCurrentImageCount() {
        const currentImages = document.querySelectorAll('#currentImagesContainer [data-image-id]');
        return currentImages.length - removedImageIds.length;
    }

    function updateRemovedImagesInput() {
        document.getElementById('removed_images').value = JSON.stringify(removedImageIds);
    }

    function submitForm(action) {
        clearErrors();

        // Validate we have at least one image
        const finalImageCount = getCurrentImageCount() + selectedNewFiles.length;
        if (finalImageCount < 1) {
            showError('Le produit doit avoir au moins une image.');
            return;
        }

        // Additional validation for stock alert threshold
        const stockQuantity = parseInt(document.getElementById('stock_quantity').value) || 0;
        const stockAlertThreshold = parseInt(document.getElementById('stock_alert_threshold').value) || 0;

        if (stockAlertThreshold > stockQuantity && stockQuantity > 0) {
            showError('Le seuil d\'alerte ne peut pas être supérieur à la quantité en stock.');
            document.getElementById('stock_alert_threshold').classList.add('is-invalid');
            return;
        }

        const formData = new FormData();
        const form = document.getElementById('productForm');

        // Add form fields
        const inputs = form.querySelectorAll('input:not([type="file"]), select, textarea');
        inputs.forEach(input => {
            if (input.type === 'hidden' || input.value.trim()) {
                formData.append(input.name, input.value);
            }
        });

        // Add new files
        selectedNewFiles.forEach(file => {
            formData.append('new_images[]', file);
        });

        // Add removed images
        if (removedImageIds.length > 0) {
            removedImageIds.forEach(id => {
                formData.append('removed_images[]', id);
            });
        }

        // Add primary image ID
        if (primaryImageId) {
            formData.append('primary_image_id', primaryImageId);
        }

        // Add action
        formData.append('action', action);

        const submitBtn = action === 'submit' ? document.getElementById('submitBtn') : document.getElementById('saveDraftBtn');
        showLoading(submitBtn);

        fetch('{{ route("coop.products.update", $product) }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json',
                'X-HTTP-Method-Override': 'PUT'
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            hideLoading(submitBtn);

            if (data.success) {
                showSuccess(data.message);
                if (data.redirect) {
                    setTimeout(() => {
                        window.location.href = data.redirect;
                    }, 1500);
                }
            } else {
                if (data.errors) {
                    displayErrors(data.errors);
                } else {
                    showError(data.message || 'Erreur lors de la mise à jour');
                }
            }
        })
        .catch(error => {
            hideLoading(submitBtn);
            console.error('Error:', error);
            showError('Erreur de connexion au serveur');
        });
    }

    // Stock Alert Modal Functions
    window.showStockAlertModal = function() {
        stockAlertQuickModal.show();
    };

    window.showStockAlertInfo = function() {
        stockAlertInfoModal.show();
    };

    window.setQuickThreshold = function(threshold) {
        document.getElementById('customThreshold').value = threshold;
    };

    window.applyQuickThreshold = function() {
        const threshold = document.getElementById('customThreshold').value;
        document.getElementById('stock_alert_threshold').value = threshold;
        updateStockStatus();
        stockAlertQuickModal.hide();
        showSuccess(`Seuil d'alerte défini à ${threshold} unités`);
    };

    function clearErrors() {
        document.querySelectorAll('.is-invalid').forEach(element => {
            element.classList.remove('is-invalid');
        });
        document.querySelectorAll('.invalid-feedback').forEach(element => {
            element.textContent = '';
        });
    }

    function displayErrors(errors) {
        Object.keys(errors).forEach(field => {
            const input = document.querySelector(`[name="${field}"], [name="${field}[]"]`);
            const errorDiv = document.getElementById(`${field}_error`);

            if (input) {
                input.classList.add('is-invalid');
            }
            if (errorDiv) {
                errorDiv.textContent = errors[field][0];
            }
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

    function showSuccess(message) {
        showAlert(message, 'success');
    }

    function showError(message) {
        showAlert(message, 'danger');
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

    // Initialize stock status on page load
    updateStockStatus();
});
</script>
@endpush
