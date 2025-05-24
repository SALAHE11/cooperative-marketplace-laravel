<?php

namespace App\Services;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class EmailService
{
    public static function sendVerificationCode($email, $code, $firstName = '', $type = 'user')
    {
        $mail = new PHPMailer(true);

        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host       = env('MAIL_HOST');
            $mail->SMTPAuth   = true;
            $mail->Username   = env('MAIL_USERNAME'); // Replace with your email
            $mail->Password   = env('MAIL_PASSWORD'); // Replace with your app password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = env('MAIL_PORT');

            // Recipients
            $mail->setFrom(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
            $mail->addAddress($email, $firstName);

            // Content
            $mail->isHTML(true);

            if ($type === 'user') {
                $mail->Subject = 'Vérification de votre compte - Coopérative E-commerce';
                $mail->Body    = "
                    <h2>Vérification de votre compte</h2>
                    <p>Bonjour $firstName,</p>
                    <p>Merci de vous être inscrit sur notre plateforme coopérative.</p>
                    <p>Votre code de vérification est: <strong style='font-size: 24px; color: #007bff;'>$code</strong></p>
                    <p>Ce code expire dans 15 minutes.</p>
                    <p>Si vous n'avez pas créé de compte, ignorez cet email.</p>
                    <br>
                    <p>Cordialement,<br>L'équipe Coopérative E-commerce</p>
                ";
            } else {
                $mail->Subject = 'Vérification email coopérative - Coopérative E-commerce';
                $mail->Body    = "
                    <h2>Vérification de l'email de la coopérative</h2>
                    <p>Bonjour,</p>
                    <p>Une demande d'inscription d'administrateur de coopérative a été faite avec cette adresse email.</p>
                    <p>Code de vérification: <strong style='font-size: 24px; color: #007bff;'>$code</strong></p>
                    <p>Ce code expire dans 15 minutes.</p>
                    <p>Si vous n'êtes pas à l'origine de cette demande, ignorez cet email.</p>
                    <br>
                    <p>Cordialement,<br>L'équipe Coopérative E-commerce</p>
                ";
            }

            $mail->send();
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    // Add this method to your existing EmailService class

public static function sendNotificationEmail($email, $subject, $htmlMessage, $firstName = '')
{
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = env('MAIL_HOST');
        $mail->SMTPAuth   = true;
        $mail->Username   = env('MAIL_USERNAME');
        $mail->Password   = env('MAIL_PASSWORD');
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = env('MAIL_PORT');

        // Recipients
        $mail->setFrom(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
        $mail->addAddress($email, $firstName);

        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = "
            <html>
            <head>
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                    .header { background: linear-gradient(45deg, #2c5aa0, #3d6bb3); color: white; text-align: center; padding: 30px; }
                    .content { padding: 30px; background: #f8f9fa; }
                    .footer { text-align: center; padding: 20px; color: #6c757d; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'>
                        <h1>Coopérative E-commerce</h1>
                    </div>
                    <div class='content'>
                        {$htmlMessage}
                    </div>
                    <div class='footer'>
                        <p>&copy; " . date('Y') . " Coopérative E-commerce. Tous droits réservés.</p>
                    </div>
                </div>
            </body>
            </html>
        ";

        $mail->send();
        return true;
    } catch (Exception $e) {
        return false;
    }
}
}
