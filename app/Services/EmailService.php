<?php

namespace App\Services;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
use Illuminate\Support\Facades\Log;

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

    public static function sendPasswordResetCode($email, $code, $firstName)
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
        $mail->Subject = 'Code de réinitialisation - Coopérative E-commerce';
        $mail->Body    = "
            <html>
            <head>
                <meta charset='UTF-8'>
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                    .header { background: linear-gradient(45deg, #2c5aa0, #3d6bb3); color: white; text-align: center; padding: 30px; border-radius: 10px 10px 0 0; }
                    .content { padding: 30px; background: #f8f9fa; border-radius: 0 0 10px 10px; text-align: center; }
                    .code { font-size: 32px; font-weight: bold; color: #dc3545; letter-spacing: 8px; margin: 20px 0; padding: 15px; background: white; border: 2px dashed #dc3545; border-radius: 8px; }
                    .footer { text-align: center; padding: 20px; color: #6c757d; }
                    .warning { background: #f8d7da; border: 1px solid #f5c6cb; padding: 15px; border-radius: 5px; margin: 20px 0; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'>
                        <h1>Coopérative E-commerce</h1>
                        <p>Code de réinitialisation</p>
                    </div>
                    <div class='content'>
                        <h2>Code de vérification</h2>
                        <p>Bonjour {$firstName},</p>
                        <p>Voici votre code de vérification pour réinitialiser votre mot de passe:</p>
                        <div class='code'>{$code}</div>
                        <p>Saisissez ce code sur la page de vérification pour continuer.</p>
                        <div class='warning'>
                            <strong>⚠️ Important:</strong> Ce code expire dans 15 minutes. Si vous n'avez pas fait cette demande, ignorez cet email.
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

public static function sendJoinRequestNotification($adminEmail, $adminName, $requesterName, $cooperativeName, $message = '')
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

        $mail->CharSet = 'UTF-8';
        $mail->Encoding = 'base64';

        // Recipients
        $mail->setFrom(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
        $mail->addAddress($adminEmail, $adminName);

        $messageHtml = $message ? "<p><strong>Message du candidat:</strong></p><blockquote style='border-left: 3px solid #007bff; padding-left: 15px; margin: 15px 0; font-style: italic;'>{$message}</blockquote>" : '';

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Nouvelle demande d\'adhésion - ' . $cooperativeName;
        $mail->Body    = "
            <html>
            <head>
                <meta charset='UTF-8'>
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                    .header { background: linear-gradient(45deg, #28a745, #20c997); color: white; text-align: center; padding: 30px; border-radius: 10px 10px 0 0; }
                    .content { padding: 30px; background: #f8f9fa; border-radius: 0 0 10px 10px; }
                    .button { display: inline-block; padding: 15px 30px; background: #007bff; color: white; text-decoration: none; border-radius: 5px; font-weight: bold; margin: 20px 0; }
                    .footer { text-align: center; padding: 20px; color: #6c757d; }
                    .info-box { background: #e3f2fd; border: 1px solid #2196f3; padding: 15px; border-radius: 5px; margin: 20px 0; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'>
                        <h1>Nouvelle Demande d'Adhésion</h1>
                        <p>{$cooperativeName}</p>
                    </div>
                    <div class='content'>
                        <h2>Bonjour {$adminName},</h2>
                        <p>Vous avez reçu une nouvelle demande d'adhésion pour votre coopérative <strong>{$cooperativeName}</strong>.</p>

                        <div class='info-box'>
                            <h3>Détails du candidat:</h3>
                            <p><strong>Nom complet:</strong> {$requesterName}</p>
                            <p><strong>Date de demande:</strong> " . now()->format('d/m/Y à H:i') . "</p>
                        </div>

                        {$messageHtml}

                        <p>Pour examiner cette demande et prendre une décision, connectez-vous à votre tableau de bord administrateur de la coopérative.</p>

                        <div style='text-align: center;'>
                            <a href='" . url('/coop/dashboard') . "' class='button'>Voir les demandes d'adhésion</a>
                        </div>

                        <p><small><strong>Note:</strong> Cette demande nécessite votre approbation pour que le candidat puisse rejoindre votre équipe d'administration.</small></p>
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
        Log::error('Join request notification email failed', [
            'admin_email' => $adminEmail,
            'error' => $e->getMessage()
        ]);
        return false;
    }
}

public static function sendJoinRequestResponse($email, $firstName, $cooperativeName, $status, $message = '')
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

        $mail->CharSet = 'UTF-8';
        $mail->Encoding = 'base64';

        // Recipients
        $mail->setFrom(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
        $mail->addAddress($email, $firstName);

        $isApproved = $status === 'approved';
        $statusText = $isApproved ? 'approuvée' : 'rejetée';
        $headerColor = $isApproved ? '#28a745' : '#dc3545';
        $iconClass = $isApproved ? 'fa-check-circle' : 'fa-times-circle';

        $messageHtml = $message ? "<div class='message-box'><h4>Message de l'administrateur:</h4><p>{$message}</p></div>" : '';

        $actionButton = $isApproved ?
            "<div style='text-align: center; margin: 30px 0;'>
                <a href='" . url('/login') . "' class='button'>Se connecter maintenant</a>
            </div>" : '';

        // Content
        $mail->isHTML(true);
        $mail->Subject = "Demande d'adhésion {$statusText} - {$cooperativeName}";
        $mail->Body    = "
            <html>
            <head>
                <meta charset='UTF-8'>
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                    .header { background: {$headerColor}; color: white; text-align: center; padding: 30px; border-radius: 10px 10px 0 0; }
                    .content { padding: 30px; background: #f8f9fa; border-radius: 0 0 10px 10px; }
                    .button { display: inline-block; padding: 15px 30px; background: #007bff; color: white; text-decoration: none; border-radius: 5px; font-weight: bold; }
                    .footer { text-align: center; padding: 20px; color: #6c757d; }
                    .message-box { background: #e3f2fd; border: 1px solid #2196f3; padding: 15px; border-radius: 5px; margin: 20px 0; }
                    .success-box { background: #d4edda; border: 1px solid #c3e6cb; padding: 15px; border-radius: 5px; margin: 20px 0; }
                    .danger-box { background: #f8d7da; border: 1px solid #f5c6cb; padding: 15px; border-radius: 5px; margin: 20px 0; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'>
                        <i class='fas {$iconClass} fa-3x mb-3'></i>
                        <h1>Demande d'Adhésion " . ucfirst($statusText) . "</h1>
                        <p>{$cooperativeName}</p>
                    </div>
                    <div class='content'>
                        <h2>Bonjour {$firstName},</h2>
                        <p>Votre demande d'adhésion à la coopérative <strong>{$cooperativeName}</strong> a été <strong>{$statusText}</strong>.</p>

                        {$messageHtml}

                        " . ($isApproved ? "
                            <div class='success-box'>
                                <h4>🎉 Félicitations!</h4>
                                <p>Vous êtes maintenant administrateur de <strong>{$cooperativeName}</strong>. Vous pouvez vous connecter et accéder à toutes les fonctionnalités d'administration.</p>
                            </div>
                            {$actionButton}
                        " : "
                            <div class='danger-box'>
                                <h4>Demande non approuvée</h4>
                                <p>Malheureusement, votre demande n'a pas été approuvée cette fois. Vous pouvez contacter la coopérative pour plus d'informations ou soumettre une nouvelle demande à l'avenir.</p>
                            </div>
                        ") . "

                        <p>Pour toute question, n'hésitez pas à contacter directement la coopérative.</p>
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
        Log::error('Join request response email failed', [
            'email' => $email,
            'status' => $status,
            'error' => $e->getMessage()
        ]);
        return false;
    }
}

public static function sendClarificationRequest($email, $firstName, $cooperativeName, $adminName, $message)
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

        $mail->CharSet = 'UTF-8';
        $mail->Encoding = 'base64';

        // Recipients
        $mail->setFrom(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
        $mail->addAddress($email, $firstName);

        // Content
        $mail->isHTML(true);
        $mail->Subject = "Demande de clarification - {$cooperativeName}";
        $mail->Body    = "
            <html>
            <head>
                <meta charset='UTF-8'>
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                    .header { background: linear-gradient(45deg, #ffc107, #ff8f00); color: white; text-align: center; padding: 30px; border-radius: 10px 10px 0 0; }
                    .content { padding: 30px; background: #f8f9fa; border-radius: 0 0 10px 10px; }
                    .footer { text-align: center; padding: 20px; color: #6c757d; }
                    .message-box { background: #fff3cd; border: 1px solid #ffeaa7; padding: 20px; border-radius: 5px; margin: 20px 0; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'>
                        <i class='fas fa-question-circle fa-3x mb-3'></i>
                        <h1>Demande de Clarification</h1>
                        <p>{$cooperativeName}</p>
                    </div>
                    <div class='content'>
                        <h2>Bonjour {$firstName},</h2>
                        <p>L'administrateur <strong>{$adminName}</strong> de la coopérative <strong>{$cooperativeName}</strong> souhaite obtenir des clarifications concernant votre demande d'adhésion.</p>

                        <div class='message-box'>
                            <h4>Message de l'administrateur:</h4>
                            <p>{$message}</p>
                        </div>

                        <p>Nous vous encourageons à répondre directement à cet email pour fournir les informations demandées et faciliter le traitement de votre demande.</p>

                        <p><strong>Coordonnées de la coopérative:</strong></p>
                        <p>Vous pouvez répondre directement à cet email ou contacter la coopérative par leurs moyens de communication habituels.</p>
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
        Log::error('Clarification request email failed', [
            'email' => $email,
            'cooperative' => $cooperativeName,
            'error' => $e->getMessage()
        ]);
        return false;
    }
}

public static function sendAdminRemovedNotification($email, $firstName, $cooperativeName, $removedByName)
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

        $mail->CharSet = 'UTF-8';
        $mail->Encoding = 'base64';

        // Recipients
        $mail->setFrom(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
        $mail->addAddress($email, $firstName);

        // Content
        $mail->isHTML(true);
        $mail->Subject = "Retrait d'administration - {$cooperativeName}";
        $mail->Body    = "
            <html>
            <head>
                <meta charset='UTF-8'>
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                    .header { background: #6c757d; color: white; text-align: center; padding: 30px; border-radius: 10px 10px 0 0; }
                    .content { padding: 30px; background: #f8f9fa; border-radius: 0 0 10px 10px; }
                    .footer { text-align: center; padding: 20px; color: #6c757d; }
                    .info-box { background: #d1ecf1; border: 1px solid #bee5eb; padding: 15px; border-radius: 5px; margin: 20px 0; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'>
                        <i class='fas fa-user-minus fa-3x mb-3'></i>
                        <h1>Retrait d'Administration</h1>
                        <p>{$cooperativeName}</p>
                    </div>
                    <div class='content'>
                        <h2>Bonjour {$firstName},</h2>
                        <p>Nous vous informons que vos droits d'administration pour la coopérative <strong>{$cooperativeName}</strong> ont été retirés par <strong>{$removedByName}</strong>.</p>

                        <div class='info-box'>
                            <h4>Conséquences:</h4>
                            <ul>
                                <li>Vous n'avez plus accès au tableau de bord administrateur</li>
                                <li>Vos privilèges de gestion ont été révoqués</li>
                            </ul>
                        </div>

                        <p>Si vous pensez qu'il s'agit d'une erreur ou si vous souhaitez des clarifications, nous vous encourageons à contacter directement la coopérative.</p>

                        <p>Merci pour votre contribution passée à <strong>{$cooperativeName}</strong>.</p>
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
        Log::error('Admin removed notification email failed', [
            'email' => $email,
            'cooperative' => $cooperativeName,
            'error' => $e->getMessage()
        ]);
        return false;
    }
}
}
