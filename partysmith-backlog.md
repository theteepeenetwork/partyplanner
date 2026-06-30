# Partysmith — Delivery Backlog (mission scope)

This is the `BACKLOG.md` the Orchestrator reads (generic spec §4). Five epics, **dependency-ordered**. Every task is scoped to be independently verifiable against the Definition of Done. "Functioning" is produced by the Builder running against the repo and proven by the Verifier — this file is the plan they execute, not the code itself.

**Anything touching pricing or payments is human-gated** (you approve before build, you review the diff before it ships).

---

## Critical path

```
Epic 1 Admin  ──┐
                ├──► Epic 2 Vendor onboarding ──► Epic 3 Customer booking ──► Epic 4 Stripe ──► Epic 5 Interactions
(vetting gates  │   (creates supply)              (consumes supply,          (completes        (booking-gated:
 supply)        │                                  ends at checkout)          the money path)    needs bookings)
```

Epics 3 and 4 are tightly coupled at the checkout seam — build the booking path *up to* payment, then complete it with Stripe. Don't try to ship Epic 3's checkout without Epic 4.

---

## Decisions to make BEFORE the relevant epic starts

These are yours, not the team's. Each blocks the epic noted.

- **Deposit %** (blocks Epic 4). Resolve 15% (checkout) vs 10% (legacy cart): is the legacy path still reachable? Is the difference intended? Pick one source of truth.
- **Refund / cancellation policy** (blocks Epic 4). What happens to the deposit on customer cancel, vendor decline, or no-show? Stripe work can't be "done" without this defined.
- **Review eligibility trigger** (blocks Epic 5). Reviews are "after the event" — what signals event completion? A date passing? An admin/vendor mark-complete? Reviews can't open without it.
- **Script accent** (blocks any brand-touching task). Mr Dafoe (brief) vs Caveat (live). One decision, then it's consistent everywhere.

---

## Epic 1 — Admin back office

**Goal:** `/admin` fully operational so the marketplace can be governed: vet vendors, moderate the catalogue, oversee bookings/reviews/messages.

**Tasks**

- [ ] Vendor vetting queue: list pending vendors, approve/reject, state transitions persisted, role takes effect live (per the DB re-check).
- [ ] Service moderation: approve/unpublish services, with reason captured.
- [ ] Bookings oversight: read view of `bookings`/`booking_items`, status, `quote_breakdown`.
- [ ] Reviews moderation: hide/remove abusive reviews.
- [ ] Messages oversight: read access to `chat_rooms`/`chat_messages` for dispute handling.

**Acceptance criteria**

- Every `/admin/*` route is reachable only behind `AdminAuth`; a customer/vendor session is rejected (tested).
- Approving a pending vendor flips their role and they can immediately reach vendor routes with no re-login.
- Each moderation action writes an auditable record.

**Verify:** role-enforcement tests (cross-role access blocked on each admin route); state-transition tests for vetting; smoke test of each admin page (loads, no console errors).
**Depends on:** existing auth/role system (in place).

---

## Epic 2 — Vendor onboarding

**Goal:** a vendor can register, get vetted, build a service via the 6-step wizard, and manage from their dashboard.

**Tasks**

- [ ] Registration → vetted state → first dashboard landing.
- [ ] 6-step wizard: basics → location/fulfilment → pricing → availability → gallery → review & publish.
- [ ] Wizard resilience: back/forward without data loss; per-step validation; resumable if abandoned mid-flow.
- [ ] Pricing step routes to the correct one of the six models and persists to the right table.
- [ ] Dashboard: bookings, calendar, earnings, quote-settings.

**Acceptance criteria**

- A vendor completes all 6 steps and the service publishes with pricing stored in the correct pricing table.
- Leaving at step 4 and returning preserves steps 1–3.
- Each pricing model is selectable and round-trips (save → reload → identical).
- **House rules honoured:** no visible card borders; guest ranges shown as fixed ranges, never "up to".

**Verify:** wizard E2E (Playwright) covering completion + mid-flow abandonment/resume + each pricing branch; unit tests that each pricing model persists/loads correctly; axe pass on every step; responsive at all breakpoints (wizard is the highest-risk surface).
**Depends on:** Epic 1 (vetting must work to move a vendor to live).

---

## Epic 3 — Customer booking pathway

**Goal:** a customer goes hero-search → browse → service detail → event basket → checkout, end to end (payment completed in Epic 4).

**Tasks**

- [ ] Hero search (occasion · category · location · date) → filtered results honouring vendor coverage/radius.
- [ ] Service detail: gallery, pricing rendered correctly per model, reviews surfaced. Short description + description sit *beneath* customisation options; reviews placeholder present (house rule).
- [ ] Add to event basket (`event_basket_items`); basket management.
- [ ] Quote generation on configure: `EventQuoteBuilder → EventBookingQuote` produces a correct line-item quote.
- [ ] Checkout assembly up to the pay step: deposit calculated, `quote_breakdown` JSON stored on `booking_items`.

**Acceptance criteria**

- Search respects coverage: a service outside its travel radius for the event location does not appear as bookable.
- Each of the six pricing models produces a correct quote, including the guest-range boundary (50 vs 51) and quantity min/max.
- Basket totals and the assembled deposit match the quote breakdown exactly.

**Verify:** quote-pipeline unit tests (the highest-value coverage — every automation rule + every pricing model); E2E of the full browse→basket→checkout-assembly journey; boundary tests on guest ranges and quantity limits.
**Depends on:** Epic 2 (need real published services to book).

---

## Epic 4 — Sandbox Stripe integration *(human-gated)*

**Goal:** checkout completes against Stripe **test mode** — deposit charged, booking confirmed, money path auditable and idempotent.

**Tasks**

- [ ] Enable the keyed path (test keys in env) without breaking the keyless fallback (site still works with no keys).
- [ ] PaymentIntent creation at the agreed deposit % on the assembled booking total.
- [ ] `/webhook/stripe`: verify the signature; handle success/failure; **idempotent** (a replayed event doesn't double-process).
- [ ] Persist to `payments` / `payment_schedules`; flip booking status on confirmed payment.
- [ ] Surface success/failure/pending states to the customer; deposit-protection messaging clear at the pay step.
- [ ] Tie in `VendorQuoteAutomation` outcome (auto-accept vs manual) so payment and quote acceptance stay consistent.

**Acceptance criteria**

- A test-card success confirms the booking, writes a `payments` row, and updates status exactly once even if the webhook fires twice.
- A declined/abandoned payment leaves no confirmed booking and no orphaned state.
- With no keys configured, payment is cleanly disabled and the rest of the site is unaffected (regression anchor).
- Deposit equals the **agreed** % (see Decisions) — asserted by test.

**Verify:** payment-path integration tests incl. webhook signature + replay/idempotency; the keyless-fallback regression test; deposit-amount assertion. This is the most heavily-tested epic — it moves money.
**Depends on:** Epic 3 (checkout must reach the pay step); deposit + refund decisions made.

---

## Epic 5 — Customer ↔ vendor interactions

**Goal:** booking-gated chat works, and customers can review after the event.

**Tasks**

- [ ] On booking, create the `chat_rooms` link between customer and vendor.
- [ ] Chat: send/receive `chat_messages`, gated so only parties to a booking can access the room.
- [ ] Vendor message templates (`vendor_message_templates`) usable in chat.
- [ ] Post-event review: opens on the eligibility trigger (see Decisions), writes `reviews`, rating surfaces on the service page.

**Acceptance criteria**

- A user with no booking relationship cannot open or read a chat room (tested).
- A review can only be left once the eligibility trigger has fired, and only by the booking's customer.
- A new review updates the service's surfaced rating.

**Verify:** access-control tests on chat rooms (the security-critical bit); review-eligibility tests; E2E of book → message → (event completes) → review.
**Depends on:** Epics 3–4 (a real booking must exist).

---

## How the team runs this

The Orchestrator works **top-down, respecting the dependency arrows** — it does not start Epic 3 tasks while Epic 2 is incomplete. Each task goes Builder → Verifier against the Definition of Done; any task tagged *human-gated* (all of Epic 4, the pricing-step task in Epic 2, anything touching `quote_breakdown`) pauses for your approval before build and your review before ship. Findings the UX Auditor raises along the way append to this backlog as fix-tasks and are prioritised against the epics.

Scope is large; the protocol's exit conditions and tight task-scoping are what keep it from sprawling. Treat each checkbox as one Builder→Verify cycle, not a sprint.
