@extends('layouts.app')

@section('title', 'Commande Confirmée - Coopérative E-commerce')

@section('content')
<div class="container py-4">
    <!-- Success Header -->
    <div class="text-center mb-5">
        <i class="fas fa-check-circle fa-5x text-success mb-3"></i>
        <h1 class="h2 text-success mb-2">Commande Confirmée!</h1>
        <p class="lead text-muted">Merci pour votre commande. Voici les détails de votre achat.</p>
    </div>

    <div class="row">
        <!-- Order Details -->
        <div class="col-lg-8 mb-4">
            @foreach($orders as $order)
                <div class="card mb-4 shadow">
                    <div class="card-header bg-primary text-white">
                        <div class="row align-items-center">
                            <div class="col">
                                <h5 class="mb-0">
                                    <i class="fas fa-shopping-bag me-2"></i>
                                    Commande #{{ $order->order_number }}
                                </h5>
                            </div>
                            <div class="col-auto">
                                <span class="badge bg-warning text-dark">{{ ucfirst($order->status) }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Cooperative Info -->
                        @php
                            $cooperative = $order->orderItems->first()->cooperative;
                        @endphp
                        <div class="alert alert-info">
                            <div class="row">
                                <div class="col-auto">
                                    <i class="fas fa-building fa-2x text-primary"></i>
                                </div>
                                <div class="col">
                                    <h6 class="mb-1">{{ $cooperative->name }}</h6>
                                    <p class="mb-1">
                                        <i class="fas fa-map-marker-alt me-1"></i>
                                        {{ $cooperative->address }}
                                    </p>
                                    <p class="mb-0">
                                        <i class="fas fa-phone me-1"></i>
                                        {{ $cooperative->phone }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Order Items -->
                        <h6 class="text-muted mb-3">Articles commandés:</h6>
                        @foreach($order->orderItems as $item)
                            <div class="row align-items-center border-bottom py-2">
                                <div class="col-auto">
                                    @if($item->product && $item->product->primaryImageUrl)
                                        <img src="{{ $item->product->primaryImageUrl }}"
                                             class="rounded" style="width: 50px; height: 50px; object-fit: cover;">
                                    @else
                                        <div class="bg-light rounded d-flex align-items-center justify-content-center"
                                             style="width: 50px; height: 50px;">
                                            <i class="fas fa-image text-muted"></i>
                                        </div>
                                    @endif
                                </div>
                                <div class="col">
                                    <h6 class="mb-1">{{ $item->product ? $item->product->name : 'Produit supprimé' }}</h6>
                                    <small class="text-muted">{{ $item->quantity }} × {{ number_format($item->unit_price, 2) }} MAD</small>
                                </div>
                                <div class="col-auto">
                                    <strong>{{ number_format($item->subtotal, 2) }} MAD</strong>
                                </div>
                            </div>
                        @endforeach

                        <!-- Order Total -->
                        <div class="text-end mt-3 pt-3 border-top">
                            <div class="h4 text-success mb-0">
                                Total: {{ number_format($order->total_amount, 2) }} MAD
                            </div>
                            <small class="text-muted">Paiement: {{ ucfirst($order->payment_method) }}</small>
                        </div>

                        <!-- Action Buttons -->
                        <div class="text-center mt-4">
                            <div class="btn-group" role="group">
                                <a href="{{ route('client.orders.show', $order) }}" class="btn btn-primary">
                                    <i class="fas fa-eye me-1"></i>
                                    Voir les détails
                                </a>
                                @if($order->clientReceipt)
                                    <a href="{{ route('client.receipts.client', $order->clientReceipt) }}"
                                       class="btn btn-outline-success" target="_blank">
                                        <i class="fas fa-download me-1"></i>
                                        Télécharger le reçu
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Next Steps -->
        <div class="col-lg-4">
            <div class="card bg-primary text-white mb-4">
                <div class="card-body text-center">
                    <i class="fas fa-info-circle fa-3x mb-3"></i>
                    <h5 class="card-title">Prochaines Étapes</h5>
                    <div class="text-start">
                        <div class="d-flex align-items-start mb-3">
                            <div class="bg-white text-primary rounded-circle d-flex align-items-center justify-content-center me-3"
                                 style="width: 30px; height: 30px; min-width: 30px;">
                                <small><strong>1</strong></small>
                            </div>
                            <div>
                                <strong>Préparation</strong>
                                <div class="small">Votre commande est en cours de préparation.</div>
                            </div>
                        </div>
                        <div class="d-flex align-items-start mb-3">
                            <div class="bg-white text-primary rounded-circle d-flex align-items-center justify-content-center me-3"
                                 style="width: 30px; height: 30px; min-width: 30px;">
                                <small><strong>2</strong></small>
                            </div>
                            <div>
                                <strong>Notification</strong>
                                <div class="small">Vous recevrez un email quand votre commande sera prête.</div>
                            </div>
                        </div>
                        <div class="d-flex align-items-start">
                            <div class="bg-white text-primary rounded-circle d-flex align-items-center justify-content-center me-3"
                                 style="width: 30px; height: 30px; min-width: 30px;">
                                <small><strong>3</strong></small>
                            </div>
                            <div>
                                <strong>Retrait</strong>
                                <div class="small">Récupérez votre commande avec votre reçu.</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-bolt me-2"></i>
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
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
