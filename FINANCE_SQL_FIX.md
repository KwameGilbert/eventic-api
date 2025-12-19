# 🔧 Finance Dashboard SQL Error - FIXED

## ❌ **Error:**
```
SQLSTATE[42S22]: Column not found: 1054 Unknown column 'status' in 'where clause'
SQL: select * from `order_items` where `event_id` in (...) and `status` = paid
```

---

## 🔍 **Root Cause:**

The finance dashboard code was trying to query `order_items.status`, but that column **doesn't exist** in the database schema.

### **Database Schema:**

**✅ `orders` table HAS `status` column:**
```sql
orders
├─ id
├─ user_id
├─ total_amount
├─ status  ← HERE (values: pending, paid, failed, refunded, cancelled)
├─ payment_reference
└─ created_at
```

**❌ `order_items` table DOES NOT have `status` column:**
```sql
order_items
├─ id
├─ order_id  ← Foreign key to orders table
├─ event_id
├─ ticket_type_id
├─ quantity
├─ unit_price
├─ total_price
└─ created_at
```

**Why:** The status is at the **order level**, not the individual item level. If an order is paid, all its items are paid.

---

## ✅ **Solution Applied:**

Changed all financial queries to use **`whereHas`** to check the related order's status instead of checking order_items directly.

### **Before (BROKEN):**
```php
$orderItems = OrderItem::whereIn('event_id', $eventIds)
    ->where('status', 'paid')  // ❌ column doesn't exist
    ->get();
```

### **After (FIXED):**
```php
$orderItems = OrderItem::whereIn('event_id', $eventIds)
    ->whereHas('order', function ($query) {
        $query->where('status', 'paid');  // ✅ checks orders.status
    })
    ->get();
```

---

## 📝 **Files Modified:**

### **OrganizerController.php**

**Fixed Method 1:** `getEventsRevenue()` (Line ~1669)
```php
// OLD:
$orderItems = OrderItem::where('event_id', $event->id)
    ->where('status', 'paid')  // ❌ WRONG
    ->get();

// NEW:
$orderItems = OrderItem::where('event_id', $event->id)
    ->whereHas('order', function ($query) {
        $query->where('status', 'paid');  // ✅ CORRECT
    })
    ->with('order')
    ->get();
```

**Fixed Method 2:** `calculateEventsRevenue()` (Line ~1821)
```php
// OLD:
$orderItems = OrderItem::whereIn('event_id', $eventIds)
    ->where('status', 'paid')  // ❌ WRONG
    ->get();

// NEW:
$orderItems = OrderItem::whereIn('event_id', $eventIds)
    ->whereHas('order', function ($query) {
        $query->where('status', 'paid');  // ✅ CORRECT
    })
    ->get();
```

---

## ✅ **Verification:**

The `OrderItem` model already has the necessary relationship:

```php
// src/models/OrderItem.php (Line 51-54)
public function order()
{
    return $this->belongsTo(Order::class, 'order_id');
}
```

This allows us to use `whereHas('order', ...)` to filter by the parent order's status.

---

## 🎯 **How It Works Now:**

1. **Query order items:**
   ```php
   OrderItem::where('event_id', $eventId)
   ```

2. **Filter by paid orders using relationship:**
   ```php
   ->whereHas('order', function ($query) {
       $query->where('status', 'paid');
   })
   ```

3. **Eager load the order (optional, for efficiency):**
   ```php
   ->with('order')
   ```

4. **Get results:**
   ```php
   ->get();
   ```

**SQL Generated:**
```sql
SELECT * FROM `order_items`
WHERE `event_id` = ?
AND EXISTS (
    SELECT * FROM `orders`
    WHERE `order_items`.`order_id` = `orders`.`id`
    AND `orders`.`status` = 'paid'
)
```

---

## 🧪 **Testing:**

The finance dashboard should now work properly:

1. **Organizer Finance Page:**
   - `/organizer/finance`
   - Should load without SQL errors
   - Shows correct revenue calculations

2. **Admin Finance Dashboard:**
   - `/admin/dashboard`
   - Should load platform-wide statistics
   - Shows aggregated revenue

3. **Events Revenue:**
   - `/organizer/finance` → Events tab
   - Shows per-event revenue breakdown
   - Only counts items from paid orders

4. **Awards Revenue:**
   - `/organizer/finance` → Awards tab
   - Shows per-award revenue breakdown
   - Vote status is correctly on award_votes table (this was already correct)

---

## 📊 **Impact:**

**Fixed Endpoints:**
- ✅ `GET /v1/organizers/finance/overview`
- ✅ `GET /v1/organizers/finance/events`
- ✅ `GET /v1/admin/dashboard`

**What Now Works:**
- ✅ Platform revenue calculations
- ✅ Per-event revenue tracking
- ✅ Per-award revenue tracking
- ✅ Payout eligibility calculations
- ✅ Financial statistics

---

## 🎉 **Status: FIXED!**

The SQL error is resolved. All financial queries now properly check the `orders.status` column via the relationship instead of the non-existent `order_items.status` column.
