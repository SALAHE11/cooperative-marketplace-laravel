<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Vérification Email - Coopérative E-commerce</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(45deg, #2c5aa0, #3d6bb3); color: white; text-align: center; padding: 30px; }
        .content { padding: 30px; background: #f8f9fa; }
        .code { font-size: 24px; font-weight: bold; color: #007bff; text-align: center; margin: 20px 0; }
        .footer { text-align: center; padding: 20px; color: #6c757d; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Coopérative E-commerce</h1>
            <p>Vérification de votre email</p>
        </div>
        <div class="content">
            <h2>Bonjour {{ $firstName ?? '' }},</h2>
            <p>Votre code de vérification est:</p>
            <div class="code">{{ $code }}</div>
            <p>Ce code expire dans 15 minutes.</p>
            <p>Si vous n'avez pas créé de compte, ignorez cet email.</p>
        </div>
        <div class="footer">
            <p>&copy; {{ date('Y') }} Coopérative E-commerce. Tous droits réservés.</p>
        </div>
    </div>
</body>
</html>
