@extends('layouts.app')

@section('title', 'Commande #' . $order->order_number . ' - Coopérative E-commerce')

@push('styles')
<style>
.order-timeline {
    position: relative;
    padding-left: 30px;
}

.order-timeline::before {
    content: '';
    position: absolute;
    left: 8px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #dee2e6;
}

.timeline-item {
    position: relative;
    margin-bottom: 20px;
}

.timeline-item::before {
    content: '';
    position: absolute;
    left: -26px;
    top: 4px;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    background: #dee2e6;
    border: 2px solid #fff;
}

.timeline-item.completed::before {
    background: #28a745;
}

.timeline-item.current::before {
    background: #007bff;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% { box-shadow: 0 0 0 0 rgba(0, 123, 255, 0.7); }
    70% { box-shadow: 0 0 0 10px rgba(0, 123, 255, 0); }
    100% { box-shadow: 0 0 0 0 rgba(0, 123, 255, 0); }
}

.status-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 15px;
}

.receipt-section {
    background: #f8f9fa;
    border: 2px solid #dee2e6;
    border-radius: 10px;
    margin: 20px 0;
}

.auth-receipt-card {
    border: 2px solid #ffc107;
    background: #fff3cd;
}

.order-item-card {
    border: none;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    margin-bottom: 15px;
    transition: all 0.3s ease;
}

.order-item-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.15);
}

.action-buttons {
    position: sticky;
    top: 20px;
}
</style>
@endpush

@section('content')
<div class="container py-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('client.dashboard') }}">Accueil</a></li>
            <li class="breadcrumb-item"><a href="{{ route('client.orders.index') }}">Mes Commandes</a></li>
            <li class="breadcrumb-item active">{{ $order->order_number }}</li>
        </ol>
    </nav>

    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">Commande #{{ $order->order_number }}</h1>
                    <p class="text-muted">Passée le {{ $order->created_at->format('d/m/Y à H:i') }}</p>
                </div>
                <div>
                    <span class="badge bg-{{ $order->status === 'pending' ? 'warning' : ($order->status === 'ready' ? 'info' : ($order->status === 'completed' ? 'success' : 'danger')) }} fs-6 me-2">
                        {{ ucfirst($order->status) }}
                    </span>
                    <a href="{{ route('client.orders.index') }}" class="btn btn-outline-primary">
                        <i class="fas fa-arrow-left me-1"></i>
                        Mes commandes
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Order Details -->
        <div class="col-lg-8">
            <!-- Order Status -->
            <div class="card status-card mb-4">
                <div class="card-body text-center py-4">
                    <i class="fas fa-{{ $order->status === 'pending' ? 'clock' : ($order->status === 'ready' ? 'check-circle' : ($order->status === 'completed' ? 'handshake' : 'times-circle')) }} fa-3x mb-3"></i>
                    <h4>
                        @if($order->status === 'pending')
                            Commande en Préparation
                        @elseif($order->status === 'ready')
                            Commande Prête pour Retrait
                        @elseif($order->status === 'completed')
                            Commande Terminée
                        @else
                            Commande Annulée
                        @endif
                    </h4>
                    @if($order->status === 'pending')
                        <p class="mb-0">Votre commande est en cours de préparation par la coopérative.</p>
                        @if($order->estimated_ready_at)
                            <p class="mb-0"><strong>Estimation:</strong> {{ $order->estimated_ready_at->format('d/m/Y à H:i') }}</p>
                        @endif
                    @elseif($order->status === 'ready')
                        <p class="mb-0">Votre commande est prête! Vous pouvez la récupérer à la coopérative.</p>
                        @if($order->ready_at)
                            <p class="mb-0"><strong>Prête depuis:</strong> {{ $order->ready_at->diffForHumans() }}</p>
                        @endif
                    @elseif($order->status === 'completed')
                        <p class="mb-0">Votre commande a été récupérée avec succès.</p>
                        @if($order->picked_up_at)
                            <p class="mb-0"><strong>Récupérée le:</strong> {{ $order->picked_up_at->format('d/m/Y à H:i') }}</p>
                        @endif
                    @endif
                </div>
            </div>

            <!-- Cooperative Information -->
            @php
                $cooperatives = $order->orderItems->groupBy('cooperative_id');
            @endphp

            @foreach($cooperatives as $cooperativeId => $items)
                @php $cooperative = $items->first()->cooperative; @endphp
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-building me-2"></i>
                            {{ $cooperative->name }}
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="text-muted mb-2">Informations de retrait</h6>
                                <p class="mb-1">
                                    <i class="fas fa-map-marker-alt text-danger me-2"></i>
                                    <strong>{{ $cooperative->address }}</strong>
                                </p>
                                <p class="mb-1">
                                    <i class="fas fa-phone text-success me-2"></i>
                                    {{ $cooperative->phone }}
                                </p>
                                <p class="mb-0">
                                    <i class="fas fa-envelope text-info me-2"></i>
                                    {{ $cooperative->email }}
                                </p>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-muted mb-2">Secteur d'activité</h6>
                                <p class="mb-0">{{ $cooperative->sector_of_activity }}</p>

                                @if($order->pickup_instructions)
                                    <h6 class="text-muted mb-2 mt-3">Instructions spéciales</h6>
                                    <div class="alert alert-info py-2">
                                        {{ $order->pickup_instructions }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach

            <!-- Order Items -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-shopping-cart me-2"></i>
                        Articles Commandés ({{ $order->orderItems->count() }})
                    </h5>
                </div>
                <div class="card-body">
                    @foreach($order->orderItems as $item)
                        <div class="card order-item-card">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col-auto">
                                        @if($item->product && $item->product->primaryImageUrl)
                                            <img src="{{ $item->product->primaryImageUrl }}"
                                                 class="rounded" style="width: 80px; height: 80px; object-fit: cover;">
                                        @else
                                            <div class="bg-light rounded d-flex align-items-center justify-content-center"
                                                 style="width: 80px; height: 80px;">
                                                <i class="fas fa-image text-muted fa-2x"></i>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="col">
                                        <h6 class="mb-1">{{ $item->product ? $item->product->name : 'Produit supprimé' }}</h6>
                                        <p class="text-muted mb-2">
                                            {{ $item->product ? Str::limit($item->product->description, 100) : '' }}
                                        </p>
                                        <div class="row">
                                            <div class="col-auto">
                                                <small class="text-muted">Quantité:</small>
                                                <span class="fw-bold">{{ $item->quantity }}</span>
                                            </div>
                                            <div class="col-auto">
                                                <small class="text-muted">Prix unitaire:</small>
                                                <span class="fw-bold">{{ number_format($item->unit_price, 2) }} MAD</span>
                                            </div>
                                            <div class="col-auto">
                                                <small class="text-muted">Sous-total:</small>
                                                <span class="fw-bold text-success">{{ number_format($item->subtotal, 2) }} MAD</span>
                                            </div>
                                        </div>
                                        <div class="mt-2">
                                            <small class="text-muted">
                                                <i class="fas fa-building me-1"></i>
                                                {{ $item->cooperative->name }}
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach

                    <!-- Order Total -->
                    <div class="text-end mt-4 pt-3 border-top">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="row">
                                    <div class="col-6">
                                        <small class="text-muted">Mode de paiement:</small>
                                        <div class="fw-bold">{{ ucfirst($order->payment_method) }}</div>
                                    </div>
                                    <div class="col-6">
                                        <small class="text-muted">Statut paiement:</small>
                                        <div>
                                            <span class="badge bg-{{ $order->payment_status === 'paid' ? 'success' : 'warning' }}">
                                                {{ ucfirst($order->payment_status) }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="h4 text-success mb-0">
                                    Total: {{ number_format($order->total_amount, 2) }} MAD
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Order Timeline -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-history me-2"></i>
                        Suivi de Commande
                    </h5>
                </div>
                <div class="card-body">
                    <div class="order-timeline">
                        <div class="timeline-item completed">
                            <div class="fw-bold">Commande passée</div>
                            <div class="text-muted">{{ $order->created_at->format('d/m/Y à H:i') }}</div>
                            <small class="text-muted">Paiement confirmé</small>
                        </div>

                        @if($order->status !== 'cancelled')
                            <div class="timeline-item {{ in_array($order->status, ['ready', 'completed']) ? 'completed' : 'current' }}">
                                <div class="fw-bold">En préparation</div>
                                @if($order->estimated_ready_at)
                                    <div class="text-muted">Estimation: {{ $order->estimated_ready_at->format('d/m/Y à H:i') }}</div>
                                @endif
                                <small class="text-muted">Coopérative prépare votre commande</small>
                            </div>

                            <div class="timeline-item {{ $order->status === 'completed' ? 'completed' : ($order->status === 'ready' ? 'current' : '') }}">
                                <div class="fw-bold">Prêt pour retrait</div>
                                @if($order->ready_at)
                                    <div class="text-muted">{{ $order->ready_at->format('d/m/Y à H:i') }}</div>
                                @endif
                                <small class="text-muted">Notification envoyée</small>
                            </div>

                            <div class="timeline-item {{ $order->status === 'completed' ? 'completed' : '' }}">
                                <div class="fw-bold">Retiré</div>
                                @if($order->picked_up_at)
                                    <div class="text-muted">{{ $order->picked_up_at->format('d/m/Y à H:i') }}</div>
                                    @if($order->picked_up_by === 'authorized_person')
                                        <small class="text-info">Retiré par une personne autorisée</small>
                                    @else
                                        <small class="text-muted">Retiré en personne</small>
                                    @endif
                                @else
                                    <small class="text-muted">En attente de retrait</small>
                                @endif
                            </div>
                        @else
                            <div class="timeline-item current">
                                <div class="fw-bold text-danger">Commande annulée</div>
                                <small class="text-muted">Commande annulée</small>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Receipts & Actions Sidebar -->
        <div class="col-lg-4">
            <div class="action-buttons">
                <!-- Quick Actions -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="fas fa-bolt me-2"></i>
                            Actions Rapides
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            @if($order->clientReceipt)
                                <a href="{{ route('client.receipts.client', $order->clientReceipt) }}"
                                   class="btn btn-success" target="_blank">
                                    <i class="fas fa-download me-1"></i>
                                    Télécharger le Reçu
                                </a>
                            @endif

                            @if($order->status === 'ready' || $order->status === 'completed')
                                <button class="btn btn-outline-primary" onclick="shareOrder()">
                                    <i class="fas fa-share me-1"></i>
                                    Partager la Commande
                                </button>
                            @endif

                            <a href="{{ route('client.products.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-shopping-cart me-1"></i>
                                Nouvelle Commande
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Client Receipt -->
                @if($order->clientReceipt)
                    <div class="card receipt-section mb-4">
                        <div class="card-header bg-success text-white">
                            <h6 class="mb-0">
                                <i class="fas fa-receipt me-2"></i>
                                Reçu Client
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="text-center mb-3">
                                <div class="fw-bold">{{ $order->clientReceipt->receipt_number }}</div>
                                <small class="text-muted">{{ $order->clientReceipt->issued_at->format('d/m/Y H:i') }}</small>
                            </div>

                            <div class="alert alert-info text-center py-2">
                                <strong>Code de vérification:</strong><br>
                                <span class="fs-5 fw-bold">{{ $order->clientReceipt->verification_code }}</span>
                            </div>

                            <div class="d-grid">
                                <a href="{{ route('client.receipts.client', $order->clientReceipt) }}"
                                   class="btn btn-success btn-sm" target="_blank">
                                    <i class="fas fa-download me-1"></i>
                                    Télécharger
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Authorization Receipts -->
                    @if($order->clientReceipt->authorizationReceipts->count() > 0)
                        <div class="card auth-receipt-card mb-4">
                            <div class="card-header bg-warning text-dark">
                                <h6 class="mb-0">
                                    <i class="fas fa-user-friends me-2"></i>
                                    Retrait Délégué
                                </h6>
                            </div>
                            <div class="card-body">
                                @foreach($order->clientReceipt->authorizationReceipts as $authReceipt)
                                    <div class="border rounded p-3 mb-3">
                                        <div class="fw-bold">{{ $authReceipt->authorized_person_name }}</div>
                                        <small class="text-muted">{{ $authReceipt->auth_number }}</small>
                                        <div class="mt-2">
                                            <span class="badge bg-{{ $authReceipt->is_used ? 'success' : ($authReceipt->validity_end < now() ? 'danger' : 'warning') }}">
                                                @if($authReceipt->is_used)
                                                    Utilisé
                                                @elseif($authReceipt->validity_end < now())
                                                    Expiré
                                                @else
                                                    Valide
                                                @endif
                                            </span>
                                        </div>
                                        <div class="mt-2">
                                            <a href="{{ route('client.receipts.authorization', $authReceipt) }}"
                                               class="btn btn-outline-warning btn-sm" target="_blank">
                                                <i class="fas fa-download me-1"></i>
                                                Voir le reçu
                                            </a>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <!-- Create Authorization Receipt -->
                        @if($order->status !== 'completed' && $order->status !== 'cancelled')
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h6 class="mb-0">
                                        <i class="fas fa-user-plus me-2"></i>
                                        Déléguer le Retrait
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <p class="text-muted small mb-3">
                                        Permettez à une autre personne de récupérer votre commande.
                                    </p>
                                    <button class="btn btn-outline-warning w-100" onclick="showAuthorizationModal()">
                                        <i class="fas fa-plus me-1"></i>
                                        Créer une Autorisation
                                    </button>
                                </div>
                            </div>
                        @endif
                    @endif
                @endif

                <!-- Contact Support -->
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="fas fa-headset me-2"></i>
                            Besoin d'Aide?
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            @foreach($cooperatives as $cooperativeId => $items)
                                @php $cooperative = $items->first()->cooperative; @endphp
                                <a href="tel:{{ $cooperative->phone }}" class="btn btn-outline-success btn-sm">
                                    <i class="fas fa-phone me-1"></i>
                                    {{ $cooperative->name }}
                                </a>
                            @endforeach
                            <button class="btn btn-outline-info btn-sm" onclick="showSupportInfo()">
                                <i class="fas fa-envelope me-1"></i>
                                Support Général
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Authorization Modal -->
<div class="modal fade" id="authorizationModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-user-plus me-2"></i>
                    Créer un Reçu d'Autorisation
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Important:</strong> La personne autorisée devra présenter ce reçu et sa pièce d'identité pour récupérer la commande.
                </div>

                <form id="authorizationForm">
                    <div class="mb-3">
                        <label for="authorized_person_name" class="form-label">Nom de la personne autorisée *</label>
                        <input type="text" class="form-control" id="authorized_person_name"
                               name="authorized_person_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="authorized_person_cin" class="form-label">CIN ou Passeport *</label>
                        <input type="text" class="form-control" id="authorized_person_cin"
                               name="authorized_person_cin" required>
                    </div>
                    <div class="mb-3">
                        <label for="authorization_validity_days" class="form-label">Validité *</label>
                        <select class="form-select" id="authorization_validity_days" name="authorization_validity_days" required>
                            <option value="7">7 jours</option>
                            <option value="15" selected>15 jours</option>
                            <option value="30">30 jours</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-warning" onclick="createAuthorizationReceipt()">
                    <i class="fas fa-check me-1"></i>
                    Créer l'Autorisation
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function showAuthorizationModal() {
    const modal = new bootstrap.Modal(document.getElementById('authorizationModal'));
    document.getElementById('authorizationForm').reset();
    modal.show();
}

function createAuthorizationReceipt() {
    const form = document.getElementById('authorizationForm');
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }

    const formData = new FormData(form);
    const data = {};
    for (let [key, value] of formData.entries()) {
        data[key] = value;
    }

    fetch('{{ route("client.receipts.create-authorization", $order->clientReceipt) }}', {
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
            showAlert(data.message, 'success');
            bootstrap.Modal.getInstance(document.getElementById('authorizationModal')).hide();

            // Open the authorization receipt in new tab
            if (data.download_url) {
                window.open(data.download_url, '_blank');
            }

            setTimeout(() => location.reload(), 1500);
        } else {
            showAlert(data.message, 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('Erreur lors de la création de l\'autorisation', 'danger');
    });
}

function shareOrder() {
    const url = window.location.href;
    const text = `Ma commande #{{ $order->order_number }} sur Coopérative E-commerce`;

    if (navigator.share) {
        navigator.share({
            title: text,
            url: url
        });
    } else {
        // Fallback: copy to clipboard
        navigator.clipboard.writeText(url).then(() => {
            showAlert('Lien copié dans le presse-papiers!', 'success');
        });
    }
}

function showSupportInfo() {
    alert('Support: support@cooperative-ecommerce.ma\nTéléphone: +212 5XX-XXXXXX\nHeures: 9h-18h (Lun-Ven)');
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

// Auto-refresh for pending/ready orders
@if(in_array($order->status, ['pending', 'ready']))
setInterval(() => {
    location.reload();
}, 60000); // Refresh every minute
@endif
</script>
@endsection
