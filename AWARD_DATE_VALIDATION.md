# Award Date Validation - Implementation Documentation

## User Requirement
Add validation constraints to ensure proper chronological order of award dates when creating or updating awards.

---

## Date Order Constraint

Awards must follow this chronological order:

```
voting_start  →  voting_end  →  ceremony_date
```

### Business Logic:
1. **Voting must start before it ends**
   - `voting_start < voting_end`
   
2. **Voting must end before the ceremony**
   - `voting_end < ceremony_date`

### Why This Matters:
- **Prevents confusion:** Results can't be shown before voting ends
- **Logical flow:** Ceremony announces winners AFTER voting closes
- **Data integrity:** Ensures timeline makes sense
- **User experience:** Clear expectations for voters and attendees

---

## Implementation

### Location
**File:** `src/controllers/AwardController.php`

### Methods Updated:
1. **`create()`** - Lines 251-262 (new validation)
2. **`update()`** - Lines 400-412 (new validation)

---

## Create Award Validation

### Code Added (After Required Fields Check):
```php
// Validate date order: voting_start < voting_end < ceremony_date
$votingStart = new \DateTime($data['voting_start']);
$votingEnd = new \DateTime($data['voting_end']);
$ceremonyDate = new \DateTime($data['ceremony_date']);

if ($votingStart >= $votingEnd) {
    return ResponseHelper::error($response, 'Voting start date must be before voting end date', 400);
}

if ($votingEnd >= $ceremonyDate) {
    return ResponseHelper::error($response, 'Voting must end before the ceremony date', 400);
}
```

### Validation Flow:
1. Parse all three dates from request data
2. Check: `voting_start < voting_end` ❌ Error if violated
3. Check: `voting_end < ceremony_date` ❌ Error if violated
4. Continue with award creation ✅ If all valid

---

## Update Award Validation

### Code Added (After Slug Update Logic):
```php
// Validate date order if any dates are being updated
$votingStart = isset($data['voting_start']) ? new \DateTime($data['voting_start']) : new \DateTime($award->voting_start);
$votingEnd = isset($data['voting_end']) ? new \DateTime($data['voting_end']) : new \DateTime($award->voting_end);
$ceremonyDate = isset($data['ceremony_date']) ? new \DateTime($data['ceremony_date']) : new \DateTime($award->ceremony_date);

if ($votingStart >= $votingEnd) {
    return ResponseHelper::error($response, 'Voting start date must be before voting end date', 400);
}

if ($votingEnd >= $ceremonyDate) {
    return ResponseHelper::error($response, 'Voting must end before the ceremony date', 400);
}
```

### Smart Validation:
- Uses **new date if provided**, otherwise uses **existing date from database**
- This ensures partial updates are still validated correctly
- Example: If only updating `ceremony_date`, it's checked against existing `voting_end`

---

## Error Messages

### Error 1: Voting Start After Voting End
```json
{
  "success": false,
  "message": "Voting start date must be before voting end date",
  "status": 400
}
```

**When:** `voting_start >= voting_end`

**Example:**
```
voting_start: 2024-02-15
voting_end:   2024-02-10  ❌ Before start!
```

### Error 2: Voting End After Ceremony
```json
{
  "success": false,
  "message": "Voting must end before the ceremony date",
  "status": 400
}
```

**When:** `voting_end >= ceremony_date`

**Example:**
```
voting_end:    2024-03-20
ceremony_date: 2024-03-15  ❌ Before voting ends!
```

---

## Valid Examples

### Example 1: Good Award Timeline
```json
{
  "voting_start": "2024-01-15 00:00:00",
  "voting_end": "2024-02-01 23:59:59",
  "ceremony_date": "2024-02-15 19:00:00"
}
```
✅ **Valid!**
- Voting: Jan 15 - Feb 1 (17 days)
- Ceremony: Feb 15 (14 days after voting ends)

### Example 2: Tight But Valid
```json
{
  "voting_start": "2024-03-01 08:00:00",
  "voting_end": "2024-03-10 20:00:00",
  "ceremony_date": "2024-03-10 20:00:01"
}
```
✅ **Valid!**
- Ceremony starts 1 second after voting ends (technically allowed)

### Example 3: Same Day Events
```json
{
  "voting_start": "2024-04-01 00:00:00",
  "voting_end": "2024-04-01 18:00:00",
  "ceremony_date": "2024-04-01 20:00:00"
}
```
✅ **Valid!**
- All on same day but in correct order

---

## Invalid Examples

### ❌ Example 1: Voting Ends Before It Starts
```json
{
  "voting_start": "2024-02-10",
  "voting_end": "2024-02-05",     ❌ 5 days BEFORE start
  "ceremony_date": "2024-02-20"
}
```
**Error:** "Voting start date must be before voting end date"

### ❌ Example 2: Ceremony Before Voting Ends
```json
{
  "voting_start": "2024-01-01",
  "voting_end": "2024-02-01",
  "ceremony_date": "2024-01-15"    ❌ During voting period
}
```
**Error:** "Voting must end before the ceremony date"

### ❌ Example 3: Same Start and End
```json
{
  "voting_start": "2024-03-01 12:00:00",
  "voting_end": "2024-03-01 12:00:00",   ❌ Exact same time
  "ceremony_date": "2024-03-02"
}
```
**Error:** "Voting start date must be before voting end date"

### ❌ Example 4: Ceremony Same as Voting End
```json
{
  "voting_start": "2024-04-01",
  "voting_end": "2024-04-15",
  "ceremony_date": "2024-04-15"    ❌ Same as voting end
}
```
**Error:** "Voting must end before the ceremony date"

---

## Update Scenarios

### Scenario 1: Update Only Ceremony Date
```php
// Existing award:
voting_start: 2024-01-01
voting_end:   2024-01-31
ceremony_date: 2024-02-15

// Update request:
{
  "ceremony_date": "2024-02-20"  // Moving ceremony later
}
```
✅ **Valid** - New date (Feb 20) is after existing voting_end (Jan 31)

### Scenario 2: Update Only Voting End
```php
// Existing award:
voting_start: 2024-01-01
voting_end:   2024-01-31
ceremony_date: 2024-02-15

// Update request:
{
  "voting_end": "2024-02-10"  // Extending voting
}
```
✅ **Valid** - New end (Feb 10) is before existing ceremony_date (Feb 15)

### Scenario 3: Invalid Update
```php
// Existing award:
voting_start: 2024-01-01
voting_end:   2024-01-31
ceremony_date: 2024-02-15

// Update request:
{
  "ceremony_date": "2024-01-20"  // Moving ceremony earlier
}
```
❌ **Invalid** - New ceremony (Jan 20) is before existing voting_end (Jan 31)
**Error:** "Voting must end before the ceremony date"

---

## Testing

### Test Case 1: Create with Valid Dates
```bash
POST /v1/awards
{
  "title": "Music Awards 2024",
  "voting_start": "2024-01-01",
  "voting_end": "2024-01-31",
  "ceremony_date": "2024-02-15"
}
```
**Expected:** ✅ 201 Created

### Test Case 2: Create with Invalid Order
```bash
POST /v1/awards
{
  "title": "Invalid Award",
  "voting_start": "2024-02-01",
  "voting_end": "2024-01-01",    // Before start!
  "ceremony_date": "2024-03-01"
}
```
**Expected:** ❌ 400 Bad Request
```json
{
  "success": false,
  "message": "Voting start date must be before voting end date"
}
```

### Test Case 3: Update with Invalid Ceremony
```bash
PUT /v1/awards/123
{
  "ceremony_date": "2023-12-01"  // In the past, before voting
}
```
**Expected:** ❌ 400 Bad Request
```json
{
  "success": false,
  "message": "Voting must end before the ceremony date"
}
```

---

## Frontend Impact

### Form Validation Needed:
The frontend should also validate dates before submission:

```javascript
// Example validation in CreateAward.jsx
const validateDates = () => {
  const start = new Date(formData.voting_start);
  const end = new Date(formData.voting_end);
  const ceremony = new Date(formData.ceremony_date);

  if (start >= end) {
    showError('Voting start must be before voting end');
    return false;
  }

  if (end >= ceremony) {
    showError('Voting must end before ceremony date');
    return false;
  }

  return true;
};
```

### Date Picker Constraints:
- **Voting End:** Min date = voting_start + 1 day
- **Ceremony:** Min date = voting_end + 1 day

---

## Benefits

1. **Data Integrity** ✅
   - Prevents illogical timelines
   - Ensures awards can function properly

2. **Better UX** ✅
   - Clear error messages guide users
   - Prevents confusion about when events happen

3. **Business Logic** ✅
   - Ceremony can't happen during voting
   - Results revealed at correct time

4. **Consistency** ✅
   - Same validation on create and update
   - Works with partial updates

---

## Edge Cases Handled

### ✅ Partial Updates
If only updating one date, validates against existing dates

### ✅ Timezone Handling
Uses DateTime objects which handle timezones correctly

### ✅ Same Day Events
Allows multiple events on same day if times are in correct order

### ✅ Leap Years & Month Boundaries
DateTime handles all calendar edge cases

---

## Summary

**Added Validation:** Date chronology check  
**Constraint:** `voting_start < voting_end < ceremony_date`  
**Files Modified:** `AwardController.php` (2 methods)  
**Error Codes:** 400 Bad Request  
**Impact:** Prevents invalid award timelines  
**Status:** ✅ Complete

Now awards will always have a logical, chronological flow from voting start through ceremony!
