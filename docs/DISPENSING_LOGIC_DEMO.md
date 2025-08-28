# Dispensing Logic - Cancelled Items Protection

## Scenario: 10 items, 2 cancelled due to stock issues

### Example Cash Sale with Mixed Item Status:

```
Sale ID: CS2025-001
Items:
1. Paracetamol 500mg x10    - STATUS: pending     - Stock: 50    ✅ Can dispense
2. Amoxicillin 250mg x20    - STATUS: pending     - Stock: 30    ✅ Can dispense  
3. Insulin 100IU x5         - STATUS: cancelled   - Stock: 0     ❌ CANCELLED - Will NOT dispense
4. Metformin 500mg x30      - STATUS: pending     - Stock: 100   ✅ Can dispense
5. Aspirin 75mg x60         - STATUS: pending     - Stock: 200   ✅ Can dispense
6. Omeprazole 20mg x14      - STATUS: pending     - Stock: 25    ✅ Can dispense
7. Atenolol 50mg x28        - STATUS: cancelled   - Stock: 2     ❌ CANCELLED - Will NOT dispense
8. Simvastatin 20mg x28     - STATUS: pending     - Stock: 40    ✅ Can dispense
9. Lisinopril 10mg x30      - STATUS: pending     - Stock: 35    ✅ Can dispense
10. Hydrochlorothiazide x30 - STATUS: pending     - Stock: 45    ✅ Can dispense
```

## Code Flow When "Dispense All" is clicked:

### Step 1: MedicationCashSale->canBeDispensed()
```php
public function canBeDispensed()
{
    return $this->is_paid && $this->hasDispensableItems();
}
```
- Checks if sale is paid ✅
- Checks if has ANY dispensable items ✅ (8 out of 10 items)
- Result: `true` - Sale can be dispensed

### Step 2: Controller dispense() method iterates through items
```php
foreach ($medicationCashSale->items as $item) {
    if (!$item->canBeDispensed()) {
        continue; // SKIPS cancelled items #3 and #7
    }
    $this->dispenseMedicationItem($item);
}
```

### Step 3: MedicationCashSaleItem->canBeDispensed() for each item
```php
public function canBeDispensed()
{
    return $this->status === self::STATUS_PENDING && $this->quantity > 0;
}
```

**Results:**
- Item #1: status='pending' → ✅ DISPENSE
- Item #2: status='pending' → ✅ DISPENSE
- Item #3: status='cancelled' → ❌ SKIP (continue)
- Item #4: status='pending' → ✅ DISPENSE
- Item #5: status='pending' → ✅ DISPENSE
- Item #6: status='pending' → ✅ DISPENSE
- Item #7: status='cancelled' → ❌ SKIP (continue)
- Item #8: status='pending' → ✅ DISPENSE
- Item #9: status='pending' → ✅ DISPENSE
- Item #10: status='pending' → ✅ DISPENSE

### Final Result:
- **8 items dispensed** (pending items only)
- **2 items skipped** (cancelled items #3 and #7)
- Sale marked as completed
- Stock reduced only for the 8 dispensed items
- Cancelled items remain cancelled and untouched

## Protection Mechanisms:

1. **Status Check**: Only `pending` items can be dispensed
2. **Loop Skip**: `continue` statement skips non-dispensable items
3. **Stock Validation**: Only considers stock for dispensable items
4. **UI Logic**: Dispense button only shows when there are dispensable items

## Code References:

- `MedicationCashSaleItem::canBeDispensed()` - Lines 104-107
- `MedicationCashSaleController::dispense()` - Lines 256-263
- Stock validation logic - Lines 74-76
- UI logic in view - Line 198

This ensures that cancelled items are completely protected from dispensing!
