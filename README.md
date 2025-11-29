# Eventic API

> A robust, scalable event management and ticketing platform built with Slim Framework and Eloquent ORM

[![PHP Version](https://img.shields.io/badge/PHP-8.1%2B-blue)](https://www.php.net/)
[![Slim Framework](https://img.shields.io/badge/Slim-4.12-green)](https://www.slimframework.com/)
[![License](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)

---

## 📋 Table of Contents

- [Features](#-features)
- [Requirements](#-requirements)
- [Quick Start](#-quick-start)
- [Project Structure](#-project-structure)
- [Configuration](#-configuration)
- [Documentation](#-documentation)
- [Security](#-security)
- [Contributing](#-contributing)
- [License](#-license)

---

## ✨ Features

### **🔐 Authentication & Security**
- **JWT-based authentication** with refresh tokens
- **Argon2id password hashing** (industry best practice)
- **Database-backed refresh tokens** (revocable, per-device)
- **Rate limiting** on sensitive endpoints
- **Audit logging** for all security events
- **Email verification** with signed URLs
- **Password reset** with secure tokens
- **CORS configuration** for cross-origin requests

### **🏗️ Architecture**
- **Clean separation of concerns** with organized folder structure
- **Dependency Injection** using PHP-DI
- **Middleware pipeline** for request processing
- **PSR-7 HTTP messages** for standard interfaces
- **PSR-4 autoloading** for class organization
- **Environment-based configuration** (.env files)

### **💾 Database**
- **Eloquent ORM** for elegant database interactions
- **Phinx migrations** for version-controlled schema
- **Database seeding** for development data
- **Connection pooling** support
- **Multiple database** support (MySQL, PostgreSQL)

### **🛠️ Developer Experience**
- **Comprehensive logging** with Monolog
- **Error handling** with custom handlers
- **API response standardization** via ResponseHelper
- **Detailed documentation** for all components
- **Testing support** (structure ready for PHPUnit)

---

## 📦 Requirements

- **PHP** >= 8.1
- **Composer** >= 2.0
- **MySQL** >= 5.7 or **PostgreSQL** >= 12
- **Apache** or **Nginx** web server
- **PHP Extensions:**
  - `pdo_mysql` or `pdo_pgsql`
  - `mbstring`
  - `json`
  - `openssl`
  - `sodium` (for Argon2id)

---

## 🚀 Quick Start

### 1. Clone the Repository

```bash
git clone https://github.com/yourusername/eventic.git
cd eventic
```

### 2. Install Dependencies

```bash
composer install
```

### 3. Configure Environment

```bash
cp .env.example .env
```

Edit `.env` and set your configuration:

```env
# Application
APP_NAME=Eventic
APP_ENV=development
APP_VERSION=1.0.0
BASE_PATH=/

# Database
LOCAL_DB_DRIVER=mysql
LOCAL_DB_HOST=localhost
LOCAL_DB_PORT=3306
LOCAL_DB_DATABASE=eventic
LOCAL_DB_USERNAME=root
LOCAL_DB_PASSWORD=

# JWT Configuration
JWT_SECRET=your-256-bit-secret-generate-with-command-below
JWT_ALGORITHM=HS256
JWT_EXPIRE=3600
REFRESH_TOKEN_EXPIRE=604800
REFRESH_TOKEN_ALGO=sha256

# Email Configuration
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=587
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_FROM_ADDRESS=noreply@eventic.com
MAIL_FROM_NAME=Eventic

# CORS
CORS_ALLOWED_ORIGINS=*

# Rate Limiting
RATE_LIMIT=5
RATE_LIMIT_WINDOW=1

# Logging
LOG_LEVEL=DEBUG
```

**Generate a secure JWT secret:**
```bash
php -r "echo bin2hex(random_bytes(32));"
```

### 4. Run Database Migrations

```bash
composer phinx-migrate
```

### 5. Start Development Server

```bash
composer start
```

The API will be available at `http://localhost:8080`

### 6. Test the API

```bash
# Health check
curl http://localhost:8080/health

# Register a user
curl -X POST http://localhost:8080/v1/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "SecurePass123"
  }'
```

---

## 📁 Project Structure

```
eventic/
├── public/                      # Public web root
│   └── index.php               # Application entry point
│
├── src/                        # Application source code
│   ├── bootstrap/              # Application bootstrap files
│   │   ├── app.php            # Main bootstrap orchestrator
│   │   ├── services.php       # Service container registration
│   │   ├── middleware.php     # Middleware configuration
│   │   └── routes.php         # Route registration
│   │
│   ├── config/                 # Configuration files
│   │   ├── AppConfig.php      # App settings
│   │   ├── CorsConfig.php     # CORS configuration (deleted, now in AppConfig)
│   │   └── EloquentBootstrap.php # Database bootstrap
│   │
│   ├── controllers/            # Request handlers
│   │   ├── AuthController.php
│   │   └── UserController.php
│   │
│   ├── middleware/             # HTTP middleware
│   │   ├── AuthMiddleware.php
│   │   ├── RateLimitMiddleware.php
│   │   └── RequestResponseLoggerMiddleware.php
│   │
│   ├── models/                 # Eloquent models
│   │   ├── UsersModel.php
│   │   ├── RefreshTokenModel.php
│   │   ├── PasswordResetModel.php
│   │   └── AuditLogsModel.php
│   │
│   ├── routes/                 # Route definitions
│   │   ├── api.php
│   │   └── v1/
│   │       ├── AuthRoute.php
│   │       └── UserRoute.php
│   │
│   ├── services/               # Business logic
│   │   ├── AuthService.php
│   │   ├── EmailService.php
│   │   ├── PasswordResetService.php
│   │   └── VerificationService.php
│   │
│   ├── helper/                 # Helper utilities
│   │   ├── ResponseHelper.php
│   │   ├── ErrorHandler.php
│   │   └── LoggerFactory.php
│   │
│   └── logs/                   # Application logs
│       ├── app/
│       ├── http/
│       └── error/
│
├── database/                   # Database files
│   ├── migrations/            # Phinx migrations
│   └── seeds/                 # Database seeders
│
├── docs/                      # Documentation
│   ├── AUTHENTICATION.md
│   ├── API.md
│   ├── DEPLOYMENT.md
│   └── SECURITY.md
│
├── vendor/                    # Composer dependencies
├── .env                      # Environment config (gitignored)
├── .env.example             # Example environment config
├── composer.json            # Composer configuration
├── phinx.php               # Phinx configuration
└── README.md              # This file
```

See [`docs/ARCHITECTURE.md`](docs/ARCHITECTURE.md) for detailed structure explanation.

---

## ⚙️ Configuration

### Environment Variables

All configuration is done via environment variables in the `.env` file.

**Required Variables:**
- `JWT_SECRET` - Secret key for signing JWTs (256-bit recommended)
- `JWT_ALGORITHM` - Algorithm for JWT signing (HS256, HS512)
- `JWT_EXPIRE` - Access token expiry in seconds
- `REFRESH_TOKEN_EXPIRE` - Refresh token expiry in seconds
- `REFRESH_TOKEN_ALGO` - Hashing algorithm for refresh tokens
- `APP_ENV` - Environment (development, production)

See [`.env.example`](.env.example) for all available options.

### Database Configuration

Supports environment-specific database configurations:

- **Development:** `LOCAL_DB_*` variables
- **Production:** `PROD_DB_*` variables

The system automatically selects based on `APP_ENV`.

### CORS Configuration

Control cross-origin requests:

```env
# Allow all origins (development only!)
CORS_ALLOWED_ORIGINS=*

# Production - specific domains
CORS_ALLOWED_ORIGINS=https://yourdomain.com,https://app.yourdomain.com
```

---

## 📚 Documentation

Comprehensive documentation is available in the `docs/` directory:

- **[Authentication Guide](docs/AUTHENTICATION.md)** - Complete auth system documentation
- **[API Reference](docs/API.md)** - All API endpoints with examples
- **[Security Best Practices](docs/SECURITY.md)** - Security guidelines
- **[Deployment Guide](docs/DEPLOYMENT.md)** - Production deployment
- **[Architecture Overview](docs/ARCHITECTURE.md)** - System design
- **[Migration Guide](docs/MIGRATION_GUIDE.md)** - Database migrations

### Quick Links

- [Quick Start Guide](AUTH_QUICKSTART.md) - Get started in 5 minutes
- [Security Audit Report](SECURITY_AUDIT_REPORT.md) - Security improvements
- [Bootstrap README](src/bootstrap/README.md) - Bootstrap system explanation

---

## 🔒 Security

### Security Features

✅ **Argon2id Password Hashing** - Most secure algorithm (64MB memory, 4 iterations)  
✅ **JWT Token Authentication** - Stateless, scalable auth  
✅ **Refresh Token Rotation** - Prevents replay attacks  
✅ **Rate Limiting** - Brute force protection  
✅ **Audit Logging** - Track all security events  
✅ **Input Validation** - Prevent injection attacks  
✅ **CORS Configuration** - Control cross-origin access  
✅ **SSL/TLS Support** - Encrypted communications  

### Reporting Security Issues

If you discover a security vulnerability, please email security@eventic.com. Do not use the issue tracker.

---

## 🧪 Testing

```bash
# Run all tests
composer test

# Run with coverage
composer test-coverage

# Run specific test
./vendor/bin/phpunit tests/AuthServiceTest.php
```

---

## 📊 API Endpoints

### Authentication

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| POST | `/v1/auth/register` | Register new user | ❌ |
| POST | `/v1/auth/login` | User login | ❌ |
| POST | `/v1/auth/refresh` | Refresh access token | ❌ |
| GET | `/v1/auth/me` | Get current user | ✅ |
| POST | `/v1/auth/logout` | Logout user | ✅ |

### Users

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| GET | `/v1/users` | List all users | ✅ |
| GET | `/v1/users/{id}` | Get user by ID | ✅ |
| POST | `/v1/users` | Create user | ✅ |
| PUT | `/v1/users/{id}` | Update user | ✅ |
| DELETE | `/v1/users/{id}` | Delete user | ✅ |

See [API Documentation](docs/API.md) for complete reference.

---

## 🛠️ Development

### Available Commands

```bash
# Start development server
composer start

# Run migrations
composer phinx-migrate

# Rollback migrations
composer phinx-rollback

# Create new migration
composer phinx-create MigrationName

# Run seeders
composer phinx-seed

# Create new seeder
composer phinx-create-seed SeederName

# Check migration status
composer phinx-status
```

### Adding a New Feature

1. **Create Migration** (if database changes needed)
```bash
composer phinx-create CreateEventsTable
```

2. **Create Model**
```bash
# Create src/models/EventModel.php
```

3. **Create Service**
```bash
# Create src/services/EventService.php
```

4. **Create Controller**
```bash
# Create src/controllers/EventController.php
```

5. **Register in Container**
```php
// Edit src/bootstrap/services.php
$container->set(EventService::class, function () {
    return new EventService();
});
```

6. **Add Routes**
```php
// Create src/routes/v1/EventRoute.php
```

---

## 🌐 Deployment

### Production Checklist

- [ ] Set `APP_ENV=production` in `.env`
- [ ] Generate strong `JWT_SECRET` (256-bit minimum)
- [ ] Configure `CORS_ALLOWED_ORIGINS` to specific domains
- [ ] Enable database SSL (`PROD_DB_SSL=true`)
- [ ] Set up HTTPS/SSL certificates
- [ ] Configure email service (SMTP credentials)
- [ ] Set appropriate rate limits
- [ ] Enable error logging
- [ ] Set up database backups
- [ ] Configure reverse proxy (Nginx/Apache)
- [ ] Set proper file permissions

See [Deployment Guide](docs/DEPLOYMENT.md) for detailed instructions.

---

## 🤝 Contributing

Contributions are welcome! Please follow these steps:

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

Please ensure your code follows PSR-12 coding standards.

---

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

---

## 👥 Authors

**Gilbert Elikplim Kukah**
- Email: kwamegilbert1114@gmail.com
- GitHub: [@yourusername](https://github.com/yourusername)

---

## 🙏 Acknowledgments

- [Slim Framework](https://www.slimframework.com/) - Fast PHP micro-framework
- [Eloquent ORM](https://laravel.com/docs/eloquent) - Laravel's elegant ORM
- [Firebase JWT](https://github.com/firebase/php-jwt) - JWT implementation
- [PHP-DI](https://php-di.org/) - Dependency injection container
- [Phinx](https://phinx.org/) - Database migrations

---

## 📞 Support

- **Documentation:** [docs/](docs/)
- **Issues:** [GitHub Issues](https://github.com/yourusername/eventic/issues)
- **Email:** support@eventic.com

---

**Built with ❤️ using Slim Framework and Eloquent ORM**
