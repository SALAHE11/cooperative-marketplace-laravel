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
                        <p class="text-muted">Inscrivez votre coopérative sur notre plateforme</p>
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

                    <form method="POST" action="{{ route('coop.register') }}" id="coopRegisterForm" enctype="multipart/form-data">
                        @csrf

                        <!-- Administrator Information -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h4 class="text-primary mb-3">
                                    <i class="fas fa-user-tie me-2"></i>
                                    Informations de l'Administrateur
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

                        <hr>

                        <!-- Cooperative Information -->
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
                                       value="{{ old('coop_name') }}"
                                       required>
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
                                       value="{{ old('coop_email') }}"
                                       required>
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
                                       value="{{ old('coop_phone') }}"
                                       required>
                                @error('coop_phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="legal_status" class="form-label">Statut Juridique *</label>
                                <select class="form-control @error('legal_status') is-invalid @enderror"
                                        id="legal_status"
                                        name="legal_status"
                                        required>
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
                                       value="{{ old('date_created') }}"
                                       required>
                                @error('date_created')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="sector_of_activity" class="form-label">Secteur d'Activité *</label>
                                <select class="form-control @error('sector_of_activity') is-invalid @enderror"
                                        id="sector_of_activity"
                                        name="sector_of_activity"
                                        required>
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
                                          rows="3"
                                          required>{{ old('coop_address') }}</textarea>
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
                                Soumettre la Demande d'Inscription
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
</style>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
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
                // Validate file size (2MB)
                if (file.size > 2 * 1024 * 1024) {
                    alert('Le fichier est trop volumineux. Taille maximale: 2MB');
                    logoInput.value = '';
                    return;
                }

                // Validate file type
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

        // Form submission with loading state
        const form = document.getElementById('coopRegisterForm');
        const submitBtn = document.getElementById('registerBtn');

        form.addEventListener('submit', function(e) {
            if (form.checkValidity()) {
                showLoading(submitBtn);
            }
        });

        // Email validation - ensure cooperative email is different from personal email
        const personalEmail = document.getElementById('email');
        const coopEmail = document.getElementById('coop_email');

        coopEmail.addEventListener('blur', function() {
            if (this.value && personalEmail.value && this.value === personalEmail.value) {
                this.setCustomValidity('L\'email de la coopérative doit être différent de votre email personnel');
                this.classList.add('is-invalid');
                showFieldError(this, 'L\'email de la coopérative doit être différent de votre email personnel');
            } else {
                this.setCustomValidity('');
                this.classList.remove('is-invalid');
                clearErrors(this);
            }
        });

        // Real-time password matching
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
