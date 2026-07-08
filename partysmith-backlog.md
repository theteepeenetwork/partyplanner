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

## Decisions — RESOLVED 2026-07-03

All four blocking decisions are made. The team builds to these; changing them requires reopening the decision here.

- **Deposit %** ✅ **10% is the single source of truth** (revised 2026-07-03; was 15%). Change event checkout (`EventController`, currently 15%) to 10%, and retire the legacy cart: redirect `/cart/*` routes to the event-basket flow. Epic 4 asserts deposit = 10%. Update the CLAUDE.md "15% deposit" gotcha when the code change ships.
- **Refund / cancellation policy** ✅ Computed **per `booking_item`** (its share of the deposit), issued as Stripe **partial refunds** against the original PaymentIntent:
  - **Vendor declines/cancels** → that item's deposit refunded 100%, automatically.
  - **Customer cancels a confirmed item** → tiered by notice before the event date: **≥30 days = 100% · 14–29 days = 50% · <14 days or no-show = 0%**.
  - **Customer cancels while the quote is still pending** (vendor hasn't accepted) → free cancellation, 100%, regardless of notice.
  - Tiers are **platform-wide** for Epic 4; vendor-overridable presets are a later enhancement (see structured-cancellation-policy backlog item).
- **Review eligibility trigger** ✅ **Hybrid:** review window opens automatically **N days after the event date** unless the booking (item) was cancelled. Default N=2 unless overridden during Epic 5 build.
- **Script accent** ✅ **Mr Dafoe** (per the brief). Migrate any live Caveat usage; consistent everywhere.
- **Launch strategy** ✅ (added 2026-07-03, business review):
  - **Wedge:** North East England — Newcastle–Durham–Teesside–York corridor. All founding-vendor recruitment targets overlapping coverage of this area. No multi-region work until the wedge proves out.
  - **Launch categories:** catering (guest-based), DJs & photo booths (duration), kids' entertainers (packages). High quote-automation fit; no dependency on unbuilt pricing models. Adjustable if vendor recruitment says otherwise.
  - **Commission:** **10%, vendor-side, deducted at payout. Commission-only at launch** — no customer booking fees, no subscriptions. Premium/featured/subscription revenue is post-liquidity (year 2).
  - **Founding-vendor offer:** zero commission on first 5 bookings (or 3 months), founding badge, free featured placement. Target 25–30 vendors hand-onboarded before public launch.
  - **Launch gate (not a date):** a test search in the wedge returns **≥3 priced quotes in every launch category**. Add a quote-coverage metric to the admin dashboard (Epic 1 add-on).
  - **Leakage defence:** platform-protection messaging in chat, at quote acceptance, and on the booking page ("payments outside Party Smith aren't protected"); reviews stay booking-gated.
- **Vendor payout release** ✅ (added 2026-07-03) Funds are only ever released after the customer has **paid in full.** **Balance is due 14 days before the event** (deposit at booking, remainder at T−14 — inside the no-refund window by construction). Release timing is a per-vendor **payout tier**:
  - **Tier 0 (default):** held until **T+2 days after the event** (same trigger as the review window), per `booking_item`, unless cancelled/refunded.
  - **Tier 1 (track record):** released **on receipt of full payment**. Unlocked after **5 successful events** (completed, no refund, no dispute). Any dispute/refund **demotes to tier 0**; re-earn required.
  - Build the tier mechanism (per-vendor `payout_tier` + policy lookup in the payout engine) in Epic 4; everyone launches on tier 0.
  - Implies Stripe Connect with platform-triggered transfers (not instant pass-through) — an Epic 4 architecture requirement. Get a legal sanity-check on FCA safeguarding before live money.

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

---

## Follow-ups from the wizard-fixes cycle (PR #87, 2026-07-02)

Verifier-recommended items from the F1/F5/F3 wizard fixes (all three PASSED with an environmental waiver on automated a11y/perf scans):

- [ ] Add axe-core/Lighthouse tooling to CI or the dev environment so the a11y AA / Perf ≥ 90 gates can actually run — they were waived, not passed, on PR #87. Priority given F3 was an accessibility fix verified only manually.
- [ ] Add a phpunit/functional test covering wizard step2→step3 session behaviour: unchanged step2 resubmit preserves `step3_data`; a genuine event_types/pricing_type change clears it. No coverage exists today (the F1 data-loss fix is browser-verified only).
- [ ] Focus management on step3 validation failure: move focus to the first error container/offending field. Deferred from F3 because the submit handler's four validators only aggregate a boolean; needs a small rework of that flow in `public/assets/js/service_forms/step3.js`.
- [ ] Commit a proper `.php-cs-fixer.dist.php` (CodeIgniter4 ruleset via nexusphp/cs-config, `->notPath('Views')` — the fixer mangles mixed HTML/PHP templates). No config is committed today, so the documented `fix app/` command falls back to the wrong default ruleset. Do NOT bulk-reformat existing code in the same change.

---

## Status audit (2026-07-03, code-inspection; suite not run — no host PHP in audit env)

Per-task verdicts from a three-agent read-only audit. **Execution order lives in `partysmith-workplan.md`** — that file is the handoff for Claude Code.

| Epic | Task | Verdict | Note |
| --- | --- | --- | --- |
| 1 | Vendor vetting queue | **MISSING** | No `vendor_status` column anywhere; vendors live on registration → workplan B1 |
| 1 | Service moderation | PARTIAL | Toggle works; no reason capture / audit record → B5 |
| 1 | Bookings oversight | DONE | Full read view incl. `quote_breakdown` |
| 1 | Reviews moderation | DONE | Edit/delete + profanity filter; no audit record → B5 |
| 1 | Messages oversight | DONE | Approve/reject/flag with reviewed_by/admin_note |
| 1 | AdminAuth on all routes | DONE | Whole group filtered, DB re-check per request; **role tests MISSING** → B5 |
| 2 | Registration → vetted → dashboard | PARTIAL | Works, but no vetting state (blocked by Epic 1.1) |
| 2 | Wizard steps | DONE* | 7 steps, order differs from the (stale) task wording — implementation is canonical |
| 2 | Wizard resilience | DONE | Session persistence + stale-pricing clearing, unit-tested |
| 2 | Pricing → correct table (all 6 models) | DONE | Round-trip tests missing → C1 |
| 2 | Vendor dashboard | DONE | Bookings/calendar/earnings/quote-settings all routed |
| 3 | Hero search honours coverage radius | **PARTIAL — critical** | Location is free-text LIKE; radius never enforced in browse → B2 |
| 3 | Service detail house rules | PARTIAL | Structure present; house-rule placement unverified → C2 |
| 3 | Event basket | DONE | Deposit hardcoded 15% → F1/A1 |
| 3 | Quote generation | DONE | EventBookingQuote well-tested (725-line test); EventQuoteBuilder thin → A5 |
| 3 | Checkout assembly | DONE | `quote_breakdown` persists |
| 4 | Keyed path + keyless fallback | DONE | Clean fallback |
| 4 | PaymentIntent at agreed % | PARTIAL | 15% in code vs decided 10% → F1/A1 |
| 4 | Webhook signature/success/failure/idempotency | DONE (core) | Only 2 event types; refund/cancel missing → B6 |
| 4 | Payments persistence + status flip | **PARTIAL — critical** | **Booking never flips to 'confirmed' on payment success** → A3 |
| 4 | Customer payment states | PARTIAL | Structure exists; messaging unverified |
| 4 | Quote-automation tie-in | PARTIAL | Automation runs **before** payment confirms — can auto-accept unpaid bookings → A3 |
| 5 | Chat room on booking | DONE | Created in processCheckout |
| 5 | Chat gated to booking parties | DONE | Triple-checked in ChatController; **tests missing** → C4 |
| 5 | Vendor templates in chat | PARTIAL | Model exists, used in quotes, absent from chat UI → C4 |
| 5 | Post-event review | DONE* | Trigger is `event.date < today`; decision says **T+2** → B4 |
| F2 | £15 stub | **STILL LIVE** | Routed POST, hardcoded £15 → A2 |
| F3 | Automation branch tests / travel guard | OPEN | 1 of 8 branches tested; string-match guard remains → A4 |
| F4 | Stripe E2E path | PARTIAL | Core sound; lifecycle events missing → B6 |
| F5 | EventQuoteBuilder coverage | OPEN | Single test → A5 |

---

## F1 — Deposit consolidation & legacy cart retirement (scoped 2026-07-03, human-gated)

**Decision applied:** 10% is the single deposit (see Decisions). Code-grounded scope:

**In scope**

- [ ] Introduce a single deposit constant (e.g. `DEPOSIT_PERCENT = 0.10`) in one place, following existing patterns (business logic in `app/Libraries/`). All call sites read it.
- [ ] `EventController` — replace both hardcoded `0.15` sites (`~L470` checkout assembly, `~L631` quote JSON endpoint) with the constant.
- [ ] Views — remove hardcoded "15%" copy; render the percent from a value passed by the controller: `event/checkout.php`, `event/basket.php`, `partials/event_planning_card.php`, `dashboard/customer_booking_detail.php`.
- [ ] Retire the legacy cart: remove the seven `/cart*` routes (`Routes.php` L119–127); GET `/cart` and `/cart/add/(:num)` become redirects to the customer dashboard with a flash message; POST money-path routes (`submit`, `submitToVendors`, `processPayment`) must cease to exist (405/redirect, never processed). Delete `CartController` (494 lines) and `cart_view.php`. Grep confirms no inbound links from views/JS today.
- [ ] Update docs: `CLAUDE.md` gotchas ("15% deposit" / "legacy cart 10%" lines) and `README.md` cart mentions.

**Out of scope:** F2 (orphaned £15 `PaymentController::createPaymentIntent` stub — separate task), Stripe/payment-schedule work (Epic 4), refund logic.

**Acceptance criteria**

1. Deposit percent is defined in exactly one place; `grep -rn "0\.15\|0\.10" app/` yields no deposit literals outside that definition.
2. Unit test asserts checkout assembly deposit = 10% of quote total, rounded to 2dp (boundary: penny-rounding case).
3. No rendered customer-facing view contains "15%"; basket/checkout/booking-detail/planning-card show 10% sourced from the constant.
4. Every former `/cart` URL redirects (no 404s); no POST to any cart route can move money; `CartController` and `cart_view.php` are gone.
5. Full suite green (`composer test`) with no regressions; `php -l` and php-cs-fixer clean on touched files; touched pages browser-verified with no console errors.

**Verify:** verifier runs the Definition-of-Done gates; FAIL returns a specific gap to the builder.

---

## Onboarding coverage gaps (audit refresh, 2026-07-03)

From re-verifying `VENDOR_ONBOARDING_AUDIT.md` against current code and the UK supplier landscape (Add to Event / Poptop taxonomies). Audit roadmap items 1, 2, 3 and 5 have shipped; these are the remaining holes, ordered by supplier classes unblocked. **Anything marked human-gated touches pricing/deposits — approve before build, review diff before ship.**

- [ ] **Multi-day events & bookings** *(human-gated — quote pipeline)*: add an event end date and make duration "days" pricing bookable across a range. Unblocks glamping, hot tubs, light-up letters, exhibitions, festivals. Engine currently assumes a single `events.date` throughout `EventBookingQuote`.
- [ ] **Per-staff × hours pricing model** *(human-gated — new `pricing_type`)*: (headcount × hours × rate), min-staffing. Unblocks security, waiting staff, bar staff, first-aid/medical cover. Add as a first-class `pricing_type` per the CLAUDE.md constraint, not bolted onto duration.
- [ ] **Consumer-facing compliance/credentials**: surface DBS, SIA, food-hygiene rating, alcohol/TENs, PLI on public/private listings + browse filter. Fields exist only in the corporate branch today. Cheap trust win; mostly form + display.
- [ ] **A→B distance pricing for transport** *(human-gated — new pricing path)*: per-journey/per-mile quoting; the radius travel model misfits limos/party buses/coaches.
- [ ] **Seasonal / day-of-week pricing** *(human-gated)*: peak (Sat/Christmas) vs off-peak modifiers on existing models.
- [ ] **Refundable security deposits / damage waivers** *(human-gated — money path)*: hire businesses (inflatables, hot tubs, furniture) need a refundable deposit distinct from the 15% booking deposit. Depends on the Epic 4 refund-policy decision.
- [ ] **Minimum spend** (general, not corporate-only): common for caterers/mobile bars.
- [ ] **Structured cancellation policy**: replace the step-6 free-text textarea (currently pre-filled with a `[placeholder]` template) with structured refund tiers; prerequisite for automating refunds in Epic 4.
- [ ] **Taxonomy top-up** (data-only): add category rows for hot tubs, fireworks/pyrotechnics, bell tents/glamping structures, fairground rides, Santa's grottos, waste management.
- [ ] **Specific event-type taxonomy** (audit roadmap 4): vendor-selectable wedding/birthday/festival/school/charity types, customer-filterable; today only public/private/corporate buckets.

---

## Storefront redesign — follow-ups (2026-07-08, mode-B lander)

From the `tenant/home.php` redesign (hero CTAs, on-page date field, sticky header, reviews, closing CTA). No schema changes were made; these are the gaps found and deferred:

- [ ] **Dedicated custom-package enquiry mechanism on the storefront.** The new closing "Send an enquiry" CTA points at the vendor phone (`tel:`), or the on-page quote/date field when no phone is set — there is **no enquiry form/route** on a tenant host (`TenantController` has no contact endpoint; the marketplace `contact` route 404s on tenant hosts by design). A lightweight guest enquiry form (name/email/message → vendor) would let "can't see what you need" leads convert without a phone call. Rendered with graceful fallback for now; needs a route + a `vendor_enquiries` store (or reuse `chat_rooms` un-gated for pre-booking enquiries) — flag before build, it borders the messaging system.
- [ ] **Per-service pricing on the mode-B cards is only as good as `fromPrice()`.** Cards call `TenantBookingFlow::fromPrice()`; services with no pricing config render no price (graceful, by design). Not a defect, but if the launch categories expect a visible "from" on every card, ensure the wizard requires at least one pricing row before publish.

## Time-slot booking (2026-07-08, shipped — storefront)

Time-based (hours-duration) storefront services now book a **slot**, not a whole day: the customer picks a **start time** (required), the window becomes `start → start+duration`, and availability clashes only on time overlap padded by the service's **setup/breakdown minutes** (already vendor-set in wizard step 4 — no schema or wizard change needed). Persisted on `bookings`/`booking_items.start_time/end_time`. The multi-service lander greys out services already booked across the chosen date (+ time). Engine: `ServiceAvailabilityChecker` (time-aware), `TenantBookingFlow::resolveWindow()`; enforced through `EventQuoteBuilder`. Fixed time blocks keep their own window; day-duration + guest/quantity/package stay whole-date. **Human-gated areas touched (approved before build):** quote pipeline (`EventQuoteBuilder` now passes the window to the checker) and booking writes.

Deliberate follow-ups (not done in this PR):

- [ ] **Enforce vendor operating hours as slot bounds.** `service_availability` (per-weekday open/close) is not yet checked — a start time outside opening hours isn't rejected. Wire it into `ServiceAvailabilityChecker` slot mode.
- [ ] **Lander time probe is coarse.** With a chosen time, time-based cards grey out using a nominal 30-min probe window from the start (the per-service duration isn't known on the lander). Precise greying would need the duration; the exact slot is still enforced at quote time. Consider an AJAX per-card check keyed on the shortest duration tier.
- [ ] **Marketplace booking path doesn't capture a start time.** Only the storefront flow (`TenantController`) sets the window today; `EventController` bookings still store null times (treated as whole-day by the checker — fail-closed, safe). If the marketplace should also slot-book, mirror the capture there.
- [ ] **Multi-day (day-duration) still books whole-date** — tracked separately under the multi-day events backlog item.

## Selectable storefront colour themes (2026-07-08, shipped — storefront + checkout)

Vendors pick one of **6 curated colour themes** (clean, warm, porcelain, graphite, teal, indigo) on `/profile/my-site`; the choice themes the whole white-label journey — storefront **and** every checkout page. Registry: `App\Libraries\StorefrontThemes` (keys/labels/preview colours = single source of truth); full palettes live as `.sf-theme-*` classes in `tenant-storefront.css`; the shared tenant header applies `body.sf-theme-{key}`, so one switch flows through every tenant page. New nullable `vendor_sites.theme` column (migration `AddThemeToVendorSites`; null → resolves to default `clean`). The editor now shows a theme picker with a live preview instead of raw hex pickers (per decision: **presets only, full palette**).

Notes / follow-ups (not defects):

- [ ] **`vendor_sites.primary_color` / `secondary_color` are now unused** (presets replaced free hex). Columns kept so no data is destroyed; a later migration could drop them. The old inline hex injection + `tenant_hex_color`/`tenant_darken_hex`/`tenant_contrast_safe` helpers and `Profile::normaliseHexColour` were removed.
- [ ] **Existing vendors default to `clean`** (theme is null until they pick). If a closer auto-mapping from their old hue is wanted, that's a one-off data migration.
- [ ] **Themes use `oklch()`** (as authored in the design). Support is effectively universal on current browsers; pre-2023 browsers would fall back to unstyled colours. Add hex fallbacks only if analytics show meaningful legacy traffic.
- Layout unchanged — this is the theming layer on the current storefront/checkout, not the zip's alternate ModernStorefront layout.

## Booking confirmation redesign — account funnel (2026-07-08, shipped — storefront)

Reworked the tenant confirmation page (`tenant/booked.php`) per the "Booking Confirmation Redesign" design, **frame 1a (manage-first split)**: celebratory hero → horizontal 3-step "what happens next" → two columns (account-creation form as the wide primary column + booking summary aside). Replaces the old one-line "Create →" nudge.

Every guest checkout already auto-creates a customer account (`findOrCreateCustomer`), so "create an account" = **claim that account by setting a password**. New tenant route `POST /account/create` → `TenantController::createAccount()`. **Security spine:** password-setting is gated to an account THIS session created (`tenant_claimable_user`, set at checkout only when the account was new) — a session that merely owns a booking linked to a *pre-existing* account cannot reset that account's password (takeover guard, regression-tested). Plus session-owns-booking + field validation (8+ chars, match, terms).

Follow-ups / notes (not defects):

- [ ] **No logged-in area on the tenant host by design.** After claiming, the user signs in on the **main marketplace** (`/login`) to manage/pay/message — the tenant host stays guest-only. If a tenant-hosted "my booking" area is ever wanted, it's a larger auth piece.
- [ ] **Design offered two layouts** (1a split — implemented; 1b linear funnel — not built). Swap is a view-only change if 1b is later preferred.
- Terms/Privacy links follow the existing marketplace convention (`register.php` points them at `/contact`, since no dedicated `/terms` or `/privacy` route exists).

Notes for the Verifier (not defects):

- **Palette:** the hero primary CTA uses a white fill with vendor-primary text (not marketplace coral-vermillion) — deliberate. This is the **white-label storefront**, whose entire design system themes on the vendor's `--sf-primary`/`--sf-accent`; trust elements stay neutral and no PartySmith branding appears. Hardcoding coral would break the tenant theming contract. The "accent" role maps to the vendor CTA treatment here.
- **Coverage** in the hero meta row reads `services.service_location` (exists in prod schema + `database_update.sql`); the SQLite tenant-test `services` table was missing it, so `service_location` was added to the test migration to exercise the pill.
