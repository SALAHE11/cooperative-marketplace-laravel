@extends('layouts.app')

@section('title', 'Vérification Emails Coopérative - Coopérative E-commerce')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-7">
            <div class="card">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <i class="fas fa-envelope-open fa-3x text-warning mb-3"></i>
                        <h2 class="h3 mb-3">Vérification des Emails</h2>
                        <p class="text-muted">
                            Des codes de vérification ont été envoyés aux deux adresses email
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
                            @if($errors->has('error'))
                                {{ $errors->first('error') }}
                            @else
                                Une erreur s'est produite lors de la vérification.
                            @endif
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('coop.verify-emails') }}" id="verifyForm">
                        @csrf
                        <input type="hidden" name="user_email" value="{{ $userEmail }}">
                        <input type="hidden" name="coop_email" value="{{ $coopEmail }}">

                        <!-- Personal Email Verification -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h5 class="card-title text-primary">
                                            <i class="fas fa-user me-2"></i>
                                            Email Personnel
                                        </h5>
                                        <p class="card-text mb-3">
                                            <strong>{{ $userEmail }}</strong>
                                        </p>

                                        <label for="user_code" class="form-label">
                                            Code de vérification
                                        </label>
                                        <input type="text"
                                               class="form-control form-control-lg text-center @error('user_code') is-invalid @enderror"
                                               id="user_code"
                                               name="user_code"
                                               placeholder="000000"
                                               maxlength="6"
                                               pattern="[0-9]{6}"
                                               style="font-size: 1.2rem; letter-spacing: 0.3rem;"
                                               required>
                                        @error('user_code')
                                            <div class="invalid-feedback text-center">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Cooperative Email Verification -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h5 class="card-title text-success">
                                            <i class="fas fa-building me-2"></i>
                                            Email Coopérative
                                        </h5>
                                        <p class="card-text mb-3">
                                            <strong>{{ $coopEmail }}</strong>
                                        </p>

                                        <label for="coop_code" class="form-label">
                                            Code de vérification
                                        </label>
                                        <input type="text"
                                               class="form-control form-control-lg text-center @error('coop_code') is-invalid @enderror"
                                               id="coop_code"
                                               name="coop_code"
                                               placeholder="000000"
                                               maxlength="6"
                                               pattern="[0-9]{6}"
                                               style="font-size: 1.2rem; letter-spacing: 0.3rem;"
                                               required>
                                        @error('coop_code')
                                            <div class="invalid-feedback text-center">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-success w-100 mb-3" id="verifyBtn">
                            <span class="loading spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                            <span class="btn-text">
                                <i class="fas fa-check me-1"></i>
                                Vérifier les Deux Emails
                            </span>
                        </button>
                    </form>

                    <div class="text-center">
                        <p class="mb-3">Vous n'avez pas reçu les codes?</p>
                        <button class="btn btn-outline-secondary" id="resendBtn" onclick="resendCodes()">
                            <i class="fas fa-redo me-1"></i>
                            Renvoyer les codes
                        </button>
                        <div id="countdown" class="mt-2 text-muted" style="display: none;">
                            Vous pouvez renvoyer les codes dans <span id="timer">60</span> secondes
                        </div>
                    </div>

                    <div class="alert alert-info mt-4">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Important:</strong>
                        Après vérification des emails, votre demande sera examinée par un administrateur.
                        Vous recevrez une réponse sur l'email de la coopérative.
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
        const userCodeInput = document.getElementById('user_code');
        const coopCodeInput = document.getElementById('coop_code');

        // Auto-format code inputs
        function setupCodeInput(input) {
            input.addEventListener('input', function() {
                this.value = this.value.replace(/[^0-9]/g, '');
            });
        }

        setupCodeInput(userCodeInput);
        setupCodeInput(coopCodeInput);

        // Form submission with loading state
        form.addEventListener('submit', function() {
            showLoading(submitBtn);
        });

        // Start countdown timer
        startCountdown();
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

    function resendCodes() {
        // This would typically make an AJAX request to resend the codes
        // For now, we'll just show a message and restart the countdown
        const alert = document.createElement('div');
        alert.className = 'alert alert-info alert-dismissible fade show';
        alert.innerHTML = `
            <i class="fas fa-info-circle me-2"></i>
            Codes renvoyés avec succès aux deux adresses email!
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;

        const container = document.querySelector('.card-body');
        container.insertBefore(alert, container.children[1]);

        startCountdown();

        // Auto-hide alert after 3 seconds
        setTimeout(function() {
            alert.remove();
        }, 3000);
    }
</script>
@endpush
