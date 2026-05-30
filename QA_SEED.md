# QA Seed Data

A complete, self-contained data set for manual / browser QA of the customer
and vendor journeys. Everything below is created by a single seeder and every
service is **quoteable immediately** with a deterministic, documented result.

```bash
php spark db:seed QASeeder
```

The seeder (`app/Database/Seeds/QASeeder.php`) is **idempotent** — it removes
its own previously-created rows before inserting, so it is safe to run as many
times as you like (see [Reseeding safely](#reseeding-safely)).

---

## What gets created

| Entity | Count | Notes |
| --- | --- | --- |
| Customers | 2 | `customer1@c.com`, `customer2@c.com` |
| Vendors | 2 | `vendor1@v.com`, `vendor2@v.com` |
| Services | 4 | All **active**, one per private pricing model |
| Demo events | 2 | One per customer, with coordinates for £0 travel |

Each service is fully configured:

- **Active** (`status = active`)
- **Image** — a primary `service_images` row pointing at a real file in
  `public/uploads/services/` (renders, no 404)
- **Event-type mapping** — available for `private` events
- **Coverage data** — base coordinates (central London), `free_coverage_radius`
  80 km, `paid_coverage_radius` 120 km, `travel_fee_per_km` £1.50
- **Structured pricing** — a `services_private_event_pricing` parent row plus
  the model-specific tier rows
- **One optional extra** — opt-in, *not* included in the base quote

---

## Login credentials

All accounts share the password **`password`**. These match `TEST_ACCOUNTS.md`.

| Role | Email | Username | Password |
| --- | --- | --- | --- |
| Customer | `customer1@c.com` | `customer1` | `password` |
| Customer | `customer2@c.com` | `customer2` | `password` |
| Vendor | `vendor1@v.com` | `vendor1` | `password` |
| Vendor | `vendor2@v.com` | `vendor2` | `password` |

Login is at `/login` and accepts either the email or the username.

---

## Services

| Service | Owner | Pricing model | Category |
| --- | --- | --- | --- |
| QA Catering Co — Buffet & Grazing | `vendor1` | Guest-based | Catering & Drinks |
| QA Snapshot — Photo Booth Hire | `vendor1` | Duration-based | Photo Booths & Experiences |
| QA Grand Marquees — Marquee Packages | `vendor2` | Package-based | Marquees & Outdoor Events |
| QA Comfort Hire — Event Chair Hire | `vendor2` | Quantity-based | Furniture & Equipment Hire |

**Pricing detail**

- **Guest-based** — £8.00/guest (1–50), £6.00/guest (51–150), £5.00/guest (151–1000)
- **Duration-based** — 3 h £250, 5 h £400, 8 h £600 *(default = first/3 h)*
- **Package-based** — Bronze £750, Silver £1200, Gold £1800 *(default = Bronze)*
- **Quantity-based** — £4.00/chair, minimum 50 chairs, no upper limit *(default qty = 50)*

> The "default" option is what the quote engine selects when a service is added
> with no option chosen, which is why every service quotes immediately.

---

## Demo events

| Event | Owner | Type | Guests | Setting | Coordinates |
| --- | --- | --- | --- | --- | --- |
| QA Summer Wedding | `customer1@c.com` | Wedding | 80 | private | London (51.5033, -0.1196) |
| QA Birthday Party | `customer2@c.com` | Birthday | 120 | private | London (51.5033, -0.1196) |

Both events carry coordinates that sit inside every service's free coverage
radius, so travel resolves to **£0 with no warnings**. (Events created through
the UI rely on network geocoding; in an offline environment they have no
coordinates and you'll see a benign "Travel could not be calculated" warning —
the subtotal is unaffected.)

---

## Expected quote outputs

Add a service to a demo event (Browse → service → *Add to event* → choose the
matching event), or hit `/event/quote-preview/{serviceId}/{eventId}` while
logged in. Event checkout uses a **15% deposit**.

### Customer One — "QA Summer Wedding" (80 guests)

| Service | Quote line | Total | Deposit (15%) |
| --- | --- | ---: | ---: |
| QA Catering Co — Buffet & Grazing | Guest-based service (80 guests × £6.00) | **£480.00** | £72.00 |
| QA Snapshot — Photo Booth Hire | Duration (3 hour(s)) | **£250.00** | £37.50 |
| QA Grand Marquees — Marquee Packages | Package: Bronze | **£750.00** | £112.50 |
| QA Comfort Hire — Event Chair Hire | …Event Chair Hire (50 chairs × £4.00) | **£200.00** | £30.00 |

All four return `warnings: []`, `errors: []`, `distance_km: 0`.

### Customer Two — "QA Birthday Party" (120 guests)

| Service | Quote line | Total | Deposit (15%) |
| --- | --- | ---: | ---: |
| QA Catering Co — Buffet & Grazing | Guest-based service (120 guests × £6.00) | **£720.00** | £108.00 |
| QA Snapshot — Photo Booth Hire | Duration (3 hour(s)) | **£250.00** | £37.50 |
| QA Grand Marquees — Marquee Packages | Package: Bronze | **£750.00** | £112.50 |
| QA Comfort Hire — Event Chair Hire | …Event Chair Hire (50 chairs × £4.00) | **£200.00** | £30.00 |

> Only the guest-based total changes with guest count; the duration, package
> and quantity services quote the same regardless of the event.

**Note on baskets:** a single event allows only one service per vendor.
`vendor1` owns the catering + photo-booth services and `vendor2` owns the
marquee + chair services, so to basket all four you must remove and re-add, or
use two events.

---

## Reseeding safely

The seeder cleans up before inserting, keyed on the four seeded email
addresses, in foreign-key-safe order:

1. `event_basket_items` and `events` for the seeded customers
2. all child pricing / image / event-type / coverage / extras rows for the
   seeded vendors' services
3. the seeded `services`
4. the seeded `users`

So you can simply re-run it at any time:

```bash
php spark db:seed QASeeder
```

This **only** touches rows belonging to the four seeded accounts — other users,
services and events are left untouched.

### Prerequisites

Import the base schema first (see `README.md`), then run the seeder. The
service images referenced by the seeder already ship in
`public/uploads/services/`, so no extra asset setup is required.
