<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Coopérative E-commerce')</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <style>
        :root {
            --primary-color: #2c5aa0;
            --secondary-color: #28a745;
            --accent-color: #ffc107;
            --danger-color: #dc3545;
            --light-bg: #f8f9fa;
            --dark-text: #343a40;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, var(--light-bg) 0%, #e3f2fd 100%);
            min-height: 100vh;
        }

        .navbar-brand {
            font-weight: bold;
            color: var(--primary-color) !important;
        }

        .btn-primary {
            background: linear-gradient(45deg, var(--primary-color), #3d6bb3);
            border: none;
            border-radius: 25px;
            padding: 10px 25px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(44, 90, 160, 0.3);
        }

        .btn-success {
            background: linear-gradient(45deg, var(--secondary-color), #34ce57);
            border: none;
            border-radius: 25px;
            padding: 10px 25px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(40, 167, 69, 0.3);
        }

        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.9);
            transition: all 0.3s ease;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
        }

        .form-control {
            border-radius: 10px;
            border: 2px solid #e9ecef;
            padding: 12px 15px;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(44, 90, 160, 0.25);
        }

        .alert {
            border-radius: 10px;
            border: none;
            font-weight: 500;
        }

        .footer {
            background: linear-gradient(45deg, var(--primary-color), #2c5aa0);
            color: white;
            padding: 20px 0;
            margin-top: auto;
        }

        .loading {
            display: none;
        }

        .spinner-border {
            width: 1rem;
            height: 1rem;
        }

        .hero-section {
            background: linear-gradient(135deg, var(--primary-color) 0%, #3d6bb3 100%);
            color: white;
            padding: 80px 0;
            text-align: center;
        }

        .feature-card {
            text-align: center;
            padding: 30px 20px;
            margin: 20px 0;
        }

        .feature-icon {
            font-size: 3rem;
            color: var(--primary-color);
            margin-bottom: 20px;
        }

        .registration-type-card {
            cursor: pointer;
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }

        .registration-type-card:hover {
            border-color: var(--primary-color);
            transform: scale(1.02);
        }

        .registration-type-card.selected {
            border-color: var(--primary-color);
            background: rgba(44, 90, 160, 0.1);
        }

        @media (max-width: 768px) {
            .hero-section {
                padding: 40px 0;
            }

            .card {
                margin: 10px;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light bg-light shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="{{ route('home') }}">
                <i class="fas fa-handshake me-2"></i>
                Coopérative E-commerce
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    @auth
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user me-1"></i>
                                {{ Auth::user()->full_name }}
                            </a>
                            <ul class="dropdown-menu">
                                <li>
                                    @if(Auth::user()->isSystemAdmin())
                                        <a class="dropdown-item" href="{{ route('admin.dashboard') }}">
                                            <i class="fas fa-tachometer-alt me-1"></i> Tableau de bord
                                        </a>
                                    @elseif(Auth::user()->isCooperativeAdmin())
                                        <a class="dropdown-item" href="{{ route('coop.dashboard') }}">
                                            <i class="fas fa-tachometer-alt me-1"></i> Tableau de bord
                                        </a>
                                    @else
                                        <a class="dropdown-item" href="{{ route('client.dashboard') }}">
                                            <i class="fas fa-tachometer-alt me-1"></i> Tableau de bord
                                        </a>
                                    @endif
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="dropdown-item">
                                            <i class="fas fa-sign-out-alt me-1"></i> Déconnexion
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </li>
                    @else
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('login') }}">
                                <i class="fas fa-sign-in-alt me-1"></i> Connexion
                            </a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="registerDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user-plus me-1"></i> Inscription
                            </a>
                            <ul class="dropdown-menu">
                                <li>
                                    <a class="dropdown-item" href="{{ route('client.register') }}">
                                        <i class="fas fa-user me-1"></i> Client
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="{{ route('coop.register') }}">
                                        <i class="fas fa-building me-1"></i> Coopérative
                                    </a>
                                </li>
                            </ul>
                        </li>
                    @endauth
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main>
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="footer mt-5">
        <div class="container text-center">
            <p>&copy; {{ date('Y') }} Coopérative E-commerce. Tous droits réservés.</p>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Custom JavaScript -->
    <script>
        // Show loading state for forms
        function showLoading(button) {
            const spinner = button.querySelector('.loading');
            const text = button.querySelector('.btn-text');

            if (spinner && text) {
                spinner.style.display = 'inline-block';
                text.style.display = 'none';
                button.disabled = true;
            }
        }

        // Hide loading state
        function hideLoading(button) {
            const spinner = button.querySelector('.loading');
            const text = button.querySelector('.btn-text');

            if (spinner && text) {
                spinner.style.display = 'none';
                text.style.display = 'inline';
                button.disabled = false;
            }
        }

        // Auto-hide alerts after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                setTimeout(function() {
                    if (alert) {
                        alert.style.transition = 'opacity 0.5s';
                        alert.style.opacity = '0';
                        setTimeout(function() {
                            alert.remove();
                        }, 500);
                    }
                }, 5000);
            });
        });

        // Form validation helpers
        function validateEmail(email) {
            const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return re.test(email);
        }

        function validatePhone(phone) {
            const re = /^[\+]?[0-9\s\-\(\)]{10,}$/;
            return re.test(phone);
        }

        // Real-time form validation
        document.addEventListener('DOMContentLoaded', function() {
            const forms = document.querySelectorAll('form');

            forms.forEach(function(form) {
                const inputs = form.querySelectorAll('input[required], select[required], textarea[required]');

                inputs.forEach(function(input) {
                    input.addEventListener('blur', function() {
                        validateInput(this);
                    });

                    input.addEventListener('input', function() {
                        clearErrors(this);
                    });
                });
            });
        });

        function validateInput(input) {
            const value = input.value.trim();
            let isValid = true;
            let message = '';

            // Remove existing error styling
            input.classList.remove('is-invalid');

            // Check if required field is empty
            if (input.required && !value) {
                isValid = false;
                message = 'Ce champ est requis.';
            }

            // Email validation
            else if (input.type === 'email' && value && !validateEmail(value)) {
                isValid = false;
                message = 'Veuillez entrer une adresse email valide.';
            }

            // Phone validation
            else if (input.name === 'phone' && value && !validatePhone(value)) {
                isValid = false;
                message = 'Veuillez entrer un numéro de téléphone valide.';
            }

            // Password confirmation
            else if (input.name === 'password_confirmation') {
                const password = document.querySelector('input[name="password"]');
                if (password && value !== password.value) {
                    isValid = false;
                    message = 'Les mots de passe ne correspondent pas.';
                }
            }

            if (!isValid) {
                input.classList.add('is-invalid');
                showFieldError(input, message);
            }

            return isValid;
        }

        function clearErrors(input) {
            input.classList.remove('is-invalid');
            const feedback = input.parentNode.querySelector('.invalid-feedback');
            if (feedback) {
                feedback.remove();
            }
        }

        function showFieldError(input, message) {
            clearErrors(input);

            const errorDiv = document.createElement('div');
            errorDiv.className = 'invalid-feedback d-block';
            errorDiv.textContent = message;

            input.parentNode.appendChild(errorDiv);
        }
    </script>

    @stack('scripts')
</body>
</html>
