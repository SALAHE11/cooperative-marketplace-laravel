@extends('layouts.app')

@section('title', 'Finaliser ma Commande - Coopérative E-commerce')

@push('styles')
<style>
.checkout-section {
    border: 1px solid #dee2e6;
    border-radius: 10px;
    margin-bottom: 20px;
    background: white;
}

.checkout-header {
    background: #f8f9fa;
    padding: 15px 20px;
    border-bottom: 1px solid #dee2e6;
    border-radius: 10px 10px 0 0;
}

.cooperative-section {
    border: 2px solid #007bff;
    border-radius: 10px;
    margin-bottom: 20px;
    background: #f8f9ff;
}

.cooperative-header {
    background: #007bff;
    color: white;
    padding: 15px 20px;
    margin: -2px -2px 15px -2px;
    border-radius: 8px 8px 0 0;
}

.payment-method {
    border: 2px solid #dee2e6;
    border-radius: 10px;
    padding: 15px;
    cursor: pointer;
    transition: all 0.3s ease;
    margin-bottom: 10px;
}

.payment-method:hover {
    border-color: #007bff;
    background: #f8f9ff;
}

.payment-method.selected {
    border-color: #007bff;
    background: #e3f2fd;
}

.order-item {
    border-bottom: 1px solid #eee;
    padding: 10px 0;
}

.order-item:last-child {
    border-bottom: none;
}
</style>
@endpush

@section('content')
<div class="container py-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">
                    <i class="fas fa-credit-card me-2"></i>
                    Finaliser ma Commande
                </h1>
                <a href="{{ route('client.cart.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i>
                    Retour au panier
                </a>
            </div>
        </div>
    </div>

    <form id="checkoutForm">
        @csrf
        <div class="row">
            <!-- Order Summary -->
            <div class="col-lg-8">
                <!-- Contact Information -->
                <div class="checkout-section">
                    <div class="checkout-header">
                        <h5 class="mb-0">
                            <i class="fas fa-user me-2"></i>
                            Informations de Contact
                        </h5>
                    </div>
                    <div class="p-4">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="client_name" class="form-label">Nom complet</label>
                                <input type="text" class="form-control" id="client_name"
                                       value="{{ Auth::user()->full_name }}" readonly>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="client_email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="client_email"
                                       value="{{ Auth::user()->email }}" readonly>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="client_phone" class="form-label">Téléphone *</label>
                                <input type="tel" class="form-control" id="client_phone" name="client_phone"
                                       value="{{ Auth::user()->phone }}" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="pickup_instructions" class="form-label">Instructions pour le retrait</label>
                                <textarea class="form-control" id="pickup_instructions" name="pickup_instructions"
                                          rows="2" placeholder="Instructions spéciales..."></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Order Items by Cooperative -->
                @foreach($itemsByCooperative as $cooperativeId => $items)
                    @php $cooperative = $items->first()->cooperative; @endphp
                    <div class="cooperative-section">
                        <div class="cooperative-header">
                            <h5 class="mb-0">
                                <i class="fas fa-building me-2"></i>
                                {{ $cooperative->name }}
                            </h5>
                            <small>Retrait à: {{ $cooperative->address }}</small>
                        </div>
                        <div class="p-3">
                            @foreach($items as $item)
                                <div class="order-item">
                                    <div class="row align-items-center">
                                        <div class="col-auto">
                                            @if($item->getProductImage())
                                                <img src="{{ $item->getProductImage() }}" alt="{{ $item->getProductName() }}"
                                                     class="rounded" style="width: 60px; height: 60px; object-fit: cover;">
                                            @else
                                                <div class="bg-light rounded d-flex align-items-center justify-content-center"
                                                     style="width: 60px; height: 60px;">
                                                    <i class="fas fa-image text-muted"></i>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="col">
                                            <h6 class="mb-1">{{ $item->getProductName() }}</h6>
                                            <small class="text-muted">
                                                {{ $item->quantity }} × {{ number_format($item->unit_price, 2) }} MAD
                                            </small>
                                        </div>
                                        <div class="col-auto">
                                            <strong>{{ number_format($item->subtotal, 2) }} MAD</strong>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                            <div class="text-end mt-3 pt-2 border-top">
                                <strong>Sous-total: {{ number_format($items->sum('subtotal'), 2) }} MAD</strong>
                            </div>
                        </div>
                    </div>
                @endforeach

                <!-- Payment Method -->
                <div class="checkout-section">
                    <div class="checkout-header">
                        <h5 class="mb-0">
                            <i class="fas fa-credit-card me-2"></i>
                            Mode de Paiement
                        </h5>
                    </div>
                    <div class="p-4">
                        <div class="payment-method" onclick="selectPaymentMethod('cash')">
                            <div class="d-flex align-items-center">
                                <input type="radio" class="form-check-input me-3" name="payment_method" value="cash" id="cash" checked>
                                <div>
                                    <i class="fas fa-money-bill-wave fa-2x text-success me-3"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1">Espèces</h6>
                                    <small class="text-muted">Paiement simulé automatiquement</small>
                                </div>
                            </div>
                        </div>

                        <div class="payment-method" onclick="selectPaymentMethod('card')">
                            <div class="d-flex align-items-center">
                                <input type="radio" class="form-check-input me-3" name="payment_method" value="card" id="card">
                                <div>
                                    <i class="fas fa-credit-card fa-2x text-primary me-3"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1">Carte Bancaire</h6>
                                    <small class="text-muted">Paiement simulé automatiquement</small>
                                </div>
                            </div>
                        </div>

                        <div class="payment-method" onclick="selectPaymentMethod('bank_transfer')">
                            <div class="d-flex align-items-center">
                                <input type="radio" class="form-check-input me-3" name="payment_method" value="bank_transfer" id="bank_transfer">
                                <div>
                                    <i class="fas fa-university fa-2x text-info me-3"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1">Virement Bancaire</h6>
                                    <small class="text-muted">Paiement simulé automatiquement</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Authorization Receipt Option -->
                <div class="checkout-section">
                    <div class="checkout-header">
                        <h5 class="mb-0">
                            <i class="fas fa-user-friends me-2"></i>
                            Délégation de Retrait (Optionnel)
                        </h5>
                    </div>
                    <div class="p-4">
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="create_authorization_receipt"
                                   name="create_authorization_receipt" onchange="toggleAuthorizationForm()">
                            <label class="form-check-label" for="create_authorization_receipt">
                                <strong>Créer un reçu d'autorisation</strong><br>
                                <small class="text-muted">Permettre à une autre personne de récupérer ma commande</small>
                            </label>
                        </div>

                        <div id="authorization-form" style="display: none;">
                            <div class="bg-light p-3 rounded">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="authorized_person_name" class="form-label">Nom de la personne autorisée *</label>
                                        <input type="text" class="form-control" id="authorized_person_name"
                                               name="authorized_person_name" placeholder="Nom complet">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="authorized_person_cin" class="form-label">CIN de la personne autorisée *</label>
                                        <input type="text" class="form-control" id="authorized_person_cin"
                                               name="authorized_person_cin" placeholder="CIN ou Passeport">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="authorization_validity_days" class="form-label">Validité (jours) *</label>
                                        <select class="form-select" id="authorization_validity_days" name="authorization_validity_days">
                                            <option value="7">7 jours</option>
                                            <option value="15" selected>15 jours</option>
                                            <option value="30">30 jours</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>
                                    La personne autorisée devra présenter ce reçu d'autorisation et sa pièce d'identité pour récupérer la commande.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Order Total & Submit -->
            <div class="col-lg-4">
                <div class="card shadow sticky-top">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-receipt me-2"></i>
                            Récapitulatif
                        </h5>
                    </div>
                    <div class="card-body">
                        <!-- Order Summary -->
                        <div class="d-flex justify-content-between mb-2">
                            <span>Articles ({{ $cart->total_items }})</span>
                            <span>{{ number_format($cart->total_amount, 2) }} MAD</span>
                        </div>

                        <hr>
                        <div class="d-flex justify-content-between mb-3">
                            <strong class="h6">Total à payer</strong>
                            <strong class="h5 text-success">{{ number_format($cart->total_amount, 2) }} MAD</strong>
                        </div>

                        <!-- Payment Button -->
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-success btn-lg" id="payButton">
                                <i class="fas fa-lock me-2"></i>
                                Confirmer et Payer
                            </button>
                            <small class="text-muted text-center">
                                <i class="fas fa-shield-alt me-1"></i>
                                Paiement automatique simulé
                            </small>
                        </div>

                        <!-- Important Notes -->
                        <div class="mt-4">
                            <h6 class="text-warning">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                Important
                            </h6>
                            <ul class="list-unstyled small text-muted">
                                <li class="mb-1">
                                    <i class="fas fa-check text-success me-1"></i>
                                    Retrait uniquement en coopérative
                                </li>
                                <li class="mb-1">
                                    <i class="fas fa-check text-success me-1"></i>
                                    Reçu client généré automatiquement
                                </li>
                                <li class="mb-1">
                                    <i class="fas fa-check text-success me-1"></i>
                                    Code de vérification fourni
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize payment method
    selectPaymentMethod('cash');

    // Form submission
    document.getElementById('checkoutForm').addEventListener('submit', function(e) {
        e.preventDefault();
        processPayment();
    });
});

function selectPaymentMethod(method) {
    // Update radio button
    document.getElementById(method).checked = true;

    // Update visual selection
    document.querySelectorAll('.payment-method').forEach(el => {
        el.classList.remove('selected');
    });
    event.currentTarget.classList.add('selected');
}

function toggleAuthorizationForm() {
    const checkbox = document.getElementById('create_authorization_receipt');
    const form = document.getElementById('authorization-form');
    const requiredFields = ['authorized_person_name', 'authorized_person_cin', 'authorization_validity_days'];

    if (checkbox.checked) {
        form.style.display = 'block';
        requiredFields.forEach(field => {
            document.getElementById(field).setAttribute('required', 'required');
        });
    } else {
        form.style.display = 'none';
        requiredFields.forEach(field => {
            document.getElementById(field).removeAttribute('required');
        });
    }
}

function processPayment() {
    const button = document.getElementById('payButton');
    const originalText = button.innerHTML;

    // Validate form
    const form = document.getElementById('checkoutForm');
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }

    // Show loading
    button.disabled = true;
    button.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Traitement du paiement...';

    // Collect form data
    const formData = new FormData(form);
    const data = {};
    for (let [key, value] of formData.entries()) {
        data[key] = value;
    }

    // Convert checkbox to boolean
    data.create_authorization_receipt = document.getElementById('create_authorization_receipt').checked;

    fetch('{{ route("client.checkout.process") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Success animation
            button.innerHTML = '<i class="fas fa-check me-2"></i>Paiement réussi!';
            button.classList.remove('btn-success');
            button.classList.add('btn-outline-success');

            showAlert(data.message, 'success');

            // Redirect after delay
            setTimeout(() => {
                window.location.href = data.redirect;
            }, 1500);
        } else {
            showAlert(data.message, 'danger');
            button.innerHTML = originalText;
            button.disabled = false;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('Erreur lors du traitement du paiement', 'danger');
        button.innerHTML = originalText;
        button.disabled = false;
    });
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
</script>
@endsection
