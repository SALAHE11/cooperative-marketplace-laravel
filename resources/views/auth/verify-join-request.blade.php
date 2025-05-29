@extends('layouts.app')

@section('title', 'Vérification Email - Demande d\'Adhésion')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <i class="fas fa-user-check fa-3x text-success mb-3"></i>
                        <h2 class="h3 mb-3">Vérification Email</h2>
                        <p class="text-muted">
                            Un code de vérification a été envoyé à<br>
                            <strong>{{ $email }}</strong>
                        </p>
                        <div class="alert alert-info mt-3">
                            <small>
                                <i class="fas fa-info-circle me-1"></i>
                                Demande d'adhésion à: <strong>{{ $cooperativeName }}</strong>
                            </small>
                        </div>
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

                    <form method="POST" action="{{ route('coop.verify-join-request') }}" id="verifyForm">
                        @csrf
                        <input type="hidden" name="email" value="{{ $email }}">

                        <div class="mb-4">
                            <label for="code" class="form-label text-center d-block">
                                <i class="fas fa-key me-1"></i>
                                Entrez le code de vérification
                            </label>
                            <input type="text"
                                   class="form-control form-control-lg text-center @error('code') is-invalid @enderror"
                                   id="code"
                                   name="code"
                                   placeholder="000000"
                                   maxlength="6"
                                   pattern="[0-9]{6}"
                                   style="font-size: 1.5rem; letter-spacing: 0.5rem;"
                                   required
                                   autofocus>
                            <small class="form-text text-muted text-center d-block mt-2">
                                Code à 6 chiffres
                            </small>
                            @error('code')
                                <div class="invalid-feedback text-center">{{ $message }}</div>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-success w-100 mb-3" id="verifyBtn">
                            <span class="loading spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                            <span class="btn-text">
                                <i class="fas fa-check me-1"></i>
                                Vérifier et Envoyer la Demande
                            </span>
                        </button>
                    </form>

                    <div class="text-center">
                        <p class="mb-3">Vous n'avez pas reçu le code?</p>
                        <button class="btn btn-outline-secondary" id="resendBtn" onclick="resendCode()">
                            <i class="fas fa-redo me-1"></i>
                            Renvoyer le code
                        </button>
                        <div id="countdown" class="mt-2 text-muted" style="display: none;">
                            Vous pouvez renvoyer le code dans <span id="timer">60</span> secondes
                        </div>
                    </div>

                    <div class="alert alert-warning mt-4">
                        <i class="fas fa-clock me-2"></i>
                        <strong>Étape suivante:</strong>
                        Après vérification, votre demande sera envoyée à l'administrateur de la coopérative pour approbation.
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
        const form = document.getElementById('verifyForm');
        const submitBtn = document.getElementById('verifyBtn');
        const codeInput = document.getElementById('code');

        // Auto-format code input
        codeInput.addEventListener('input', function() {
            this.value = this.value.replace(/[^0-9]/g, '');
            if (this.value.length === 6) {
                form.submit();
            }
        });

        // Form submission with loading state
        form.addEventListener('submit', function() {
            showLoading(submitBtn);
        });

        // Start countdown timer
        startCountdown();

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

    let countdownTimer;

    function startCountdown() {
        const resendBtn = document.getElementById('resendBtn');
        const countdown = document.getElementById('countdown');
        const timer = document.getElementById('timer');
        let seconds = 60;

        resendBtn.disabled = true;
        countdown.style.display = 'block';

        countdownTimer = setInterval(function() {
            seconds--;
            timer.textContent = seconds;

            if (seconds <= 0) {
                clearInterval(countdownTimer);
                resendBtn.disabled = false;
                countdown.style.display = 'none';
            }
        }, 1000);
    }

    function resendCode() {
        const alert = document.createElement('div');
        alert.className = 'alert alert-info alert-dismissible fade show';
        alert.innerHTML = `
            <i class="fas fa-info-circle me-2"></i>
            Code renvoyé avec succès!
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;

        const container = document.querySelector('.card-body');
        container.insertBefore(alert, container.children[1]);

        startCountdown();

        setTimeout(function() {
            alert.remove();
        }, 3000);
    }
</script>
@endpush
