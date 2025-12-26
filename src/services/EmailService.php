<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * EmailService
 * 
 * Handles all email sending operations using external template files
 */
class EmailService
{
    private PHPMailer $mailer;
    private string $fromEmail;
    private string $fromName;
    private string $templatePath;

    public function __construct()
    {
        $this->mailer = new PHPMailer(true);
        
        // Configure SMTP
        $this->configureSMTP();
        
        $this->fromEmail = $_ENV['MAIL_FROM_ADDRESS'] ?? 'noreply@eventic.com';
        $this->fromName = $_ENV['MAIL_FROM_NAME'] ?? 'Eventic';
        $this->templatePath = dirname(__DIR__, 2) . '/templates/email/';
    }

    /**
     * Configure SMTP settings
     */
    private function configureSMTP(): void
    {
        try {
            $this->mailer->isSMTP();
            $this->mailer->Host = $_ENV['MAIL_HOST'] ?? 'smtp.gmail.com';
            $this->mailer->SMTPAuth = true;
            $this->mailer->Username = $_ENV['MAIL_USERNAME'] ?? 'eventic@gmail.com';
            $this->mailer->Password = $_ENV['MAIL_PASSWORD'] ?? 'eventic123';
            $this->mailer->SMTPSecure = $_ENV['MAIL_ENCRYPTION'] ?? 'tls';
            $this->mailer->Port = (int)($_ENV['MAIL_PORT'] ?? 587);
            $this->mailer->CharSet = 'UTF-8';
        } catch (Exception $e) {
            error_log('SMTP configuration error: ' . $e->getMessage());
        }
    }

    // ========================================
    // TEMPLATE LOADING SYSTEM
    // ========================================

    /**
     * Load a template configuration from JSON file
     */
    private function loadTemplateConfig(string $templateName): ?array
    {
        $jsonPath = $this->templatePath . $templateName . '.json';
        
        if (!file_exists($jsonPath)) {
            error_log("Email template config not found: {$jsonPath}");
            return null;
        }
        
        $config = json_decode(file_get_contents($jsonPath), true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log("Invalid JSON in email template config: {$jsonPath}");
            return null;
        }
        
        return $config;
    }

    /**
     * Load HTML content from a template file
     */
    private function loadTemplateContent(string $filename): ?string
    {
        $htmlPath = $this->templatePath . $filename;
        
        if (!file_exists($htmlPath)) {
            error_log("Email template content not found: {$htmlPath}");
            return null;
        }
        
        return file_get_contents($htmlPath);
    }

    /**
     * Load the base template
     */
    private function loadBaseTemplate(): string
    {
        $basePath = $this->templatePath . 'base.html';
        
        if (!file_exists($basePath)) {
            error_log("Base email template not found: {$basePath}");
            return '{{content}}'; // Fallback to just content
        }
        
        return file_get_contents($basePath);
    }

    /**
     * Replace placeholders in template with actual values
     */
    private function replacePlaceholders(string $template, array $variables): string
    {
        foreach ($variables as $key => $value) {
            $template = str_replace('{{' . $key . '}}', (string)$value, $template);
        }
        
        return $template;
    }

    /**
     * Get common template variables
     */
    private function getCommonVariables(): array
    {
        return [
            'app_url' => $_ENV['FRONTEND_URL'] ?? 'https://eventic.com',
            'support_email' => $_ENV['MAIL_FROM_ADDRESS'] ?? 'support@eventic.com',
            'year' => date('Y'),
            'social_facebook' => $_ENV['SOCIAL_FACEBOOK'] ?? '#',
            'social_twitter' => $_ENV['SOCIAL_TWITTER'] ?? '#',
            'social_instagram' => $_ENV['SOCIAL_INSTAGRAM'] ?? '#',
        ];
    }

    /**
     * Build a complete email from template
     */
    private function buildEmailFromTemplate(string $templateName, array $variables = []): ?array
    {
        // Load template config
        $config = $this->loadTemplateConfig($templateName);
        if (!$config) {
            return null;
        }
        
        // Load content template
        $contentHtml = $this->loadTemplateContent($config['content_file']);
        if (!$contentHtml) {
            return null;
        }
        
        // Merge variables with common ones
        $allVariables = array_merge($this->getCommonVariables(), $variables);
        
        // Replace placeholders in content
        $content = $this->replacePlaceholders($contentHtml, $allVariables);
        
        // Load base template and inject content
        $baseTemplate = $this->loadBaseTemplate();
        $allVariables['content'] = $content;
        $allVariables['subject'] = $config['subject'];
        $allVariables['preheader'] = $config['preheader'] ?? '';
        
        // Build final HTML
        $finalHtml = $this->replacePlaceholders($baseTemplate, $allVariables);
        
        return [
            'subject' => $this->replacePlaceholders($config['subject'], $allVariables),
            'body' => $finalHtml,
        ];
    }

    // ========================================
    // EMAIL SENDING METHODS
    // ========================================

    /**
     * Send welcome email on registration
     */
    public function sendWelcomeEmail(User $user): bool
    {
        try {
            $email = $this->buildEmailFromTemplate('welcome', [
                'user_name' => htmlspecialchars($user->name),
                'user_email' => htmlspecialchars($user->email),
            ]);
            
            if (!$email) {
                error_log('Failed to build welcome email template');
                return false;
            }

            $this->mailer->clearAddresses();
            $this->mailer->setFrom($this->fromEmail, $this->fromName);
            $this->mailer->addAddress($user->email, $user->name);
            
            $this->mailer->isHTML(true);
            $this->mailer->Subject = $email['subject'];
            $this->mailer->Body = $email['body'];
            
            $this->mailer->send();
            return true;

        } catch (Exception $e) {
            error_log('Welcome email error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send email verification email
     */
    public function sendEmailVerificationEmail(User $user, string $verificationUrl): bool
    {
        try {
            $email = $this->buildEmailFromTemplate('email_verification', [
                'user_name' => htmlspecialchars($user->name),
                'user_email' => htmlspecialchars($user->email),
                'verification_url' => $verificationUrl,
            ]);
            
            if (!$email) {
                error_log('Failed to build verification email template');
                return false;
            }

            $this->mailer->clearAddresses();
            $this->mailer->setFrom($this->fromEmail, $this->fromName);
            $this->mailer->addAddress($user->email, $user->name);
            
            $this->mailer->isHTML(true);
            $this->mailer->Subject = $email['subject'];
            $this->mailer->Body = $email['body'];
            
            $this->mailer->send();
            return true;

        } catch (Exception $e) {
            error_log('Verification email error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send password reset email
     */
    public function sendPasswordResetEmail(User $user, string $token): bool
    {
        try {
            $resetUrl = ($_ENV['FRONTEND_URL'] ?? 'http://localhost:5173') . "/reset-password?token={$token}&email=" . urlencode($user->email);
            
            $email = $this->buildEmailFromTemplate('password_reset', [
                'user_name' => htmlspecialchars($user->name),
                'user_email' => htmlspecialchars($user->email),
                'reset_url' => $resetUrl,
            ]);
            
            if (!$email) {
                error_log('Failed to build password reset email template');
                return false;
            }

            $this->mailer->clearAddresses();
            $this->mailer->setFrom($this->fromEmail, $this->fromName);
            $this->mailer->addAddress($user->email, $user->name);
            
            $this->mailer->isHTML(true);
            $this->mailer->Subject = $email['subject'];
            $this->mailer->Body = $email['body'];
            
            $this->mailer->send();
            return true;

        } catch (Exception $e) {
            error_log('Password reset email error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send password changed confirmation email
     */
    public function sendPasswordChangedEmail(User $user): bool
    {
        try {
            $email = $this->buildEmailFromTemplate('password_changed', [
                'user_name' => htmlspecialchars($user->name),
                'user_email' => htmlspecialchars($user->email),
                'timestamp' => date('F j, Y \a\t g:i A T'),
            ]);
            
            if (!$email) {
                error_log('Failed to build password changed email template');
                return false;
            }

            $this->mailer->clearAddresses();
            $this->mailer->setFrom($this->fromEmail, $this->fromName);
            $this->mailer->addAddress($user->email, $user->name);
            
            $this->mailer->isHTML(true);
            $this->mailer->Subject = $email['subject'];
            $this->mailer->Body = $email['body'];
            
            $this->mailer->send();
            return true;

        } catch (Exception $e) {
            error_log('Password changed email error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Generic send method for NotificationService compatibility
     */
    public function send(string $to, string $subject, string $body, ?string $fromName = null): bool
    {
        try {
            $this->mailer->clearAddresses();
            $this->mailer->setFrom($this->fromEmail, $fromName ?? $this->fromName);
            $this->mailer->addAddress($to);
            
            $this->mailer->isHTML(true);
            $this->mailer->Subject = $subject;
            $this->mailer->Body = $body;
            
            $this->mailer->send();
            return true;

        } catch (Exception $e) {
            error_log('Email send error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send a custom email using a template
     * 
     * @param string $templateName Name of the template (without extension)
     * @param string $toEmail Recipient email
     * @param string $toName Recipient name
     * @param array $variables Template variables
     * @return bool
     */
    public function sendFromTemplate(string $templateName, string $toEmail, string $toName, array $variables = []): bool
    {
        try {
            $email = $this->buildEmailFromTemplate($templateName, $variables);
            
            if (!$email) {
                error_log("Failed to build email template: {$templateName}");
                return false;
            }

            $this->mailer->clearAddresses();
            $this->mailer->setFrom($this->fromEmail, $this->fromName);
            $this->mailer->addAddress($toEmail, $toName);
            
            $this->mailer->isHTML(true);
            $this->mailer->Subject = $email['subject'];
            $this->mailer->Body = $email['body'];
            
            $this->mailer->send();
            return true;

        } catch (Exception $e) {
            error_log("Template email error ({$templateName}): " . $e->getMessage());
            return false;
        }
    }
}
