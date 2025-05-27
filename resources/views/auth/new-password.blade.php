@extends('layouts.app')

@section('title', 'Nouveau mot de passe - Coopérative E-commerce')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <i class="fas fa-lock-open fa-3x text-success mb-3"></i>
                        <h2 class="h3 mb-3">Nouveau mot de passe</h2>
                        <p class="text-muted">
                            Choisissez un nouveau mot de passe sécurisé pour<br>
                            <strong>{{ $email }}</strong>
                        </p>
                    </div>

                    @if($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            @foreach($errors->all() as $error)
                                {{ $error }}<br>
                            @endforeach
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('password.new.submit') }}" id="newPasswordForm">
                        @csrf
                        <input type="hidden" name="email" value="{{ $email }}">

                        <div class="mb-3">
                            <label for="password" class="form-label">
                                <i class="fas fa-lock me-1"></i>
                                Nouveau mot de passe
                            </label>
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
                            <small class="form-text text-muted">
                                Minimum 8 caractères
                            </small>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="password_confirmation" class="form-label">
                                <i class="fas fa-lock me-1"></i>
                                Confirmer le nouveau mot de passe
                            </label>
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

                        <button type="submit" class="btn btn-success w-100 mb-3" id="updateBtn">
                            <span class="loading spinner-border spinner-border-sm me-2" role="status" aria-hidden="true" style="display: none;"></span>
                            <span class="btn-text">
                                <i class="fas fa-check me-1"></i>
                                Définir le nouveau mot de passe
                            </span>
                        </button>
                    </form>

                    <div class="alert alert-success">
                        <i class="fas fa-check-circle me-2"></i>
                        <strong>Presque terminé!</strong>
                        Votre nouveau mot de passe sera actif immédiatement après validation.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
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

        // Form submission with loading state
        const form = document.getElementById('newPasswordForm');
        const submitBtn = document.getElementById('updateBtn');

        form.addEventListener('submit', function(e) {
            if (form.checkValidity()) {
                showLoading(submitBtn);
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
</script>
@endpush
