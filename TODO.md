# Flash-Sale Checkout Implementation Plan

## 1. Update ProductController to calculate available stock dynamically
- [x] Modify `show` method to compute available stock as total stock minus active holds
- [x] Ensure calculation is accurate and performant

## 2. Update HoldController to validate stock availability
- [x] Add stock validation before creating hold
- [x] Ensure database locks prevent race conditions
- [x] Update stock reduction logic if needed

## 3. Update OrderController to check hold validity
- [x] Validate hold is not expired
- [x] Validate hold is not already used
- [x] Prevent reuse of holds

## 4. Enhance PaymentWebhookController for out-of-order handling
- [x] Improve idempotency logic
- [x] Handle webhooks arriving before order creation
- [x] Ensure correct final state regardless of order

## 5. Implement caching for stock availability
- [x] Add cache layer for available stock calculation
- [x] Invalidate cache on hold/order changes
- [x] Use appropriate cache driver

## 6. Create job/command for expired holds release
- [x] Create Artisan command to release expired holds
- [x] Schedule command to run periodically
- [x] Ensure background processing doesn't double-run

## 7. Write automated tests
- [x] Test parallel hold attempts at stock boundary (no oversell)
- [x] Test hold expiry returns availability
- [x] Test webhook idempotency (same key repeated)
- [x] Test webhook arriving before order creation

## 8. Update README
- [x] Document assumptions and invariants
- [x] Add setup and running instructions
- [x] Include testing instructions
- [x] Document logs/metrics locations
