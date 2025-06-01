<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reçu Client #{{ $receipt->receipt_number }}</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background: #f8f9fa; }
        .receipt-container { max-width: 800px; margin: 0 auto; background: white; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        .header { background: linear-gradient(135deg, #007bff, #0056b3); color: white; padding: 30px; text-align: center; }
        .header h1 { margin: 0; font-size: 2.5rem; }
        .content { padding: 30px; }
        .info-section { margin-bottom: 30px; padding: 20px; background: #f8f9fa; border-radius: 8px; }
        .info-row { display: flex; justify-content: space-between; margin-bottom: 10px; }
        .verification-code { font-size: 1.8rem; font-weight: bold; text-align: center; background: #fff3cd; padding: 20px; border-radius: 8px; }
        .no-print { text-align: center; margin: 20px 0; }
        .btn { display: inline-block; padding: 10px 20px; margin: 5px; background: #007bff; color: white; text-decoration: none; border-radius: 5px; }
        @media print { .no-print { display: none; } }
    </style>
</head>
<body>
    <div class="receipt-container">
        <div class="header">
            <h1>REÇU CLIENT</h1>
            <div>{{ $receipt->cooperative->name }}</div>
            <div>#{{ $receipt->receipt_number }}</div>
        </div>

        <div class="content">
            <div class="info-section">
                <h3>Informations de la Commande</h3>
                <div class="info-row">
                    <span><strong>Numéro de commande:</strong></span>
                    <span>#{{ $receipt->order->order_number }}</span>
                </div>
                <div class="info-row">
                    <span><strong>Date:</strong></span>
                    <span>{{ $receipt->order->created_at->format('d/m/Y à H:i') }}</span>
                </div>
                <div class="info-row">
                    <span><strong>Client:</strong></span>
                    <span>{{ $receipt->user->full_name }}</span>
                </div>
                <div class="info-row">
                    <span><strong>Email:</strong></span>
                    <span>{{ $receipt->user->email }}</span>
                </div>
            </div>

            <div class="info-section">
                <h3>Articles Commandés</h3>
                @foreach($receipt->order->orderItems as $item)
                <div class="info-row">
                    <span>{{ $item->product ? $item->product->name : 'Produit supprimé' }} ({{ $item->quantity }})</span>
                    <span>{{ number_format($item->subtotal, 2) }} MAD</span>
                </div>
                @endforeach
                <hr>
                <div class="info-row">
                    <span><strong>TOTAL:</strong></span>
                    <span><strong>{{ number_format($receipt->total_amount, 2) }} MAD</strong></span>
                </div>
            </div>

            <div class="verification-code">
                <h3>Code de Vérification</h3>
                <div style="font-size: 2rem; color: #856404;">{{ $receipt->verification_code }}</div>
                <p>Présentez ce code lors du retrait</p>
            </div>

            <div class="info-section">
                <h3>Retrait</h3>
                <p><strong>Adresse:</strong> {{ $receipt->cooperative->address }}</p>
                <p><strong>Téléphone:</strong> {{ $receipt->cooperative->phone }}</p>
                <p><strong>À apporter:</strong> Ce reçu et votre pièce d'identité</p>
            </div>
        </div>
    </div>

    <div class="no-print">
        <button class="btn" onclick="window.print()">🖨️ Imprimer</button>
        <button class="btn" onclick="window.close()">✅ Fermer</button>
        <a href="{{ route('client.orders.show', $receipt->order) }}" class="btn">📋 Voir la commande</a>
    </div>
</body>
</html>
