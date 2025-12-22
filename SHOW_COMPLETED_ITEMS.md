# Show Published & Completed Awards/Events - Implementation Summary

## User Request
Update the home page and awards/events listing pages to show BOTH "published" AND "completed" statuses, not just "published" awards/events.

---

## Changes Made

### Backend - Award Controller
**File:** `src/controllers/AwardController.php`

#### 1. Award List Endpoint (Line 64)
**Before:**
```php
// Default to published awards for public endpoint
$query->where('status', Award::STATUS_PUBLISHED);
```

**After:**
```php
// Default to published and completed awards for public endpoint
$query->whereIn('status', [Award::STATUS_PUBLISHED, Award::STATUS_COMPLETED]);
```

#### 2. Featured Awards Endpoint (Line 136)
**Before:**
```php
$query = Award::with(['categories.nominees', 'organizer.user', 'images'])
    ->where('status', Award::STATUS_PUBLISHED)
    ->where('is_featured', true);
```

**After:**
```php
$query = Award::with(['categories.nominees', 'organizer.user', 'images'])
    ->whereIn('status', [Award::STATUS_PUBLISHED, Award::STATUS_COMPLETED])
    ->where('is_featured', true);
```

#### 3. Award Search Endpoint (Line 509)
**Before:**
```php
$awards = Award::with(['categories.nominees', 'organizer.user'])
    ->where('status', Award::STATUS_PUBLISHED)
    ->where(function ($q) use ($query) {
        // search logic
    })
    ->get();
```

**After:**
```php
$awards = Award::with(['categories.nominees', 'organizer.user'])
    ->whereIn('status', [Award::STATUS_PUBLISHED, Award::STATUS_COMPLETED])
    ->where(function ($q) use ($query) {
        // search logic
    })
    ->get();
```

---

### Backend - Event Controller
**File:** `src/controllers/EventController.php`

#### 1. Event List Endpoint (Line 48)
**Before:**
```php
// Default to published events for public endpoint
$query->where('status', Event::STATUS_PUBLISHED);
```

**After:**
```php
// Default to published and completed events for public endpoint
$query->whereIn('status', [Event::STATUS_PUBLISHED, Event::STATUS_COMPLETED]);
```

#### 2. Event Search Endpoint (Line 665)
**Before:**
```php
$events = Event::with(['ticketTypes', 'eventType', 'organizer.user'])
    ->where('status', Event::STATUS_PUBLISHED)
    ->where(function ($q) use ($query) {
        // search logic
    })
    ->get();
```

**After:**
```php
$events = Event::with(['ticketTypes', 'eventType', 'organizer.user'])
    ->whereIn('status', [Event::STATUS_PUBLISHED, Event::STATUS_COMPLETED])
    ->where(function ($q) use ($query) {
        // search logic
    })
    ->get();
```

**Note:** Featured events endpoint (lines 135-145) was NOT changed because it specifically filters for upcoming events only (`start_time > now`), so completed events wouldn't appear there anyway.

---

## Affected Endpoints

### Awards:
- ✅ `GET /v1/awards` - Main list (now includes completed)
- ✅ `GET /v1/awards/featured` - Featured (now includes completed)
- ✅ `GET /v1/awards/search` - Search (now includes completed)
- ❌ `GET /v1/awards/{id}` - Single award (no change - already shows any status)

### Events:
- ✅ `GET /v1/events` - Main list (now includes completed)
- ✅ `GET /v1/events/search` - Search (now includes completed)
- ❌ `GET /v1/events/featured` - Featured (unchanged - only shows upcoming)
- ❌ `GET /v1/events/{id}` - Single event (no change - already shows any status)

---

## How It Works

### Before:
```php
->where('status', 'published')  // Only published
```
- Awards/Events with status="published" ✅
- Awards/Events with status="completed" ❌

### After:
```php
->whereIn('status', ['published', 'completed'])  // Both
```
- Awards/Events with status="published" ✅
- Awards/Events with status="completed" ✅

---

## Status Flow Reminder

### Awards:
```
draft → pending → published → completed
```

### Events:
```
draft → pending → published → completed
                            ↘ cancelled
```

**When are they marked "completed"?**
- **Awards:** Immediately after ceremony_date passes (user reverted the 6-hour buffer)
- **Events:** After event end_time passes (automatic or manual)

---

## Frontend Impact

### Home Page
- Will now show completed awards in the awards section
- Will now show completed events in the events section (though most users filter for upcoming)

### Awards Listing Page  
- Will now show BOTH active (published) and past (completed) awards
- Users can see results from past awards
- Voting is still closed for completed awards (controlled by voting_end date)

### Events Listing Page
- Will now show BOTH upcoming (published) and past (completed) events
- Users can browse event history
- Ticket purchasing disabled for completed events

---

## Benefits

1. **Historical Data:** Users can browse past awards/events
2. **Results Access:** People can see vote results from completed awards
3. **Portfolio Effect:** Organizers' past events remain visible
4. **Better UX:** No sudden disappearance when status changes to completed

---

## Query Parameter Override

If you want to show ONLY published or ONLY completed, you can still use the `status` parameter:

```
GET /v1/awards?status=published     // Only published
GET /v1/awards?status=completed     // Only completed
GET /v1/awards                      // Both (new default)
```

---

## Files Modified

1. **AwardController.php** - 3 changes
   - Line 64: index() method  
   - Line 136: featured() method
   - Line 509: search() method

2. **EventController.php** - 2 changes
   - Line 48: index() method
   - Line 665: search() method

---

## Testing

### Test Awards:
```bash
# Should return both published and completed
GET /v1/awards

# Should return both published and completed featured awards
GET /v1/awards/featured

# Should search across both statuses
GET /v1/awards/search?query=music
```

### Test Events:
```bash
# Should return both published and completed
GET /v1/events

# Should search across both statuses
GET /v1/events/search?query=concert
```

---

## Summary

**Changes:** 5 endpoints updated
**Status:** ✅ Complete
**Impact:** Frontend will now show both active and past awards/events
**Backwards Compatible:** Yes (existing status filters still work)

All public-facing endpoints now include both "published" and "completed" statuses by default, giving users access to historical data while still allowing filtering for specific statuses when needed.
