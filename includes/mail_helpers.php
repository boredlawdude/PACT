<?php
declare(strict_types=1);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function password_reset_mail_config(string $envKey, ?string $constantName = null, ?string $default = null): string
{
    if ($constantName !== null && defined($constantName)) {
        $value = constant($constantName);
        if ($value !== '') {
            return (string) $value;
        }
    }

    $value = $_ENV[$envKey] ?? $default;

    return $value === null ? '' : (string) $value;
}

function send_reset_email(string $toEmail, string $resetLink): void
{
    $mail = new PHPMailer(true);
    $mail->SMTPDebug  = 0;
    $mail->Debugoutput = function ($str, $level) { error_log("SMTPDBG[$level] $str"); };

    try {
        $host = password_reset_mail_config('SMTP_HOST', 'SMTP_HOST');
        $username = password_reset_mail_config('SMTP_USERNAME', 'SMTP_USERNAME');
        $password = password_reset_mail_config('SMTP_PASSWORD', 'SMTP_PASSWORD');
        $fromEmail = password_reset_mail_config('MAIL_FROM_EMAIL', 'MAIL_FROM_EMAIL');
        $fromName = password_reset_mail_config('MAIL_FROM_NAME', 'MAIL_FROM_NAME');
        $port = (int) password_reset_mail_config('SMTP_PORT', 'SMTP_PORT', '587');
        $secure = strtolower(password_reset_mail_config('SMTP_SECURE', 'SMTP_SECURE', 'tls'));

        if ($host === '' || $username === '' || $password === '' || $fromEmail === '') {
            throw new RuntimeException('Password reset email is not configured.');
        }

        $mail->isSMTP();
        $mail->Host       = $host;
        $mail->SMTPAuth   = true;
        $mail->Username   = $username;
        $mail->Password   = $password;
        $mail->Port       = $port;
        $mail->Timeout    = 15;

        if ($secure === 'ssl') {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        } elseif ($secure === 'tls') {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        } else {
            $mail->SMTPSecure = '';
            $mail->SMTPAutoTLS = false;
        }

        $mail->setFrom($fromEmail, $fromName);
        $mail->addAddress($toEmail);

        $mail->isHTML(true);
        $mail->Subject = 'Password reset link';

        $safeLink = htmlspecialchars($resetLink, ENT_QUOTES, 'UTF-8');

        $mail->Body = "
            <p>You requested a password reset.</p>
            <p><a href=\"{$safeLink}\">Click here to reset your password</a></p>
            <p>If you did not request this, you can ignore this email.</p>
            <p>This link expires in 60 minutes.</p>
        ";

        $mail->AltBody =
            "You requested a password reset.\n\n" .
            "Reset link: {$resetLink}\n\n" .
            "If you did not request this, ignore this email.\n" .
            "This link expires in 60 minutes.\n";

        $mail->send();
    } catch (Exception $e) {
        $detail = $mail->ErrorInfo !== '' ? $mail->ErrorInfo : $e->getMessage();
        error_log('Password reset email failed: ' . $detail);
        throw new RuntimeException('Password reset email is temporarily unavailable.', 0, $e);
    }
}
