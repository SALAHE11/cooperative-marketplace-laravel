@extends('layouts.app')

@section('title', 'Tableau de Bord Administrateur - Coopérative E-commerce')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">Tableau de Bord Administrateur</h1>
                    <p class="text-muted">Bienvenue, {{ Auth::user()->full_name ?? 'Test User' }}</p>
                </div>
                <div class="text-end">
                    <small class="text-muted">Dernière connexion: {{ Auth::user()->last_login_at?->format('d/m/Y H:i') }}</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <!-- Cooperatives Stats -->
        <div class="col-xl-2 col-md-4 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Coopératives Actives
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ \App\Models\Cooperative::where('status', 'approved')->count() }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-building fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Users Stats -->
        <div class="col-xl-2 col-md-4 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Clients Actifs
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ \App\Models\User::where('role', 'client')->whereNotNull('email_verified_at')->count() }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pending Cooperatives -->
        <div class="col-xl-2 col-md-4 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Demandes Coopératives
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ \App\Models\Cooperative::where('status', 'pending')->count() }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Product Stats -->
        @php
            $productStats = [
                'total' => \App\Models\Product::whereIn('status', ['pending', 'approved', 'rejected', 'needs_info'])->count(),
                'pending' => \App\Models\Product::where('status', 'pending')->count(),
                'approved' => \App\Models\Product::where('status', 'approved')->count(),
                'rejected_or_info' => \App\Models\Product::whereIn('status', ['rejected', 'needs_info'])->count()
            ];
        @endphp

        <!-- Pending Products -->
        <div class="col-xl-2 col-md-4 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Produits en Attente
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $productStats['pending'] }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-box fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Approved Products -->
        <div class="col-xl-2 col-md-4 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Produits Approuvés
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $productStats['approved'] }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Categories -->
        <div class="col-xl-2 col-md-4 mb-4">
            <div class="card border-left-dark shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-dark text-uppercase mb-1">
                                Catégories Totales
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ \App\Models\Category::count() }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-tags fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Content Row -->
    <div class="row">
        <!-- Pending Product Requests -->
        <div class="col-lg-8 mb-4">
            <div class="card shadow">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-box-open me-2"></i>
                        Demandes de Produits en Attente
                    </h6>
                    <a href="{{ route('admin.product-requests.index') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-list me-1"></i>
                        Gérer toutes
                    </a>
                </div>
                <div class="card-body">
                    @php
                        $pendingProducts = \App\Models\Product::with(['cooperative', 'category', 'images'])
                            ->where('status', 'pending')
                            ->latest('submitted_at')
                            ->take(5)
                            ->get();
                    @endphp

                    @if($pendingProducts->count() > 0)
                        <div class="row">
                            @foreach($pendingProducts as $product)
                                <div class="col-md-6 col-lg-4 mb-3">
                                    <div class="card product-preview-card h-100" onclick="viewProductRequest({{ $product->id }})">
                                        <div class="position-relative">
                                            @if($product->primaryImageUrl)
                                                <img src="{{ $product->primaryImageUrl }}"
                                                     class="card-img-top product-preview-image"
                                                     alt="{{ $product->name }}">
                                            @else
                                                <div class="card-img-top product-preview-image bg-light d-flex align-items-center justify-content-center">
                                                    <i class="fas fa-image fa-2x text-muted"></i>
                                                </div>
                                            @endif
                                            <span class="position-absolute top-0 end-0 m-2 badge bg-warning">En attente</span>
                                        </div>
                                        <div class="card-body p-3">
                                            <h6 class="card-title mb-2">{{ Str::limit($product->name, 30) }}</h6>
                                            <p class="card-text small text-muted mb-2">
                                                <i class="fas fa-building me-1"></i>
                                                {{ $product->cooperative->name }}
                                            </p>
                                            <p class="card-text small text-muted mb-2">
                                                <i class="fas fa-tag me-1"></i>
                                                {{ $product->category->name }}
                                            </p>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span class="fw-bold text-success">{{ number_format($product->price, 2) }} MAD</span>
                                                <small class="text-muted">{{ $product->submitted_at->format('d/m') }}</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                            <h5>Aucune demande en attente</h5>
                            <p class="text-muted">Toutes les demandes de produits ont été traitées.</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Pending Cooperatives -->
            <div class="card shadow mt-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-building me-2"></i>
                        Demandes de Coopératives en Attente
                    </h6>
                    <a href="{{ route('admin.cooperatives.index') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-list me-1"></i>
                        Gérer toutes
                    </a>
                </div>
                <div class="card-body">
                    @php
                        $pendingCoops = \App\Models\Cooperative::with('admin')->where('status', 'pending')->latest()->take(5)->get();
                    @endphp

                    @if($pendingCoops->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th>Coopérative</th>
                                        <th>Secteur</th>
                                        <th>Administrateur</th>
                                        <th>Vérification</th>
                                        <th>Date Demande</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($pendingCoops as $coop)
                                    <tr>
                                        <td>{{ $coop->name }}</td>
                                        <td>{{ $coop->sector_of_activity }}</td>
                                        <td>{{ $coop->admin->full_name ?? 'N/A' }}</td>
                                        <td>
                                            <div>
                                                @if($coop->admin && $coop->admin->email_verified_at)
                                                    <span class="badge bg-success mb-1">
                                                        <i class="fas fa-user"></i> Utilisateur ✓
                                                    </span>
                                                @else
                                                    <span class="badge bg-warning mb-1">
                                                        <i class="fas fa-user"></i> Utilisateur ?
                                                    </span>
                                                @endif
                                                <br>
                                                @if($coop->email_verified_at)
                                                    <span class="badge bg-success">
                                                        <i class="fas fa-building"></i> Coopérative ✓
                                                    </span>
                                                @else
                                                    <span class="badge bg-warning">
                                                        <i class="fas fa-building"></i> Coopérative ?
                                                    </span>
                                                @endif
                                            </div>
                                        </td>
                                        <td>{{ $coop->created_at->format('d/m/Y') }}</td>
                                        <td>
                                            <a href="{{ route('admin.cooperatives.show', $coop) }}" class="btn btn-info btn-sm">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                            <h5>Aucune demande en attente</h5>
                            <p class="text-muted">Toutes les demandes de coopératives ont été traitées.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Actions Rapides</h6>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        <a href="{{ route('admin.cooperatives.index') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <div>
                                <i class="fas fa-building text-primary me-3"></i>
                                Gérer Coopératives
                            </div>
                            <span class="badge bg-info">{{ \App\Models\Cooperative::where('status', 'pending')->count() }}</span>
                        </a>
                        <a href="{{ route('admin.users.index') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <div>
                                <i class="fas fa-users text-success me-3"></i>
                                Gérer Utilisateurs
                            </div>
                            <i class="fas fa-chevron-right"></i>
                        </a>
                        <a href="{{ route('admin.product-requests.index') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <div>
                                <i class="fas fa-box-open text-warning me-3"></i>
                                Demandes de Produits
                            </div>
                            <span class="badge bg-warning">{{ $productStats['pending'] }}</span>
                        </a>
                        <a href="{{ route('admin.categories.index') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <div>
                                <i class="fas fa-tags text-info me-3"></i>
                                Gérer Catégories
                            </div>
                            <i class="fas fa-chevron-right"></i>
                        </a>
                        <a href="#" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <div>
                                <i class="fas fa-chart-bar text-dark me-3"></i>
                                Rapports & Statistiques
                            </div>
                            <i class="fas fa-chevron-right"></i>
                        </a>
                        <a href="#" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <div>
                                <i class="fas fa-cog text-secondary me-3"></i>
                                Paramètres Système
                            </div>
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Product Management Summary -->
            <div class="card shadow mt-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-warning">
                        <i class="fas fa-box-open me-2"></i>
                        Résumé Produits
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6 border-end">
                            <div class="h4 mb-0 text-warning">{{ $productStats['pending'] }}</div>
                            <small class="text-muted">En attente</small>
                        </div>
                        <div class="col-6">
                            <div class="h4 mb-0 text-success">{{ $productStats['approved'] }}</div>
                            <small class="text-muted">Approuvés</small>
                        </div>
                    </div>
                    <hr>
                    <div class="row text-center">
                        <div class="col-6 border-end">
                            <div class="h5 mb-0 text-danger">{{ $productStats['rejected_or_info'] }}</div>
                            <small class="text-muted">Rejetés/Info</small>
                        </div>
                        <div class="col-6">
                            <div class="h5 mb-0 text-primary">{{ $productStats['total'] }}</div>
                            <small class="text-muted">Total</small>
                        </div>
                    </div>
                    <div class="text-center mt-3">
                        <a href="{{ route('admin.product-requests.index') }}" class="btn btn-warning btn-sm">
                            <i class="fas fa-eye me-1"></i>
                            Voir toutes les demandes
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activities -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Activités Récentes</h6>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        @php
                            $recentProducts = \App\Models\Product::with(['cooperative', 'category'])
                                ->whereIn('status', ['pending', 'approved', 'rejected'])
                                ->where('submitted_at', '!=', null)
                                ->latest('submitted_at')
                                ->take(3)
                                ->get();

                            $recentCategories = \App\Models\Category::latest()->take(2)->get();
                            $recentCoops = \App\Models\Cooperative::latest()->take(2)->get();
                        @endphp

                        @foreach($recentProducts as $product)
                        <div class="timeline-item">
                            <div class="timeline-marker bg-{{ $product->status === 'approved' ? 'success' : ($product->status === 'pending' ? 'warning' : 'danger') }}"></div>
                            <div class="timeline-content">
                                <h6 class="timeline-title">
                                    @if($product->status === 'pending')
                                        Nouvelle demande de produit
                                    @elseif($product->status === 'approved')
                                        Produit approuvé
                                    @else
                                        Produit rejeté
                                    @endif
                                </h6>
                                <p class="timeline-text">
                                    <strong>{{ $product->name }}</strong> par {{ $product->cooperative->name }}
                                    <br>
                                    <small class="text-muted">
                                        <i class="fas fa-tag me-1"></i>
                                        {{ $product->category->name }} - {{ number_format($product->price, 2) }} MAD
                                    </small>
                                </p>
                                <small class="text-muted">{{ $product->submitted_at->diffForHumans() }}</small>
                            </div>
                        </div>
                        @endforeach

                        @foreach($recentCategories as $category)
                        <div class="timeline-item">
                            <div class="timeline-marker bg-info"></div>
                            <div class="timeline-content">
                                <h6 class="timeline-title">Nouvelle catégorie créée</h6>
                                <p class="timeline-text">Catégorie "{{ $category->name }}" a été ajoutée au système.</p>
                                <small class="text-muted">{{ $category->created_at->diffForHumans() }}</small>
                            </div>
                        </div>
                        @endforeach

                        @foreach($recentCoops as $coop)
                        <div class="timeline-item">
                            <div class="timeline-marker bg-{{ $coop->status === 'approved' ? 'success' : 'primary' }}"></div>
                            <div class="timeline-content">
                                <h6 class="timeline-title">
                                    {{ $coop->status === 'approved' ? 'Coopérative approuvée' : 'Nouvelle demande de coopérative' }}
                                </h6>
                                <p class="timeline-text">{{ $coop->name }} - {{ $coop->sector_of_activity }}</p>
                                <small class="text-muted">{{ $coop->created_at->diffForHumans() }}</small>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.border-left-primary { border-left: 0.25rem solid #4e73df !important; }
.border-left-success { border-left: 0.25rem solid #1cc88a !important; }
.border-left-info { border-left: 0.25rem solid #36b9cc !important; }
.border-left-warning { border-left: 0.25rem solid #f6c23e !important; }
.border-left-danger { border-left: 0.25rem solid #e74a3b !important; }
.border-left-dark { border-left: 0.25rem solid #5a5c69 !important; }

.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline:before {
    content: '';
    position: absolute;
    left: 15px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #e3e6f0;
}

.timeline-item {
    position: relative;
    margin-bottom: 30px;
}

.timeline-marker {
    position: absolute;
    left: -37px;
    top: 5px;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    border: 2px solid #fff;
    box-shadow: 0 0 0 3px #e3e6f0;
}

.timeline-content {
    padding-left: 20px;
}

.timeline-title {
    margin-bottom: 5px;
    font-size: 14px;
    font-weight: 600;
}

.timeline-text {
    margin-bottom: 5px;
    color: #6c757d;
    font-size: 13px;
}

.product-preview-card {
    cursor: pointer;
    transition: all 0.3s ease;
    border: none;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.product-preview-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.15);
}

.product-preview-image {
    height: 120px;
    width: 100%;
    object-fit: cover;
}
</style>
@endsection

@push('scripts')
<script>
function viewProductRequest(productId) {
    window.open(`{{ route('admin.product-requests.index') }}?search=&status=pending`, '_blank');
}

// Auto-refresh stats every 5 minutes
setInterval(function() {
    // You can implement AJAX refresh of stats here if needed
}, 300000);
</script>
@endpush
