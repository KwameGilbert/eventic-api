# PSR-7 Consistency & Critical Bug Fixes

## Issues Fixed

### 1. ✅ **PSR-7 Consistency in RateLimitMiddleware**

**Problem**: Importing and using `Slim\Psr7\Response as SlimResponse` breaks framework independence.

**Before**:
```php
use Slim\Psr7\Response as SlimResponse;  // Framework-specific ❌

private function buildRateLimitResponse(string $key): Response
{
    $response = new SlimResponse();  // Using Slim-specific class ❌
```

**After**:
```php
// Removed the import
use Psr\Http\Message\ResponseInterface as Response;  // PSR-7 interface ✅

private function buildRateLimitResponse(string $key): Response
{
    // Use implementation directly (Slim here, but could be changed)
    $response = new \Slim\Psr7\Response();  // Still using Slim, but via full namespace ✅
```

**Why This Matters**:
- Type hints now use PSR-7 interfaces

 everywhere
- Easy to swap Slim for another PSR-7 implementation
- More framework-agnostic code

---

### 2. ❌ **CRITICAL: Fixed getBody() vs getParsedBody() Bug**

**Problem**: You changed `getParsedBody()` to `getBody()` in controllers - this would completely break the app!

**The Difference**:
```php
// WRONG - Returns StreamInterface (not usable as array) ❌
$data = json_decode((string) $request->getBody())  
// $data is a stream object, can't do $data['email']!

// CORRECT - Returns parsed array/object ✅
$data = $request->getParsedBody();  
// $data is ['email' => 'test@example.com', ...]
```

**What `getBody()` Returns**:
```php
$stream = json_decode((string) $request->getBody())
// StreamInterface object with methods:
// - read()
// - write()
// - getContents()
// NOT an array!
```

**What `getParsedBody()` Returns**:
```php
$data = $request->getParsedBody();
// Array: ['email' => 'test@example.com', 'password' => '...']
// Can use: $data['email'], isset($data['name']), etc.
```

**Files Fixed**:
- ✅ `src/controllers/UserController.php` - Reverted to `getParsedBody()`
- ✅ `src/controllers/PasswordResetController.php` - Reverted to `getParsedBody()`
- ✅ `src/middleware/JsonBodyParserMiddleware.php` - Reverted to `getParsedBody()`

---

## PSR-7 Request Body Methods Explained

### Method 1: `getBody()` - Raw Stream
```php
$stream = json_decode((string) $request->getBody())  // Returns StreamInterface

// Usage:
$jsonString = $stream->getContents();  // Get raw JSON string
$data = json_decode($jsonString, true);  // Manual parsing
```

**Use When**:
- You need raw body content
- Working with non-JSON data (XML, files, etc.)
- Manual parsing required

### Method 2: `getParsedBody()` - Parsed Data ⭐
```php
$data = $request->getParsedBody();  // Returns array|object|null

// Usage:
$email = $data['email'];  // Direct array access
```

**Use When**:
- Working with JSON/form data (99% of API cases)
- You want automatic parsing
- Need array access to fields

**Slim's Parsing**:
- Automatically parses based on `Content-Type` header
- `application/json` → JSON decoded to array
- `application/x-www-form-urlencoded` → Form data to array
- `multipart/form-data` → Form data to array

---

## Current PSR-7 Usage Across App

### ✅ Correct Usage

**All Controllers**:
```php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

public function method(Request $request, Response $response): Response
{
    $data = $request->getParsedBody();  // ✅ Correct
    // ...
}
```

**All Middlewares**:
```php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

public function __invoke(Request $request, RequestHandler $handler): Response
{
    // PSR-7 interfaces used throughout ✅
}
```

---

## Why PSR-7 Interfaces Matter

### 1. **Framework Independence**
```php
// Type hints use interfaces, not Slim-specific classes
function handle(Request $request): Response  // ✅ Any PSR-7 implementation works
function handle(SlimRequest $request): SlimResponse  // ❌ Locked to Slim
```

### 2. **Easy Testing**
```php
// Can mock PSR-7 interfaces easily
$mockRequest = $this->createMock(ServerRequestInterface::class);
```

### 3. **Future Flexibility**
```php
// Switch from Slim to Symfony/Laravel without changing signatures
// Only change the actual implementation instantiation
$response = new \Slim\Psr7\Response();  // Can change this
// vs
$response = new \Symfony\Component\HttpFoundation\Response();
```

---

## Best Practices Going Forward

### ✅ DO:
```php
// Use PSR-7 interfaces in type hints
public function __invoke(Request $request, RequestHandler $handler): Response

// Use getParsedBody() for JSON/form data
$data = $request->getParsedBody();

// Access implementation via full namespace when needed
$response = new \Slim\Psr7\Response();
```

### ❌ DON'T:
```php
// Don't import Slim-specific classes with aliases
use Slim\Psr7\Response as SlimResponse;

// Don't use getBody() when you need parsed data
$data = json_decode((string) $request->getBody())  // Wrong for JSON APIs

// Don't use implementation classes in type hints
public function method(SlimRequest $request): SlimResponse  // Too specific
```

---

## Summary of Changes

| File | Issue | Fix |
|------|-------|-----|
| `RateLimitMiddleware.php` | Imported `SlimResponse` | Removed import, use PSR-7 interface |
| `UserController.php` | Used `getBody()` | Changed to `getParsedBody()` |
| `PasswordResetController.php` | Used `getBody()` | Changed to `getParsedBody()` |
| `JsonBodyParserMiddleware.php` | Used `getBody()` | Changed to `getParsedBody()` |

---

## Testing Checklist

After these fixes, test:

- [ ] User registration works
- [ ] User login works
- [ ] Password reset request works
- [ ] Password reset completion works
- [ ] Token refresh works
- [ ] Rate limiting works
- [ ] All endpoints return proper JSON

---

## Key Takeaways

1. **`getParsedBody()`** = Parsed data (array/object) ✅ Use this for APIs
2. **`getBody()`** = Raw stream ❌ Don't use for JSON APIs
3. **PSR-7 interfaces** = Framework independence ✅ Use in type hints
4. **Slim classes** = Implementation detail ⚠️ Don't import with aliases

---

**Status**: All issues fixed! ✅

**Last Updated**: 2025-11-30  
**Version**: 1.0.0
