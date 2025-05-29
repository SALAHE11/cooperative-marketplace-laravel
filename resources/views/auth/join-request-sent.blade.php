@extends('layouts.app')

@section('title', 'Demande Envoyée - Coopérative E-commerce')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card">
                <div class="card-body p-5 text-center">
                    <div class="mb-4">
                        <i class="fas fa-paper-plane fa-4x text-success mb-3"></i>
                        <h2 class="h3 mb-3 text-success">Demande d'Adhésion Envoyée!</h2>
                    </div>

                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i>
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <div class="alert alert-info text-start">
                        <h5 class="alert-heading">
                            <i class="fas fa-info-circle me-2"></i>
                            Prochaines étapes
                        </h5>
                        <hr>
                        <ul class="mb-0">
                            <li class="mb-2">
                                <strong>1. En attente d'approbation:</strong>
                                L'administrateur de la coopérative va examiner votre demande.
                            </li>
                            <li class="mb-2">
                                <strong>2. Notification par email:</strong>
                                Vous recevrez un email avec la décision (approbation ou refus).
                            </li>
                            <li class="mb-2">
                                <strong>3. Accès au tableau de bord:</strong>
                                Si approuvé, vous pourrez vous connecter et accéder aux fonctionnalités d'administration.
                            </li>
                        </ul>
                    </div>

                    <div class="row text-center mb-4">
                        <div class="col-md-4">
                            <div class="feature-box p-3">
                                <i class="fas fa-clock fa-2x text-warning mb-2"></i>
                                <h6>En Attente</h6>
                                <small class="text-muted">Votre demande est en cours d'examen</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="feature-box p-3">
                                <i class="fas fa-envelope fa-2x text-info mb-2"></i>
                                <h6>Notification</h6>
                                <small class="text-muted">Vous serez notifié par email</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="feature-box p-3">
                                <i class="fas fa-user-shield fa-2x text-success mb-2"></i>
                                <h6>Accès Admin</h6>
                                <small class="text-muted">Accès aux fonctionnalités d'administration</small>
                            </div>
                        </div>
                    </div>

                    <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                        <a href="{{ route('login') }}" class="btn btn-primary">
                            <i class="fas fa-sign-in-alt me-1"></i>
                            Se Connecter
                        </a>
                        <a href="{{ route('home') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-home me-1"></i>
                            Retour à l'Accueil
                        </a>
                    </div>

                    <div class="mt-4 pt-4 border-top">
                        <h6 class="text-muted mb-3">Questions Fréquentes</h6>
                        <div class="accordion" id="faqAccordion">
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="faq1">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse1">
                                        Combien de temps prend l'approbation?
                                    </button>
                                </h2>
                                <div id="collapse1" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body text-start">
                                        Le délai d'approbation dépend de l'administrateur de la coopérative.
                                        En général, vous devriez recevoir une réponse dans les 2-5 jours ouvrables.
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="faq2">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse2">
                                        Que faire si ma demande est refusée?
                                    </button>
                                </h2>
                                <div id="collapse2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body text-start">
                                        Si votre demande est refusée, vous pouvez contacter directement la coopérative
                                        pour obtenir plus d'informations ou soumettre une nouvelle demande après avoir
                                        résolu les problèmes mentionnés.
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="faq3">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse3">
                                        Puis-je modifier ma demande?
                                    </button>
                                </h2>
                                <div id="collapse3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body text-start">
                                        Une fois envoyée, la demande ne peut pas être modifiée.
                                        Cependant, vous pouvez contacter l'administrateur de la coopérative
                                        directement pour fournir des informations supplémentaires.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.feature-box {
    border-radius: 10px;
    transition: transform 0.2s;
}

.feature-box:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}
</style>
@endsection
