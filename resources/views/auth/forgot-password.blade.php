@extends('layouts.app')

@section('title', 'Mot de passe oublié - Coopérative E-commerce')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <i class="fas fa-key fa-3x text-warning mb-3"></i>
                        <h2 class="h3 mb-3">Mot de passe oublié</h2>
                        <p class="text-muted">
                            Entrez votre adresse email pour recevoir un code de vérification
                        </p>
                    </div>

                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i>
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            @foreach($errors->all() as $error)
                                {{ $error }}<br>
                            @endforeach
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('password.send-code') }}" id="forgotForm">
                        @csrf

                        <div class="mb-4">
                            <label for="email" class="form-label">
                                <i class="fas fa-envelope me-1"></i>
                                Adresse Email
                            </label>
                            <input type="email"
                                   class="form-control form-control-lg @error('email') is-invalid @enderror"
                                   id="email"
                                   name="email"
                                   value="{{ old('email') }}"
                                   placeholder="votre.email@exemple.com"
                                   required
                                   autofocus>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-warning w-100 mb-3" id="sendCodeBtn">
                            <span class="loading spinner-border spinner-border-sm me-2" role="status" aria-hidden="true" style="display: none;"></span>
                            <span class="btn-text">
                                <i class="fas fa-paper-plane me-1"></i>
                                Envoyer le code de vérification
                            </span>
                        </button>
                    </form>

                    <div class="text-center">
                        <p class="mb-2">Vous vous souvenez de votre mot de passe ?</p>
                        <a href="{{ route('login') }}" class="btn btn-outline-primary">
                            <i class="fas fa-sign-in-alt me-1"></i>
                            Retour à la connexion
                        </a>
                    </div>

                    <div class="alert alert-info mt-4">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Important:</strong>
                        Le code de vérification expire dans 15 minutes. Vérifiez aussi votre dossier spam.
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
        const form = document.getElementById('forgotForm');
        const submitBtn = document.getElementById('sendCodeBtn');

        form.addEventListener('submit', function() {
            showLoading(submitBtn);
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
