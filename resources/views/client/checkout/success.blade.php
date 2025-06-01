@extends('layouts.app')

@section('title', 'Commande Confirmée - Coopérative E-commerce')

@push('styles')
<style>
.success-animation {
    animation: bounceIn 1s ease-in-out;
}

@keyframes bounceIn {
    0% { transform: scale(0.3); opacity: 0; }
    50% { transform: scale(1.05); }
    70% { transform: scale(0.9); }
    100% { transform: scale(1); opacity: 1; }
}

.order-card {
    transition: all 0.3s ease;
    border: none;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    margin-bottom: 20px;
}

.order-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
}

.step-indicator {
    background: linear-gradient(45deg, #007bff, #0056b3);
    color: white;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    margin-right: 15px;
    flex-shrink: 0;
}

.timeline-step {
    display: flex;
    align-items: center;
    margin-bottom: 20px;
    padding: 15px;
    background: rgba(0, 123, 255, 0.05);
    border-radius: 10px;
    border-left: 4px solid #007bff;
}

.cooperative-badge {
    background: linear-gradient(45deg, #28a745, #20c997);
    color: white;
    padding: 5px 10px;
    border-radius: 15px;
    font-size: 0.8rem;
    font-weight: bold;
}

.amount-highlight {
    background: linear-gradient(45deg, #28a745, #20c997);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    font-weight: bold;
}
</style>
@endpush

@section('content')
<div class="container py-4">
    <!-- Success Header -->
    <div class="text-center mb-5 success-animation">
        <div class="mb-4">
            <i class="fas fa-check-circle fa-5x text-success"></i>
        </div>
        <h1 class="h2 text-success mb-2">🎉 Commande Confirmée!</h1>
        <p class="lead text-muted">Merci pour votre commande. Voici les détails de votre achat.</p>
        <div class="alert alert-success d-inline-block">
            <i class="fas fa-info-circle me-2"></i>
            <strong>{{ $orders->count() }}</strong> commande(s) créée(s) pour un total de
            <strong>{{ number_format($orders->sum('total_amount'), 2) }} MAD</strong>
        </div>
    </div>

    <div class="row">
        <!-- Order Details -->
        <div class="col-lg-8 mb-4">
            @foreach($orders as $index => $order)
                <div class="card order-card">
                    <div class="card-header bg-primary text-white">
                        <div class="row align-items-center">
                            <div class="col">
                                <h5 class="mb-0">
                                    <i class="fas fa-shopping-bag me-2"></i>
                                    Commande #{{ $order->order_number }}
                                </h5>
                                <small>Passée le {{ $order->created_at->format('d/m/Y à H:i') }}</small>
                            </div>
                            <div class="col-auto">
                                <span class="badge bg-warning text-dark fs-6">{{ ucfirst($order->status) }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Cooperative Info -->
                        @php
                            $cooperative = $order->orderItems->first()->cooperative;
                        @endphp
                        <div class="alert alert-info border-0" style="background: linear-gradient(135deg, #e3f2fd, #bbdefb);">
                            <div class="row align-items-center">
                                <div class="col-auto">
                                    <i class="fas fa-building fa-2x text-primary"></i>
                                </div>
                                <div class="col">
                                    <h6 class="mb-1 fw-bold">{{ $cooperative->name }}</h6>
                                    <p class="mb-1">
                                        <i class="fas fa-map-marker-alt me-1 text-danger"></i>
                                        {{ $cooperative->address }}
                                    </p>
                                    <p class="mb-0">
                                        <i class="fas fa-phone me-1 text-success"></i>
                                        {{ $cooperative->phone }}
                                        @if($cooperative->email)
                                            <span class="ms-3">
                                                <i class="fas fa-envelope me-1 text-info"></i>
                                                {{ $cooperative->email }}
                                            </span>
                                        @endif
                                    </p>
                                </div>
                                <div class="col-auto">
                                    <span class="cooperative-badge">{{ $cooperative->sector_of_activity }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Order Items -->
                        <h6 class="text-muted mb-3">
                            <i class="fas fa-list me-2"></i>
                            Articles commandés ({{ $order->orderItems->count() }}):
                        </h6>

                        <div class="row">
                            @foreach($order->orderItems as $item)
                                <div class="col-12 mb-3">
                                    <div class="d-flex align-items-center p-3 bg-light rounded">
                                        <div class="me-3">
                                            @if($item->product && $item->product->primaryImageUrl)
                                                <img src="{{ $item->product->primaryImageUrl }}"
                                                     class="rounded" style="width: 60px; height: 60px; object-fit: cover;">
                                            @else
                                                <div class="bg-white rounded d-flex align-items-center justify-content-center border"
                                                     style="width: 60px; height: 60px;">
                                                    <i class="fas fa-image text-muted"></i>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1">{{ $item->product ? $item->product->name : 'Produit supprimé' }}</h6>
                                            <small class="text-muted">
                                                {{ $item->quantity }} × {{ number_format($item->unit_price, 2) }} MAD
                                            </small>
                                            @if($item->product)
                                                <div class="mt-1">
                                                    <small class="text-muted">{{ Str::limit($item->product->description, 80) }}</small>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="text-end">
                                            <div class="fw-bold text-success">{{ number_format($item->subtotal, 2) }} MAD</div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Order Summary -->
                        <div class="row mt-4 pt-3 border-top">
                            <div class="col-md-6">
                                <div class="row g-3">
                                    <div class="col-6">
                                        <small class="text-muted d-block">Mode de paiement:</small>
                                        <div class="fw-bold">
                                            <i class="fas fa-{{ $order->payment_method === 'card' ? 'credit-card' : ($order->payment_method === 'bank_transfer' ? 'university' : 'money-bill-wave') }} me-1"></i>
                                            {{ ucfirst($order->payment_method) }}
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <small class="text-muted d-block">Statut paiement:</small>
                                        <span class="badge bg-{{ $order->payment_status === 'paid' ? 'success' : 'warning' }}">
                                            <i class="fas fa-{{ $order->payment_status === 'paid' ? 'check' : 'clock' }} me-1"></i>
                                            {{ ucfirst($order->payment_status) }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 text-md-end">
                                <div class="h4 amount-highlight mb-0">
                                    Total: {{ number_format($order->total_amount, 2) }} MAD
                                </div>
                                @if($order->clientReceipt)
                                    <small class="text-muted">
                                        Reçu: {{ $order->clientReceipt->receipt_number }}
                                    </small>
                                @endif
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="text-center mt-4">
                            <div class="btn-group flex-wrap" role="group">
                                <a href="{{ route('client.orders.show', $order) }}" class="btn btn-primary">
                                    <i class="fas fa-eye me-1"></i>
                                    Voir les détails
                                </a>
                                @if($order->clientReceipt)
                                    <a href="{{ route('client.receipts.client', $order->clientReceipt) }}"
                                       class="btn btn-success" target="_blank">
                                        <i class="fas fa-download me-1"></i>
                                        Télécharger le reçu
                                    </a>
                                @endif
                                <button class="btn btn-outline-info" onclick="shareOrder('{{ $order->order_number }}', '{{ route('client.orders.show', $order) }}')">
                                    <i class="fas fa-share me-1"></i>
                                    Partager
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Next Steps & Quick Actions -->
        <div class="col-lg-4">
            <!-- Next Steps -->
            <div class="card mb-4" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                <div class="card-body">
                    <div class="text-center mb-4">
                        <i class="fas fa-route fa-3x mb-3"></i>
                        <h5 class="card-title">Prochaines Étapes</h5>
                    </div>

                    <div class="timeline-step">
                        <div class="step-indicator">1</div>
                        <div>
                            <strong>Préparation en cours</strong>
                            <div class="small">Votre commande est en cours de préparation par {{ $orders->count() > 1 ? 'les coopératives' : 'la coopérative' }}.</div>
                        </div>
                    </div>

                    <div class="timeline-step">
                        <div class="step-indicator">2</div>
                        <div>
                            <strong>Notification</strong>
                            <div class="small">Vous recevrez un email quand votre commande sera prête pour le retrait.</div>
                        </div>
                    </div>

                    <div class="timeline-step">
                        <div class="step-indicator">3</div>
                        <div>
                            <strong>Retrait</strong>
                            <div class="small">Récupérez votre commande avec votre reçu et votre pièce d'identité.</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-rocket me-2"></i>
                        Actions Rapides
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('client.orders.index') }}" class="btn btn-outline-primary">
                            <i class="fas fa-list me-1"></i>
                            Voir toutes mes commandes
                        </a>
                        <a href="{{ route('client.products.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-search me-1"></i>
                            Continuer mes achats
                        </a>
                        <a href="{{ route('client.dashboard') }}" class="btn btn-outline-info">
                            <i class="fas fa-home me-1"></i>
                            Retour à l'accueil
                        </a>
                        @if($orders->count() === 1 && $orders->first()->clientReceipt)
                            <a href="{{ route('client.receipts.client', $orders->first()->clientReceipt) }}"
                               class="btn btn-success" target="_blank">
                                <i class="fas fa-receipt me-1"></i>
                                Ouvrir mon reçu
                            </a>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Important Information -->
            <div class="card">
                <div class="card-header bg-warning text-dark">
                    <h6 class="mb-0">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Informations Importantes
                    </h6>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2">
                            <i class="fas fa-clock text-primary me-2"></i>
                            <small><strong>Délai:</strong> Les commandes sont généralement prêtes sous 24-48h</small>
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-id-card text-info me-2"></i>
                            <small><strong>Retrait:</strong> Munissez-vous de votre reçu et de votre pièce d'identité</small>
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-user-friends text-success me-2"></i>
                            <small><strong>Délégation:</strong> Vous pouvez créer un reçu d'autorisation pour déléguer le retrait</small>
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-phone text-warning me-2"></i>
                            <small><strong>Contact:</strong> Contactez directement la coopérative en cas de question</small>
                        </li>
                        <li>
                            <i class="fas fa-envelope text-secondary me-2"></i>
                            <small><strong>Support:</strong> support@cooperative-ecommerce.ma pour l'aide générale</small>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Row -->
    @if($orders->count() > 1)
        <div class="row mt-4">
            <div class="col-12">
                <div class="alert alert-success text-center">
                    <h5 class="mb-2">
                        <i class="fas fa-check-circle me-2"></i>
                        Récapitulatif Total
                    </h5>
                    <p class="mb-0">
                        <strong>{{ $orders->count() }}</strong> commande(s) pour un montant total de
                        <strong class="fs-4 amount-highlight">{{ number_format($orders->sum('total_amount'), 2) }} MAD</strong>
                    </p>
                    <small class="text-muted">
                        Toutes vos commandes ont été confirmées et les coopératives ont été notifiées.
                    </small>
                </div>
            </div>
        </div>
    @endif
</div>

<script>
function shareOrder(orderNumber, orderUrl) {
    const text = `Ma commande ${orderNumber} sur Coopérative E-commerce`;

    if (navigator.share) {
        navigator.share({
            title: text,
            url: orderUrl
        });
    } else {
        // Fallback: copy to clipboard
        navigator.clipboard.writeText(orderUrl).then(() => {
            showAlert('Lien de la commande copié dans le presse-papiers!', 'success');
        });
    }
}

// Update cart badge since cart should be empty now
document.addEventListener('DOMContentLoaded', function() {
    updateNavCartBadge();
});
</script>
@endsection
