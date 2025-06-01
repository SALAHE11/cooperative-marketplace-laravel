<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reçu Client #{{ $receipt->receipt_number }}</title>
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
        }
        .header {
            background: linear-gradient(135deg, #007bff, #0056b3);
            color: white;
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
        .info-section {
            margin-bottom: 30px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 8px;
            border-left: 4px solid #007bff;
        }
        .info-section h3 {
            margin-top: 0;
            color: #007bff;
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
        .verification-code {
            font-size: 1.8rem;
            font-weight: bold;
            text-align: center;
            background: #fff3cd;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            border: 2px solid #ffc107;
        }
        .verification-code h3 {
            color: #856404;
            margin-top: 0;
        }
        .verification-code .code {
            color: #856404;
            letter-spacing: 3px;
            font-size: 2rem;
            margin: 10px 0;
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
        .pickup-section {
            background: #d4edda;
            border: 2px solid #28a745;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        .pickup-section h3 {
            color: #155724;
            margin-top: 0;
        }
        .qr-placeholder {
            width: 120px;
            height: 120px;
            background: #f8f9fa;
            border: 2px dashed #dee2e6;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px auto;
            font-size: 0.9rem;
            color: #666;
            text-align: center;
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
            background: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            border: none;
            cursor: pointer;
            font-weight: bold;
        }
        .btn:hover {
            background: #0056b3;
        }
        .btn-success {
            background: #28a745;
        }
        .btn-success:hover {
            background: #1e7e34;
        }
        .btn-info {
            background: #17a2b8;
        }
        .btn-info:hover {
            background: #138496;
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
            <h1>REÇU CLIENT</h1>
            <div class="subtitle">{{ $receipt->cooperative->name }}</div>
            <div class="subtitle">#{{ $receipt->receipt_number }}</div>
        </div>

        <div class="content">
            <!-- Order Information -->
            <div class="info-section">
                <h3>📋 Informations de la Commande</h3>
                <div class="info-row">
                    <span class="info-label">Numéro de commande:</span>
                    <span class="info-value">#{{ $receipt->order->order_number }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Date de commande:</span>
                    <span class="info-value">{{ $receipt->order->created_at->format('d/m/Y à H:i') }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Date du reçu:</span>
                    <span class="info-value">{{ $receipt->created_at->format('d/m/Y à H:i') }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Mode de paiement:</span>
                    <span class="info-value">{{ ucfirst($receipt->order->payment_method) }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Statut du paiement:</span>
                    <span class="info-value">{{ ucfirst($receipt->order->payment_status) }}</span>
                </div>
            </div>

            <!-- Client Information -->
            <div class="info-section">
                <h3>👤 Informations du Client</h3>
                <div class="info-row">
                    <span class="info-label">Nom:</span>
                    <span class="info-value">{{ $receipt->user->full_name }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Email:</span>
                    <span class="info-value">{{ $receipt->user->email }}</span>
                </div>
                @if($receipt->user->phone)
                <div class="info-row">
                    <span class="info-label">Téléphone:</span>
                    <span class="info-value">{{ $receipt->user->phone }}</span>
                </div>
                @endif
            </div>

            <!-- Order Items -->
            <div class="items-section">
                <h3>🛒 Articles Commandés</h3>
                @foreach($receipt->order->orderItems as $item)
                <div class="item-row">
                    <span>{{ $item->product ? $item->product->name : 'Produit supprimé' }} ({{ $item->quantity }})</span>
                    <span>{{ number_format($item->subtotal, 2) }} MAD</span>
                </div>
                @endforeach
                <div class="item-row">
                    <span>TOTAL:</span>
                    <span>{{ number_format($receipt->total_amount, 2) }} MAD</span>
                </div>
            </div>

            <!-- Verification Code -->
            <div class="verification-code">
                <h3>🔐 Code de Vérification</h3>
                <div class="code">{{ $receipt->verification_code }}</div>
                <p><strong>Présentez ce code lors du retrait</strong></p>

                <!-- QR Code Placeholder -->
                <div class="qr-placeholder">
                    QR CODE<br>
                    <small>{{ $receipt->verification_code }}</small>
                </div>
            </div>

            <!-- Pickup Information -->
            <div class="pickup-section">
                <h3>📍 Informations de Retrait</h3>
                <div class="info-row">
                    <span class="info-label">Coopérative:</span>
                    <span class="info-value">{{ $receipt->cooperative->name }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Adresse:</span>
                    <span class="info-value">{{ $receipt->cooperative->address }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Téléphone:</span>
                    <span class="info-value">{{ $receipt->cooperative->phone }}</span>
                </div>
                @if($receipt->cooperative->email)
                <div class="info-row">
                    <span class="info-label">Email:</span>
                    <span class="info-value">{{ $receipt->cooperative->email }}</span>
                </div>
                @endif

                <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #c3e6cb;">
                    <p><strong>📋 À apporter lors du retrait:</strong></p>
                    <ul style="margin: 10px 0; padding-left: 20px;">
                        <li>Ce reçu (imprimé ou numérique)</li>
                        <li>Votre pièce d'identité</li>
                        <li>Le code de vérification: <strong>{{ $receipt->verification_code }}</strong></li>
                    </ul>
                </div>
            </div>

            <!-- Legal Notice -->
            <div class="info-section">
                <h3>⚖️ Mentions Légales</h3>
                <ul style="font-size: 0.9rem; margin: 0; padding-left: 20px;">
                    <li>Ce reçu est valable uniquement pour la commande spécifiée ci-dessus.</li>
                    <li>La présentation de ce reçu et d'une pièce d'identité est obligatoire pour le retrait.</li>
                    <li>La coopérative se réserve le droit de vérifier l'identité du porteur.</li>
                    <li>En cas de perte de ce reçu, contactez immédiatement la coopérative.</li>
                    <li>Les produits commandés doivent être retirés dans les délais convenus.</li>
                </ul>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p><strong>Coopérative E-commerce</strong> - Plateforme de vente en ligne</p>
            <p>Ce reçu client certifie votre commande et vous donne droit au retrait des produits.</p>
            <p>Pour toute question, contactez le support: support@cooperative-ecommerce.ma</p>
            <p>Merci de votre confiance et bon shopping!</p>
        </div>
    </div>

    <!-- Action Buttons (Not printed) -->
    <div class="no-print">
        <button class="btn" onclick="window.print()">🖨️ Imprimer</button>
        <button class="btn btn-success" onclick="window.close()">✅ Fermer</button>
        <a href="{{ route('client.orders.show', $receipt->order) }}" class="btn btn-info">📋 Voir la commande</a>
        @if($receipt->order->status !== 'completed' && $receipt->order->status !== 'cancelled')
            <button class="btn" onclick="shareReceipt()" style="background: #6f42c1;">📤 Partager</button>
        @endif
    </div>

    <script>
        function shareReceipt() {
            const url = window.location.href;
            const text = `Mon reçu client #{{ $receipt->receipt_number }} - Commande #{{ $receipt->order->order_number }}`;

            if (navigator.share) {
                navigator.share({
                    title: text,
                    url: url
                });
            } else {
                // Fallback: copy to clipboard
                navigator.clipboard.writeText(url).then(() => {
                    alert('Lien du reçu copié dans le presse-papiers!');
                });
            }
        }
    </script>
</body>
</html>
