# Partysmith — Workplan (audited 2026-07-03)

Ordered todo list for Claude Code. Each item is one builder→verifier cycle against the
Definition of Done in CLAUDE.md. Work strictly top-down within a phase; phases are
dependency-ordered. Items marked **[HUMAN-GATED]** touch money/pricing — Mark approves
before build and reviews the diff before merge. Open PRs as drafts; never merge.

Grounding: full per-task audit verdicts are in `partysmith-backlog.md`
§ "Status audit". Decisions (deposit 10%, refund tiers, T−14 balance, payout tiers,
review T+2, Mr Dafoe, launch strategy) are in `partysmith-backlog.md` § Decisions —
build to those values, do not re-ask.

---

## Phase A — Money-path correctness (do first, in order)

- [x] **A1. F1 — Deposit consolidation & legacy cart retirement** [HUMAN-GATED, scope approved]
  *(verifier PASS 2026-07-04 — PR #90; suite 57/57; live gates green; cs-fixer gate noted as unexecutable until the config lands)*
  Implement exactly `partysmith-backlog.md` § "F1 — Deposit consolidation & legacy cart
  retirement (scoped 2026-07-03)". Single 10% constant; both `EventController` sites;
  four views; `/cart*` routes removed/redirected; `CartController` + `cart_view.php`
  deleted; CLAUDE.md/README updated; deposit unit test with rounding boundary.

- [x] **A2. F2 — Delete the orphaned £15 PaymentIntent stub**
  *(verifier PASS 2026-07-04 — PR #92; suite 58/58; live 404 confirmed; controller fully orphaned, deleted whole)*
  `PaymentController::createPaymentIntent()` (hardcoded £15) and its route
  (`Routes.php` ~L124, `payment/createPaymentIntent`) still exist and accept POSTs.
  Delete both. If nothing else in `PaymentController` is routed, delete the controller.
  Verify: route returns 404; grep shows no references; suite green.

- [ ] **A3. Flip booking status on payment confirmation + align quote automation** [HUMAN-GATED]
  Two coupled defects: (1) `processCheckout()` inserts bookings as 'pending' and the
  webhook success handler (`WebhookController::handlePaymentSucceeded`) never flips
  booking status to 'confirmed' — paid bookings stay pending forever. (2)
  `VendorQuoteAutomation::evaluateAfterCheckout()` runs before payment confirmation, so
  auto-accept can fire on a failed payment. Fix: webhook success → booking confirmed →
  then run automation (or re-verify payment state before auto-accept). Keyless/simulated
  mode must still work. Tests: status-flip on success, no flip on failure, no
  double-processing on webhook replay, automation never accepts an unpaid booking.

- [ ] **A4. F3 — VendorQuoteAutomation: structured travel code + full branch tests**
  The travel guard string-matches human-readable warning text
  (`str_contains($w, 'exceeds the vendor'...)`, `VendorQuoteAutomation.php` ~L44–53) —
  rewording a warning silently breaks it. Add a structured warning `code` to
  EventBookingQuote warnings and match on that. Then cover all 8 outcomes
  (auto_accept_disabled, quote_errors, over_max_amount, travel_warning,
  event_setting_not_allowed, insufficient_lead_time, unavailable, rules_matched) —
  only 1 of 8 is tested today (`VendorQuoteAutomationTest.php`).

- [ ] **A5. F5 — EventQuoteBuilder coverage**
  `EventQuoteBuilderTest.php` has a single test (mergeServiceLocation). Add tests for
  `build()` across each pricing model, missing-pricing-rows error handling, and travel
  fee integration. Include boundary tests: guest-range edge (50 vs 51), quantity
  min/max clamps.

## Phase B — Launch blockers

- [ ] **B1. Vendor vetting queue (Epic 1.1 — entirely missing)**
  No `vendor_status` column exists; vendors are live the moment they register. Add
  `users.vendor_status` (pending/approved/rejected), a `/admin/vendors` vetting queue
  (list pending, approve/reject with reason), gate vendor-only routes on approved
  status (pending vendors see a "under review" dashboard state), role/status takes
  effect without re-login (AdminAuth-style DB re-check). Migration must be additive;
  default existing vendors to 'approved'. Tests: state transitions + route gating.

- [ ] **B2. Travel-radius filter in browse (Epic 3.1 — critical gap)**
  Browse "location" is a free-text LIKE on `services.service_location`; coverage radius
  is never enforced in search (haversine logic already exists in EventBookingQuote
  ~L842). Backlog Epic 3 acceptance: out-of-radius services must not appear bookable.
  Geocode-or-match the searched location, filter by each service's coverage radius
  (respect `no_travel_limit`). Tests: in-radius shown, out-of-radius excluded,
  nationwide flag bypasses.

- [ ] **B3. Quote-coverage metric on admin dashboard (launch gate)**
  Per § Launch strategy: admin widget showing, for the wedge (NE England) and each
  launch category, how many services return a valid instant quote for a standard test
  search (e.g. 50 guests, Newcastle, 30 days out). Read-only; powers the "≥3 quotes
  per category" launch gate.

- [ ] **B4. Review trigger: align to the T+2 decision**
  `BookingItemModel::isReviewableByCustomer()` opens reviews when `event.date < today`;
  the decision is **T+2 days** (and never for cancelled items). Change the gate,
  add boundary tests (event day, T+1, T+2, cancelled item).

- [ ] **B5. Admin audit records + role-enforcement tests (Epic 1 acceptance)**
  Messages moderation records reviewed_by/reviewed_at/admin_note; Reviews, Services and
  Bookings moderation actions write no audit record. Add a single `admin_actions` log
  (actor, action, target, reason, timestamp) used by all four. Add the missing
  role-enforcement tests: customer/vendor sessions rejected on each /admin route group,
  admin accepted.

- [ ] **B6. Webhook lifecycle events (F4 completion)** [HUMAN-GATED]
  Webhook handles only payment_intent.succeeded/payment_failed. Add
  payment_intent.canceled and charge.refunded handling consistent with the refund
  decision (per-booking_item partial refunds, tiers 100/50/0 at ≥30/14–29/<14 days,
  pending-cancel free). Persist refund records; keep idempotency (prefer per-event-id
  dedupe). Full refund *execution* UI is Epic 4 follow-on; this task is the webhook +
  persistence layer.

## Phase C — Verification & polish

- [ ] **C1. Wizard E2E + pricing round-trip tests (Epic 2 verify step)**
  Playwright: complete wizard run per pricing model branch; mid-flow abandon/resume.
  PHPUnit: save → reload → identical for each of the six pricing tables. Also the
  step2→step3 session-preservation test from § Follow-ups.

- [ ] **C2. Service detail house-rule check (Epic 3.2)**
  Verify/fix: gallery, per-model pricing rendering, reviews surfaced; short + long
  description sit *beneath* customisation options; reviews placeholder present; no
  visible card borders; guest ranges shown as fixed ranges (never "up to").

- [ ] **C3. Booking-path E2E (Epic 3 verify step)**
  Playwright: hero search → service detail → add to basket → checkout assembly
  (keyless mode), asserting basket totals equal quote breakdown and deposit = 10%.

- [ ] **C4. Vendor message templates in chat (Epic 5.3)**
  `vendor_message_templates` exist and are used in quote compose but not chat. Add a
  template picker to the vendor chat UI. Plus the missing chat access-control tests
  (non-party blocked from room, no-booking customer can't start chat).

- [ ] **C5. PR #87 follow-ups (from § Follow-ups)**
  axe-core/Lighthouse tooling so a11y/perf gates actually run; step3 focus management
  on validation failure; commit `.php-cs-fixer.dist.php` (CodeIgniter4 ruleset,
  `->notPath('Views')`, no bulk reformat).

---

## Standing instructions for every item

1. Branch per item, draft PR, conventional summary; never merge — Mark reviews.
2. Run `docker exec partyplanner sh -c 'find app -name "*.php" -exec php -l {} \;'`
   and `docker exec partyplanner php vendor/bin/phpunit --testdox` before claiming done.
3. Browser-verify touched pages (QASeeder first: `php spark db:seed QASeeder`); check
   console errors.
4. Only the verifier marks an item complete; tick the box here in the same PR.
5. Respect CLAUDE.md architecture rules (thin controllers, logic in app/Libraries/, one
   pricing model per service, don't touch unrelated files) and the house rules.
