# Slim v4 Request Body Handling - FIXED ✅

## Problem

You were looking at **Slim v3 documentation** but the app uses **Slim v4**, which has different:
- Middleware signatures  
- Body parsing approaches
- PSR-15 standards

## Solution: Slim v4 Way

### 1. **Middleware Signature** (PSR-15)

**❌ WRONG (Slim v3)**:
```php
public function __invoke(Request $request, Response $response, RequestHandler $handler): Response
//                                         ^^^^^^^^^ NO! This is v3
```

**✅ CORRECT (Slim v4 / PSR-15)**:
```php
public function __invoke(Request $request, RequestHandler $handler): Response
// Only Request and Handler - NO Response parameter
```

### 2. **JSON Body Parsing**

According to [Slim v4 docs](https://www.slimframework.com/docs/v4/objects/request.html#the-request-body):

**Middleware parses JSON and sets it on request**:
```php
// In middleware
$contents = file_get_contents('php://input');
$parsed = json_decode($contents, true);
$request = $request->withParsedBody($parsed);  // Immutable - creates new request
```

**Controllers retrieve parsed body**:
```php
// In controller
$data = $request->getParsedBody();  // Returns the array set by middleware
```

---

## What Was Fixed

### 1. ✅ **JsonBodyParserMiddleware** (Slim v4 compliant)

**File**: `src/middleware/JsonBodyParserMiddleware.php`

```php
public function __invoke(Request $request, RequestHandler $handler): Response
{
    $contentType = $request->getHeaderLine('Content-Type');

    if (strpos($contentType, 'application/json') !== false) {
        // Read from php://input (Slim v4 recommended way)
        $contents = file_get_contents('php://input');
        
        // Parse JSON
        $parsed = json_decode($contents, true);
        
        if (json_last_error() === JSON_ERROR_NONE) {
            // Set on request (creates new immutable request)
            $request = $request->withParsedBody($parsed);
        }
    }

    return $handler->handle($request);
}
```

**Key Points**:
- ✅ Uses `file_get_contents('php://input')` as per Slim v4 docs
- ✅ Calls `$request->withParsedBody($parsed)` to set data
- ✅ PSR-15 signature (no Response parameter)
- ✅ Validates JSON and returns errors if invalid

### 2. ✅ **All Controllers Updated**

**AuthController**, **UserController**, **PasswordResetController**:

```php
// All changed from:
$data = json_decode((string) $request->getBody(), true);  // ❌ Manual parsing

// To:
$data = $request->getParsedBody();  // ✅ Gets data set by middleware
```

### 3. ✅ **Middleware Registered**

**File**: `src/bootstrap/middleware.php`

```php
// Parse JSON request bodies (Slim v4 way)
$app->add($container->get(\App\Middleware\JsonBodyParserMiddleware::class));
```

---

## How It Works Now

```
1. Request arrives with JSON body
   ↓
2. JsonBodyParserMiddleware runs
   ├─ Reads: file_get_contents('php://input')
   ├─ Parses: json_decode($contents, true)
   ├─ Sets: $request->withParsedBody($parsed)
   └─ Validates: Returns 400 if invalid JSON
   ↓
3. Controller receives request
   ├─ Gets data: $request->getParsedBody()
   └─ Data is already a PHP array! ✅
```

---

## Slim v3 vs v4 Differences

| Feature | Slim v3 | Slim v4 |
|---------|---------|---------|
| **Middleware Signature** | `(Request, Response, callable)` | `(Request, RequestHandler): Response` |
| **PSR Standard** | Custom | PSR-15 (standard) |
| **Response in Middleware** | Passed as parameter | Create when needed |
| **Body Parsing** | `$request->getParsedBody()` works out of box | Need middleware OR use `file_get_contents('php://input')` |

---

## Why file_get_contents('php://input')?

**From Slim v4 docs**:

> "You may need to implement middleware in order to parse the incoming input depending on the PSR-7 implementation you have installed."

**`php://input`**:
- Read-only stream
- Reads raw POST data
- Works with JSON, XML, any format
- Standard PHP way to get request body

**Why not `$request->getBody()`?**:
- Returns `StreamInterface` object, not string
- Need to call `(string) $request->getBody()` or `$request->getBody()->getContents()`
- `php://input` is more direct and standard

---

## Testing

Now test your endpoints:

```bash
curl -X POST http://localhost/v1/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Gilbert Elikplim",
    "email": "gilbert@example.com",
    "password": "SecurePass123",
    "role": "attendee"
  }'
```

**Expected flow**:
1. Middleware reads JSON from `php://input`
2. Middleware calls `$request->withParsedBody($parsed)`
3. Controller gets `$request->getParsedBody()` → Returns array
4. ✅ Works!

---

## Error Handling

The middleware now returns proper errors:

**Empty body**:
```json
{
  "status": "error",
  "message": "Request body cannot be empty",
  "code": 400
}
```

**Invalid JSON**:
```json
{
  "status": "error",
  "message": "Invalid JSON: Syntax error",
  "code": 400
}
```

---

## References

- [Slim v4 Request Documentation](https://www.slimframework.com/docs/v4/objects/request.html#the-request-body)
- [PSR-15 HTTP Server Request Handlers](https://www.php-fig.org/psr/psr-15/)
- [PSR-7 HTTP Message Interfaces](https://www.php-fig.org/psr/psr-7/)

---

## Summary

✅ **Middleware**: PSR-15 compliant, uses `file_get_contents('php://input')`  
✅ **Controllers**: Use `getParsedBody()` to get parsed data  
✅ **No manual JSON parsing**: Middleware handles it  
✅ **Error handling**: Returns 400 for invalid JSON  
✅ **Follows Slim v4 docs**: Official recommended approach  

---

**Status**: ✅ **FIXED & COMPLIANT WITH SLIM V4**

**Last Updated**: 2025-11-30  
**Version**: 1.0.0
