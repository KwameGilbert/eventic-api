<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * EmailService
 * 
 * Handles all email sending operations
 */
class EmailService
{
    private PHPMailer $mailer;
    private string $fromEmail;
    private string $fromName;

    public function __construct()
    {
        $this->mailer = new PHPMailer(true);
        
        // Configure SMTP
        $this->configureSMTP();
        
        $this->fromEmail = $_ENV['MAIL_FROM_ADDRESS'] ?? 'noreply@eventic.com';
        $this->fromName = $_ENV['MAIL_FROM_NAME'] ?? 'Eventic';
    }

    /**
     * Configure SMTP settings
     */
    private function configureSMTP(): void
    {
        try {
            $this->mailer->isSMTP();
            $this->mailer->Host = $_ENV['MAIL_HOST'] ?? 'smtp.mailtrap.io';
            $this->mailer->SMTPAuth = true;
            $this->mailer->Username = $_ENV['MAIL_USERNAME'] ?? '';
            $this->mailer->Password = $_ENV['MAIL_PASSWORD'] ?? '';
            $this->mailer->SMTPSecure = $_ENV['MAIL_ENCRYPTION'] ?? 'tls';
            $this->mailer->Port = (int)($_ENV['MAIL_PORT'] ?? 587);
            $this->mailer->CharSet = 'UTF-8';
        } catch (Exception $e) {
            error_log('SMTP configuration error: ' . $e->getMessage());
        }
    }

    /**
     * Send welcome email on registration
     *
     * @param User $user User
     * @return bool Success
     */
    public function sendWelcomeEmail(User $user): bool
    {
        try {
            $this->mailer->clearAddresses();
            $this->mailer->setFrom($this->fromEmail, $this->fromName);
            $this->mailer->addAddress($user->email, $user->name);
            
            $this->mailer->isHTML(true);
            $this->mailer->Subject = 'Welcome to Eventic!';
            $this->mailer->Body = $this->getWelcomeEmailTemplate($user);
            
            $this->mailer->send();
            return true;

        } catch (Exception $e) {
            error_log('Welcome email error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send email verification email
     *
     * @param User $user User
     * @param string $verificationUrl Verification URL
     * @return bool Success
     */
    public function sendEmailVerificationEmail(User $user, string $verificationUrl): bool
    {
        try {
            $this->mailer->clearAddresses();
            $this->mailer->setFrom($this->fromEmail, $this->fromName);
            $this->mailer->addAddress($user->email, $user->name);
            
            $this->mailer->isHTML(true);
            $this->mailer->Subject = 'Verify Your Email Address';
            $this->mailer->Body = $this->getVerificationEmailTemplate($user, $verificationUrl);
            
            $this->mailer->send();
            return true;

        } catch (Exception $e) {
            error_log('Verification email error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send password reset email
     *
     * @param User $user User
     * @param string $token Reset token
     * @return bool Success
     */
    public function sendPasswordResetEmail(User $user, string $token): bool
    {
        try {
            $resetUrl = $_ENV['APP_URL'] . "/reset-password?token={$token}&email=" . urlencode($user->email);

            $this->mailer->clearAddresses();
            $this->mailer->setFrom($this->fromEmail, $this->fromName);
            $this->mailer->addAddress($user->email, $user->name);
            
            $this->mailer->isHTML(true);
            $this->mailer->Subject = 'Password Reset Request';
            $this->mailer->Body = $this->getPasswordResetEmailTemplate($user, $resetUrl);
            
            $this->mailer->send();
            return true;

        } catch (Exception $e) {
            error_log('Password reset email error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send password changed confirmation email
     *
     * @param User $user User
     * @return bool Success
     */
    public function sendPasswordChangedEmail(User $user): bool
    {
        try {
            $this->mailer->clearAddresses();
            $this->mailer->setFrom($this->fromEmail, $this->fromName);
            $this->mailer->addAddress($user->email, $user->name);
            
            $this->mailer->isHTML(true);
            $this->mailer->Subject = 'Password Changed Successfully';
            $this->mailer->Body = $this->getPasswordChangedEmailTemplate($user);
            
            $this->mailer->send();
            return true;

        } catch (Exception $e) {
            error_log('Password changed email error: ' . $e->getMessage());
            return false;
        }
    }

    // ========================================
    // EMAIL TEMPLATES
    // ========================================

    private function getWelcomeEmailTemplate(User $user): string
    {
        $name = htmlspecialchars($user->name);
        return "
            <html>
            <body style='font-family: Arial, sans-serif;'>
                <h2>Welcome to Eventic, {$name}!</h2>
                <p>Thank you for registering. We're excited to have you on board.</p>
                <p>You can now start exploring events and booking tickets.</p>
                <br>
                <p>Best regards,<br>The Eventic Team</p>
            </body>
            </html>
        ";
    }

    private function getVerificationEmailTemplate(User $user, string $verificationUrl): string
    {
        $name = htmlspecialchars($user->name);
        return "
            <html>
            <body style='font-family: Arial, sans-serif;'>
                <h2>Verify Your Email</h2>
                <p>Hi {$name},</p>
                <p>Please click the button below to verify your email address:</p>
                <p>
                    <a href='{$verificationUrl}' style='background-color: #4CAF50; color: white; padding: 14px 20px; text-decoration: none; border-radius: 4px;'>
                        Verify Email
                    </a>
                </p>
                <p>This link will expire in 24 hours.</p>
                <p>If you didn't create this account, you can safely ignore this email.</p>
                <br>
                <p>Best regards,<br>The Eventic Team</p>
            </body>
            </html>
        ";
    }

    private function getPasswordResetEmailTemplate(User $user, string $resetUrl): string
    {
        $name = htmlspecialchars($user->name);
        return "
            <html>
            <body style='font-family: Arial, sans-serif;'>
                <h2>Password Reset Request</h2>
                <p>Hi {$name},</p>
                <p>We received a request to reset your password. Click the button below to reset it:</p>
                <p>
                    <a href='{$resetUrl}' style='background-color: #2196F3; color: white; padding: 14px 20px; text-decoration: none; border-radius: 4px;'>
                        Reset Password
                    </a>
                </p>
                <p>This link will expire in 1 hour.</p>
                <p>If you didn't request this, please ignore this email and your password will remain unchanged.</p>
                <br>
                <p>Best regards,<br>The Eventic Team</p>
            </body>
            </html>
        ";
    }

    private function getPasswordChangedEmailTemplate(User $user): string
    {
        $name = htmlspecialchars($user->name);
        return "
            <html>
            <body style='font-family: Arial, sans-serif;'>
                <h2>Password Changed Successfully</h2>
                <p>Hi {$name},</p>
                <p>This is a confirmation that your password has been changed successfully.</p>
                <p>If you didn't make this change, please contact our support team immediately.</p>
                <br>
                <p>Best regards,<br>The Eventic Team</p>
            </body>
            </html>
        ";
    }
}
