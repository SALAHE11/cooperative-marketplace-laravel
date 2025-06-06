@extends('layouts.app')

@section('title', 'Bienvenue - Coopérative E-commerce')

@section('content')
<!-- Hero Section -->
<section class="hero-section">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <h1 class="display-4 mb-4">
                    <i class="fas fa-handshake me-3"></i>
                    Bienvenue sur la Plateforme Coopérative
                </h1>
                <p class="lead mb-5">
                    Découvrez et soutenez les produits locaux des coopératives marocaines.
                    Une plateforme qui connecte les producteurs locaux avec les consommateurs.
                </p>
                <div class="d-flex flex-wrap justify-content-center gap-3">
                    <a href="{{ route('client.register') }}" class="btn btn-light btn-lg">
                        <i class="fas fa-user me-2"></i>
                        Inscription Client
                    </a>
                    <a href="{{ route('coop.register') }}" class="btn btn-outline-light btn-lg">
                        <i class="fas fa-building me-2"></i>
                        Inscription Coopérative
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="py-5">
    <div class="container">
        <div class="row">
            <div class="col-12 text-center mb-5">
                <h2 class="h1 mb-4">Pourquoi Choisir Notre Plateforme?</h2>
                <p class="lead text-muted">Une solution complète pour les coopératives et leurs clients</p>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-4 col-md-6">
                <div class="card feature-card h-100">
                    <div class="card-body">
                        <div class="feature-icon">
                            <i class="fas fa-leaf"></i>
                        </div>
                        <h4>Produits Authentiques</h4>
                        <p class="text-muted">
                            Découvrez des produits authentiques directement des coopératives locales,
                            garantissant qualité et traçabilité.
                        </p>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-6">
                <div class="card feature-card h-100">
                    <div class="card-body">
                        <div class="feature-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <h4>Soutien Communautaire</h4>
                        <p class="text-muted">
                            Soutenez directement les producteurs locaux et contribuez au développement
                            économique de votre région.
                        </p>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-6">
                <div class="card feature-card h-100">
                    <div class="card-body">
                        <div class="feature-icon">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <h4>Transactions Sécurisées</h4>
                        <p class="text-muted">
                            Profitez d'un système de paiement sécurisé avec des reçus vérifiables
                            et un système d'autorisation pour les retraits.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto text-center">
                <h3 class="mb-4">Prêt à Commencer?</h3>
                <p class="lead mb-4">
                    Rejoignez notre communauté de coopératives et de clients satisfaits.
                </p>
                @guest
                    <div class="d-flex flex-wrap justify-content-center gap-3">
                        <a href="{{ route('login') }}" class="btn btn-primary btn-lg">
                            <i class="fas fa-sign-in-alt me-2"></i>
                            Se Connecter
                        </a>
                        <a href="{{ route('client.register') }}" class="btn btn-outline-primary btn-lg">
                            <i class="fas fa-user-plus me-2"></i>
                            Créer un Compte
                        </a>
                    </div>
                @else
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle me-2"></i>
                        Bienvenue, {{ Auth::user()->full_name }}!
                        <a href="
                            @if(Auth::user()->isSystemAdmin())
                                {{ route('admin.dashboard') }}
                            @elseif(Auth::user()->isCooperativeAdmin())
                                {{ route('coop.dashboard') }}
                            @else
                                {{ route('client.dashboard') }}
                            @endif
                        " class="alert-link">Accéder à votre tableau de bord</a>
                    </div>
                @endguest
            </div>
        </div>
    </div>
</section>
@endsection
