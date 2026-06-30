# Partysmith — Project Config

Layers on top of `web-team-agents.md`. That file is the **protocol** (roles, loop, Definition of Done, token rules); this file fills in the `<PROJECT>` placeholders, wires the real commands, and sets Partysmith-specific priorities. Load both; keep both compact.

**Stack:** CodeIgniter 4 · PHP 8.3+ · MariaDB · Bootstrap · vanilla JS.
**The fact that shapes everything:** money flows through the quote pipeline. A silent miscalculation costs real cash, so verification weight goes on logic correctness, not visual polish.

---

## Team shape (override of generic §1)

**Run it human-orchestrated.** You (Mark) are the Orchestrator and the Product Researcher; the autonomous core is **UX Auditor → Builder → Verifier**.

*Rationale (opinion, high confidence):* on a marketplace that takes deposits, you want a human deciding what gets built and in what order anyway — so the tokens you'd spend on an autonomous Orchestrator/Researcher buy you nothing here and cost you control. Keep the autonomous loop tight and let it run on audits and approved fixes; gate features and anything touching pricing/payments on your sign-off.

Scale up to the full five-agent autonomous version only if you ever want unattended overnight passes — and even then, never on pricing, payments, or role-enforcement code.

---

## Layer A gates — wired (run cheapest-first; stop at first failure)

| # | Gate | Command | Notes |
| --- | --- | --- | --- |
| 1 | Code style | `vendor/bin/php-cs-fixer fix --dry-run --diff` | The repo uses **php-cs-fixer** + `nexusphp/cs-config` (not phpcs). Cheapest gate. |
| 2 | Static analysis | *(not installed)* — `composer require --dev phpstan/phpstan` then `vendor/bin/phpstan analyse app/` | **Not yet in the repo.** Highest-value addition: high signal on the Libraries, cheap to run. Add it. |
| 3 | Unit + integration | `composer test` (= `vendor/bin/phpunit`) | PHPUnit **10.5**; `CIUnitTestCase`; DB-touching tests use `DatabaseTestTrait`. `composer test` is already wired. |
| 4 | Browser smoke + console | `npx playwright test` | Critical routes load, no console errors, key journeys complete. |
| 5 | Accessibility | `npx @axe-core/cli <url>` (or axe in Playwright) | WCAG 2.2 AA, zero critical/serious. |
| 6 | Performance | `npx lighthouse <url>` | Public routes only; Perf ≥ 90. |

**Test DB:** CI4 defaults to SQLite3 in-memory; for fidelity to MariaDB behaviour (esp. JSON columns like `quote_breakdown`), point `phpunit.xml` at a disposable MariaDB test schema and use `DatabaseTestTrait` with migrations.

> **Already in the repo:** PHPUnit 10.5 + `composer test`, Playwright (`npm run test:e2e`), a 4-route smoke spec, and unit tests for the quote pipeline. **Missing:** PHPStan (add it), and E2E beyond smoke (the wizard/booking/checkout journeys). See `partysmith-findings.md` for the grounded gap analysis — several "high-value test targets" below already exist and should not be rebuilt.

---

## High-value test targets (Builder builds coverage here first, in order)

1. **Quote pipeline — `EventQuoteBuilder → EventBookingQuote → VendorQuoteAutomation`.** The USP and the money. Cover every automation rule independently: auto-accept toggle, max-amount cap, travel-radius eligibility, min-lead-days, blackout dates, allowed event settings. Assert the `quote_automation_log` audit row is written on accept *and* decline.
2. **The six pricing models — one test class each.** Boundary cases that will bite:
   - Guest-based: the range edges (a 50-guest event vs 51 — off-by-one at the `1–50 / 51–100` seam).
   - Quantity: min/max enforcement.
   - Public event: commission % and the max-pitch-fee cap interacting.
   - Private event: the parent row correctly linking to its guest/duration/package/quantity tier.
   - Tiered packages: inclusions resolve per named tier.
   - Duration: hourly vs daily vs block selection.
3. **Deposit correctness.** Assert the live checkout path charges **15%**. *Flag for your decision:* the legacy cart's **10%** — is that path still reachable, and is the difference intentional? Treat as a candidate financial inconsistency until you confirm. (Inference, medium confidence it needs resolving.)
4. **Role enforcement.** For each protected route, assert the wrong role is blocked (`AdminAuth`, `requireVendor()`, `requireCustomer()`). Assert the live DB re-check works — a role promotion takes effect without a session restart. Security-critical; belongs in the gate set, not just the backlog.
5. **Stripe-optional path.** With no Stripe keys, the site still browses/registers/creates services and payment actions are *disabled, not broken*. Good regression anchor.

---

## UX audit priorities (Auditor works these surfaces first, by risk)

1. **Vendor 6-step service-creation wizard** (basics → location → pricing → availability → gallery → review). *Highest abandonment risk* (inference, high confidence — long wizards are where vendors drop). Per step: progress visibility, back/forward without data loss, validation timing (inline vs on-submit), error recovery, and mobile behaviour.
2. **Homepage hero search** (occasion · category · location · date) — the funnel entry; a weak search costs every downstream conversion.
3. **Service detail page** — gallery, pricing clarity (the six models render differently — each must read cleanly), reviews surfacing.
4. **Checkout + deposit** — is the 15% deposit and what it protects unambiguous at the point of payment?

---

## House rules (project consistency criteria — Auditor scores against these)

Your standing UI conventions. A touched surface that violates any of these scores **Consistency < 3** on the rubric (auto-fail of that dimension):

- **Cards have no visible borders** — separate by shadow/ground, not strokes.
- **Guest ranges are fixed and explicit** — e.g. "51–100", never vague "up to" phrasing. (Direct tie to guest-based pricing UI.)
- **Service detail order** — short description and description sit *beneath* the customisation options, with a reviews placeholder present.
- **Script accent unresolved** — the brief specifies **Mr Dafoe**, the live brand uses **Caveat**. The Auditor flags this on any brand-touching work and routes it to you; it does **not** silently pick one.

---

## Breakpoints & thresholds (fills generic §2)

- **Breakpoints:** 360 / 768 / 1024 / 1440 (Bootstrap-aligned).
- **Gates:** a11y ≥ 95 and zero critical/serious · Perf ≥ 90 · no test regressions · no new console errors.
- **Rubric:** mean ≥ 4.0, no single dimension < 3.
- **Exit conditions:** iteration cap 5 · diminishing-returns stop at < 0.3 rubric gain.

---

## Token notes specific to this repo

- **Scoped reads matter more here** (large codebase). Auditor cites `file:line` into `app/Libraries/` and `app/Controllers/`; Builder opens only those ranges. Nobody reads the whole tree.
- **Cheapest-first gate order is a real saving:** a `phpcs`/`phpstan` failure halts before you spend Playwright/Lighthouse tokens.
- **Model tiering:** gates 1–3 are tool runs producing compact pass/fail — let the Verifier run them on a cheaper model. Reserve the strongest model for the Builder when it's working inside the quote pipeline, where the reasoning actually matters.
