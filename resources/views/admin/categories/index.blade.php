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
                        <i class="fas fa-tags me-2"></i>
                        Gestion des Catégories
                    </h1>
                    <p class="text-muted">Gérer les catégories de produits de la plateforme</p>
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
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Catégories
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="totalCategories">{{ $categories->total() }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-tags fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Catégories avec Produits
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $categories->where('products_count', '>', 0)->count() }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-box fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Catégories Vides
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $categories->where('products_count', 0)->count() }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-empty-set fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Total Produits
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $categories->sum('products_count') }}
                            </div>
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
                        Liste des Catégories
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
                                <th>
                                    <i class="fas fa-tag me-1"></i>
                                    Nom
                                </th>
                                <th>
                                    <i class="fas fa-align-left me-1"></i>
                                    Description
                                </th>
                                <th>
                                    <i class="fas fa-box me-1"></i>
                                    Nombre de Produits
                                </th>
                                <th>
                                    <i class="fas fa-calendar me-1"></i>
                                    Date de Création
                                </th>
                                <th>
                                    <i class="fas fa-cogs me-1"></i>
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($categories as $category)
                            <tr data-category-name="{{ strtolower($category->name) }}">
                                <td>
                                    <strong class="text-primary">{{ $category->name }}</strong>
                                </td>
                                <td>
                                    <span class="text-muted">
                                        {{ $category->description ?: 'Aucune description' }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $category->products_count > 0 ? 'primary' : 'secondary' }}">
                                        {{ $category->products_count }} produit(s)
                                    </span>
                                </td>
                                <td>
                                    <div>
                                        <strong>{{ $category->created_at->format('d/m/Y') }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $category->created_at->format('H:i') }}</small>
                                    </div>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-warning btn-sm"
                                                onclick="editCategory({{ $category->id }}, '{{ addslashes($category->name) }}', '{{ addslashes($category->description ?? '') }}')"
                                                title="Modifier cette catégorie">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button type="button" class="btn btn-danger btn-sm"
                                                onclick="deleteCategory({{ $category->id }}, '{{ addslashes($category->name) }}', {{ $category->products_count }})"
                                                title="Supprimer cette catégorie">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

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

                <!-- Pagination -->
                <div class="d-flex justify-content-center mt-3">
                    {{ $categories->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-tags fa-4x text-muted mb-3"></i>
                    <h4>Aucune catégorie trouvée</h4>
                    <p class="text-muted">Commencez par ajouter votre première catégorie de produits.</p>
                    <button class="btn btn-primary" onclick="openAddModal()">
                        <i class="fas fa-plus me-2"></i>
                        Ajouter une Catégorie
                    </button>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Add/Edit Category Modal -->
<div class="modal fade" id="categoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">
                    <i class="fas fa-plus me-2"></i>
                    Ajouter une Catégorie
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="categoryForm">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="categoryName" class="form-label">
                            <i class="fas fa-tag me-1"></i>
                            Nom de la Catégorie *
                        </label>
                        <input type="text" class="form-control" id="categoryName" name="name" required>
                        <div class="invalid-feedback" id="nameError"></div>
                    </div>
                    <div class="mb-3">
                        <label for="categoryDescription" class="form-label">
                            <i class="fas fa-align-left me-1"></i>
                            Description
                        </label>
                        <textarea class="form-control" id="categoryDescription" name="description" rows="3"
                                  placeholder="Description optionnelle de la catégorie"></textarea>
                        <div class="invalid-feedback" id="descriptionError"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>
                        Annuler
                    </button>
                    <button type="submit" class="btn btn-primary" id="submitBtn">
                        <span class="spinner-border spinner-border-sm me-2 d-none" role="status"></span>
                        <i class="fas fa-save me-1"></i>
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
.border-left-primary { border-left: 0.25rem solid #4e73df !important; }
.border-left-success { border-left: 0.25rem solid #1cc88a !important; }
.border-left-info { border-left: 0.25rem solid #36b9cc !important; }
.border-left-warning { border-left: 0.25rem solid #f6c23e !important; }

.table-hover tbody tr:hover {
    background-color: rgba(0,0,0,.075);
}

.btn-group .btn {
    border-radius: 0.25rem !important;
    margin-right: 3px;
}

.btn-group .btn:last-child {
    margin-right: 0;
}

.search-highlight {
    background-color: #fff3cd;
    font-weight: bold;
}
</style>
@endpush

@push('scripts')
<script>
let editingCategoryId = null;
let categoryModal = null;

// Setup CSRF token and initialize modal
document.addEventListener('DOMContentLoaded', function() {
    // Initialize modal instance once
    categoryModal = new bootstrap.Modal(document.getElementById('categoryModal'), {
        keyboard: false,
        backdrop: 'static'
    });

    // Setup form submission handler
    setupFormHandler();
});

// Setup form submission handler
function setupFormHandler() {
    const form = document.getElementById('categoryForm');
    if (form) {
        form.addEventListener('submit', handleFormSubmit);
    }
}

// Open add category modal
function openAddModal() {
    editingCategoryId = null;
    resetForm();
    document.getElementById('modalTitle').innerHTML = '<i class="fas fa-plus me-2"></i>Ajouter une Catégorie';
    document.getElementById('submitText').textContent = 'Ajouter';
    categoryModal.show();
}

// Edit category
function editCategory(id, name, description) {
    editingCategoryId = id;
    resetForm();
    document.getElementById('modalTitle').innerHTML = '<i class="fas fa-edit me-2"></i>Modifier la Catégorie';
    document.getElementById('submitText').textContent = 'Mettre à jour';
    document.getElementById('categoryName').value = name;
    document.getElementById('categoryDescription').value = description || '';
    categoryModal.show();
}

// Reset form to initial state
function resetForm() {
    const form = document.getElementById('categoryForm');
    form.reset();
    clearFormErrors();
    resetSubmitButton();
}

// Reset submit button to initial state
function resetSubmitButton() {
    const submitBtn = document.getElementById('submitBtn');
    const spinner = submitBtn.querySelector('.spinner-border');
    const submitText = document.getElementById('submitText');

    spinner.classList.add('d-none');
    submitBtn.disabled = false;

    if (editingCategoryId) {
        submitText.textContent = 'Mettre à jour';
    } else {
        submitText.textContent = 'Ajouter';
    }
}

// Handle form submission
async function handleFormSubmit(e) {
    e.preventDefault();

    const submitBtn = document.getElementById('submitBtn');
    const spinner = submitBtn.querySelector('.spinner-border');
    const submitText = document.getElementById('submitText');

    // Prevent double submission
    if (submitBtn.disabled) {
        return;
    }

    // Show loading state
    spinner.classList.remove('d-none');
    submitText.textContent = 'En cours...';
    submitBtn.disabled = true;
    clearFormErrors();

    // Get form data
    const name = document.getElementById('categoryName').value.trim();
    const description = document.getElementById('categoryDescription').value.trim();

    // Prepare request data
    const requestData = {
        name: name,
        description: description,
        _token: document.querySelector('meta[name="csrf-token"]').getAttribute('content')
    };

    try {
        let url, method;

        if (editingCategoryId) {
            url = `/admin/categories/${editingCategoryId}`;
            method = 'PUT';
            requestData._method = 'PUT';
        } else {
            url = '/admin/categories';
            method = 'POST';
        }

        const response = await fetch(url, {
            method: 'POST', // Always use POST, Laravel will handle _method
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': requestData._token
            },
            body: JSON.stringify(requestData)
        });

        const data = await response.json();

        if (response.ok && data.success) {
            showAlert(data.message, 'success');
            categoryModal.hide();
            // Reload page to show updated data
            setTimeout(() => window.location.reload(), 1000);
        } else {
            if (response.status === 422 && data.errors) {
                // Handle validation errors
                Object.keys(data.errors).forEach(field => {
                    if (field === 'name') {
                        showFieldError('categoryName', 'nameError', data.errors[field][0]);
                    } else if (field === 'description') {
                        showFieldError('categoryDescription', 'descriptionError', data.errors[field][0]);
                    }
                });
            } else {
                showAlert(data.message || data.error || 'Erreur lors de la sauvegarde', 'error');
            }
        }
    } catch (error) {
        console.error('Erreur:', error);
        showAlert('Erreur de connexion au serveur', 'error');
    } finally {
        // Reset button state
        resetSubmitButton();
    }
}

// Delete category
async function deleteCategory(id, name, productCount) {
    if (productCount > 0) {
        showAlert(`Impossible de supprimer la catégorie "${name}" car elle contient ${productCount} produit(s). Veuillez d'abord déplacer ou supprimer les produits associés.`, 'error');
        return;
    }

    if (!confirm(`Êtes-vous sûr de vouloir supprimer la catégorie "${name}" ?`)) {
        return;
    }

    try {
        const response = await fetch(`/admin/categories/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            }
        });

        const data = await response.json();

        if (data.success) {
            showAlert(data.message, 'success');
            // Reload page to show updated data
            setTimeout(() => window.location.reload(), 1000);
        } else {
            showAlert(data.error, 'error');
        }
    } catch (error) {
        console.error('Erreur:', error);
        showAlert('Erreur de connexion au serveur', 'error');
    }
}

// Filter categories
function filterCategories() {
    const searchTerm = document.getElementById('searchInput').value.toLowerCase().trim();
    const table = document.getElementById('categoriesTable');
    const noResults = document.getElementById('noResults');
    const rows = table ? table.querySelectorAll('tbody tr') : [];
    let visibleRows = 0;

    rows.forEach(row => {
        const categoryName = row.getAttribute('data-category-name');
        const description = row.querySelector('td:nth-child(2)').textContent.toLowerCase();

        if (!searchTerm || categoryName.includes(searchTerm) || description.includes(searchTerm)) {
            row.style.display = '';
            visibleRows++;

            // Highlight search term
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
    document.getElementById('searchInput').value = '';
    filterCategories();
}

// Highlight search term
function highlightSearchTerm(row, searchTerm) {
    const nameCell = row.querySelector('td:first-child strong');
    const descCell = row.querySelector('td:nth-child(2) span');

    if (nameCell) {
        const originalText = nameCell.getAttribute('data-original') || nameCell.textContent;
        nameCell.setAttribute('data-original', originalText);
        const regex = new RegExp(`(${escapeRegExp(searchTerm)})`, 'gi');
        nameCell.innerHTML = originalText.replace(regex, '<span class="search-highlight">$1</span>');
    }

    if (descCell) {
        const originalText = descCell.getAttribute('data-original') || descCell.textContent;
        descCell.setAttribute('data-original', originalText);
        const regex = new RegExp(`(${escapeRegExp(searchTerm)})`, 'gi');
        descCell.innerHTML = originalText.replace(regex, '<span class="search-highlight">$1</span>');
    }
}

// Remove highlight
function removeHighlight(row) {
    const nameCell = row.querySelector('td:first-child strong');
    const descCell = row.querySelector('td:nth-child(2) span');

    if (nameCell && nameCell.getAttribute('data-original')) {
        nameCell.innerHTML = nameCell.getAttribute('data-original');
    }

    if (descCell && descCell.getAttribute('data-original')) {
        descCell.innerHTML = descCell.getAttribute('data-original');
    }
}

// Show alert
function showAlert(message, type) {
    const alertContainer = document.getElementById('alertContainer');
    const alertClass = type === 'error' ? 'alert-danger' : 'alert-success';
    const icon = type === 'error' ? 'fas fa-exclamation-circle' : 'fas fa-check-circle';

    alertContainer.innerHTML = `
        <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
            <i class="${icon} me-2"></i>
            ${escapeHtml(message)}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
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

// Show field error
function showFieldError(fieldId, errorId, message) {
    const field = document.getElementById(fieldId);
    const errorDiv = document.getElementById(errorId);

    field.classList.add('is-invalid');
    errorDiv.textContent = message;
}

// Clear form errors
function clearFormErrors() {
    const fields = ['categoryName', 'categoryDescription'];
    const errors = ['nameError', 'descriptionError'];

    fields.forEach(fieldId => {
        document.getElementById(fieldId).classList.remove('is-invalid');
    });

    errors.forEach(errorId => {
        document.getElementById(errorId).textContent = '';
    });
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
