<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reçu d'Autorisation #{{ $authReceipt->auth_number }}</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 20px;
            background: #f8f9fa;
            color: #333;
        }
        .receipt-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            overflow: hidden;
            border: 3px solid #ffc107;
        }
        .header {
            background: linear-gradient(135deg, #ffc107, #e0a800);
            color: #212529;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 2.5rem;
            font-weight: bold;
        }
        .header .subtitle {
            margin: 10px 0 0 0;
            font-size: 1.1rem;
        }
        .content {
            padding: 30px;
        }
        .warning-section {
            background: #fff3cd;
            border: 2px solid #ffc107;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 30px;
            text-align: center;
        }
        .warning-section h3 {
            color: #856404;
            margin-top: 0;
        }
        .info-section {
            margin-bottom: 30px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 8px;
            border-left: 4px solid #ffc107;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            padding: 5px 0;
        }
        .info-label {
            font-weight: bold;
            color: #666;
        }
        .info-value {
            color: #333;
        }
        .authorized-person {
            background: linear-gradient(135deg, #17a2b8, #138496);
            color: white;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            margin: 20px 0;
        }
        .verification-section {
            background: #d1ecf1;
            border: 2px solid #17a2b8;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            margin: 20px 0;
        }
        .verification-code {
            font-size: 2.2rem;
            font-weight: bold;
            color: #0c5460;
            letter-spacing: 4px;
            margin: 15px 0;
            padding: 15px;
            background: white;
            border-radius: 8px;
            border: 2px solid #17a2b8;
        }
        .validity-section {
            background: #d4edda;
            border: 2px solid #28a745;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            margin: 20px 0;
        }
        .items-section {
            background: #e9ecef;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .items-section h3 {
            margin-top: 0;
            color: #495057;
        }
        .item-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #dee2e6;
        }
        .item-row:last-child {
            border-bottom: none;
            font-weight: bold;
            font-size: 1.1rem;
            color: #28a745;
            border-top: 2px solid #28a745;
            padding-top: 10px;
            margin-top: 10px;
        }
        .footer {
            background: #f8f9fa;
            padding: 20px;
            text-align: center;
            color: #666;
            font-size: 0.9rem;
        }
        .no-print {
            text-align: center;
            margin: 20px 0;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            margin: 5px;
            background: #ffc107;
            color: #212529;
            text-decoration: none;
            border-radius: 5px;
            border: none;
            cursor: pointer;
            font-weight: bold;
        }
        .btn:hover {
            background: #e0a800;
        }
        .btn-info {
            background: #17a2b8;
            color: white;
        }
        .btn-info:hover {
            background: #138496;
        }
        .checklist {
            background: #e9ecef;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .checklist ul {
            margin: 10px 0;
        }
        .checklist li {
            margin-bottom: 8px;
        }
        @media print {
            body {
                background: white;
                padding: 0;
            }
            .receipt-container {
                box-shadow: none;
                border-radius: 0;
            }
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="receipt-container">
        <!-- Header -->
        <div class="header">
            <h1>REÇU D'AUTORISATION</h1>
            <div class="subtitle">{{ $authReceipt->clientReceipt->cooperative->name }}</div>
            <div class="subtitle">#{{ $authReceipt->auth_number }}</div>
        </div>

        <div class="content">
            <!-- Warning Section -->
            <div class="warning-section">
                <h3>⚠️ DOCUMENT OFFICIEL D'AUTORISATION</h3>
                <p><strong>Ce document autorise le retrait de commande par une personne désignée.</strong></p>
                <p>La présentation de ce reçu et d'une pièce d'identité valide est obligatoire.</p>
            </div>

            <!-- Original Order Information -->
            <div class="info-section">
                <h3>Commande Originale</h3>
                <div class="info-row">
                    <span class="info-label">Numéro de commande:</span>
                    <span class="info-value">#{{ $authReceipt->clientReceipt->order->order_number }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Reçu client:</span>
                    <span class="info-value">#{{ $authReceipt->clientReceipt->receipt_number }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Montant total:</span>
                    <span class="info-value">{{ number_format($authReceipt->clientReceipt->total_amount, 2) }} MAD</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Date de commande:</span>
                    <span class="info-value">{{ $authReceipt->clientReceipt->order->created_at->format('d/m/Y à H:i') }}</span>
                </div>
            </div>

            <!-- Client Information -->
            <div class="info-section">
                <h3>Client Commanditaire</h3>
                <div class="info-row">
                    <span class="info-label">Nom:</span>
                    <span class="info-value">{{ $authReceipt->clientReceipt->user->full_name }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Email:</span>
                    <span class="info-value">{{ $authReceipt->clientReceipt->user->email }}</span>
                </div>
                @if($authReceipt->clientReceipt->user->phone)
                <div class="info-row">
                    <span class="info-label">Téléphone:</span>
                    <span class="info-value">{{ $authReceipt->clientReceipt->user->phone }}</span>
                </div>
                @endif
            </div>

            <!-- Authorized Person -->
            <div class="authorized-person">
                <h3>👤 PERSONNE AUTORISÉE</h3>
                <h2>{{ $authReceipt->authorized_person_name }}</h2>
                <p>Cette personne est officiellement autorisée à récupérer la commande</p>
            </div>

            <!-- Order Items -->
            <div class="items-section">
                <h3>🛒 Articles à Récupérer</h3>
                @foreach($authReceipt->clientReceipt->order->orderItems as $item)
                <div class="item-row">
                    <span>{{ $item->product ? $item->product->name : 'Produit supprimé' }} ({{ $item->quantity }})</span>
                    <span>{{ number_format($item->subtotal, 2) }} MAD</span>
                </div>
                @endforeach
                <div class="item-row">
                    <span>TOTAL:</span>
                    <span>{{ number_format($authReceipt->clientReceipt->total_amount, 2) }} MAD</span>
                </div>
            </div>

            <!-- Authorization Details -->
            <div class="info-section">
                <h3>Détails de l'Autorisation</h3>
                <div class="info-row">
                    <span class="info-label">Numéro d'autorisation:</span>
                    <span class="info-value">#{{ $authReceipt->auth_number }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Code unique:</span>
                    <span class="info-value">{{ $authReceipt->unique_code }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Date d'émission:</span>
                    <span class="info-value">{{ $authReceipt->validity_start->format('d/m/Y à H:i') }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Valide jusqu'au:</span>
                    <span class="info-value">{{ $authReceipt->validity_end->format('d/m/Y à H:i') }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Statut:</span>
                    <span class="info-value">
                        @if($authReceipt->is_used)
                            ✅ Utilisé le {{ $authReceipt->used_at->format('d/m/Y à H:i') }}
                        @elseif($authReceipt->is_revoked)
                            ❌ Révoqué
                        @elseif($authReceipt->validity_end < now())
                            ⏰ Expiré
                        @else
                            ✅ Valide
                        @endif
                    </span>
                </div>
            </div>

            <!-- Validity Status -->
            <div class="validity-section">
                <h3>
                    @if($authReceipt->is_used)
                        ✅ AUTORISATION UTILISÉE
                    @elseif($authReceipt->is_revoked)
                        ❌ AUTORISATION RÉVOQUÉE
                    @elseif($authReceipt->validity_end < now())
                        ⏰ AUTORISATION EXPIRÉE
                    @else
                        ✅ AUTORISATION VALIDE
                    @endif
                </h3>
                @if(!$authReceipt->is_used && !$authReceipt->is_revoked && $authReceipt->validity_end >= now())
                    <p>Cette autorisation est valide jusqu'au {{ $authReceipt->validity_end->format('d/m/Y à H:i') }}</p>
                @endif
            </div>

            <!-- Verification Code -->
            <div class="verification-section">
                <h3>🔐 Code de Vérification</h3>
                <div class="verification-code">{{ $authReceipt->unique_code }}</div>
                <p><strong>Code à présenter à la coopérative</strong></p>
            </div>

            <!-- Pickup Instructions -->
            <div class="checklist">
                <h3>📋 PROCÉDURE DE RETRAIT</h3>
                <p><strong>La personne autorisée doit:</strong></p>
                <ul>
                    <li>✓ Présenter ce reçu d'autorisation (imprimé ou numérique)</li>
                    <li>✓ Présenter une pièce d'identité valide correspondant au nom: <strong>{{ $authReceipt->authorized_person_name }}</strong></li>
                    <li>✓ Fournir le code de vérification: <strong>{{ $authReceipt->unique_code }}</strong></li>
                    <li>✓ Se rendre à l'adresse de retrait dans les délais de validité</li>
                </ul>

                <p><strong>Lieu de retrait:</strong></p>
                <div style="background: white; padding: 15px; border-radius: 5px; margin-top: 10px;">
                    <strong>{{ $authReceipt->clientReceipt->cooperative->name }}</strong><br>
                    {{ $authReceipt->clientReceipt->cooperative->address }}<br>
                    Téléphone: {{ $authReceipt->clientReceipt->cooperative->phone }}
                </div>
            </div>

            <!-- Legal Notice -->
            <div class="info-section">
                <h3>⚖️ Mentions Légales</h3>
                <ul style="font-size: 0.9rem;">
                    <li>Ce document d'autorisation est nominatif et non transférable.</li>
                    <li>La coopérative se réserve le droit de vérifier l'identité de la personne autorisée.</li>
                    <li>En cas de doute, la coopérative peut refuser la remise de la commande.</li>
                    <li>Le client commanditaire reste responsable de sa commande jusqu'au retrait effectif.</li>
                    <li>Cette autorisation peut être révoquée à tout moment par le client commanditaire.</li>
                </ul>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>Coopérative E-commerce - Plateforme de vente en ligne</p>
            <p>Ce reçu d'autorisation est valide uniquement pour la commande spécifiée ci-dessus.</p>
            <p>Pour toute question, contactez le support: support@cooperative-ecommerce.ma</p>
        </div>
    </div>

    <!-- Action Buttons (Not printed) -->
    <div class="no-print">
        <button class="btn" onclick="window.print()">🖨️ Imprimer</button>
        <button class="btn btn-info" onclick="window.close()">✅ Fermer</button>
        <a href="{{ route('client.orders.show', $authReceipt->clientReceipt->order) }}" class="btn">📋 Voir la commande</a>
        <a href="{{ route('client.receipts.client', $authReceipt->clientReceipt) }}" class="btn">📄 Reçu client</a>
    </div>

    <script>
        // Check validity and show warning if expired
        @if($authReceipt->validity_end < now())
        window.onload = function() {
            alert('⚠️ ATTENTION: Cette autorisation a expiré le {{ $authReceipt->validity_end->format("d/m/Y à H:i") }}');
        };
        @endif
    </script>
</body>
</html>
