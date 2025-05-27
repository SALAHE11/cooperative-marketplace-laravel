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
            $mail->Username   = env('MAIL_USERNAME');
            $mail->Password   = env('MAIL_PASSWORD');
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = env('MAIL_PORT');

            // IMPORTANT: Set character encoding to UTF-8
            $mail->CharSet = 'UTF-8';
            $mail->Encoding = 'base64';

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
            } elseif ($type === 'admin') {
                $mail->Subject = 'Vérification compte administrateur - Coopérative E-commerce';
                $mail->Body    = "
                    <h2>Vérification de votre compte administrateur</h2>
                    <p>Bonjour $firstName,</p>
                    <p>Merci d'avoir accepté l'invitation d'administrateur.</p>
                    <p>Votre code de vérification est: <strong style='font-size: 24px; color: #007bff;'>$code</strong></p>
                    <p>Ce code expire dans 15 minutes.</p>
                    <p>Une fois vérifié, vous aurez accès aux fonctionnalités d'administration.</p>
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

    public static function sendAdminInvitation($email, $token, $inviterName)
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

            // IMPORTANT: Set character encoding to UTF-8
            $mail->CharSet = 'UTF-8';
            $mail->Encoding = 'base64';

            // Recipients
            $mail->setFrom(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
            $mail->addAddress($email);

            // Content
            $registrationUrl = url("/admin/register/{$token}");

            $mail->isHTML(true);
            $mail->Subject = 'Invitation Administrateur - Coopérative E-commerce';
            $mail->Body    = "
                <html>
                <head>
                    <meta charset='UTF-8'>
                    <style>
                        body { font-family: Arial, sans-serif; line-height: 1.6; }
                        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                        .header { background: linear-gradient(45deg, #2c5aa0, #3d6bb3); color: white; text-align: center; padding: 30px; border-radius: 10px 10px 0 0; }
                        .content { padding: 30px; background: #f8f9fa; border-radius: 0 0 10px 10px; }
                        .button { display: inline-block; padding: 15px 30px; background: #007bff; color: white; text-decoration: none; border-radius: 5px; font-weight: bold; margin: 20px 0; }
                        .footer { text-align: center; padding: 20px; color: #6c757d; }
                        .warning { background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 5px; margin: 20px 0; }
                    </style>
                </head>
                <body>
                    <div class='container'>
                        <div class='header'>
                            <h1>Coopérative E-commerce</h1>
                            <p>Invitation Administrateur</p>
                        </div>
                        <div class='content'>
                            <h2>Vous êtes invité(e) à devenir administrateur</h2>
                            <p>Bonjour,</p>
                            <p><strong>{$inviterName}</strong> vous a invité(e) à rejoindre l'équipe d'administration de la plateforme Coopérative E-commerce.</p>
                            <p>En tant qu'administrateur, vous aurez accès à:</p>
                            <ul>
                                <li>Gestion des utilisateurs et coopératives</li>
                                <li>Validation des demandes d'inscription</li>
                                <li>Administration de la plateforme</li>
                            </ul>
                            <div style='text-align: center;'>
                                <a href='{$registrationUrl}' class='button'>Accepter l'invitation</a>
                            </div>
                            <div class='warning'>
                                <strong>⚠️ Important:</strong> Cette invitation expire dans 7 jours. Si vous n'êtes pas à l'origine de cette demande, ignorez cet email.
                            </div>
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

    public static function sendPasswordResetEmail($email, $token, $firstName)
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

            // IMPORTANT: Set character encoding to UTF-8
            $mail->CharSet = 'UTF-8';
            $mail->Encoding = 'base64';

            // Recipients
            $mail->setFrom(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
            $mail->addAddress($email, $firstName);

            // Content
            $resetUrl = url("/password/reset/{$token}");

            $mail->isHTML(true);
            $mail->Subject = 'Réinitialisation de mot de passe - Coopérative E-commerce';
            $mail->Body    = "
                <html>
                <head>
                    <meta charset='UTF-8'>
                    <style>
                        body { font-family: Arial, sans-serif; line-height: 1.6; }
                        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                        .header { background: linear-gradient(45deg, #2c5aa0, #3d6bb3); color: white; text-align: center; padding: 30px; border-radius: 10px 10px 0 0; }
                        .content { padding: 30px; background: #f8f9fa; border-radius: 0 0 10px 10px; }
                        .button { display: inline-block; padding: 15px 30px; background: #dc3545; color: white; text-decoration: none; border-radius: 5px; font-weight: bold; margin: 20px 0; }
                        .footer { text-align: center; padding: 20px; color: #6c757d; }
                        .warning { background: #f8d7da; border: 1px solid #f5c6cb; padding: 15px; border-radius: 5px; margin: 20px 0; }
                    </style>
                </head>
                <body>
                    <div class='container'>
                        <div class='header'>
                            <h1>Coopérative E-commerce</h1>
                            <p>Réinitialisation de mot de passe</p>
                        </div>
                        <div class='content'>
                            <h2>Demande de réinitialisation</h2>
                            <p>Bonjour {$firstName},</p>
                            <p>Vous avez demandé une réinitialisation de votre mot de passe.</p>
                            <p>Cliquez sur le bouton ci-dessous pour créer un nouveau mot de passe:</p>
                            <div style='text-align: center;'>
                                <a href='{$resetUrl}' class='button'>Réinitialiser mon mot de passe</a>
                            </div>
                            <div class='warning'>
                                <strong>⚠️ Important:</strong> Ce lien expire dans 1 heure. Si vous n'avez pas fait cette demande, ignorez cet email.
                            </div>
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

            // IMPORTANT: Set character encoding to UTF-8
            $mail->CharSet = 'UTF-8';
            $mail->Encoding = 'base64';

            // Recipients
            $mail->setFrom(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
            $mail->addAddress($email, $firstName);

            // Content
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = "
                <html>
                <head>
                    <meta charset='UTF-8'>
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
