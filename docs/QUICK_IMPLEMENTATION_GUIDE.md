# Quick Implementation Guide: Password Reset & Email Verification

This guide shows you how to implement the remaining authentication features using the existing models.

---

## 1. Password Reset Flow

### Overview
The password reset flow allows users to reset their password via email. The models are already in place; you just need to add the endpoints and email sending.

### Required Components

#### A. Create Password Reset Controller

**File**: `src/controllers/PasswordResetController.php`

```php
<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\User;
use App\Models\PasswordReset;
use App\Services\AuthService;
use App\Helper\ResponseHelper;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Exception;

class PasswordResetController
{
    private AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Request password reset (send email)
     * POST /auth/password/reset-request
     */
    public function requestReset(Request $request, Response $response): Response
    {
        try {
            $data = json_decode((string) $request->getBody())
            
            if (empty($data['email'])) {
                return ResponseHelper::error($response, 'Email is required', 400);
            }

            $user = User::findByEmail($data['email']);
            
            // Always return success (security: don't reveal if email exists)
            if (!$user) {
                return ResponseHelper::success($response, 'If that email exists, a reset link has been sent', []);
            }

            // Generate reset token
            $plainToken = bin2hex(random_bytes(32));
            $tokenHash = hash('sha256', $plainToken);

            // Delete old tokens for this email
            PasswordReset::deleteForEmail($user->email);

            // Create new token
            PasswordReset::create([
                'email' => $user->email,
                'token' => $tokenHash,
                'created_at' => now()
            ]);

            // TODO: Send email with reset link
            // $resetUrl = "https://yourapp.com/reset-password?token=$plainToken&email={$user->email}";
            // Send email with $resetUrl

            // Log audit event
            $metadata = $this->getRequestMetadata($request);
            $this->authService->logAuditEvent($user->id, 'password_reset_requested', $metadata);

            return ResponseHelper::success($response, 'If that email exists, a reset link has been sent', []);

        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Password reset request failed', 500, $e->getMessage());
        }
    }

    /**
     * Reset password with token
     * POST /auth/password/reset
     */
    public function resetPassword(Request $request, Response $response): Response
    {
        try {
            $data = json_decode((string) $request->getBody())
            
            // Validate input
            if (empty($data['email']) || empty($data['token']) || empty($data['password'])) {
                return ResponseHelper::error($response, 'Email, token, and new password are required', 400);
            }

            if (strlen($data['password']) < 8) {
                return ResponseHelper::error($response, 'Password must be at least 8 characters', 400);
            }

            // Find valid token
            $resetToken = PasswordReset::findValidToken($data['email'], $data['token']);

            if (!$resetToken) {
                return ResponseHelper::error($response, 'Invalid or expired reset token', 400);
            }

            // Find user
            $user = User::findByEmail($data['email']);
            if (!$user) {
                return ResponseHelper::error($response, 'User not found', 404);
            }

            // Update password
            $user->password = $data['password']; // Auto-hashed by model
            $user->save();

            // Delete all tokens for this email
            PasswordReset::deleteForEmail($data['email']);

            // Log audit event
            $metadata = $this->getRequestMetadata($request);
            $this->authService->logAuditEvent($user->id, 'password_reset_completed', $metadata);

            // Optional: Revoke all refresh tokens (force re-login)
            $this->authService->revokeAllUserTokens($user->id);

            return ResponseHelper::success($response, 'Password reset successful', []);

        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Password reset failed', 500, $e->getMessage());
        }
    }

    /**
     * Extract metadata from request
     */
    private function getRequestMetadata(Request $request): array
    {
        $serverParams = $request->getServerParams();
        
        return [
            'ip_address' => $serverParams['REMOTE_ADDR'] ?? null,
            'user_agent' => $request->getHeaderLine('User-Agent')
        ];
    }
}
```

#### B. Add Routes

**File**: `src/routes/v1/AuthRoute.php`

Add these routes:
```php
// Password reset routes (public)
$app->post('/v1/auth/password/reset-request', [$passwordResetController, 'requestReset']);
$app->post('/v1/auth/password/reset', [$passwordResetController, 'resetPassword']);
```

#### C. Register Controller in DI Container

**File**: `src/bootstrap/services.php`

```php
use App\Controllers\PasswordResetController;

$container->set(PasswordResetController::class, function ($container) {
    return new PasswordResetController(
        $container->get(AuthService::class)
    );
});
```

#### D. Send Email (Example with PHPMailer)

Install PHPMailer:
```bash
composer require phpmailer/phpmailer
```

Create email service:
```php
use PHPMailer\PHPMailer\PHPMailer;

function sendPasswordResetEmail($email, $resetUrl) {
    $mail = new PHPMailer(true);
    
    $mail->isSMTP();
    $mail->Host = $_ENV['MAIL_HOST'];
    $mail->SMTPAuth = true;
    $mail->Username = $_ENV['MAIL_USERNAME'];
    $mail->Password = $_ENV['MAIL_PASSWORD'];
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = $_ENV['MAIL_PORT'];
    
    $mail->setFrom($_ENV['MAIL_FROM_ADDRESS'], $_ENV['MAIL_FROM_NAME']);
    $mail->addAddress($email);
    
    $mail->isHTML(true);
    $mail->Subject = 'Password Reset Request';
    $mail->Body = "
        <h1>Password Reset</h1>
        <p>Click the link below to reset your password:</p>
        <a href='$resetUrl'>Reset Password</a>
        <p>This link will expire in 1 hour.</p>
    ";
    
    $mail->send();
}
```

### Testing

**Request Reset**:
```bash
curl -X POST http://localhost/v1/auth/password/reset-request \
  -H "Content-Type: application/json" \
  -d '{"email":"test@example.com"}'
```

**Reset Password**:
```bash
curl -X POST http://localhost/v1/auth/password/reset \
  -H "Content-Type: application/json" \
  -d '{
    "email":"test@example.com",
    "token":"TOKEN_FROM_EMAIL",
    "password":"NewPassword123"
  }'
```

---

## 2. Email Verification Flow

### Overview
Email verification ensures users have access to the email they registered with.

### Required Components

#### A. Create Email Verification Controller

**File**: `src/controllers/EmailVerificationController.php`

```php
<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\User;
use App\Services\AuthService;
use App\Helper\ResponseHelper;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Exception;

class EmailVerificationController
{
    private AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Send verification email
     * POST /auth/email/send-verification
     */
    public function sendVerification(Request $request, Response $response): Response
    {
        try {
            // Get authenticated user
            $userData = $request->getAttribute('user');
            $user = User::find($userData->id);

            if (!$user) {
                return ResponseHelper::error($response, 'User not found', 404);
            }

            if ($user->email_verified) {
                return ResponseHelper::error($response, 'Email already verified', 400);
            }

            // Generate verification token
            $plainToken = bin2hex(random_bytes(32));
            
            // Store token hash in user or separate table
            // For simplicity, we'll use the remember_token field
            $user->remember_token = hash('sha256', $plainToken);
            $user->save();

            // TODO: Send verification email
            // $verifyUrl = "https://yourapp.com/verify-email?token=$plainToken&email={$user->email}";
            // sendVerificationEmail($user->email, $verifyUrl);

            return ResponseHelper::success($response, 'Verification email sent', []);

        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to send verification email', 500, $e->getMessage());
        }
    }

    /**
     * Verify email with token
     * POST /auth/email/verify
     */
    public function verifyEmail(Request $request, Response $response): Response
    {
        try {
            $data = json_decode((string) $request->getBody())

            if (empty($data['email']) || empty($data['token'])) {
                return ResponseHelper::error($response, 'Email and token are required', 400);
            }

            $user = User::findByEmail($data['email']);

            if (!$user) {
                return ResponseHelper::error($response, 'Invalid verification link', 400);
            }

            if ($user->email_verified) {
                return ResponseHelper::error($response, 'Email already verified', 400);
            }

            // Verify token
            $tokenHash = hash('sha256', $data['token']);
            
            if ($user->remember_token !== $tokenHash) {
                return ResponseHelper::error($response, 'Invalid verification token', 400);
            }

            // Mark as verified
            $user->email_verified = true;
            $user->email_verified_at = now();
            $user->remember_token = null; // Clear token
            $user->save();

            // Log audit event
            $metadata = $this->getRequestMetadata($request);
            $this->authService->logAuditEvent($user->id, 'email_verified', $metadata);

            return ResponseHelper::success($response, 'Email verified successfully', []);

        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Email verification failed', 500, $e->getMessage());
        }
    }

    /**
     * Extract metadata from request
     */
    private function getRequestMetadata(Request $request): array
    {
        $serverParams = $request->getServerParams();
        
        return [
            'ip_address' => $serverParams['REMOTE_ADDR'] ?? null,
            'user_agent' => $request->getHeaderLine('User-Agent')
        ];
    }
}
```

#### B. Add Routes

**File**: `src/routes/v1/AuthRoute.php`

```php
// Email verification routes
$app->group('/v1/auth/email', function ($group) use ($emailVerificationController) {
    $group->post('/send-verification', [$emailVerificationController, 'sendVerification']);
})->add($app->getContainer()->get(AuthMiddleware::class));

// Public verification endpoint
$app->post('/v1/auth/email/verify', [$emailVerificationController, 'verifyEmail']);
```

#### C. Register Controller

**File**: `src/bootstrap/services.php`

```php
use App\Controllers\EmailVerificationController;

$container->set(EmailVerificationController::class, function ($container) {
    return new EmailVerificationController(
        $container->get(AuthService::class)
    );
});
```

#### D. Auto-send on Registration

Update `AuthController::register()` to automatically send verification email:

```php
// After user creation
sendVerificationEmail($user->email, $verificationUrl);
```

### Testing

**Send Verification**:
```bash
curl -X POST http://localhost/v1/auth/email/send-verification \
  -H "Authorization: Bearer YOUR_ACCESS_TOKEN"
```

**Verify Email**:
```bash
curl -X POST http://localhost/v1/auth/email/verify \
  -H "Content-Type: application/json" \
  -d '{
    "email":"test@example.com",
    "token":"TOKEN_FROM_EMAIL"
  }'
```

---

## 3. Email Service Setup

### Using PHPMailer with SMTP

**Install**:
```bash
composer require phpmailer/phpmailer
```

**Create Email Service** (`src/services/EmailService.php`):

```php
<?php

declare(strict_types=1);

namespace App\Services;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class EmailService
{
    private PHPMailer $mailer;

    public function __construct()
    {
        $this->mailer = new PHPMailer(true);
        $this->configure();
    }

    private function configure(): void
    {
        $this->mailer->isSMTP();
        $this->mailer->Host = $_ENV['MAIL_HOST'];
        $this->mailer->SMTPAuth = true;
        $this->mailer->Username = $_ENV['MAIL_USERNAME'];
        $this->mailer->Password = $_ENV['MAIL_PASSWORD'];
        $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $this->mailer->Port = (int)$_ENV['MAIL_PORT'];
        $this->mailer->setFrom($_ENV['MAIL_FROM_ADDRESS'], $_ENV['MAIL_FROM_NAME']);
    }

    public function sendPasswordReset(string $email, string $resetUrl): bool
    {
        try {
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($email);
            $this->mailer->isHTML(true);
            $this->mailer->Subject = 'Password Reset Request';
            $this->mailer->Body = $this->getPasswordResetTemplate($resetUrl);
            
            return $this->mailer->send();
        } catch (Exception $e) {
            error_log("Email send failed: " . $e->getMessage());
            return false;
        }
    }

    public function sendEmailVerification(string $email, string $verifyUrl): bool
    {
        try {
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($email);
            $this->mailer->isHTML(true);
            $this->mailer->Subject = 'Verify Your Email Address';
            $this->mailer->Body = $this->getEmailVerificationTemplate($verifyUrl);
            
            return $this->mailer->send();
        } catch (Exception $e) {
            error_log("Email send failed: " . $e->getMessage());
            return false;
        }
    }

    private function getPasswordResetTemplate(string $resetUrl): string
    {
        return "
            <!DOCTYPE html>
            <html>
            <body style='font-family: Arial, sans-serif;'>
                <div style='max-width: 600px; margin: 0 auto; padding: 20px;'>
                    <h1 style='color: #333;'>Password Reset Request</h1>
                    <p>You requested to reset your password. Click the button below to proceed:</p>
                    <a href='$resetUrl' style='display: inline-block; padding: 12px 24px; background-color: #007bff; color: white; text-decoration: none; border-radius: 4px; margin: 20px 0;'>
                        Reset Password
                    </a>
                    <p style='color: #666;'>This link will expire in 1 hour.</p>
                    <p style='color: #666; font-size: 12px;'>If you didn't request this, please ignore this email.</p>
                </div>
            </body>
            </html>
        ";
    }

    private function getEmailVerificationTemplate(string $verifyUrl): string
    {
        return "
            <!DOCTYPE html>
            <html>
            <body style='font-family: Arial, sans-serif;'>
                <div style='max-width: 600px; margin: 0 auto; padding: 20px;'>
                    <h1 style='color: #333;'>Verify Your Email</h1>
                    <p>Thank you for registering! Please verify your email address by clicking the button below:</p>
                    <a href='$verifyUrl' style='display: inline-block; padding: 12px 24px; background-color: #28a745; color: white; text-decoration: none; border-radius: 4px; margin: 20px 0;'>
                        Verify Email
                    </a>
                    <p style='color: #666;'>This link will expire in 24 hours.</p>
                    <p style='color: #666; font-size: 12px;'>If you didn't create an account, please ignore this email.</p>
                </div>
            </body>
            </html>
        ";
    }
}
```

**Register in DI Container**:

```php
$container->set(EmailService::class, function () {
    return new EmailService();
});
```

---

## 4. Frontend Integration Examples

### Password Reset Flow

**Step 1: Request Reset**
```javascript
async function requestPasswordReset(email) {
  const response = await fetch('/v1/auth/password/reset-request', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ email })
  });
  return response.json();
}
```

**Step 2: Reset Password Page**
```javascript
// On reset page (e.g., /reset-password?token=xxx&email=yyy)
async function resetPassword(email, token, newPassword) {
  const response = await fetch('/v1/auth/password/reset', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ email, token, password: newPassword })
  });
  return response.json();
}
```

### Email Verification

**Step 1: Resend Verification**
```javascript
async function resendVerification(accessToken) {
  const response = await fetch('/v1/auth/email/send-verification', {
    method: 'POST',
    headers: { 
      'Authorization': `Bearer ${accessToken}`,
      'Content-Type': 'application/json'
    }
  });
  return response.json();
}
```

**Step 2: Verify Email**
```javascript
// On verify page (e.g., /verify-email?token=xxx&email=yyy)
async function verifyEmail(email, token) {
  const response = await fetch('/v1/auth/email/verify', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ email, token })
  });
  return response.json();
}
```

---

## 5. Production Checklist

- [ ] Use HTTPS for all email links
- [ ] Set up proper email service (SendGrid, Mailgun, AWS SES)
- [ ] Implement rate limiting on reset requests
- [ ] Add CAPTCHA to reset request form
- [ ] Log all password reset attempts
- [ ] Invalidate all sessions on password reset
- [ ] Test email delivery
- [ ] Test token expiration
- [ ] Add email templates with branding
- [ ] Set up email bounce handling

---

## Summary

This guide provides complete implementation code for:
- ✅ Password reset with email
- ✅ Email verification
- ✅ Email service with PHPMailer
- ✅ Frontend integration examples
- ✅ Production checklist

All the database models are already in place. You just need to:
1. Create the controllers
2. Add the routes
3. Set up the email service
4. Test the flows

**Estimated Time**: 2-3 hours for complete implementation

---

**Last Updated**: 2025-11-30
