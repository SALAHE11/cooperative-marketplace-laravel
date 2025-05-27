
@extends('layouts.app')

@section('title', 'Gestion des Catégories - Admin')

@section('content')
<div class="container-fluid py-4">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-sitemap me-2"></i>
                        Gestion des Catégories Hiérarchiques
                    </h1>
                    <p class="text-muted">Gérer les catégories et sous-catégories de produits</p>
                </div>
                <div>
                    <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-primary me-2">
                        <i class="fas fa-arrow-left me-2"></i>
                        Retour au tableau de bord
                    </a>
                    <button class="btn btn-primary" onclick="openAddModal()">
                        <i class="fas fa-plus me-2"></i>
                        Ajouter une Catégorie
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Alerts -->
    <div id="alertContainer"></div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-2 col-md-4 mb-3">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Catégories
                            </div>
                            <div class="h6 mb-0 font-weight-bold text-gray-800">{{ $stats['total'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-tags fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 mb-3">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Catégories Racines
                            </div>
                            <div class="h6 mb-0 font-weight-bold text-gray-800">{{ $stats['roots'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-tree fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 mb-3">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Sous-catégories
                            </div>
                            <div class="h6 mb-0 font-weight-bold text-gray-800">{{ $stats['subcategories'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-sitemap fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 mb-3">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Avec Produits
                            </div>
                            <div class="h6 mb-0 font-weight-bold text-gray-800">{{ $stats['with_products'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-box fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 mb-3">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                Niveaux Max
                            </div>
                            <div class="h6 mb-0 font-weight-bold text-gray-800">{{ $stats['max_level'] + 1 }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-layer-group fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 mb-3">
            <div class="card border-left-dark shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-dark text-uppercase mb-1">
                                Total Produits
                            </div>
                            <div class="h6 mb-0 font-weight-bold text-gray-800">{{ $stats['total_products'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-cubes fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Categories Table -->
    <div class="card shadow">
        <div class="card-header py-3">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-list me-2"></i>
                        Hiérarchie des Catégories
                    </h6>
                </div>
                <div class="col-md-6">
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="fas fa-search"></i>
                        </span>
                        <input type="text" class="form-control" id="searchInput"
                               placeholder="Rechercher une catégorie..." onkeyup="filterCategories()">
                        <button class="btn btn-outline-secondary" type="button" onclick="clearSearch()">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body">
            @if($categories->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover" id="categoriesTable">
                        <thead class="table-dark">
                            <tr>
                                <th style="width: 35%">
                                    <i class="fas fa-sitemap me-1"></i>
                                    Hiérarchie des Catégories
                                </th>
                                <th style="width: 20%">
                                    <i class="fas fa-align-left me-1"></i>
                                    Description
                                </th>
                                <th style="width: 10%">
                                    <i class="fas fa-box me-1"></i>
                                    Produits
                                </th>
                                <th style="width: 10%">
                                    <i class="fas fa-sitemap me-1"></i>
                                    Enfants
                                </th>
                                <th style="width: 15%">
                                    <i class="fas fa-calendar me-1"></i>
                                    Date
                                </th>
                                <th style="width: 10%">
                                    <i class="fas fa-cogs me-1"></i>
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($categories as $category)
                            <tr data-category-id="{{ $category->id }}"
                                data-category-name="{{ strtolower($category->name) }}"
                                data-level="{{ $category->level }}"
                                class="category-level-{{ $category->level }}">
                                <td>
                                    <div class="d-flex align-items-center hierarchy-cell">
                                        <!-- Hierarchical Indentation -->
                                        <div class="hierarchy-indent" style="width: {{ $category->level * 25 }}px;">
                                            @if($category->level > 0)
                                                @for($i = 0; $i < $category->level; $i++)
                                                    @if($i == $category->level - 1)
                                                        <span class="hierarchy-connector">└─</span>
                                                    @else
                                                        <span class="hierarchy-line">│&nbsp;&nbsp;</span>
                                                    @endif
                                                @endfor
                                            @endif
                                        </div>

                                        <!-- Category Icon -->
                                        <div class="category-icon me-2">
                                            @if($category->level == 0)
                                                <i class="fas fa-folder-open text-primary fa-lg"></i>
                                            @elseif($category->hasChildren())
                                                <i class="fas fa-folder text-warning"></i>
                                            @else
                                                <i class="fas fa-tag text-info"></i>
                                            @endif
                                        </div>

                                        <!-- Category Info -->
                                        <div class="category-info">
                                            <div class="category-name fw-bold text-{{ $category->level == 0 ? 'primary' : ($category->level == 1 ? 'dark' : 'muted') }}">
                                                {{ $category->name }}
                                            </div>
                                            @if($category->level > 0 && $category->parent)
                                                <small class="text-muted">
                                                    <i class="fas fa-level-up-alt me-1"></i>
                                                    {{ $category->parent->name }}
                                                </small>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <small class="text-muted">
                                        {{ Str::limit($category->description ?: 'Aucune description', 40) }}
                                    </small>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $category->products_count > 0 ? 'primary' : 'secondary' }}">
                                        {{ $category->products_count }}
                                    </span>
                                </td>
                                <td>
                                    @if($category->hasChildren())
                                        <span class="badge bg-info">
                                            <i class="fas fa-sitemap me-1"></i>
                                            {{ $category->children_count }}
                                        </span>
                                    @else
                                        <span class="badge bg-light text-dark">
                                            <i class="fas fa-leaf me-1"></i>
                                            0
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <small>
                                        <strong>{{ $category->created_at->format('d/m/Y') }}</strong><br>
                                        <span class="text-muted">{{ $category->created_at->format('H:i') }}</span>
                                    </small>
                                </td>
                                <td>
                                    <div class="btn-group-vertical btn-group-sm" role="group">
                                        <button type="button" class="btn btn-success btn-sm mb-1"
                                                onclick="addSubcategory({{ $category->id }}, '{{ addslashes($category->name) }}')"
                                                title="Ajouter sous-catégorie">
                                            <i class="fas fa-plus me-1"></i>
                                        </button>
                                        <button type="button" class="btn btn-warning btn-sm mb-1"
                                                onclick="editCategory({{ $category->id }}, '{{ addslashes($category->name) }}', '{{ addslashes($category->description ?? '') }}', {{ $category->parent_id ?? 'null' }})"
                                                title="Modifier cette catégorie">
                                            <i class="fas fa-edit me-1"></i>
                                        </button>
                                        <button type="button" class="btn btn-danger btn-sm"
                                                onclick="deleteCategory({{ $category->id }}, '{{ addslashes($category->name) }}', {{ $category->products_count }}, {{ $category->children_count }})"
                                                title="Supprimer cette catégorie">
                                            <i class="fas fa-trash me-1"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-center mt-3">
                    {{ $categories->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-sitemap fa-4x text-muted mb-3"></i>
                    <h4>Aucune catégorie trouvée</h4>
                    <p class="text-muted">Commencez par ajouter votre première catégorie de produits.</p>
                    <button class="btn btn-primary" onclick="openAddModal()">
                        <i class="fas fa-plus me-2"></i>
                        Ajouter une Catégorie
                    </button>
                </div>
            @endif

            <!-- No Results Message (hidden by default) -->
            <div id="noResults" class="text-center py-4 d-none">
                <i class="fas fa-search fa-3x text-muted mb-3"></i>
                <h5>Aucun résultat trouvé</h5>
                <p class="text-muted">Aucune catégorie ne correspond à votre recherche.</p>
                <button class="btn btn-outline-primary" onclick="clearSearch()">
                    <i class="fas fa-times me-2"></i>
                    Effacer la recherche
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Add/Edit Category Modal -->
<div class="modal fade" id="categoryModal" tabindex="-1" aria-labelledby="modalTitle" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">
                    <i class="fas fa-plus me-2"></i>
                    Ajouter une Catégorie
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="categoryForm">
                @csrf
                <div class="modal-body">
                    <!-- Parent Category Selection -->
                    <div class="mb-3">
                        <label for="parentCategory" class="form-label">
                            <i class="fas fa-sitemap me-1"></i>
                            Catégorie Parent
                        </label>
                        <select class="form-select" id="parentCategory" name="parent_id">
                            <option value="">-- Catégorie Racine --</option>
                            @foreach($allCategories as $cat)
                                <option value="{{ $cat->id }}" data-level="{{ $cat->level }}">
                                    {{ str_repeat('└─ ', $cat->level) }}{{ $cat->name }}
                                </option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback" id="parentError"></div>
                        <small class="form-text text-muted">
                            Choisissez une catégorie parent ou laissez vide pour créer une catégorie racine
                        </small>
                    </div>

                    <!-- Category Name -->
                    <div class="mb-3">
                        <label for="categoryName" class="form-label">
                            <i class="fas fa-tag me-1"></i>
                            Nom de la Catégorie *
                        </label>
                        <input type="text" class="form-control" id="categoryName" name="name" required
                               placeholder="Entrez le nom de la catégorie">
                        <div class="invalid-feedback" id="nameError"></div>
                    </div>

                    <!-- Category Description -->
                    <div class="mb-3">
                        <label for="categoryDescription" class="form-label">
                            <i class="fas fa-align-left me-1"></i>
                            Description
                        </label>
                        <textarea class="form-control" id="categoryDescription" name="description" rows="3"
                                  placeholder="Description optionnelle de la catégorie"></textarea>
                        <div class="invalid-feedback" id="descriptionError"></div>
                    </div>

                    <!-- Breadcrumb Preview -->
                    <div class="mb-3" id="breadcrumbPreview" style="display: none;">
                        <label class="form-label">
                            <i class="fas fa-route me-1"></i>
                            Chemin de la Catégorie
                        </label>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb" id="breadcrumbContainer">
                                <!-- Breadcrumb will be populated here -->
                            </ol>
                        </nav>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>
                        Annuler
                    </button>
                    <button type="submit" class="btn btn-primary" id="submitBtn">
                        <span class="spinner-border spinner-border-sm me-2 d-none" role="status" aria-hidden="true"></span>
                        <i class="fas fa-save me-1" id="submitIcon"></i>
                        <span id="submitText">Ajouter</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
/* Border colors for cards */
.border-left-primary { border-left: 0.25rem solid #4e73df !important; }
.border-left-success { border-left: 0.25rem solid #1cc88a !important; }
.border-left-info { border-left: 0.25rem solid #36b9cc !important; }
.border-left-warning { border-left: 0.25rem solid #f6c23e !important; }
.border-left-danger { border-left: 0.25rem solid #e74a3b !important; }
.border-left-dark { border-left: 0.25rem solid #5a5c69 !important; }

/* Table hierarchy styling */
.hierarchy-cell {
    position: relative;
}

.hierarchy-indent {
    display: flex;
    align-items: center;
    font-family: monospace;
    color: #6c757d;
    font-size: 14px;
}

.hierarchy-connector {
    color: #495057;
    font-weight: bold;
    margin-right: 5px;
}

.hierarchy-line {
    color: #dee2e6;
    margin-right: 5px;
}

.category-level-0 .category-name {
    font-size: 1.1em;
    font-weight: 700 !important;
}

.category-level-1 .category-name {
    font-size: 1.05em;
    font-weight: 600 !important;
}

.category-level-2 .category-name {
    font-size: 1em;
    font-weight: 500 !important;
}

.category-level-3 .category-name {
    font-size: 0.95em;
    font-weight: 400 !important;
}

.category-icon {
    min-width: 20px;
}

.table-hover tbody tr:hover {
    background-color: rgba(0,0,0,.075);
}

/* Search highlighting */
.search-highlight {
    background-color: #fff3cd;
    font-weight: bold;
    padding: 2px 4px;
    border-radius: 3px;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .hierarchy-indent {
        font-size: 12px;
    }

    .btn-group-vertical .btn {
        padding: 4px 8px;
        font-size: 0.8em;
    }
}
</style>
@endpush

@push('scripts')
<script>
let editingCategoryId = null;
let categoryModal = null;
let parentCategoryId = null;

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    initializeCategoryManagement();
});

function initializeCategoryManagement() {
    // Initialize Bootstrap modal
    const modalElement = document.getElementById('categoryModal');
    if (modalElement) {
        categoryModal = new bootstrap.Modal(modalElement, {
            keyboard: false,
            backdrop: 'static'
        });
    }

    // Setup form submission handler
    const form = document.getElementById('categoryForm');
    if (form) {
        form.addEventListener('submit', handleFormSubmit);
    }

    // Setup parent category change handler
    const parentSelect = document.getElementById('parentCategory');
    if (parentSelect) {
        parentSelect.addEventListener('change', updateBreadcrumbPreview);
    }

    // Setup name input for breadcrumb preview
    const nameInput = document.getElementById('categoryName');
    if (nameInput) {
        nameInput.addEventListener('input', updateBreadcrumbPreview);
    }

    // Setup CSRF token for all AJAX requests
    const token = document.querySelector('meta[name="csrf-token"]');
    if (token) {
        axios.defaults.headers.common['X-CSRF-TOKEN'] = token.getAttribute('content');
    }
}

// Open add category modal
function openAddModal() {
    editingCategoryId = null;
    parentCategoryId = null;
    resetForm();
    updateModalTitle('add');
    if (categoryModal) {
        categoryModal.show();
    }
}

// Add subcategory
function addSubcategory(parentId, parentName) {
    editingCategoryId = null;
    parentCategoryId = parentId;
    resetForm();
    updateModalTitle('add_sub', parentName);

    // Set parent category
    const parentSelect = document.getElementById('parentCategory');
    if (parentSelect) {
        parentSelect.value = parentId;
        updateBreadcrumbPreview();
    }

    if (categoryModal) {
        categoryModal.show();
    }
}

// Edit category
function editCategory(id, name, description, parentId) {
    editingCategoryId = id;
    parentCategoryId = null;
    resetForm();
    updateModalTitle('edit');

    // Populate form fields
    document.getElementById('categoryName').value = name;
    document.getElementById('categoryDescription').value = description || '';
    document.getElementById('parentCategory').value = parentId || '';

    updateBreadcrumbPreview();

    if (categoryModal) {
        categoryModal.show();
    }
}

// Update modal title and button text
function updateModalTitle(mode, parentName = '') {
    const modalTitle = document.getElementById('modalTitle');
    const submitText = document.getElementById('submitText');
    const submitIcon = document.getElementById('submitIcon');

    if (mode === 'add') {
        modalTitle.innerHTML = '<i class="fas fa-plus me-2"></i>Ajouter une Catégorie';
        submitText.textContent = 'Ajouter';
        submitIcon.className = 'fas fa-save me-1';
    } else if (mode === 'add_sub') {
        modalTitle.innerHTML = `<i class="fas fa-plus me-2"></i>Ajouter une Sous-catégorie à "${parentName}"`;
        submitText.textContent = 'Ajouter';
        submitIcon.className = 'fas fa-save me-1';
    } else {
        modalTitle.innerHTML = '<i class="fas fa-edit me-2"></i>Modifier la Catégorie';
        submitText.textContent = 'Mettre à jour';
        submitIcon.className = 'fas fa-save me-1';
    }
}

// Update breadcrumb preview
function updateBreadcrumbPreview() {
    const parentSelect = document.getElementById('parentCategory');
    const preview = document.getElementById('breadcrumbPreview');
    const container = document.getElementById('breadcrumbContainer');
    const nameInput = document.getElementById('categoryName');

    if (!parentSelect.value) {
        preview.style.display = 'none';
        return;
    }

    // Build breadcrumb path
    const selectedOption = parentSelect.selectedOptions[0];
    const parentText = selectedOption.textContent.trim();

    container.innerHTML = `
        <li class="breadcrumb-item">${parentText}</li>
        <li class="breadcrumb-item active">${nameInput.value || 'Nouvelle catégorie'}</li>
    `;

    preview.style.display = 'block';
}

// Reset form to initial state
function resetForm() {
    const form = document.getElementById('categoryForm');
    if (form) {
        form.reset();
    }
    clearFormErrors();
    resetSubmitButton();
    document.getElementById('breadcrumbPreview').style.display = 'none';
}

// Clear form validation errors
function clearFormErrors() {
    const fields = ['parentCategory', 'categoryName', 'categoryDescription'];
    const errors = ['parentError', 'nameError', 'descriptionError'];

    fields.forEach(fieldId => {
        const field = document.getElementById(fieldId);
        if (field) {
            field.classList.remove('is-invalid');
        }
    });

    errors.forEach(errorId => {
        const error = document.getElementById(errorId);
        if (error) {
            error.textContent = '';
        }
    });
}

// Reset submit button to normal state
function resetSubmitButton() {
    const submitBtn = document.getElementById('submitBtn');
    const spinner = submitBtn?.querySelector('.spinner-border');
    const submitText = document.getElementById('submitText');
    const submitIcon = document.getElementById('submitIcon');

    if (submitBtn) {
        submitBtn.disabled = false;
    }

    if (spinner) {
        spinner.classList.add('d-none');
    }

    if (submitIcon) {
        submitIcon.classList.remove('d-none');
    }

    if (submitText) {
        submitText.textContent = editingCategoryId ? 'Mettre à jour' : 'Ajouter';
    }
}

// Handle form submission
async function handleFormSubmit(e) {
    e.preventDefault();

    const submitBtn = document.getElementById('submitBtn');
    const spinner = submitBtn?.querySelector('.spinner-border');
    const submitText = document.getElementById('submitText');
    const submitIcon = document.getElementById('submitIcon');

    // Prevent double submission
    if (submitBtn?.disabled) {
        return;
    }

    // Show loading state
    if (submitBtn) submitBtn.disabled = true;
    if (spinner) spinner.classList.remove('d-none');
    if (submitIcon) submitIcon.classList.add('d-none');
    if (submitText) submitText.textContent = 'En cours...';

    clearFormErrors();

    // Get form data
    const name = document.getElementById('categoryName')?.value?.trim() || '';
    const description = document.getElementById('categoryDescription')?.value?.trim() || '';
    const parentId = document.getElementById('parentCategory')?.value || null;

    // Prepare request data
    const requestData = {
        name: name,
        description: description || null,
        parent_id: parentId
    };

    try {
        let url, method;

        if (editingCategoryId) {
            url = `/admin/categories/${editingCategoryId}`;
            method = 'PUT';
        } else {
            url = '/admin/categories';
            method = 'POST';
        }

        const response = await fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            },
            body: JSON.stringify(requestData)
        });

        const data = await response.json();

        if (response.ok && data.success) {
            showAlert(data.message, 'success');
            if (categoryModal) {
                categoryModal.hide();
            }
            // Reload page after a short delay
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            if (response.status === 422 && data.errors) {
                // Handle validation errors
                handleValidationErrors(data.errors);
            } else {
                showAlert(data.message || data.error || 'Erreur lors de la sauvegarde', 'error');
            }
        }
    } catch (error) {
        console.error('Request error:', error);
        showAlert('Erreur de connexion au serveur', 'error');
    } finally {
        resetSubmitButton();
    }
}

// Handle validation errors
function handleValidationErrors(errors) {
    Object.keys(errors).forEach(field => {
        if (field === 'name') {
            showFieldError('categoryName', 'nameError', errors[field][0]);
        } else if (field === 'description') {
            showFieldError('categoryDescription', 'descriptionError', errors[field][0]);
        } else if (field === 'parent_id') {
            showFieldError('parentCategory', 'parentError', errors[field][0]);
        }
    });
}

// Show field error
function showFieldError(fieldId, errorId, message) {
    const field = document.getElementById(fieldId);
    const errorDiv = document.getElementById(errorId);

    if (field) {
        field.classList.add('is-invalid');
    }
    if (errorDiv) {
        errorDiv.textContent = message;
    }
}

// Delete category
async function deleteCategory(id, name, productCount, childrenCount) {
    if (productCount > 0) {
        showAlert(`Impossible de supprimer la catégorie "${name}" car elle contient ${productCount} produit(s). Veuillez d'abord déplacer ou supprimer les produits associés.`, 'error');
        return;
    }

    if (childrenCount > 0) {
        showAlert(`Impossible de supprimer la catégorie "${name}" car elle contient ${childrenCount} sous-catégorie(s). Veuillez d'abord déplacer ou supprimer les sous-catégories.`, 'error');
        return;
    }

    if (!confirm(`Êtes-vous sûr de vouloir supprimer la catégorie "${name}" ?`)) {
        return;
    }

    try {
        const response = await fetch(`/admin/categories/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                'Accept': 'application/json'
            }
        });

        const data = await response.json();

        if (data.success) {
            showAlert(data.message, 'success');
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            showAlert(data.error || 'Erreur lors de la suppression', 'error');
        }
    } catch (error) {
        console.error('Delete error:', error);
        showAlert('Erreur de connexion au serveur', 'error');
    }
}

// Filter categories
function filterCategories() {
    const searchTerm = document.getElementById('searchInput')?.value?.toLowerCase()?.trim() || '';
    const table = document.getElementById('categoriesTable');
    const noResults = document.getElementById('noResults');
    const rows = table?.querySelectorAll('tbody tr') || [];
    let visibleRows = 0;

    rows.forEach(row => {
        const categoryName = row.getAttribute('data-category-name') || '';
        const description = row.querySelector('td:nth-child(2)')?.textContent?.toLowerCase() || '';

        if (!searchTerm || categoryName.includes(searchTerm) || description.includes(searchTerm)) {
            row.style.display = '';
            visibleRows++;
            if (searchTerm) {
                highlightSearchTerm(row, searchTerm);
            } else {
                removeHighlight(row);
            }
        } else {
            row.style.display = 'none';
            removeHighlight(row);
        }
    });

    // Show/hide no results message
    if (table && noResults) {
        if (visibleRows === 0 && searchTerm) {
            table.style.display = 'none';
            noResults.classList.remove('d-none');
        } else {
            table.style.display = '';
            noResults.classList.add('d-none');
        }
    }
}

// Clear search
function clearSearch() {
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.value = '';
        filterCategories();
    }
}

// Highlight search term
function highlightSearchTerm(row, searchTerm) {
    const nameCell = row.querySelector('.category-name');
    const descCell = row.querySelector('td:nth-child(2) small');

    [nameCell, descCell].forEach(cell => {
        if (cell) {
            const originalText = cell.getAttribute('data-original') || cell.textContent;
            cell.setAttribute('data-original', originalText);
            const regex = new RegExp(`(${escapeRegExp(searchTerm)})`, 'gi');
            cell.innerHTML = originalText.replace(regex, '<span class="search-highlight">$1</span>');
        }
    });
}

// Remove highlight
function removeHighlight(row) {
    const nameCell = row.querySelector('.category-name');
    const descCell = row.querySelector('td:nth-child(2) small');

    [nameCell, descCell].forEach(cell => {
        if (cell && cell.getAttribute('data-original')) {
            cell.innerHTML = cell.getAttribute('data-original');
        }
    });
}

// Show alert
function showAlert(message, type) {
    const alertContainer = document.getElementById('alertContainer');
    if (!alertContainer) return;

    const alertClass = type === 'error' ? 'alert-danger' : 'alert-success';
    const icon = type === 'error' ? 'fas fa-exclamation-circle' : 'fas fa-check-circle';

    alertContainer.innerHTML = `
        <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
            <i class="${icon} me-2"></i>
            ${escapeHtml(message)}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    `;

    // Auto-hide after 5 seconds
    setTimeout(() => {
        const alert = alertContainer.querySelector('.alert');
        if (alert) {
            alert.remove();
        }
    }, 5000);
}

// Utility functions
function escapeHtml(text) {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, m => map[m]);
}

function escapeRegExp(string) {
    return string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
}
</script>
@endpush
