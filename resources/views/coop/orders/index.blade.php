@extends('layouts.app')

@section('title', 'Gestion des Commandes - Coopérative E-commerce')

@push('styles')
<style>
.order-card {
    transition: all 0.3s ease;
    border: none;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    margin-bottom: 20px;
}

.order-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.15);
}

.status-badge {
    font-size: 0.75rem;
    padding: 0.375rem 0.75rem;
    border-radius: 1rem;
}

.priority-indicator {
    width: 4px;
    height: 100%;
    position: absolute;
    left: 0;
    top: 0;
    border-radius: 4px 0 0 4px;
}

.priority-high { background: #dc3545; }
.priority-medium { background: #ffc107; }
.priority-low { background: #28a745; }

.quick-stats {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 15px;
}
</style>
@endpush

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">Gestion des Commandes</h1>
                    <p class="text-muted">{{ Auth::user()->cooperative->name }}</p>
                </div>
                <a href="{{ route('coop.dashboard') }}" class="btn btn-outline-primary">
                    <i class="fas fa-arrow-left me-1"></i>
                    Retour au tableau de bord
                </a>
            </div>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card quick-stats">
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-3 col-6 mb-3 mb-md-0">
                            <div class="h4 mb-0">{{ $statusCounts['all'] }}</div>
                            <div class="small">Total Commandes</div>
                        </div>
                        <div class="col-md-3 col-6 mb-3 mb-md-0">
                            <div class="h4 mb-0">{{ $statusCounts['pending'] }}</div>
                            <div class="small">En Préparation</div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="h4 mb-0">{{ $statusCounts['ready'] }}</div>
                            <div class="small">Prêtes</div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="h4 mb-0">{{ $statusCounts['completed'] }}</div>
                            <div class="small">Terminées</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters and Search -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" class="row align-items-end">
                        <div class="col-md-3 mb-3">
                            <label for="search" class="form-label">Rechercher</label>
                            <input type="text" class="form-control" id="search" name="search"
                                   value="{{ $search }}" placeholder="N° commande, client...">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="status" class="form-label">Statut</label>
                            <select class="form-select" id="status" name="status">
                                <option value="all" {{ $status === 'all' ? 'selected' : '' }}>Tous</option>
                                <option value="pending" {{ $status === 'pending' ? 'selected' : '' }}>En préparation</option>
                                <option value="ready" {{ $status === 'ready' ? 'selected' : '' }}>Prêtes</option>
                                <option value="completed" {{ $status === 'completed' ? 'selected' : '' }}>Terminées</option>
                                <option value="cancelled" {{ $status === 'cancelled' ? 'selected' : '' }}>Annulées</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="fas fa-search me-1"></i>
                                Rechercher
                            </button>
                            <a href="{{ route('coop.orders.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-1"></i>
                                Reset
                            </a>
                        </div>
                        <div class="col-md-3 mb-3 text-end">
                            <button type="button" class="btn btn-success" onclick="refreshOrders()">
                                <i class="fas fa-sync-alt me-1"></i>
                                Actualiser
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Status Tabs -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="btn-group w-100" role="group">
                <a href="{{ route('coop.orders.index') }}"
                   class="btn {{ $status === 'all' ? 'btn-primary' : 'btn-outline-primary' }}">
                    Toutes ({{ $statusCounts['all'] }})
                </a>
                <a href="{{ route('coop.orders.index', ['status' => 'pending']) }}"
                   class="btn {{ $status === 'pending' ? 'btn-warning' : 'btn-outline-warning' }}">
                    En préparation ({{ $statusCounts['pending'] }})
                </a>
                <a href="{{ route('coop.orders.index', ['status' => 'ready']) }}"
                   class="btn {{ $status === 'ready' ? 'btn-info' : 'btn-outline-info' }}">
                    Prêtes ({{ $statusCounts['ready'] }})
                </a>
                <a href="{{ route('coop.orders.index', ['status' => 'completed']) }}"
                   class="btn {{ $status === 'completed' ? 'btn-success' : 'btn-outline-success' }}">
                    Terminées ({{ $statusCounts['completed'] }})
                </a>
                <a href="{{ route('coop.orders.index', ['status' => 'cancelled']) }}"
                   class="btn {{ $status === 'cancelled' ? 'btn-danger' : 'btn-outline-danger' }}">
                    Annulées ({{ $statusCounts['cancelled'] }})
                </a>
            </div>
        </div>
    </div>

    <!-- Orders List -->
    @if($orders->count() > 0)
        <div class="row">
            @foreach($orders as $order)
                <div class="col-lg-6 col-xl-4 mb-4">
                    <div class="card order-card position-relative">
                        <!-- Priority Indicator -->
                        <div class="priority-indicator priority-{{ $order->status === 'pending' ? 'high' : ($order->status === 'ready' ? 'medium' : 'low') }}"></div>

                        <div class="card-header d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-0">#{{ $order->order_number }}</h6>
                                <small class="text-muted">{{ $order->created_at->format('d/m/Y H:i') }}</small>
                            </div>
                            <span class="badge status-badge bg-{{ $order->status === 'pending' ? 'warning' : ($order->status === 'ready' ? 'info' : ($order->status === 'completed' ? 'success' : 'danger')) }}">
                                {{ ucfirst($order->status) }}
                            </span>
                        </div>

                        <div class="card-body">
                            <!-- Customer Info -->
                            <div class="mb-3">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-user text-primary me-2"></i>
                                    <div>
                                        <strong>{{ $order->user->full_name }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $order->user->email }}</small>
                                        @if($order->client_phone)
                                            <br>
                                            <small class="text-muted">
                                                <i class="fas fa-phone me-1"></i>
                                                {{ $order->client_phone }}
                                            </small>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <!-- Order Items Preview -->
                            <div class="mb-3">
                                <h6 class="text-muted mb-2">Articles ({{ $order->orderItems->count() }}):</h6>
                                @foreach($order->orderItems->take(2) as $item)
                                    <div class="d-flex align-items-center mb-1">
                                        @if($item->product && $item->product->primaryImageUrl)
                                            <img src="{{ $item->product->primaryImageUrl }}"
                                                 class="me-2 rounded" style="width: 30px; height: 30px; object-fit: cover;">
                                        @else
                                            <div class="me-2 bg-light rounded d-flex align-items-center justify-content-center"
                                                 style="width: 30px; height: 30px;">
                                                <i class="fas fa-image text-muted small"></i>
                                            </div>
                                        @endif
                                        <div class="flex-grow-1">
                                            <div class="small">{{ $item->product ? Str::limit($item->product->name, 25) : 'Produit supprimé' }}</div>
                                            <div class="text-muted" style="font-size: 0.75rem;">{{ $item->quantity }} × {{ number_format($item->unit_price, 2) }} MAD</div>
                                        </div>
                                    </div>
                                @endforeach
                                @if($order->orderItems->count() > 2)
                                    <small class="text-muted">+{{ $order->orderItems->count() - 2 }} autre(s)...</small>
                                @endif
                            </div>

                            <!-- Time Info -->
                            <div class="mb-3">
                                @if($order->status === 'pending')
                                    <div class="text-warning">
                                        <i class="fas fa-clock me-1"></i>
                                        <small>
                                            @if($order->estimated_ready_at)
                                                Estimation: {{ $order->estimated_ready_at->format('H:i') }}
                                            @else
                                                Temps estimé: 2-4h
                                            @endif
                                        </small>
                                    </div>
                                @elseif($order->status === 'ready')
                                    <div class="text-info">
                                        <i class="fas fa-check-circle me-1"></i>
                                        <small>Prêt depuis {{ $order->ready_at ? $order->ready_at->diffForHumans() : 'maintenant' }}</small>
                                    </div>
                                @elseif($order->status === 'completed')
                                    <div class="text-success">
                                        <i class="fas fa-handshake me-1"></i>
                                        <small>Retiré {{ $order->picked_up_at ? $order->picked_up_at->diffForHumans() : 'récemment' }}</small>
                                    </div>
                                @endif
                            </div>

                            <!-- Authorization Receipt Status -->
                            @if($order->clientReceipt && $order->clientReceipt->authorizationReceipts->count() > 0)
                                <div class="mb-3">
                                    <div class="alert alert-info py-2">
                                        <small>
                                            <i class="fas fa-user-friends me-1"></i>
                                            <strong>Retrait délégué autorisé</strong>
                                        </small>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <div class="card-footer d-flex justify-content-between align-items-center">
                            <div>
                                <strong class="text-success">{{ number_format($order->total_amount, 2) }} MAD</strong>
                                <br>
                                <small class="text-muted">{{ ucfirst($order->payment_method) }}</small>
                            </div>
                            <div>
                                <div class="btn-group">
                                    <a href="{{ route('coop.orders.show', $order) }}" class="btn btn-primary btn-sm">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @if($order->status === 'pending')
                                        <button class="btn btn-info btn-sm" onclick="markAsReady({{ $order->id }})">
                                            <i class="fas fa-check"></i>
                                        </button>
                                    @elseif($order->status === 'ready')
                                        <button class="btn btn-success btn-sm" onclick="showPickupModal({{ $order->id }})">
                                            <i class="fas fa-handshake"></i>
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="d-flex justify-content-center">
            {{ $orders->appends(request()->query())->links() }}
        </div>
    @else
        <div class="text-center py-5">
            <i class="fas fa-inbox fa-4x text-muted mb-3"></i>
            <h4>Aucune commande trouvée</h4>
            @if($status === 'all')
                <p class="text-muted">Aucune commande n'a été passée pour votre coopérative.</p>
            @else
                <p class="text-muted">Aucune commande avec le statut "{{ $status }}".</p>
                <a href="{{ route('coop.orders.index') }}" class="btn btn-primary">
                    <i class="fas fa-list me-1"></i>
                    Voir toutes les commandes
                </a>
            @endif
        </div>
    @endif
</div>

<!-- Pickup Verification Modal -->
<div class="modal fade" id="pickupModal" tabindex="-1" aria-labelledby="pickupModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="pickupModalLabel">
                    <i class="fas fa-handshake me-2"></i>
                    Confirmer le Retrait
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="pickupForm">
                    <div class="mb-3">
                        <label for="picked_up_by" class="form-label">Retiré par</label>
                        <select class="form-select" id="picked_up_by" name="picked_up_by" required onchange="toggleVerificationFields()">
                            <option value="">Sélectionner...</option>
                            <option value="client">Client lui-même</option>
                            <option value="authorized_person">Personne autorisée</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="verification_code" class="form-label">Code de Vérification</label>
                        <input type="text" class="form-control" id="verification_code" name="verification_code"
                               placeholder="Code du reçu" required>
                        <div class="form-text">Code à 8 caractères du reçu client ou code d'autorisation</div>
                    </div>

                    <div class="mb-3">
                        <label for="pickup_notes" class="form-label">Notes (optionnel)</label>
                        <textarea class="form-control" id="pickup_notes" name="notes" rows="2"
                                  placeholder="Observations sur le retrait..."></textarea>
                    </div>

                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Important:</strong> Vérifiez l'identité de la personne qui retire la commande et assurez-vous que le code correspond.
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
let currentOrderId = null;

function refreshOrders() {
    location.reload();
}

function markAsReady(orderId) {
    if (!confirm('Marquer cette commande comme prête pour le retrait?')) {
        return;
    }

    updateOrderStatus(orderId, 'ready', 'Commande marquée comme prête');
}

function showPickupModal(orderId) {
    currentOrderId = orderId;
    const modal = new bootstrap.Modal(document.getElementById('pickupModal'));

    // Reset form
    document.getElementById('pickupForm').reset();

    modal.show();
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

    fetch(`{{ url('/coop/orders') }}/${currentOrderId}/mark-picked-up`, {
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

function updateOrderStatus(orderId, status, successMessage) {
    fetch(`{{ url('/coop/orders') }}/${orderId}/update-status`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            status: status
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert(successMessage || data.message, 'success');
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

// Auto-refresh every 30 seconds for pending and ready orders
if ('{{ $status }}' === 'pending' || '{{ $status }}' === 'ready') {
    setInterval(() => {
        location.reload();
    }, 30000);
}
</script>
@endsection
