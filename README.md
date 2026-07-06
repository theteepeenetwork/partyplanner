# PartyPlanner

A UK event services marketplace that enables customers to create events, discover suppliers, receive automated quotes, and manage bookings in one place.

Unlike traditional event directories that focus on enquiries, PartyPlanner focuses on structured pricing and automated quote generation. Vendors define pricing rules once, allowing customers to receive instant pricing and build complete event packages with minimal back-and-forth communication.

---

## Key Features

### Customers

* Create and manage events
* Browse and search suppliers
* Build packages containing multiple services
* Receive automated pricing
* Request and manage bookings
* Communicate with suppliers
* Manage payments

### Vendors

* Create service listings
* Configure structured pricing models
* Define coverage areas
* Manage bookings and enquiries
* Configure automated quote handling
* View booking activity

### Administrators

* Manage users
* Manage services
* Review bookings
* Monitor platform activity
* Manage marketplace content

---

## Technology Stack

* PHP 8.3+
* CodeIgniter 4
* MariaDB / MySQL
* Bootstrap
* JavaScript
* Stripe

---

## Local Development

### Install Dependencies

```bash
composer install
```

### Database

Create a database named:

```sql
event_marketplace
```

Import database files in this order (the base schema must come first so the
update/migration scripts have tables to alter):

1. event_marketplace.sql
2. database_update.sql
3. database_quote_automation.sql
4. database_fulfillment_extras.sql (optional)
5. database_quantity_pricing.sql (optional)
6. database_service_requirements.sql

Import each file with a UTF-8 client so accented characters are preserved, e.g.:

```bash
mysql --default-character-set=utf8mb4 event_marketplace < event_marketplace.sql
```

### Environment

```bash
cp env.example .env
```

Update database credentials as required.

### Start Development Server

```bash
CI_ENVIRONMENT=cloud php spark serve --host 127.0.0.1 --port 8888
```

Application URL:

```text
http://127.0.0.1:8888
```

---

## Testing

Run all tests:

```bash
php vendor/bin/phpunit --testdox
```

Run a single test:

```bash
php vendor/bin/phpunit tests/unit/SomeTest.php
```

Quick syntax validation:

```bash
find app -name "*.php" -exec php -l {} \;
```

---

## Core Business Concepts

### Automated Quotes

Suppliers define pricing rules once.

The platform automatically calculates pricing using:

* Guest numbers
* Duration
* Packages
* Optional extras
* Quantity pricing
* Coverage rules

This reduces manual quoting and allows suppliers to quickly accept or decline opportunities.

### Pricing Models

Each service uses one pricing structure:

* Guest-Based Pricing
* Duration-Based Pricing
* Package-Based Pricing
* Quantity-Based Pricing
* Public Event Pricing
* Private Event Pricing

### Coverage Areas

Suppliers define:

* Base location
* Coverage radius
* Travel charges

Coverage rules influence service visibility and booking eligibility.

---

## Architecture

### Key Directories

```text
app/
├── Commands/
├── Config/
├── Controllers/
│   └── Admin/
├── Libraries/
├── Models/
├── Views/
├── Filters/
└── Helpers/
```

### Important Components

#### Libraries

Core business logic is contained within:

* EventQuoteBuilder
* VendorQuoteAutomation
* QuoteNotifier
* QuoteAnalyticsRecorder

#### Service Creation

Service creation intentionally uses separate views:

* service_create_public.php
* service_create_private.php
* service_create_corporate.php

This separation is deliberate and should not be merged without a clear architectural reason.

---

## Scheduled Commands

Quote reminders:

```bash
php spark quote:remind-pending
```

Expire stale quotes:

```bash
php spark quote:expire-stale
```

---

## Payments

Stripe integration is supported but optional.

The platform should remain fully usable without Stripe credentials configured.

Current payment behaviour:

* Event checkout: 10% deposit, defined in `App\Libraries\DepositCalculator`

---

## Documentation

Additional documentation can be found in:

* docs/product.md
* CLAUDE.md

---

## Licence

Private project. All rights reserved.
