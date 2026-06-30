# Partysmith — Repo Reality Check (grounded findings)

Read from the actual `main` branch, not the brief. This corrects the backlog and config where they assumed greenfield. The Orchestrator reads this **before** the backlog so the team builds on what exists and targets real gaps. Findings are ranked by **money-risk**, with `file:line` evidence and fact/inference/opinion labels.

---

## F1 — Two live checkout systems with different deposit rates *(highest money-risk)*

- **Fact.** There are two reachable payment paths computing **different** deposits:
  - `EventController` (the event/basket flow): **15%** — `EventController.php:470` `$depositPercent = 0.15`, again at `:631`; PaymentIntent created at `:821` from the real basket total.
  - `CartController` (the legacy cart): **10%** — `CartController.php:105` `$eventDeposit = $eventTotal * 0.10`, again at `:396`.
- **Fact.** The legacy cart is still wired into the UI: `app/Views/cart_submit.php:48` posts to `cart/processPayment`. So a customer can be charged 15% or 10% depending on which entry point they reach.
- **Opinion (high confidence).** This is the single most important thing to resolve. Two parallel payment systems with divergent deposit maths on a platform that takes real money is a liability, not just an inconsistency. **Recommendation:** make `EventController`/15% canonical, retire the legacy cart path (`CartController::processPayment` + `cart_submit.php`), or — if the cart must stay — make it call the same deposit calculation. One source of truth for "what % do we take".
- **This resolves the earlier "deposit decision" blocker:** the answer is consolidation, not picking a number in the abstract.

## F2 — Orphaned PaymentIntent stub hardcoded to £15 *(billing footgun)*

- **Fact.** `PaymentController::createPaymentIntent` (route `payment/createPaymentIntent`, `Routes.php:124`) hardcodes `$depositAmount = 1500` with the comment *"For demo purposes… replace this with your actual logic"* — and the comment even misstates the value ("£5.00 or 500 pence" against a 1500 literal).
- **Inference (high confidence).** It appears orphaned — no view references `createPaymentIntent` (only `cart/processPayment` is linked). But it's a **live POST route**: anyone hitting it directly gets a £15 intent regardless of order.
- **Recommendation:** delete the route and controller method. Dead demo code that creates real payment intents shouldn't sit on a live route.

## F3 — `VendorQuoteAutomation`: money-critical, ~7 of 8 branches untested *(highest test-coverage gap)*

- **Fact.** `VendorQuoteAutomation::evaluateAfterCheckout` (`app/Libraries/VendorQuoteAutomation.php`) decides whether a booking auto-accepts — flipping `booking_items.status` to `accepted` and writing `quote_automation_log`. It has 8 outcomes: `auto_accept_disabled`, `quote_errors`, `over_max_amount`, `travel_warning`, `event_setting_not_allowed`, `insufficient_lead_time`, `unavailable`, `rules_matched`.
- **Fact.** `tests/unit/VendorQuoteAutomationTest.php` tests exactly **one** (`auto_accept_disabled`). The max-amount cap, travel guard, lead-time, blackout, allowed-settings, and the happy-path accept+log are all untested.
- **Inference (high confidence) — latent bug.** The travel-radius guard works by **string-matching warning copy** from the quote (`str_contains($w, 'exceeds the vendor')`, `'beyond the maximum'`, `'outside the vendor'`). If that human-readable warning text is ever reworded in `EventBookingQuote`, the guard silently stops firing and **out-of-radius bookings auto-accept**. This is a money-risk hiding in a string literal.
- **Recommendation:** (a) add branch tests for all 8 outcomes — this is the highest-value coverage in the codebase; (b) replace the string-match with a structured warning code, and pin the travel guard with a regression test so a copy change can't silently disable it.

## F4 — Stripe webhook: correct core, incomplete lifecycle

- **Fact (good).** `WebhookController::stripe` verifies the signature properly (`Webhook::constructEvent` with `STRIPE_WEBHOOK_SECRET`) and has a dedupe guard — `handlePaymentSucceeded` checks for an existing `payments` row by `payment_intent_id` before inserting, so a replayed `payment_intent.succeeded` updates rather than double-inserts. The keyless fallback is clean (500s out early if unconfigured).
- **Inference (medium-high).** Gaps for "fully functioning": only `payment_intent.succeeded` and `payment_intent.payment_failed` are handled — no `payment_intent.processing`, `canceled`, or `charge.refunded`, which the booking lifecycle (cancellations/refunds) will need. Idempotency is per-intent, not per-event-id. Uses raw `http_response_code()`/`exit()` rather than CI4 response objects, which works but is harder to test.
- **Recommendation:** start here for sandbox (it's basically sound), then add refund/cancel events when the refund policy is defined. Note the refund policy is still an open decision (backlog §Decisions).

## F5 — `EventQuoteBuilder`: thin coverage

- **Fact.** `tests/unit/EventQuoteBuilderTest.php` is a single method covering `mergeServiceLocation` only; the class is ~7.8KB. Lower money-risk than F3 but a real gap.

## F6 — `EventBookingQuote`: well covered — **do not rebuild**

- **Fact.** `tests/unit/EventBookingQuoteTest.php` is 725 lines. The 53KB workhorse is the best-tested unit in the repo. The backlog/config line "write quote-pipeline tests" does **not** apply here — target F3/F5 instead.

---

## Corrections to the other two files

- **Style gate is `php-cs-fixer`**, not `phpcs` (`composer.json` dev deps: `friendsofphp/php-cs-fixer`, `nexusphp/cs-config`, `codeigniter/coding-standard`). Config table corrected.
- **PHPStan is not installed.** It's the highest-value cheap gate to *add*, but it's an addition, not a wired command. Corrected.
- **Test runner:** `composer test` is wired (= `phpunit`); PHPUnit is **10.5**.
- **E2E exists but is smoke-only** (`tests/e2e/smoke.spec.js`, 4 public routes, status<400). The wizard/booking/checkout journeys are genuinely missing — that's real Epic 2/3/4 work.
- **Existing audit docs feed the epics, don't re-derive them:** `VENDOR_ONBOARDING_AUDIT.md` (Epic 2), `QA_REPORT_QUANTITY_PRICING.md` (pricing tests), `QA_SEED.md`. The team should ingest these as prior findings.
- **No sub-agents defined yet** — `.claude/` holds only `settings.json` and a `pr` skill. The agent files from `web-team-agents.md` are additive; nothing to reconcile. `CLAUDE.md` already documents the Docker/MariaDB setup, seed order, and test accounts the agents need.

---

## Revised "do this first" order (money-risk weighted)

1. **F1** — consolidate the deposit path (decision + retire/redirect legacy cart). *Human-gated.*
2. **F2** — delete the orphaned £15 stub route. Trivial, removes a live footgun.
3. **F3** — `VendorQuoteAutomation` branch tests + de-fragilise the travel guard. Highest-value coverage.
4. **F4** — confirm the new checkout's end-to-end Stripe path in sandbox; add refund/cancel webhook events once the refund policy is set.
5. **F5** — fill `EventQuoteBuilder` coverage.

Then proceed with the backlog epics (admin, vendor onboarding E2E, booking E2E) against this corrected baseline.
