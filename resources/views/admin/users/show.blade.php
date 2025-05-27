@extends('layouts.app')

@section('title', 'Détails Utilisateur - Admin')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-user me-2"></i>
                        Détails de l'utilisateur
                    </h1>
                    <p class="text-muted">Informations complètes sur {{ $user->full_name }}</p>
                </div>
                <div>
                    <a href="{{ route('admin.users.index') }}" class="btn btn-outline-primary">
                        <i class="fas fa-arrow-left me-2"></i>
                        Retour à la liste
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- User Information -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-user me-2"></i>
                        Informations Personnelles
                    </h5>
                </div>
                <div class="card-body">
                    <div class="text-center mb-4">
                        <div class="avatar-circle-large bg-primary text-white mx-auto mb-3">
                            {{ strtoupper(substr($user->first_name, 0, 1)) }}{{ strtoupper(substr($user->last_name, 0, 1)) }}
                        </div>
                        <h4 class="mb-1">{{ $user->full_name }}</h4>
                        <p class="text-muted">{{ ucfirst(str_replace('_', ' ', $user->role)) }}</p>
                    </div>

                    <div class="info-group">
                        <div class="info-item">
                            <i class="fas fa-envelope text-primary me-2"></i>
                            <strong>Email:</strong>
                            <span>{{ $user->email }}</span>
                            @if($user->email_verified_at)
                                <span class="badge bg-success ms-2">Vérifié</span>
                            @else
                                <span class="badge bg-warning ms-2">Non vérifié</span>
                            @endif
                        </div>

                        @if($user->phone)
                            <div class="info-item">
                                <i class="fas fa-phone text-primary me-2"></i>
                                <strong>Téléphone:</strong>
                                <span>{{ $user->phone }}</span>
                            </div>
                        @endif

                        @if($user->address)
                            <div class="info-item">
                                <i class="fas fa-map-marker-alt text-primary me-2"></i>
                                <strong>Adresse:</strong>
                                <span>{{ $user->address }}</span>
                            </div>
                        @endif

                        <div class="info-item">
                            <i class="fas fa-calendar text-primary me-2"></i>
                            <strong>Inscription:</strong>
                            <span>{{ $user->created_at->format('d/m/Y à H:i') }}</span>
                        </div>

                        @if($user->last_login_at)
                            <div class="info-item">
                                <i class="fas fa-clock text-primary me-2"></i>
                                <strong>Dernière connexion:</strong>
                                <span>{{ $user->last_login_at->format('d/m/Y à H:i') }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Status Management -->
            <div class="card shadow-sm mt-4">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">
                        <i class="fas fa-cogs me-2"></i>
                        Gestion du Statut
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Statut actuel:</label>
                        <div>
                            @if($user->status === 'active')
                                <span class="badge bg-success">
                                    <i class="fas fa-check-circle me-1"></i>
                                    Actif
                                </span>
                            @elseif($user->status === 'pending')
                                <span class="badge bg-warning">
                                    <i class="fas fa-clock me-1"></i>
                                    En attente
                                </span>
                            @else
                                <span class="badge bg-danger">
                                    <i class="fas fa-ban me-1"></i>
                                    Suspendu
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="d-grid gap-2">
                        @if($user->status !== 'active')
                            <form action="{{ route('admin.users.updateStatus', $user->id) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="active">
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-check-circle me-1"></i>
                                    Activer
                                </button>
                            </form>
                        @endif

                        @if($user->status !== 'pending')
                            <form action="{{ route('admin.users.updateStatus', $user->id) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="pending">
                                <button type="submit" class="btn btn-warning">
                                    <i class="fas fa-clock me-1"></i>
                                    Mettre en attente
                                </button>
                            </form>
                        @endif

                        @if($user->status !== 'suspended')
                            <form action="{{ route('admin.users.updateStatus', $user->id) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="suspended">
                                <button type="submit" class="btn btn-danger">
                                    <i class="fas fa-ban me-1"></i>
                                    Suspendre
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Additional Information -->
        <div class="col-lg-8">
            <!-- Cooperative Information (if applicable) -->
            @if($user->role === 'cooperative_admin' && $user->cooperative)
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-building me-2"></i>
                            Coopérative Associée
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="info-item mb-3">
                                    <strong>Nom:</strong>
                                    <span>{{ $user->cooperative->name }}</span>
                                </div>
                                <div class="info-item mb-3">
                                    <strong>Statut:</strong>
                                    <span class="badge bg-{{ $user->cooperative->status === 'approved' ? 'success' : ($user->cooperative->status === 'pending' ? 'warning' : 'danger') }}">
                                        {{ ucfirst($user->cooperative->status) }}
                                    </span>
                                </div>
                                <div class="info-item mb-3">
                                    <strong>Secteur d'activité:</strong>
                                    <span>{{ $user->cooperative->sector_of_activity }}</span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-item mb-3">
                                    <strong>Email:</strong>
                                    <span>{{ $user->cooperative->email }}</span>
                                </div>
                                <div class="info-item mb-3">
                                    <strong>Téléphone:</strong>
                                    <span>{{ $user->cooperative->phone }}</span>
                                </div>
                                <div class="info-item mb-3">
                                    <strong>Date de création:</strong>
                                    <span>{{ $user->cooperative->date_created ? $user->cooperative->date_created->format('d/m/Y') : 'N/A' }}</span>
                                </div>
                            </div>
                        </div>
                        @if($user->cooperative->description)
                            <div class="mt-3">
                                <strong>Description:</strong>
                                <p class="text-muted mt-2">{{ $user->cooperative->description }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Activity Statistics -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-bar me-2"></i>
                        Statistiques d'Activité
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-4">
                            <div class="stat-box">
                                <div class="stat-icon bg-primary">
                                    <i class="fas fa-shopping-cart"></i>
                                </div>
                                <h4 class="stat-number">{{ $user->orders->count() }}</h4>
                                <p class="stat-label">Commandes</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="stat-box">
                                <div class="stat-icon bg-warning">
                                    <i class="fas fa-star"></i>
                                </div>
                                <h4 class="stat-number">{{ $user->reviews->count() }}</h4>
                                <p class="stat-label">Avis donnés</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="stat-box">
                                <div class="stat-icon bg-success">
                                    <i class="fas fa-calendar-check"></i>
                                </div>
                                <h4 class="stat-number">{{ $user->created_at->diffInDays(now()) }}</h4>
                                <p class="stat-label">Jours d'ancienneté</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Orders (if any) -->
            @if($user->orders->count() > 0)
                <div class="card shadow-sm">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-shopping-bag me-2"></i>
                            Commandes Récentes
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Date</th>
                                        <th>Statut</th>
                                        <th>Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($user->orders->take(5) as $order)
                                        <tr>
                                            <td>#{{ $order->id }}</td>
                                            <td>{{ $order->created_at->format('d/m/Y') }}</td>
                                            <td>
                                                <span class="badge bg-info">{{ $order->status }}</span>
                                            </td>
                                            <td>{{ number_format($order->total, 2) }} DH</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @if($user->orders->count() > 5)
                            <div class="text-center mt-3">
                                <small class="text-muted">
                                    et {{ $user->orders->count() - 5 }} autres commandes...
                                </small>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .avatar-circle-large {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 24px;
    }

    .info-group {
        space-y: 1rem;
    }

    .info-item {
        padding: 0.75rem 0;
        border-bottom: 1px solid #e9ecef;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        flex-wrap: wrap;
    }

    .info-item:last-child {
        border-bottom: none;
    }

    .stat-box {
        padding: 1rem;
        text-align: center;
    }

    .stat-icon {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 0.5rem;
        color: white;
        font-size: 1.2rem;
    }

    .stat-number {
        font-size: 2rem;
        font-weight: 700;
        margin: 0;
        color: #495057;
    }

    .stat-label {
        color: #6c757d;
        margin: 0;
        font-size: 0.9rem;
    }

    .card {
        border: none;
        border-radius: 0.5rem;
    }

    .card-header {
        border-radius: 0.5rem 0.5rem 0 0 !important;
    }
</style>
@endpush


