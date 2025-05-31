@extends('layouts.app')

@section('title', 'Ajouter un Produit - Coopérative')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-plus me-2"></i>
                        Ajouter un Produit
                    </h1>
                    <p class="text-muted">Créer un nouveau produit pour {{ Auth::user()->cooperative->name }}</p>
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
                                    <option value="{{ $category->id }}">
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
                                   placeholder="Nom du produit" maxlength="255">
                            <div class="invalid-feedback" id="name_error"></div>
                        </div>

                        <!-- Description -->
                        <div class="mb-3">
                            <label for="description" class="form-label">
                                <i class="fas fa-align-left me-1"></i>
                                Description *
                            </label>
                            <textarea class="form-control" id="description" name="description" rows="4" required
                                      placeholder="Description détaillée du produit" maxlength="2000"></textarea>
                            <div class="invalid-feedback" id="description_error"></div>
                            <div class="form-text">
                                <span id="description_count">0</span>/2000 caractères
                            </div>
                        </div>

                        <!-- Price and Stock Row -->
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
                                               placeholder="0.00">
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
                                    </label>
                                    <input type="number" class="form-control" id="stock_quantity" name="stock_quantity"
                                           min="0" required placeholder="0">
                                    <div class="invalid-feedback" id="stock_quantity_error"></div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="stock_alert_threshold" class="form-label">
                                        <i class="fas fa-bell me-1"></i>
                                        Seuil d'Alerte Stock *
                                    </label>
                                    <input type="number" class="form-control" id="stock_alert_threshold" name="stock_alert_threshold"
                                           min="0" max="1000" value="5" required placeholder="5">
                                    <div class="invalid-feedback" id="stock_alert_threshold_error"></div>
                                    <div class="form-text">
                                        Alerte si stock ≤ cette valeur
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Enhanced Images Upload -->
                        <div class="mb-4">
                            <label class="form-label">
                                <i class="fas fa-images me-1"></i>
                                Images du Produit * (1 à 5 images)
                            </label>

                            <!-- File Input -->
                            <div class="mb-3">
                                <input type="file" class="form-control" id="images" name="images[]"
                                       multiple accept="image/jpeg,image/png,image/jpg,image/webp" required>
                                <div class="invalid-feedback" id="images_error"></div>
                                <div class="form-text">
                                    Formats acceptés: JPEG, PNG, JPG, WEBP. Taille max: 2MB par image.
                                    <br>La première image sera définie comme image principale.
                                </div>
                            </div>

                            <!-- Image Preview Container -->
                            <div id="imagePreviewContainer" class="row" style="display: none;">
                                <!-- Previews will be added here -->
                            </div>
                        </div>
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
                        <button type="button" class="btn btn-success" id="submitBtn">
                            <span class="spinner-border spinner-border-sm me-2 d-none" role="status"></span>
                            <i class="fas fa-paper-plane me-1"></i>
                            Soumettre pour Approbation
                        </button>
                    </div>

                    <hr>

                    <div class="alert alert-info">
                        <h6><i class="fas fa-info-circle me-2"></i>Information</h6>
                        <ul class="mb-0 small">
                            <li><strong>Brouillon:</strong> Sauvegarde sans soumettre</li>
                            <li><strong>Soumettre:</strong> Envoie pour approbation admin</li>
                            <li>Tous les champs sont obligatoires</li>
                            <li>Au moins une image est requise</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Stock Alert Info Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-warning">
                        <i class="fas fa-bell me-2"></i>
                        Alertes Stock
                    </h6>
                </div>
                <div class="card-body">
                    <div class="small">
                        <p><strong>Fonctionnement:</strong></p>
                        <ul>
                            <li>Vous serez alerté quand le stock atteint le seuil configuré</li>
                            <li>Les produits en stock faible apparaîtront avec un indicateur</li>
                            <li>Le tableau de bord affichera le nombre total d'alertes</li>
                            <li>Vous pouvez modifier ce seuil à tout moment</li>
                        </ul>

                        <div class="alert alert-light mt-3">
                            <strong>Exemple:</strong> Si vous définissez le seuil à 5, vous serez alerté quand il reste 5 unités ou moins.
                        </div>
                    </div>
                </div>
            </div>

            <!-- Help Card -->
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-info">
                        <i class="fas fa-lightbulb me-2"></i>
                        Conseils
                    </h6>
                </div>
                <div class="card-body">
                    <div class="small">
                        <p><strong>Photos:</strong></p>
                        <ul>
                            <li>Utilisez des images de haute qualité</li>
                            <li>Montrez le produit sous différents angles</li>
                            <li>Évitez les arrière-plans encombrés</li>
                            <li>La première image sera l'image principale</li>
                        </ul>

                        <p><strong>Description:</strong></p>
                        <ul>
                            <li>Soyez précis et détaillé</li>
                            <li>Mentionnez les caractéristiques importantes</li>
                            <li>Indiquez la provenance et la qualité</li>
                        </ul>

                        <p><strong>Stock:</strong></p>
                        <ul>
                            <li>Configurez un seuil d'alerte réaliste</li>
                            <li>Tenez compte de votre fréquence de réapprovisionnement</li>
                            <li>Plus le produit est populaire, plus le seuil devrait être élevé</li>
                        </ul>
                    </div>
                </div>
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
}

.image-preview img {
    width: 100%;
    height: 150px;
    object-fit: cover;
    border-radius: 0.375rem;
    border: 2px solid #dee2e6;
}

.image-preview .remove-image {
    position: absolute;
    top: 5px;
    right: 5px;
    background: rgba(220, 53, 69, 0.8);
    color: white;
    border: none;
    border-radius: 50%;
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: background-color 0.2s;
}

.image-preview .remove-image:hover {
    background: rgba(220, 53, 69, 1);
}

.image-preview .primary-badge {
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

.form-control:focus {
    border-color: #007bff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

.btn {
    transition: all 0.2s ease;
}

.btn:hover {
    transform: translateY(-1px);
}

.image-preview .file-info {
    position: absolute;
    bottom: 5px;
    left: 5px;
    right: 5px;
    background: rgba(0, 0, 0, 0.7);
    color: white;
    padding: 2px 5px;
    border-radius: 3px;
    font-size: 0.7rem;
}

/* Stock alert threshold input styling */
#stock_alert_threshold {
    border-left: 3px solid #ffc107;
}

#stock_alert_threshold:focus {
    border-left-color: #ff8f00;
    box-shadow: 0 0 0 0.2rem rgba(255, 193, 7, 0.25);
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    let selectedFiles = [];

    // Character counter for description
    const descriptionTextarea = document.getElementById('description');
    const descriptionCount = document.getElementById('description_count');

    descriptionTextarea.addEventListener('input', function() {
        descriptionCount.textContent = this.value.length;
    });

    // Stock alert threshold validation
    const stockQuantityInput = document.getElementById('stock_quantity');
    const stockAlertInput = document.getElementById('stock_alert_threshold');

    // Auto-suggest stock alert threshold based on stock quantity
    stockQuantityInput.addEventListener('input', function() {
        const stockQuantity = parseInt(this.value) || 0;
        const currentThreshold = parseInt(stockAlertInput.value) || 5;

        // Suggest 10% of stock quantity or minimum 5, whichever is higher
        const suggestedThreshold = Math.max(5, Math.floor(stockQuantity * 0.1));

        // Only update if current threshold is default (5) or very low
        if (currentThreshold <= 5 && stockQuantity > 50) {
            stockAlertInput.value = suggestedThreshold;

            // Show a brief tooltip or message
            showThresholdSuggestion(suggestedThreshold);
        }
    });

    // File input change handler
    document.getElementById('images').addEventListener('change', function(e) {
        handleFileSelection(e.target.files);
    });

    // Form submission handlers
    document.getElementById('saveDraftBtn').addEventListener('click', function() {
        submitForm('save_draft');
    });

    document.getElementById('submitBtn').addEventListener('click', function() {
        submitForm('submit');
    });

    function showThresholdSuggestion(threshold) {
        // Create a small notification
        const notification = document.createElement('div');
        notification.className = 'alert alert-info alert-dismissible fade show position-fixed';
        notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        notification.innerHTML = `
            <i class="fas fa-lightbulb me-2"></i>
            Seuil d'alerte suggéré: ${threshold} unités
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;

        document.body.appendChild(notification);

        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 4000);
    }

    function handleFileSelection(files) {
        const fileArray = Array.from(files);

        // Validate file count
        if (fileArray.length < 1 || fileArray.length > 5) {
            showError('Veuillez sélectionner entre 1 et 5 images.');
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

        selectedFiles = fileArray;
        displayImagePreviews();
    }

    function displayImagePreviews() {
        const container = document.getElementById('imagePreviewContainer');
        container.innerHTML = '';

        if (selectedFiles.length === 0) {
            container.style.display = 'none';
            return;
        }

        container.style.display = 'block';

        selectedFiles.forEach((file, index) => {
            const reader = new FileReader();
            reader.onload = function(e) {
                const col = document.createElement('div');
                col.className = 'col-md-6 col-lg-4';

                const fileSize = (file.size / 1024).toFixed(1) + ' KB';

                col.innerHTML = `
                    <div class="image-preview">
                        <img src="${e.target.result}" alt="Preview ${index + 1}">
                        ${index === 0 ? '<div class="primary-badge">Principal</div>' : ''}
                        <button type="button" class="remove-image" onclick="removeImage(${index})">
                            <i class="fas fa-times"></i>
                        </button>
                        <div class="file-info">${file.name} (${fileSize})</div>
                    </div>
                `;

                container.appendChild(col);
            };
            reader.readAsDataURL(file);
        });
    }

    window.removeImage = function(index) {
        selectedFiles.splice(index, 1);
        displayImagePreviews();

        // Update file input
        const dt = new DataTransfer();
        selectedFiles.forEach(file => dt.items.add(file));
        document.getElementById('images').files = dt.files;
    };

    function submitForm(action) {
        clearErrors();

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
            if (input.value.trim()) {
                formData.append(input.name, input.value.trim());
            }
        });

        // Add files
        selectedFiles.forEach(file => {
            formData.append('images[]', file);
        });

        // Add action
        formData.append('action', action);

        const submitBtn = action === 'submit' ? document.getElementById('submitBtn') : document.getElementById('saveDraftBtn');
        showLoading(submitBtn);

        fetch('{{ route("coop.products.store") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
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
                    showError(data.message || 'Erreur lors de la sauvegarde');
                }
            }
        })
        .catch(error => {
            hideLoading(submitBtn);
            console.error('Error:', error);
            showError('Erreur de connexion au serveur');
        });
    }

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
});
</script>
@endpush
