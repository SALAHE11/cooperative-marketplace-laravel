@extends('layouts.app')

@section('title', 'Mes Commandes - Coopérative E-commerce')

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
    margin-bottom: 1rem;
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
</style>
@endpush

@section('content')
<div class="container py-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">
                    <i class="fas fa-shopping-bag me-2"></i>
                    Mes Commandes
                </h1>
                <a href="{{ route('client.products.index') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i>
                    Nouvelle commande
                </a>
            </div>
        </div>
    </div>

    <!-- Status Filter -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-auto">
                            <strong>Filtrer par statut:</strong>
                        </div>
                        <div class="col">
                            <div class="btn-group" role="group">
                                <a href="{{ route('client.orders.index') }}"
                                   class="btn btn-sm {{ $status === 'all' ? 'btn-primary' : 'btn-outline-primary' }}">
                                    Toutes ({{ $statusCounts['all'] }})
                                </a>
                                <a href="{{ route('client.orders.index', ['status' => 'pending']) }}"
                                   class="btn btn-sm {{ $status === 'pending' ? 'btn-warning' : 'btn-outline-warning' }}">
                                    En préparation ({{ $statusCounts['pending'] }})
                                </a>
                                <a href="{{ route('client.orders.index', ['status' => 'ready']) }}"
                                   class="btn btn-sm {{ $status === 'ready' ? 'btn-info' : 'btn-outline-info' }}">
                                    Prêtes ({{ $statusCounts['ready'] }})
                                </a>
                                <a href="{{ route('client.orders.index', ['status' => 'completed']) }}"
                                   class="btn btn-sm {{ $status === 'completed' ? 'btn-success' : 'btn-outline-success' }}">
                                    Terminées ({{ $statusCounts['completed'] }})
                                </a>
                                <a href="{{ route('client.orders.index', ['status' => 'cancelled']) }}"
                                   class="btn btn-sm {{ $status === 'cancelled' ? 'btn-danger' : 'btn-outline-danger' }}">
                                    Annulées ({{ $statusCounts['cancelled'] }})
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if($orders->count() > 0)
        <div class="row">
            @foreach($orders as $order)
                <div class="col-lg-6 mb-4">
                    <div class="card order-card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-0">Commande #{{ $order->order_number }}</h6>
                                <small class="text-muted">{{ $order->created_at->format('d/m/Y à H:i') }}</small>
                            </div>
                            <span class="badge status-badge bg-{{ $order->status === 'pending' ? 'warning' : ($order->status === 'ready' ? 'info' : ($order->status === 'completed' ? 'success' : 'danger')) }}">
                                {{ ucfirst($order->status) }}
                            </span>
                        </div>

                        <div class="card-body">
                            <!-- Order Items Preview -->
                            <div class="mb-3">
                                <h6 class="text-muted mb-2">Articles commandés:</h6>
                                @foreach($order->orderItems->take(3) as $item)
                                    <div class="d-flex align-items-center mb-2">
                                        @if($item->product && $item->product->primaryImageUrl)
                                            <img src="{{ $item->product->primaryImageUrl }}"
                                                 class="me-2 rounded" style="width: 40px; height: 40px; object-fit: cover;">
                                        @else
                                            <div class="me-2 bg-light rounded d-flex align-items-center justify-content-center"
                                                 style="width: 40px; height: 40px;">
                                                <i class="fas fa-image text-muted"></i>
                                            </div>
                                        @endif
                                        <div class="flex-grow-1">
                                            <div class="small fw-bold">{{ $item->product ? $item->product->name : 'Produit supprimé' }}</div>
                                            <div class="text-muted small">{{ $item->quantity }} × {{ number_format($item->unit_price, 2) }} MAD</div>
                                        </div>
                                        <div class="small fw-bold">{{ number_format($item->subtotal, 2) }} MAD</div>
                                    </div>
                                @endforeach

                                @if($order->orderItems->count() > 3)
                                    <div class="text-center">
                                        <small class="text-muted">
                                            +{{ $order->orderItems->count() - 3 }} autre(s) article(s)
                                        </small>
                                    </div>
                                @endif
                            </div>

                            <!-- Cooperative Info -->
                            <div class="mb-3">
                                @php
                                    $cooperatives = $order->orderItems->groupBy('cooperative_id');
                                @endphp
                                @foreach($cooperatives as $cooperativeId => $items)
                                    @php $cooperative = $items->first()->cooperative; @endphp
                                    <div class="d-flex align-items-center mb-2">
                                        <i class="fas fa-building text-primary me-2"></i>
                                        <div>
                                            <div class="small fw-bold">{{ $cooperative->name }}</div>
                                            <div class="text-muted small">
                                                <i class="fas fa-map-marker-alt me-1"></i>
                                                {{ $cooperative->address }}
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <!-- Order Timeline -->
                            <div class="order-timeline">
                                <div class="timeline-item completed">
                                    <div class="small">
                                        <strong>Commande passée</strong>
                                        <div class="text-muted">{{ $order->created_at->format('d/m/Y à H:i') }}</div>
                                    </div>
                                </div>

                                @if($order->status !== 'cancelled')
                                    <div class="timeline-item {{ in_array($order->status, ['ready', 'completed']) ? 'completed' : 'current' }}">
                                        <div class="small">
                                            <strong>En préparation</strong>
                                            @if($order->estimated_ready_at)
                                                <div class="text-muted">Prêt vers {{ $order->estimated_ready_at->format('d/m/Y à H:i') }}</div>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="timeline-item {{ $order->status === 'completed' ? 'completed' : ($order->status === 'ready' ? 'current' : '') }}">
                                        <div class="small">
                                            <strong>Prêt pour retrait</strong>
                                            @if($order->ready_at)
                                                <div class="text-muted">{{ $order->ready_at->format('d/m/Y à H:i') }}</div>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="timeline-item {{ $order->status === 'completed' ? 'completed' : '' }}">
                                        <div class="small">
                                            <strong>Retiré</strong>
                                            @if($order->picked_up_at)
                                                <div class="text-muted">
                                                    {{ $order->picked_up_at->format('d/m/Y à H:i') }}
                                                    @if($order->picked_up_by === 'authorized_person')
                                                        <br><span class="badge bg-info">Par personne autorisée</span>
                                                    @endif
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @else
                                    <div class="timeline-item current">
                                        <div class="small">
                                            <strong class="text-danger">Commande annulée</strong>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="card-footer d-flex justify-content-between align-items-center">
                            <div>
                                <strong class="text-success">{{ number_format($order->total_amount, 2) }} MAD</strong>
                                <br>
                                <small class="text-muted">{{ ucfirst($order->payment_method) }}</small>
                            </div>
                            <div>
                                <a href="{{ route('client.orders.show', $order) }}" class="btn btn-primary btn-sm">
                                    <i class="fas fa-eye me-1"></i>
                                    Détails
                                </a>
                                @if($order->clientReceipt)
                                    <a href="{{ route('client.receipts.client', $order->clientReceipt) }}"
                                       class="btn btn-outline-success btn-sm" target="_blank">
                                        <i class="fas fa-download me-1"></i>
                                        Reçu
                                    </a>
                                @endif
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
            <i class="fas fa-shopping-bag fa-4x text-muted mb-3"></i>
            <h4>Aucune commande trouvée</h4>
            @if($status === 'all')
                <p class="text-muted mb-4">Vous n'avez pas encore passé de commande.</p>
                <a href="{{ route('client.products.index') }}" class="btn btn-primary">
                    <i class="fas fa-search me-1"></i>
                    Découvrir nos produits
                </a>
            @else
                <p class="text-muted mb-4">Aucune commande avec le statut "{{ $status }}".</p>
                <a href="{{ route('client.orders.index') }}" class="btn btn-primary">
                    <i class="fas fa-list me-1"></i>
                    Voir toutes mes commandes
                </a>
            @endif
        </div>
    @endif
</div>
@endsection
