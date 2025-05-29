@extends('layouts.app')

@section('title', 'Inscription Coopérative - Coopérative E-commerce')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <i class="fas fa-building fa-3x text-success mb-3"></i>
                        <h2 class="h3 mb-3">Inscription Coopérative</h2>
                        <p class="text-muted">Rejoignez notre plateforme coopérative</p>
                    </div>

                    @if($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            <strong>Erreurs de validation:</strong>
                            <ul class="mb-0 mt-2">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <!-- Registration Type Selection -->
                    <div class="card mb-4 bg-primary text-white">
                        <div class="card-body">
                            <h5 class="card-title mb-3">
                                <i class="fas fa-route me-2"></i>
                                Choisissez votre type d'inscription
                            </h5>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="registration_type" id="newCoop" value="new" checked>
                                        <label class="form-check-label" for="newCoop">
                                            <strong>Créer une nouvelle coopérative</strong><br>
                                            <small>Inscrivez votre coopérative sur la plateforme</small>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="registration_type" id="joinCoop" value="join">
                                        <label class="form-check-label" for="joinCoop">
                                            <strong>Rejoindre une coopérative existante</strong><br>
                                            <small>Devenez administrateur d'une coopérative déjà inscrite</small>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('coop.register') }}" id="coopRegisterForm" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="registration_type" id="registrationTypeInput" value="new">

                        <!-- Administrator Information -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h4 class="text-primary mb-3">
                                    <i class="fas fa-user-tie me-2"></i>
                                    Informations Personnelles
                                </h4>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="first_name" class="form-label">Prénom *</label>
                                <input type="text"
                                       class="form-control @error('first_name') is-invalid @enderror"
                                       id="first_name"
                                       name="first_name"
                                       value="{{ old('first_name') }}"
                                       required>
                                @error('first_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="last_name" class="form-label">Nom *</label>
                                <input type="text"
                                       class="form-control @error('last_name') is-invalid @enderror"
                                       id="last_name"
                                       name="last_name"
                                       value="{{ old('last_name') }}"
                                       required>
                                @error('last_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email Personnel *</label>
                                <input type="email"
                                       class="form-control @error('email') is-invalid @enderror"
                                       id="email"
                                       name="email"
                                       value="{{ old('email') }}"
                                       required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="phone" class="form-label">Téléphone</label>
                                <input type="tel"
                                       class="form-control @error('phone') is-invalid @enderror"
                                       id="phone"
                                       name="phone"
                                       value="{{ old('phone') }}"
                                       placeholder="+212 6XX XXX XXX">
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 mb-3">
                                <label for="address" class="form-label">Adresse Personnelle</label>
                                <textarea class="form-control @error('address') is-invalid @enderror"
                                          id="address"
                                          name="address"
                                          rows="2">{{ old('address') }}</textarea>
                                @error('address')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="password" class="form-label">Mot de Passe *</label>
                                <div class="input-group">
                                    <input type="password"
                                           class="form-control @error('password') is-invalid @enderror"
                                           id="password"
                                           name="password"
                                           required
                                           minlength="8">
                                    <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="password_confirmation" class="form-label">Confirmer le Mot de Passe *</label>
                                <div class="input-group">
                                    <input type="password"
                                           class="form-control"
                                           id="password_confirmation"
                                           name="password_confirmation"
                                           required>
                                    <button class="btn btn-outline-secondary" type="button" id="togglePasswordConfirm">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Join Existing Cooperative Section -->
                        <div id="joinCoopSection" style="display: none;">
                            <hr>
                            <div class="row mb-4">
                                <div class="col-12">
                                    <h4 class="text-success mb-3">
                                        <i class="fas fa-search me-2"></i>
                                        Rechercher une Coopérative
                                    </h4>
                                </div>

                                <div class="col-12 mb-3">
                                    <label for="coopSearch" class="form-label">
                                        <i class="fas fa-building me-1"></i>
                                        Rechercher par nom, secteur ou localisation
                                    </label>
                                    <div class="input-group">
                                        <input type="text"
                                               class="form-control"
                                               id="coopSearch"
                                               placeholder="Tapez pour rechercher des coopératives..."
                                               autocomplete="off">
                                        <button class="btn btn-outline-primary" type="button" id="searchBtn">
                                            <i class="fas fa-search"></i>
                                        </button>
                                    </div>
                                    <small class="form-text text-muted">
                                        Recherchez parmi les coopératives approuvées sur la plateforme
                                    </small>
                                </div>

                                <!-- Search Results -->
                                <div class="col-12">
                                    <div id="searchResults" class="search-results" style="display: none;">
                                        <div class="card">
                                            <div class="card-header">
                                                <h6 class="mb-0">
                                                    <i class="fas fa-list me-2"></i>
                                                    Résultats de la recherche
                                                </h6>
                                            </div>
                                            <div class="card-body p-0">
                                                <div id="cooperativesList"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Selected Cooperative -->
                                <div class="col-12">
                                    <div id="selectedCooperative" style="display: none;">
                                        <div class="alert alert-success">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <strong>Coopérative sélectionnée:</strong>
                                                    <span id="selectedCoopName"></span>
                                                </div>
                                                <button type="button" class="btn btn-sm btn-outline-danger" id="clearSelection">
                                                    <i class="fas fa-times"></i> Changer
                                                </button>
                                            </div>
                                        </div>
                                        <input type="hidden" name="cooperative_id" id="cooperativeId">
                                    </div>
                                </div>

                                <!-- Message for Join Request -->
                                <div class="col-12 mb-3" id="joinMessage" style="display: none;">
                                    <label for="message" class="form-label">
                                        <i class="fas fa-comment me-1"></i>
                                        Message pour l'administrateur (optionnel)
                                    </label>
                                    <textarea class="form-control @error('message') is-invalid @enderror"
                                              id="message"
                                              name="message"
                                              rows="3"
                                              placeholder="Présentez-vous et expliquez pourquoi vous souhaitez rejoindre cette coopérative...">{{ old('message') }}</textarea>
                                    <small class="form-text text-muted">
                                        Ce message sera envoyé à l'administrateur actuel de la coopérative
                                    </small>
                                    @error('message')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- New Cooperative Section -->
                        <div id="newCoopSection">
                            <hr>
                            <div class="row mb-4">
                                <div class="col-12">
                                    <h4 class="text-success mb-3">
                                        <i class="fas fa-building me-2"></i>
                                        Informations de la Coopérative
                                    </h4>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="coop_name" class="form-label">Nom de la Coopérative *</label>
                                    <input type="text"
                                           class="form-control @error('coop_name') is-invalid @enderror"
                                           id="coop_name"
                                           name="coop_name"
                                           value="{{ old('coop_name') }}">
                                    @error('coop_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="coop_email" class="form-label">Email de la Coopérative *</label>
                                    <input type="email"
                                           class="form-control @error('coop_email') is-invalid @enderror"
                                           id="coop_email"
                                           name="coop_email"
                                           value="{{ old('coop_email') }}">
                                    <small class="form-text text-muted">
                                        Différent de votre email personnel
                                    </small>
                                    @error('coop_email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Logo Upload Section -->
                                <div class="col-12 mb-4">
                                    <label for="logo" class="form-label">
                                        <i class="fas fa-image me-2"></i>
                                        Logo de la Coopérative
                                    </label>
                                    <div class="card bg-light">
                                        <div class="card-body">
                                            <div class="row align-items-center">
                                                <div class="col-md-3 text-center">
                                                    <div id="logoPreview" class="logo-preview mb-3">
                                                        <i class="fas fa-image fa-4x text-muted"></i>
                                                        <p class="text-muted small mt-2">Aperçu du logo</p>
                                                    </div>
                                                </div>
                                                <div class="col-md-9">
                                                    <input type="file"
                                                           class="form-control @error('logo') is-invalid @enderror"
                                                           id="logo"
                                                           name="logo"
                                                           accept="image/*">
                                                    <small class="form-text text-muted">
                                                        <i class="fas fa-info-circle me-1"></i>
                                                        Formats acceptés: JPG, PNG, GIF. Taille maximale: 2MB. Dimensions recommandées: 200x200px.
                                                    </small>
                                                    @error('logo')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="coop_phone" class="form-label">Téléphone de la Coopérative *</label>
                                    <input type="tel"
                                           class="form-control @error('coop_phone') is-invalid @enderror"
                                           id="coop_phone"
                                           name="coop_phone"
                                           value="{{ old('coop_phone') }}">
                                    @error('coop_phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="legal_status" class="form-label">Statut Juridique *</label>
                                    <select class="form-control @error('legal_status') is-invalid @enderror"
                                            id="legal_status"
                                            name="legal_status">
                                        <option value="">Sélectionner...</option>
                                        <option value="Coopérative Agricole" {{ old('legal_status') == 'Coopérative Agricole' ? 'selected' : '' }}>
                                            Coopérative Agricole
                                        </option>
                                        <option value="Coopérative Artisanale" {{ old('legal_status') == 'Coopérative Artisanale' ? 'selected' : '' }}>
                                            Coopérative Artisanale
                                        </option>
                                        <option value="Coopérative de Services" {{ old('legal_status') == 'Coopérative de Services' ? 'selected' : '' }}>
                                            Coopérative de Services
                                        </option>
                                        <option value="Autre" {{ old('legal_status') == 'Autre' ? 'selected' : '' }}>
                                            Autre
                                        </option>
                                    </select>
                                    @error('legal_status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="date_created" class="form-label">Date de Création *</label>
                                    <input type="date"
                                           class="form-control @error('date_created') is-invalid @enderror"
                                           id="date_created"
                                           name="date_created"
                                           value="{{ old('date_created') }}">
                                    @error('date_created')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="sector_of_activity" class="form-label">Secteur d'Activité *</label>
                                    <select class="form-control @error('sector_of_activity') is-invalid @enderror"
                                            id="sector_of_activity"
                                            name="sector_of_activity">
                                        <option value="">Sélectionner...</option>
                                        <option value="Agriculture" {{ old('sector_of_activity') == 'Agriculture' ? 'selected' : '' }}>
                                            Agriculture
                                        </option>
                                        <option value="Artisanat" {{ old('sector_of_activity') == 'Artisanat' ? 'selected' : '' }}>
                                            Artisanat
                                        </option>
                                        <option value="Textile" {{ old('sector_of_activity') == 'Textile' ? 'selected' : '' }}>
                                            Textile
                                        </option>
                                        <option value="Cosmétiques" {{ old('sector_of_activity') == 'Cosmétiques' ? 'selected' : '' }}>
                                            Cosmétiques
                                        </option>
                                        <option value="Alimentaire" {{ old('sector_of_activity') == 'Alimentaire' ? 'selected' : '' }}>
                                            Alimentaire
                                        </option>
                                        <option value="Autre" {{ old('sector_of_activity') == 'Autre' ? 'selected' : '' }}>
                                            Autre
                                        </option>
                                    </select>
                                    @error('sector_of_activity')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12 mb-3">
                                    <label for="coop_address" class="form-label">Adresse de la Coopérative *</label>
                                    <textarea class="form-control @error('coop_address') is-invalid @enderror"
                                              id="coop_address"
                                              name="coop_address"
                                              rows="3">{{ old('coop_address') }}</textarea>
                                    @error('coop_address')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12 mb-3">
                                    <label for="description" class="form-label">Description de la Coopérative</label>
                                    <textarea class="form-control @error('description') is-invalid @enderror"
                                              id="description"
                                              name="description"
                                              rows="4"
                                              placeholder="Décrivez les activités et objectifs de votre coopérative...">{{ old('description') }}</textarea>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="terms" required>
                                <label class="form-check-label" for="terms">
                                    J'accepte les <a href="#" class="text-primary">conditions d'utilisation</a>
                                    et la <a href="#" class="text-primary">politique de confidentialité</a> *
                                </label>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-success w-100" id="registerBtn">
                            <span class="loading spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                            <span class="btn-text">
                                <i class="fas fa-building me-1"></i>
                                <span id="submitText">Soumettre la Demande d'Inscription</span>
                            </span>
                        </button>
                    </form>

                    <div class="text-center mt-4">
                        <p class="mb-2">Déjà un compte?</p>
                        <a href="{{ route('login') }}" class="btn btn-outline-primary">
                            <i class="fas fa-sign-in-alt me-1"></i>
                            Se Connecter
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Cooperative Details Modal -->
<div class="modal fade" id="cooperativeModal" tabindex="-1" aria-labelledby="cooperativeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="cooperativeModalLabel">
                    <i class="fas fa-building me-2"></i>
                    Détails de la Coopérative
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="cooperativeDetails">
                    <!-- Content will be loaded here -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                <button type="button" class="btn btn-success" id="selectCooperativeBtn">
                    <i class="fas fa-check me-1"></i>
                    Sélectionner cette coopérative
                </button>
            </div>
        </div>
    </div>
</div>

<style>
.logo-preview {
    width: 120px;
    height: 120px;
    border: 2px dashed #ddd;
    border-radius: 10px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    margin: 0 auto;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.logo-preview:hover {
    border-color: #007bff;
    background-color: #f8f9fa;
}

.logo-preview img {
    max-width: 100%;
    max-height: 100%;
    border-radius: 8px;
    object-fit: cover;
}

.logo-preview.has-image {
    border-style: solid;
    border-color: #28a745;
}

.logo-preview .clear-btn {
    position: absolute;
    top: 5px;
    right: 5px;
    width: 24px;
    height: 24px;
    border-radius: 50%;
    background: rgba(220, 53, 69, 0.9);
    color: white;
    border: none;
    font-size: 12px;
    cursor: pointer;
    display: none;
}

.logo-preview.has-image .clear-btn {
    display: block;
}

.search-results {
    max-height: 400px;
    overflow-y: auto;
}

.cooperative-item {
    border-bottom: 1px solid #eee;
    padding: 15px;
    cursor: pointer;
    transition: background-color 0.2s;
}

.cooperative-item:hover {
    background-color: #f8f9fa;
}

.cooperative-item:last-child {
    border-bottom: none;
}

.cooperative-logo {
    width: 50px;
    height: 50px;
    object-fit: cover;
    border-radius: 8px;
    border: 2px solid #dee2e6;
}

.cooperative-logo-placeholder {
    width: 50px;
    height: 50px;
    background: #f8f9fa;
    border: 2px dashed #dee2e6;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #6c757d;
}

.form-check-input:checked ~ .form-check-label {
    color: #0d6efd;
    font-weight: 500;
}
</style>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        let searchTimeout;
        let selectedCooperativeData = null;
        const cooperativeModal = new bootstrap.Modal(document.getElementById('cooperativeModal'));

        // Toggle registration type
        const newCoopRadio = document.getElementById('newCoop');
        const joinCoopRadio = document.getElementById('joinCoop');
        const newCoopSection = document.getElementById('newCoopSection');
        const joinCoopSection = document.getElementById('joinCoopSection');
        const registrationTypeInput = document.getElementById('registrationTypeInput');
        const submitText = document.getElementById('submitText');

        function toggleRegistrationType() {
            if (joinCoopRadio.checked) {
                newCoopSection.style.display = 'none';
                joinCoopSection.style.display = 'block';
                registrationTypeInput.value = 'join';
                submitText.textContent = 'Envoyer la Demande d\'Adhésion';

                // Remove required attributes from new coop fields
                document.querySelectorAll('#newCoopSection input[required], #newCoopSection select[required], #newCoopSection textarea[required]').forEach(input => {
                    input.removeAttribute('required');
                });
            } else {
                newCoopSection.style.display = 'block';
                joinCoopSection.style.display = 'none';
                registrationTypeInput.value = 'new';
                submitText.textContent = 'Soumettre la Demande d\'Inscription';

                // Add required attributes back to new coop fields
                document.querySelectorAll('#newCoopSection input[data-required], #newCoopSection select[data-required], #newCoopSection textarea[data-required]').forEach(input => {
                    input.setAttribute('required', 'required');
                });
            }
        }

        // Add data-required attributes for easy management
        document.querySelectorAll('#newCoopSection input[required], #newCoopSection select[required], #newCoopSection textarea[required]').forEach(input => {
            input.setAttribute('data-required', 'true');
        });

        newCoopRadio.addEventListener('change', toggleRegistrationType);
        joinCoopRadio.addEventListener('change', toggleRegistrationType);

        // Cooperative search functionality
        const coopSearch = document.getElementById('coopSearch');
        const searchBtn = document.getElementById('searchBtn');
        const searchResults = document.getElementById('searchResults');
        const cooperativesList = document.getElementById('cooperativesList');

        function searchCooperatives(query = '') {
            fetch(`{{ route('coop.search') }}?search=${encodeURIComponent(query)}&limit=10`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        displayCooperatives(data.cooperatives);
                        searchResults.style.display = 'block';
                    }
                })
                .catch(error => {
                    console.error('Search error:', error);
                    cooperativesList.innerHTML = '<div class="p-3 text-center text-danger">Erreur lors de la recherche</div>';
                });
        }

        function displayCooperatives(cooperatives) {
            if (cooperatives.length === 0) {
                cooperativesList.innerHTML = '<div class="p-3 text-center text-muted">Aucune coopérative trouvée</div>';
                return;
            }

            cooperativesList.innerHTML = cooperatives.map(coop => `
                <div class="cooperative-item" data-coop-id="${coop.id}" data-coop-name="${coop.name}">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <div class="cooperative-logo-placeholder">
                                <i class="fas fa-building"></i>
                            </div>
                        </div>
                        <div class="col">
                            <h6 class="mb-1">${coop.name}</h6>
                            <p class="mb-1 text-muted small">
                                <i class="fas fa-industry me-1"></i>
                                ${coop.sector_of_activity}
                            </p>
                            <p class="mb-0 text-muted small">
                                <i class="fas fa-map-marker-alt me-1"></i>
                                ${coop.address}
                            </p>
                        </div>
                        <div class="col-auto">
                            <button type="button" class="btn btn-outline-primary btn-sm view-details" data-coop-id="${coop.id}">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                </div>
            `).join('');

            // Add event listeners
            document.querySelectorAll('.view-details').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    viewCooperativeDetails(this.dataset.coopId);
                });
            });

            document.querySelectorAll('.cooperative-item').forEach(item => {
                item.addEventListener('click', function() {
                    viewCooperativeDetails(this.dataset.coopId);
                });
            });
        }

        function viewCooperativeDetails(coopId) {
            fetch(`{{ url('/cooperatives') }}/${coopId}/details`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        displayCooperativeDetails(data.cooperative);
                        selectedCooperativeData = data.cooperative;
                        cooperativeModal.show();
                    }
                })
                .catch(error => {
                    console.error('Details error:', error);
                    alert('Erreur lors du chargement des détails');
                });
        }

        function displayCooperativeDetails(coop) {
            const logoHtml = coop.logo_url
                ? `<img src="${coop.logo_url}" alt="Logo ${coop.name}" class="img-fluid rounded" style="max-height: 200px;">`
                : `<div class="text-center p-4 bg-light rounded">
                     <i class="fas fa-building fa-4x text-muted"></i>
                     <p class="text-muted mt-2">Aucun logo</p>
                   </div>`;

            document.getElementById('cooperativeDetails').innerHTML = `
                <div class="row">
                    <div class="col-md-4 text-center mb-3">
                        ${logoHtml}
                    </div>
                    <div class="col-md-8">
                        <h4>${coop.name}</h4>
                        <div class="mb-3">
                            <p><strong>Secteur d'activité:</strong> ${coop.sector_of_activity}</p>
                            <p><strong>Statut juridique:</strong> ${coop.legal_status}</p>
                            <p><strong>Date de création:</strong> ${coop.date_created}</p>
                            <p><strong>Email:</strong> ${coop.email}</p>
                            <p><strong>Téléphone:</strong> ${coop.phone}</p>
                            <p><strong>Adresse:</strong> ${coop.address}</p>
                        </div>
                        ${coop.description ? `
                            <div class="mb-3">
                                <strong>Description:</strong>
                                <p class="text-muted">${coop.description}</p>
                            </div>
                        ` : ''}
                    </div>
                </div>
            `;
        }

        // Select cooperative
        document.getElementById('selectCooperativeBtn').addEventListener('click', function() {
            if (selectedCooperativeData) {
                selectCooperative(selectedCooperativeData);
                cooperativeModal.hide();
            }
        });

        function selectCooperative(coop) {
            document.getElementById('cooperativeId').value = coop.id;
            document.getElementById('selectedCoopName').textContent = coop.name;
            document.getElementById('selectedCooperative').style.display = 'block';
            document.getElementById('joinMessage').style.display = 'block';
            searchResults.style.display = 'none';
            coopSearch.value = '';
        }

        // Clear selection
        document.getElementById('clearSelection').addEventListener('click', function() {
            document.getElementById('selectedCooperative').style.display = 'none';
            document.getElementById('joinMessage').style.display = 'none';
            selectedCooperativeData = null;
        });

        // Search events
        coopSearch.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                if (this.value.trim().length >= 2) {
                    searchCooperatives(this.value.trim());
                } else if (this.value.trim().length === 0) {
                    searchResults.style.display = 'none';
                }
            }, 300);
        });

        searchBtn.addEventListener('click', function() {
            const query = coopSearch.value.trim();
            if (query.length >= 2) {
                searchCooperatives(query);
            }
        });

        // Load initial cooperatives
        if (joinCoopRadio.checked) {
            searchCooperatives();
        }

        // Toggle password visibility
        function setupPasswordToggle(toggleId, inputId) {
            const toggle = document.getElementById(toggleId);
            const input = document.getElementById(inputId);

            toggle.addEventListener('click', function() {
                const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
                input.setAttribute('type', type);

                const icon = this.querySelector('i');
                icon.classList.toggle('fa-eye');
                icon.classList.toggle('fa-eye-slash');
            });
        }

        setupPasswordToggle('togglePassword', 'password');
        setupPasswordToggle('togglePasswordConfirm', 'password_confirmation');

        // Logo preview functionality
        const logoInput = document.getElementById('logo');
        const logoPreview = document.getElementById('logoPreview');

        logoInput.addEventListener('change', function(e) {
            const file = e.target.files[0];

            if (file) {
                if (file.size > 2 * 1024 * 1024) {
                    alert('Le fichier est trop volumineux. Taille maximale: 2MB');
                    logoInput.value = '';
                    return;
                }

                if (!file.type.match('image.*')) {
                    alert('Veuillez sélectionner un fichier image valide');
                    logoInput.value = '';
                    return;
                }

                const reader = new FileReader();
                reader.onload = function(e) {
                    logoPreview.innerHTML = `
                        <img src="${e.target.result}" style="width: 100%; height: 100%; object-fit: cover;">
                        <button type="button" class="clear-btn" onclick="clearLogo()">
                            <i class="fas fa-times"></i>
                        </button>
                    `;
                    logoPreview.classList.add('has-image');
                };
                reader.readAsDataURL(file);
            }
        });

        // Form submission
        const form = document.getElementById('coopRegisterForm');
        const submitBtn = document.getElementById('registerBtn');

        form.addEventListener('submit', function(e) {
            // Validate join request
            if (joinCoopRadio.checked) {
                if (!document.getElementById('cooperativeId').value) {
                    e.preventDefault();
                    alert('Veuillez sélectionner une coopérative');
                    return;
                }
            }

            if (form.checkValidity()) {
                showLoading(submitBtn);
            }
        });

        // Email validation
        const personalEmail = document.getElementById('email');
        const coopEmail = document.getElementById('coop_email');

        coopEmail.addEventListener('blur', function() {
            if (this.value && personalEmail.value && this.value === personalEmail.value) {
                this.setCustomValidity('L\'email de la coopérative doit être différent de votre email personnel');
                this.classList.add('is-invalid');
            } else {
                this.setCustomValidity('');
                this.classList.remove('is-invalid');
            }
        });

        // Password matching
        const password = document.getElementById('password');
        const passwordConfirm = document.getElementById('password_confirmation');

        passwordConfirm.addEventListener('input', function() {
            if (this.value !== password.value) {
                this.setCustomValidity('Les mots de passe ne correspondent pas');
                this.classList.add('is-invalid');
            } else {
                this.setCustomValidity('');
                this.classList.remove('is-invalid');
            }
        });

        function showLoading(button) {
            const loading = button.querySelector('.loading');
            const text = button.querySelector('.btn-text');
            if (loading && text) {
                loading.style.display = 'inline-block';
                text.style.display = 'none';
                button.disabled = true;
            }
        }
    });

    function clearLogo() {
        const logoInput = document.getElementById('logo');
        const logoPreview = document.getElementById('logoPreview');

        logoInput.value = '';
        logoPreview.innerHTML = `
            <i class="fas fa-image fa-4x text-muted"></i>
            <p class="text-muted small mt-2">Aperçu du logo</p>
        `;
        logoPreview.classList.remove('has-image');
    }
</script>
@endpush
