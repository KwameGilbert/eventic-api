# Namespace and Import Fixes ✅

## Summary
All namespaces have been standardized to **PascalCase** (`App\Models`, `App\Controllers`, etc.) to match PSR-4 standards and the `composer.json` configuration. Manual `require_once` calls for classes have been replaced with proper `use` statements.

## Changes Made

### 1. Models (`src/models/`)
All models now use `namespace App\Models;`:
- `User.php`
- `RefreshToken.php`
- `PasswordReset.php`
- `AuditLog.php`
- `BaseModel.php`

### 2. Controllers (`src/controllers/`)
All controllers now use `namespace App\Controllers;` and import models via `use`:
- `AuthController.php`
- `UserController.php`
- `PasswordResetController.php`

### 3. Services (`src/services/`)
- `AuthService.php`: Removed `require_once MODEL . 'AuditLogsModel.php'` and replaced with `use App\Models\AuditLog;`.

### 4. Composer Configuration (`composer.json`)
Verified autoload mapping:
```json
"autoload": {
    "psr-4": {
        "App\\": "src/",
        "App\\Models\\": "src/models/",
        "App\\Controllers\\": "src/controllers/",
        "App\\Services\\": "src/services/",
        "App\\Helper\\": "src/helper/",
        "App\\Middleware\\": "src/middleware/"
    }
}
```

## Next Steps
1. Run `composer dump-autoload` in your terminal to regenerate the autoload files.
2. Test the application to ensure all classes are loaded correctly.
