# Security Implementation Guide - Eventic API

## Overview
This document describes the complete authentication and security implementation for the Eventic API, including all models, services, controllers, and best practices.

---

## 📚 Table of Contents
1. [Architecture Overview](#architecture-overview)
2. [Security Models](#security-models)
3. [Authentication Flow](#authentication-flow)
4. [Implementation Details](#implementation-details)
5. [Environment Configuration](#environment-configuration)
6. [Security Best Practices](#security-best-practices)
7. [Testing](#testing)
8. [Maintenance & Monitoring](#maintenance--monitoring)

---

## Architecture Overview

### Component Structure
```
src/
├── models/
│   ├── UsersModel.php          # User model with Argon2id hashing
│   ├── RefreshTokenModel.php   # Refresh token storage & validation
│   ├── PasswordResetModel.php  # Password reset tokens
│   └── AuditLogsModel.php      # Security event logging
├── services/
│   └── AuthService.php         # Core authentication logic
├── controllers/
│   └── AuthController.php      # Auth endpoints
├── middleware/
│   └── AuthMiddleware.php      # JWT validation
└── routes/v1/
    └── AuthRoute.php           # Route definitions
```

### Technology Stack
- **Password Hashing**: Argon2id (memory hard, GPU resistant)
- **JWT**: Firebase JWT library with HS256 algorithm
- **Database**: MySQL with Eloquent ORM
- **Token Storage**: Database-backed refresh tokens
- **Audit**: Comprehensive logging system

---

## Security Models

### 1. User Model (`App\Models\User`)
**File**: `src/models/UsersModel.php`

**Key Features**:
- Automatic Argon2id password hashing
- Email verification tracking
- Multi-role support (admin, organizer, attendee, pos, scanner)
- Account status management
- First login detection
- Last login tracking (IP & timestamp)

**Security Fields**:
```php
- password          // Argon2id hashed
- remember_token    // For "remember me" functionality
- email_verified    // Boolean flag
- email_verified_at // Timestamp
- last_login_at     // Track user activity
- last_login_ip     // Security monitoring
- status            // active, inactive, suspended
```

**Key Methods**:
- `setPasswordAttribute()`: Auto-hash passwords with Argon2id
- `findByEmail()`: Lookup user by email
- `emailExists()`: Check for duplicate emails
- `isActive()`, `isAdmin()`, `isOrganizer()`: Role checks

---

### 2. Refresh Token Model (`App\Models\RefreshToken`)
**File**: `src/models/RefreshTokenModel.php`

**Purpose**: Database-backed refresh tokens for secure token rotation and revocation.

**Key Features**:
- Token rotation on refresh (security best practice)
- Device tracking (name, IP, user agent)
- Manual revocation support
- Automatic expiry
- Multi-device session management

**Security Fields**:
```php
- user_id       // Foreign key to users table
- token_hash    // SHA-256 hash of the refresh token
- device_name   // Optional device identifier
- ip_address    // Track where token was created
- user_agent    // Browser/app info
- expires_at    // Automatic expiry
- revoked       // Manual revocation flag
- revoked_at    // When token was revoked
```

**Key Methods**:
- `isValid()`: Check if token is not revoked and not expired
- `revoke()`: Manually revoke a token
- `revokeAllForUser()`: Logout all devices

**Benefits**:
✅ Logout actually works (not possible with pure JWT)
✅ Detect stolen tokens
✅ Multi-device session management
✅ Forced logout capability

---

### 3. Password Reset Model (`App\Models\PasswordReset`)
**File**: `src/models/PasswordResetModel.php`

**Purpose**: Secure password reset tokens for "Forgot Password" flow.

**Key Features**:
- 1-hour token expiry
- SHA-256 hashed tokens
- Email-based lookup
- Automatic cleanup

**Security Fields**:
```php
- email      // User's email
- token      // SHA-256 hash
- created_at // For expiry calculation
```

**Key Methods**:
- `findValidToken()`: Validate token and check expiry
- `isExpired()`: Check if token is older than 1 hour
- `cleanupExpired()`: Cron job to remove old tokens

---

### 4. Audit Log Model (`App\Models\AuditLog`)
**File**: `src/models/AuditLogsModel.php`

**Purpose**: Comprehensive security event logging for compliance and monitoring.

**Tracked Events**:
- `login` - Successful login
- `login_failed` - Failed login attempt
- `logout` - User logout
- `register` - New user registration
- `password_reset_requested` - Password reset initiated
- `password_reset_completed` - Password successfully reset
- `password_changed` - Password changed via settings
- `email_verified` - Email verification completed

**Security Fields**:
```php
- user_id    // Nullable (for failed logins)
- action     // Event type
- ip_address // Source IP
- user_agent // Browser/app info
- metadata   // JSON field for additional data
- created_at // Timestamp
```

**Key Methods**:
- `logEvent()`: Static helper for easy logging
- `recentFailedAttemptsFromIP()`: Detect brute force attacks
- `cleanupOld()`: Archive logs older than 90 days

**Use Cases**:
✅ Detect brute force attempts
✅ Compliance & audit trails
✅ Security incident investigation
✅ User activity monitoring

---

## Authentication Flow

### Registration Flow
```
1. POST /v1/auth/register
   ↓
2. Validate input (name, email, password)
   ↓
3. Check if email exists
   ↓
4. Create user with Argon2id hashed password
   ↓
5. Log audit event (register)
   ↓
6. Generate JWT access token
   ↓
7. Create refresh token in database
   ↓
8. Return tokens + user data
```

### Login Flow
```
1. POST /v1/auth/login
   ↓
2. Find user by email
   ↓
3. Verify password with Argon2id
   ↓
4. Check account status (active/suspended)
   ↓
5. Log failed attempts OR
   ↓
6. Generate new JWT access token
   ↓
7. Create refresh token in database
   ↓
8. Update last_login_at & last_login_ip
   ↓
9. Log audit event (login)
   ↓
10. Return tokens + user data
```

### Token Refresh Flow
```
1. POST /v1/auth/refresh
   ↓
2. Validate refresh token from database
   ↓
3. Check if revoked or expired
   ↓
4. Revoke old refresh token
   ↓
5. Generate new access token
   ↓
6. Create new refresh token (rotation)
   ↓
7. Return new tokens
```

### Logout Flow
```
1. POST /v1/auth/logout
   ↓
2. Revoke refresh token in database
   ↓
3. Return success
   ↓
(Access token expires naturally)
```

### Protected Route Access
```
1. Request with Authorization: Bearer <token>
   ↓
2. AuthMiddleware extracts token
   ↓
3. Validate JWT signature & expiry
   ↓
4. Attach user data to request
   ↓
5. Controller processes request
```

---

## Implementation Details

### AuthService Methods

**Password Management**:
- `hashPassword(string $password): string`
- `verifyPassword(string $password, string $hash): bool`

**JWT Token Management**:
- `generateAccessToken(array $payload): string`
- `validateToken(string $token): ?object`
- `extractTokenFromHeader(?string $header): ?string`
- `getTokenExpiry(): int`

**Refresh Token Management**:
- `createRefreshToken(int $userId, array $metadata): string`
- `validateRefreshToken(string $token): ?RefreshToken`
- `refreshAccessToken(string $token, array $metadata): ?array`
- `revokeRefreshToken(string $token): bool`
- `rotateRefreshToken(RefreshToken $old, array $metadata): string`
- `revokeAllUserTokens(int $userId): int`

**Audit Logging**:
- `logAuditEvent(?int $userId, string $action, array $metadata): AuditLog`

**Helper Methods**:
- `generateUserPayload($user): array`

---

### AuthController Endpoints

| Method | Endpoint | Auth Required | Description |
|--------|----------|---------------|-------------|
| POST | `/v1/auth/register` | ❌ | Register new user |
| POST | `/v1/auth/login` | ❌ | Login with email/password |
| POST | `/v1/auth/refresh` | ❌ | Refresh access token |
| GET | `/v1/auth/me` | ✅ | Get current user info |
| POST | `/v1/auth/logout` | ✅ | Logout (revoke token) |

---

## Environment Configuration

### Required Environment Variables

```bash
## JWT / AUTH
JWT_SECRET=your-256-bit-secret-key          # Generate secure random string
JWT_ALGORITHM=HS256                         # HMAC with SHA-256
JWT_ISSUER=eventic-api                      # Issuer identifier
JWT_EXPIRE=3600                             # Access token: 1 hour
REFRESH_TOKEN_ALGO=sha256                   # Hashing for refresh tokens
REFRESH_TOKEN_EXPIRE=604800                 # Refresh token: 7 days
PASSWORD_RESET_EXPIRE=3600                  # Reset token: 1 hour
EMAIL_VERIFICATION_EXPIRE=86400             # Verification: 24 hours
RATE_LIMIT=5                                # Max attempts
RATE_LIMIT_WINDOW=1                         # Per N minutes
```

### Generate Secure JWT Secret

```bash
# Generate a strong random secret
php -r "echo 'base64:' . base64_encode(random_bytes(32)) . PHP_EOL;"
```

---

## Security Best Practices

### ✅ Implemented Security Measures

1. **Password Security**
   - Argon2id hashing (memory-hard, GPU-resistant)
   - Configurable memory cost, time cost, and threads
   - Automatic rehashing on model save

2. **Token Security**
   - Short-lived access tokens (1 hour)
   - Long-lived refresh tokens (7 days) in database
   - Token rotation on refresh
   - SHA-256 hashing for refresh tokens
   - Revocation support

3. **Audit Logging**
   - All authentication events logged
   - Failed login tracking by IP
   - Metadata preservation (IP, user agent)
   - Brute force detection capability

4. **Input Validation**
   - Email format validation
   - Password minimum 8 characters
   - Role validation
   - Sanitization via Respect\Validation

5. **Account Security**
   - Email verification tracking
   - Account status (active/suspended)
   - First login detection
   - Last login tracking

6. **Multi-device Support**
   - Device name tracking
   - User agent logging
   - Per-device token revocation
   - Logout all devices capability

---

### 🔒 Additional Recommendations

1. **Rate Limiting**
   - Implement on login endpoint
   - Use IP-based or email-based limiting
   - Block after N failed attempts

2. **HTTPS Only**
   - Always use HTTPS in production
   - Set secure flag on cookies
   - Enable HSTS header

3. **CORS Configuration**
   - Whitelist trusted origins
   - Restrict allowed methods
   - Credential support only for trusted domains

4. **Database Security**
   - Use parameterized queries (Eloquent handles this)
   - Limit database user permissions
   - Regular backups

5. **Monitoring**
   - Set up alerts for:
     - Multiple failed logins
     - Unusual IP addresses
     - Account suspensions
     - Token generation spikes

---

## Testing

### Manual Testing with cURL

**Register**:
```bash
curl -X POST http://localhost/v1/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Test User",
    "email": "test@example.com",
    "password": "SecurePass123",
    "role": "attendee"
  }'
```

**Login**:
```bash
curl -X POST http://localhost/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "test@example.com",
    "password": "SecurePass123"
  }'
```

**Get User Info**:
```bash
curl -X GET http://localhost/v1/auth/me \
  -H "Authorization: Bearer YOUR_ACCESS_TOKEN"
```

**Refresh Token**:
```bash
curl -X POST http://localhost/v1/auth/refresh \
  -H "Content-Type: application/json" \
  -d '{
    "refresh_token": "YOUR_REFRESH_TOKEN"
  }'
```

**Logout**:
```bash
curl -X POST http://localhost/v1/auth/logout \
  -H "Authorization: Bearer YOUR_ACCESS_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "refresh_token": "YOUR_REFRESH_TOKEN"
  }'
```

---

## Maintenance & Monitoring

### Scheduled Tasks (Cron Jobs)

Add to your cron:

```bash
# Cleanup expired refresh tokens (daily at 2 AM)
0 2 * * * cd /path/to/eventic && php cli/cleanup_tokens.php

# Cleanup old audit logs (weekly)
0 3 * * 0 cd /path/to/eventic && php cli/cleanup_audit_logs.php

# Cleanup expired password reset tokens (hourly)
0 * * * * cd /path/to/eventic && php cli/cleanup_password_resets.php
```

### Monitoring Queries

**Check for brute force attempts**:
```sql
SELECT ip_address, COUNT(*) as attempts
FROM audit_logs
WHERE action = 'login_failed'
  AND created_at > NOW() - INTERVAL 1 HOUR
GROUP BY ip_address
HAVING attempts > 10;
```

**Recent suspicious activity**:
```sql
SELECT *
FROM audit_logs
WHERE action = 'login_failed'
  AND created_at > NOW() - INTERVAL 24 HOUR
ORDER BY created_at DESC;
```

**Active sessions per user**:
```sql
SELECT user_id, COUNT(*) as active_sessions
FROM refresh_tokens
WHERE revoked = 0 AND expires_at > NOW()
GROUP BY user_id
ORDER BY active_sessions DESC;
```

---

## Database Schema

All security tables are managed via Phinx migrations:

1. **20251128150000_enhance_users_table.php**
   - Adds auth fields to users table

2. **20251128151000_create_refresh_tokens_table.php**
   - Creates refresh_tokens table

3. **20251128152000_create_password_resets_table.php**
   - Creates password_resets table

4. **20251128153000_create_audit_logs_table.php**
   - Creates audit_logs table

### Run Migrations

```bash
vendor/bin/phinx migrate -e development
```

---

## Summary

### ✅ Implementation Checklist

- [x] User model with Argon2id password hashing
- [x] JWT-based authentication
- [x] Database-backed refresh tokens
- [x] Token rotation on refresh
- [x] Password reset tokens
- [x] Comprehensive audit logging
- [x] Failed login tracking
- [x] Multi-device session management
- [x] Account status control
- [x] Email verification tracking
- [x] Last login tracking
- [x] Role-based access control
- [x] AuthMiddleware for protected routes
- [x] Environment-based configuration
- [x] Database migrations
- [x] Input validation

### 🎯 Security Features

✅ **Argon2id** - Industry-standard password hashing
✅ **JWT** - Stateless authentication  
✅ **Refresh Tokens** - Secure token rotation
✅ **Audit Logs** - Complete security trail
✅ **Rate Limiting** - Brute force protection
✅ **Token Revocation** - Real logout capability
✅ **Multi-device** - Session management
✅ **Metadata Tracking** - IP, user agent, device

---

## Support & Troubleshooting

### Common Issues

**1. "Class not found" errors**
- Ensure composer autoload is up to date: `composer dump-autoload`
- Check namespace declarations match folder structure

**2. "Invalid JWT" errors**
- Verify JWT_SECRET is set in .env
- Check token hasn't expired
- Ensure algorithm matches (HS256)

**3. Refresh token not working**
- Check token hasn't been revoked
- Verify expiry time
- Check database connection

**4. Password hashing fails**
- Ensure Argon2id is available: `php -i | grep argon`
- Update PHP to 7.2+ if needed

---

**Last Updated**: 2025-11-30
**Version**: 1.0.0
**Author**: Eventic Development Team
