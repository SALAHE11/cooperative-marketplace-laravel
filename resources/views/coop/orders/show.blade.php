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

.receipt-info {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 15px;
}

.action-buttons {
    position: sticky;
    top: 20px;
}

.order-item-card {
    border: none;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    margin-bottom: 10px;
    transition: all 0.3s ease;
}

.order-item-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.15);
}
</style>
@endpush

@section('content')
<div class="container py-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('coop.dashboard') }}">Tableau de bord</a></li>
            <li class="breadcrumb-item"><a href="{{ route('coop.orders.index') }}">Commandes</a></li>
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
                    <a href="{{ route('coop.orders.index') }}" class="btn btn-outline-primary">
                        <i class="fas fa-arrow-left me-1"></i>
                        Retour à la liste
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Order Details -->
        <div class="col-lg-8">
            <!-- Customer Information -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-user me-2"></i>
                        Informations Client
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label text-muted">Nom complet</label>
                                <div class="fw-bold">{{ $order->user->full_name }}</div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-muted">Email</label>
                                <div>{{ $order->user->email }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label text-muted">Téléphone</label>
                                <div>{{ $order->client_phone ?: $order->user->phone ?: 'Non renseigné' }}</div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-muted">Adresse</label>
                                <div>{{ $order->user->address ?: 'Non renseignée' }}</div>
                            </div>
                        </div>
                    </div>

                    @if($order->pickup_instructions)
                        <div class="alert alert-info">
                            <strong>Instructions pour le retrait:</strong><br>
                            {{ $order->pickup_instructions }}
                        </div>
                    @endif
                </div>
            </div>

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

                                        @if($item->product)
                                            <div class="mt-2">
                                                <small class="text-muted">
                                                    Stock actuel:
                                                    <span class="badge bg-{{ $item->product->stock_quantity > $item->product->stock_alert_threshold ? 'success' : ($item->product->stock_quantity > 0 ? 'warning' : 'danger') }}">
                                                        {{ $item->product->stock_quantity }}
                                                    </span>
                                                </small>
                                            </div>
                                        @endif
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
                        Historique de la Commande
                    </h5>
                </div>
                <div class="card-body">
                    <div class="order-timeline">
                        <div class="timeline-item completed">
                            <div class="fw-bold">Commande reçue</div>
                            <div class="text-muted">{{ $order->created_at->format('d/m/Y à H:i') }}</div>
                            <small class="text-muted">Commande passée par le client</small>
                        </div>

                        @if($order->status !== 'cancelled')
                            <div class="timeline-item {{ in_array($order->status, ['ready', 'completed']) ? 'completed' : 'current' }}">
                                <div class="fw-bold">En préparation</div>
                                @if($order->estimated_ready_at)
                                    <div class="text-muted">Estimation: {{ $order->estimated_ready_at->format('d/m/Y à H:i') }}</div>
                                @endif
                                <small class="text-muted">Préparation des articles</small>
                            </div>

                            <div class="timeline-item {{ $order->status === 'completed' ? 'completed' : ($order->status === 'ready' ? 'current' : '') }}">
                                <div class="fw-bold">Prêt pour retrait</div>
                                @if($order->ready_at)
                                    <div class="text-muted">{{ $order->ready_at->format('d/m/Y à H:i') }}</div>
                                @endif
                                <small class="text-muted">Client notifié</small>
                            </div>

                            <div class="timeline-item {{ $order->status === 'completed' ? 'completed' : '' }}">
                                <div class="fw-bold">Retiré</div>
                                @if($order->picked_up_at)
                                    <div class="text-muted">{{ $order->picked_up_at->format('d/m/Y à H:i') }}</div>
                                    @if($order->picked_up_by === 'authorized_person')
                                        <small class="text-info">Retiré par une personne autorisée</small>
                                    @else
                                        <small class="text-muted">Retiré par le client</small>
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

                    @if($order->notes)
                        <div class="alert alert-secondary mt-4">
                            <strong>Notes:</strong><br>
                            {{ $order->notes }}
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Actions Sidebar -->
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
                        @if($order->status === 'pending')
                            <div class="d-grid gap-2">
                                <button class="btn btn-info" onclick="markAsReady()">
                                    <i class="fas fa-check me-1"></i>
                                    Marquer comme Prêt
                                </button>
                                <button class="btn btn-outline-secondary" onclick="showUpdateModal()">
                                    <i class="fas fa-edit me-1"></i>
                                    Mettre à jour
                                </button>
                            </div>
                        @elseif($order->status === 'ready')
                            <div class="d-grid gap-2">
                                <button class="btn btn-success" onclick="showPickupModal()">
                                    <i class="fas fa-handshake me-1"></i>
                                    Confirmer Retrait
                                </button>
                                <button class="btn btn-outline-secondary" onclick="showUpdateModal()">
                                    <i class="fas fa-edit me-1"></i>
                                    Mettre à jour
                                </button>
                            </div>
                        @elseif($order->status === 'completed')
                            <div class="alert alert-success text-center">
                                <i class="fas fa-check-circle fa-2x mb-2"></i>
                                <div><strong>Commande Terminée</strong></div>
                                <small>Retiré {{ $order->picked_up_at->diffForHumans() }}</small>
                            </div>
                        @else
                            <div class="alert alert-warning text-center">
                                <i class="fas fa-times-circle fa-2x mb-2"></i>
                                <div><strong>Commande Annulée</strong></div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Receipt Information -->
                @if($order->clientReceipt)
                    <div class="card receipt-info mb-4">
                        <div class="card-body text-center">
                            <i class="fas fa-receipt fa-3x mb-3"></i>
                            <h6>Reçu Client</h6>
                            <p class="mb-3">Numéro: {{ $order->clientReceipt->receipt_number }}</p>
                            <div class="mb-3">
                                <small>Code de vérification:</small>
                                <div class="fw-bold fs-5">{{ $order->clientReceipt->verification_code }}</div>
                            </div>

                            @if($order->clientReceipt->authorizationReceipts->count() > 0)
                                <div class="alert alert-warning">
                                    <i class="fas fa-user-friends me-2"></i>
                                    <strong>Retrait Délégué</strong>
                                    <br>
                                    @foreach($order->clientReceipt->authorizationReceipts as $authReceipt)
                                        <small>
                                            Autorisé: {{ $authReceipt->authorized_person_name }}<br>
                                            Code: {{ $authReceipt->unique_code }}<br>
                                            @if($authReceipt->is_used)
                                                <span class="badge bg-success">Utilisé</span>
                                            @elseif($authReceipt->validity_end < now())
                                                <span class="badge bg-danger">Expiré</span>
                                            @else
                                                <span class="badge bg-warning">Valide</span>
                                            @endif
                                        </small>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                <!-- Contact Customer -->
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="fas fa-phone me-2"></i>
                            Contact Client
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            @if($order->client_phone)
                                <a href="tel:{{ $order->client_phone }}" class="btn btn-outline-success">
                                    <i class="fas fa-phone me-1"></i>
                                    {{ $order->client_phone }}
                                </a>
                            @endif
                            <a href="mailto:{{ $order->user->email }}" class="btn btn-outline-primary">
                                <i class="fas fa-envelope me-1"></i>
                                Envoyer un email
                            </a>
                        </div>

                        <hr>

                        <div class="text-center">
                            <small class="text-muted">
                                <i class="fas fa-info-circle me-1"></i>
                                Client membre depuis {{ $order->user->created_at->format('M Y') }}
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Update Order Modal -->
<div class="modal fade" id="updateOrderModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-edit me-2"></i>
                    Mettre à jour la Commande
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="updateForm">
                    <div class="mb-3">
                        <label for="status" class="form-label">Statut</label>
                        <select class="form-select" id="status" name="status" required>
                            <option value="pending" {{ $order->status === 'pending' ? 'selected' : '' }}>En préparation</option>
                            <option value="ready" {{ $order->status === 'ready' ? 'selected' : '' }}>Prêt</option>
                            <option value="completed" {{ $order->status === 'completed' ? 'selected' : '' }}>Terminé</option>
                            <option value="cancelled" {{ $order->status === 'cancelled' ? 'selected' : '' }}>Annulé</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="notes" class="form-label">Notes</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3">{{ $order->notes }}</textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-primary" onclick="updateOrder()">
                    <i class="fas fa-save me-1"></i>
                    Mettre à jour
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Pickup Verification Modal -->
<div class="modal fade" id="pickupModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-handshake me-2"></i>
                    Confirmer le Retrait
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="pickupForm">
                    <div class="mb-3">
                        <label for="picked_up_by" class="form-label">Retiré par</label>
                        <select class="form-select" id="picked_up_by" name="picked_up_by" required>
                            <option value="">Sélectionner...</option>
                            <option value="client">Client lui-même</option>
                            <option value="authorized_person">Personne autorisée</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="verification_code" class="form-label">Code de Vérification</label>
                        <input type="text" class="form-control" id="verification_code" name="verification_code"
                               placeholder="Code du reçu" required>
                        <div class="form-text">
                            Code client: {{ $order->clientReceipt->verification_code ?? 'N/A' }}
                            @if($order->clientReceipt && $order->clientReceipt->authorizationReceipts->count() > 0)
                                <br>Code(s) d'autorisation:
                                @foreach($order->clientReceipt->authorizationReceipts as $auth)
                                    {{ $auth->unique_code }}{{ !$loop->last ? ', ' : '' }}
                                @endforeach
                            @endif
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="pickup_notes" class="form-label">Notes</label>
                        <textarea class="form-control" id="pickup_notes" name="notes" rows="2"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-success" onclick="confirmPickup()">
                    <i class="fas fa-check me-1"></i>
                    Confirmer le Retrait
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function markAsReady() {
    if (!confirm('Marquer cette commande comme prête pour le retrait?')) {
        return;
    }

    updateOrderStatus('ready', 'Commande marquée comme prête');
}

function showUpdateModal() {
    const modal = new bootstrap.Modal(document.getElementById('updateOrderModal'));
    modal.show();
}

function showPickupModal() {
    const modal = new bootstrap.Modal(document.getElementById('pickupModal'));
    modal.show();
}

function updateOrder() {
    const form = document.getElementById('updateForm');
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }

    const formData = new FormData(form);
    const data = {};
    for (let [key, value] of formData.entries()) {
        data[key] = value;
    }

    updateOrderStatus(data.status, 'Commande mise à jour', data.notes);
}

function confirmPickup() {
    const form = document.getElementById('pickupForm');
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }

    const formData = new FormData(form);
    const data = {};
    for (let [key, value] of formData.entries()) {
        data[key] = value;
    }

    fetch('{{ route("coop.orders.mark-picked-up", $order) }}', {
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
            bootstrap.Modal.getInstance(document.getElementById('pickupModal')).hide();
            setTimeout(() => location.reload(), 1000);
        } else {
            showAlert(data.message, 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('Erreur lors de la confirmation du retrait', 'danger');
    });
}

function updateOrderStatus(status, successMessage, notes = null) {
    const data = { status };
    if (notes) data.notes = notes;

    fetch('{{ route("coop.orders.update-status", $order) }}', {
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
            showAlert(successMessage || data.message, 'success');

            // Hide modals
            const updateModal = bootstrap.Modal.getInstance(document.getElementById('updateOrderModal'));
            if (updateModal) updateModal.hide();

            setTimeout(() => location.reload(), 1000);
        } else {
            showAlert(data.message, 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('Erreur lors de la mise à jour', 'danger');
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
