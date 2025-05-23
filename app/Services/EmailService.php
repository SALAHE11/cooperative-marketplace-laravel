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
}
