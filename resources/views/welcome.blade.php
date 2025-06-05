@extends('layouts.app')

@section('title', 'Bienvenue - Coopérative E-commerce')

@section('content')
<style>
    /* Reset and base styles */
    .welcome-page * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    .welcome-page {
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        line-height: 1.6;
        overflow-x: hidden;
    }

    /* Animated gradient background */
    .welcome-page .hero-section {
        min-height: 100vh;
        background: linear-gradient(-45deg, #667eea, #764ba2, #f093fb, #f5576c, #4facfe, #00f2fe);
        background-size: 400% 400%;
        animation: gradientShift 15s ease infinite;
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        overflow: hidden;
    }

    @keyframes gradientShift {
        0% { background-position: 0% 50%; }
        50% { background-position: 100% 50%; }
        100% { background-position: 0% 50%; }
    }

    /* Floating shapes */
    .welcome-page .floating-shape {
        position: absolute;
        opacity: 0.1;
        animation: float 6s ease-in-out infinite;
    }

    .welcome-page .floating-shape:nth-child(1) {
        top: 20%;
        left: 10%;
        width: 60px;
        height: 60px;
        background: white;
        border-radius: 50%;
        animation-delay: 0s;
    }

    .welcome-page .floating-shape:nth-child(2) {
        top: 60%;
        right: 15%;
        width: 80px;
        height: 80px;
        background: white;
        border-radius: 20%;
        animation-delay: 2s;
    }

    .welcome-page .floating-shape:nth-child(3) {
        bottom: 20%;
        left: 20%;
        width: 40px;
        height: 40px;
        background: white;
        transform: rotate(45deg);
        animation-delay: 4s;
    }

    @keyframes float {
        0%, 100% { transform: translateY(0px) rotate(0deg); }
        50% { transform: translateY(-20px) rotate(10deg); }
    }

    .welcome-page .container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 20px;
        position: relative;
        z-index: 2;
    }

    .welcome-page .hero-content {
        text-align: center;
        max-width: 800px;
        margin: 0 auto;
        animation: fadeInUp 1s ease-out;
    }

    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .welcome-page .hero-title {
        font-size: 3.5rem;
        font-weight: 800;
        margin-bottom: 1.5rem;
        text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        animation: slideInLeft 1s ease-out 0.3s both;
    }

    .welcome-page .hero-subtitle {
        font-size: 1.3rem;
        margin-bottom: 2.5rem;
        opacity: 0.95;
        font-weight: 300;
        animation: slideInRight 1s ease-out 0.6s both;
    }

    @keyframes slideInLeft {
        from {
            opacity: 0;
            transform: translateX(-50px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    @keyframes slideInRight {
        from {
            opacity: 0;
            transform: translateX(50px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    .welcome-page .cta-buttons {
        display: flex;
        gap: 20px;
        justify-content: center;
        flex-wrap: wrap;
        animation: fadeIn 1s ease-out 0.9s both;
    }

    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    .welcome-page .btn {
        padding: 15px 30px;
        border: none;
        border-radius: 50px;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
        display: inline-flex;
        align-items: center;
        gap: 10px;
        font-size: 1.1rem;
        cursor: pointer;
    }

    .welcome-page .btn::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
        transition: left 0.5s;
    }

    .welcome-page .btn:hover::before {
        left: 100%;
    }

    .welcome-page .btn-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        box-shadow: 0 10px 30px rgba(102, 126, 234, 0.4);
    }

    .welcome-page .btn-primary:hover {
        transform: translateY(-3px);
        box-shadow: 0 15px 40px rgba(102, 126, 234, 0.6);
        color: white;
        text-decoration: none;
    }

    .welcome-page .btn-secondary {
        background: rgba(255, 255, 255, 0.2);
        color: white;
        border: 2px solid rgba(255, 255, 255, 0.3);
        backdrop-filter: blur(10px);
    }

    .welcome-page .btn-secondary:hover {
        background: rgba(255, 255, 255, 0.3);
        transform: translateY(-3px);
        box-shadow: 0 15px 40px rgba(255, 255, 255, 0.2);
        color: white;
        text-decoration: none;
    }

    /* Features Section */
    .welcome-page .features-section {
        padding: 100px 0;
        background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        position: relative;
    }

    .welcome-page .section-title {
        text-align: center;
        margin-bottom: 80px;
    }

    .welcome-page .section-title h2 {
        font-size: 3rem;
        font-weight: 700;
        background: linear-gradient(135deg, #667eea, #764ba2);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        margin-bottom: 20px;
    }

    .welcome-page .section-title p {
        font-size: 1.2rem;
        color: #666;
        max-width: 600px;
        margin: 0 auto;
    }

    .welcome-page .features-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
        gap: 40px;
        margin-top: 60px;
    }

    .welcome-page .feature-card {
        background: white;
        border-radius: 20px;
        padding: 40px 30px;
        text-align: center;
        box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        transition: all 0.4s ease;
        position: relative;
        overflow: hidden;
        border: none;
    }

    .welcome-page .feature-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(135deg, #667eea, #764ba2);
        transform: scaleX(0);
        transition: transform 0.3s ease;
    }

    .welcome-page .feature-card:hover::before {
        transform: scaleX(1);
    }

    .welcome-page .feature-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 30px 60px rgba(0,0,0,0.15);
    }

    .welcome-page .feature-icon {
        width: 80px;
        height: 80px;
        margin: 0 auto 30px;
        background: linear-gradient(135deg, #667eea, #764ba2);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
        color: white;
        transition: all 0.3s ease;
    }

    .welcome-page .feature-card:hover .feature-icon {
        transform: scale(1.1) rotate(360deg);
    }

    .welcome-page .feature-card h4 {
        font-size: 1.5rem;
        font-weight: 600;
        margin-bottom: 20px;
        color: #333;
    }

    .welcome-page .feature-card p {
        color: #666;
        line-height: 1.6;
    }

    /* CTA Section */
    .welcome-page .cta-section {
        padding: 100px 0;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
        color: white;
        text-align: center;
        position: relative;
        overflow: hidden;
    }

    .welcome-page .cta-section::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 20"><circle cx="10" cy="10" r="1" fill="rgba(255,255,255,0.1)"/><circle cx="30" cy="5" r="1" fill="rgba(255,255,255,0.1)"/><circle cx="50" cy="15" r="1" fill="rgba(255,255,255,0.1)"/><circle cx="70" cy="8" r="1" fill="rgba(255,255,255,0.1)"/><circle cx="90" cy="12" r="1" fill="rgba(255,255,255,0.1)"/></svg>');
        animation: sparkle 20s linear infinite;
    }

    @keyframes sparkle {
        0% { transform: translateX(0); }
        100% { transform: translateX(100px); }
    }

    .welcome-page .cta-content {
        position: relative;
        z-index: 2;
        max-width: 800px;
        margin: 0 auto;
    }

    .welcome-page .cta-title {
        font-size: 2.5rem;
        font-weight: 700;
        margin-bottom: 20px;
    }

    .welcome-page .cta-subtitle {
        font-size: 1.2rem;
        margin-bottom: 40px;
        opacity: 0.9;
    }

    .welcome-page .login-section {
        background: rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(10px);
        border-radius: 20px;
        padding: 30px;
        margin-top: 40px;
        border: 1px solid rgba(255, 255, 255, 0.2);
    }

    .welcome-page .alert {
        background: rgba(255, 255, 255, 0.95);
        color: #333;
        padding: 20px;
        border-radius: 15px;
        margin-top: 20px;
        border: none;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    }

    .welcome-page .alert-link {
        color: #667eea;
        text-decoration: none;
        font-weight: 600;
    }

    .welcome-page .alert-link:hover {
        text-decoration: underline;
        color: #667eea;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .welcome-page .hero-title {
            font-size: 2.5rem;
        }

        .welcome-page .hero-subtitle {
            font-size: 1.1rem;
        }

        .welcome-page .cta-buttons {
            flex-direction: column;
            align-items: center;
        }

        .welcome-page .features-grid {
            grid-template-columns: 1fr;
            gap: 30px;
        }

        .welcome-page .section-title h2 {
            font-size: 2.2rem;
        }

        .welcome-page .cta-title {
            font-size: 2rem;
        }
    }

    /* Scroll animations */
    .welcome-page .fade-in {
        opacity: 0;
        transform: translateY(30px);
        transition: all 0.6s ease;
    }

    .welcome-page .fade-in.visible {
        opacity: 1;
        transform: translateY(0);
    }
</style>

<div class="welcome-page">
    <!-- Hero Section -->
    <section class="hero-section">
        <div class="floating-shape"></div>
        <div class="floating-shape"></div>
        <div class="floating-shape"></div>

        <div class="container">
            <div class="hero-content">
                <h1 class="hero-title">
                    <i class="fas fa-handshake" style="margin-right: 20px;"></i>
                    Plateforme Coopérative
                </h1>
                <p class="hero-subtitle">
                    Découvrez et soutenez les produits locaux des coopératives marocaines.
                    Une plateforme qui connecte les producteurs locaux avec les consommateurs.
                </p>
                <div class="cta-buttons">
                    <a href="{{ route('client.register') }}" class="btn btn-primary">
                        <i class="fas fa-user"></i>
                        Inscription Client
                    </a>
                    <a href="{{ route('coop.register') }}" class="btn btn-secondary">
                        <i class="fas fa-building"></i>
                        Inscription Coopérative
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features-section">
        <div class="container">
            <div class="section-title fade-in">
                <h2>Pourquoi Choisir Notre Plateforme?</h2>
                <p>Une solution complète pour les coopératives et leurs clients</p>
            </div>

            <div class="features-grid">
                <div class="feature-card fade-in">
                    <div class="feature-icon">
                        <i class="fas fa-leaf"></i>
                    </div>
                    <h4>Produits Authentiques</h4>
                    <p>
                        Découvrez des produits authentiques directement des coopératives locales,
                        garantissant qualité et traçabilité.
                    </p>
                </div>

                <div class="feature-card fade-in">
                    <div class="feature-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <h4>Soutien Communautaire</h4>
                    <p>
                        Soutenez directement les producteurs locaux et contribuez au développement
                        économique de votre région.
                    </p>
                </div>

                <div class="feature-card fade-in">
                    <div class="feature-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h4>Transactions Sécurisées</h4>
                    <p>
                        Profitez d'un système de paiement sécurisé avec des reçus vérifiables
                        et un système d'autorisation pour les retraits.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section">
        <div class="container">
            <div class="cta-content">
                <h3 class="cta-title">Prêt à Commencer?</h3>
                <p class="cta-subtitle">
                    Rejoignez notre communauté de coopératives et de clients satisfaits.
                </p>

                @guest
                    <div class="login-section">
                        <div class="cta-buttons">
                            <a href="{{ route('login') }}" class="btn btn-primary">
                                <i class="fas fa-sign-in-alt"></i>
                                Se Connecter
                            </a>
                            <a href="{{ route('client.register') }}" class="btn btn-secondary">
                                <i class="fas fa-user-plus"></i>
                                Créer un Compte
                            </a>
                        </div>
                    </div>
                @else
                    <div class="alert">
                        <i class="fas fa-check-circle" style="margin-right: 10px;"></i>
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
    </section>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Scroll animations
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -100px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                }
            });
        }, observerOptions);

        document.querySelectorAll('.welcome-page .fade-in').forEach(el => {
            observer.observe(el);
        });

        // Add stagger animation to feature cards
        const featureCards = document.querySelectorAll('.welcome-page .feature-card');
        featureCards.forEach((card, index) => {
            card.style.animationDelay = `${index * 0.2}s`;
        });
    });
</script>
@endsection
