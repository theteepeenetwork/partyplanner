# Event Services Marketplace

## Overview

This project is a **UK-based event services marketplace** designed to allow customers to **plan and book multiple event services in one place**, with the option for **a single consolidated payment or flexible payment structure**.

The platform focuses on **clarity, fairness, and scalability**, offering a structured alternative to existing event-hire platforms. It is designed to support both **local vendors** and **national-level growth**, without becoming pay-to-win or overly complex.

---

## Core Value Proposition

- One platform for multiple event services  
- Clear, structured pricing  
- Transparent coverage rules  
- Fair vendor exposure  
- Reduced friction for customers planning events  

---

## Supported Event Types

The platform supports a wide range of events, including:

- Weddings  
- Birthdays  
- Christenings  
- Corporate events  
- Conferences  
- Summer fairs  
- Private parties  
- Community and public events  

**Note:**  
Event type selection happens during **event creation**, not on public vendor listings.

---

## Services & Categories

### Supported Service Types

- Food services (e.g. burger vans, catering)
- Photography (photographers, videographers, photobooths)
- Transport
- Makeup and beauty
- Entertainment
- Stationery
- Gifts
- LED dance floors
- Large illuminated lettering
- Chair covers
- Amusement rides

### Category Rules

- Categories are **centrally managed**
- Vendors **cannot create their own categories**
- Categories are primarily used for:
  - Vendor onboarding
  - Search filtering
  - Data consistency

---

## Vendor & Service Model

### Required Service Fields

Each service listing must include:

- Title  
- Subtitle  
- Description  
- Images  
- Category (from predefined list)  
- Base price  
- Optional extras  
- Location  
- Coverage area  
- Cancellation policy  

---

## Pricing Models

Each service must choose **one** pricing structure.

### 1. Guest-Based Pricing

- Uses **fixed numeric guest ranges**
- No vague terms such as “up to” or “more than”
- Designed to be **algorithm-friendly**
- Typically used for catering and food services

### 2. Duration-Based Pricing

- Custom hourly or daily rates
- Flexible time blocks
- Common for DJs, photobooths, and equipment hire

Some services do not require guest-based pricing at all.

---

## Packages

Vendors can optionally define tiered packages:

- Standard  
- Premium  
- Deluxe  

Each package may vary by:
- Price
- Duration
- Inclusions

---

## Coverage & Location Logic

- Vendors define:
  - Base location
  - Coverage radius
  - Optional mileage charges
- Vendors outside a customer’s coverage area:
  - Are excluded from search results
  - May still be accessible via direct link
  - Display a warning that booking may be declined
- Coverage details are **not displayed** on the public service page

---

## Search & Discovery

Search results are filtered by:

- Service category
- Coverage area
- Event requirements

Premium vendors receive **enhanced visibility**, but not guaranteed dominance.

---

## Monetisation Model

The platform uses a **hybrid revenue model**:

- Commission per booking
- Booking fees
- Optional vendor subscriptions

### Premium Features

Premium vendors may receive:

- Improved search ranking
- Featured placements
- Inclusion in Instagram advertising
- Platform-led marketing promotions

### Fair Exposure System

- Featured listings use an **automatic rotation system**
- Prevents repeated promotion of the same vendors
- Ensures equitable visibility for premium subscribers

---

## UI / UX Design Principles

- Card-based layout
- No visible card borders
- Service details displayed **below** customisation options
- Placeholder area for reviews
- Clear visual separation between form sections
- Context-specific service creation flows:
  - Public
  - Private
  - Corporate

---

## Technical Stack

- Backend: **PHP (CodeIgniter 4)**
- Frontend: HTML, CSS, JavaScript
- Database-driven service listings

### Service Creation Architecture

Service creation uses imported PHP views:

- `service_create_public.php`
- `service_create_private.php`
- `service_create_corporate.php`

This approach is intentionally retained for clarity and separation of logic.

---

## Business Constraints

- Development is done evenings and weekends
- Target launch: **February 2025**
- Designed to scale nationally while supporting local vendors
- Prioritises consistency and clarity over unrestricted vendor freedom

---

## Guiding Principles

- Reduce cognitive load for customers
- Keep pricing structured and machine-readable
- Prevent ambiguity and misuse
- Ensure fair vendor exposure
- Build trust through transparency
- Design for long-term scalability

---

## Status

Service onboarding is largely complete.  
Current focus is on **vendor acquisition**, **UI refinement**, and **pre-launch readiness**.


## Core USP: Automated Event Quotes

The primary aim of this platform is to **automate event quoting**.

Instead of manual enquiries, emails, and back-and-forth conversations, the website generates **structured, instant quotes** based on vendor-defined rules. Vendors can then **accept or decline events with minimal time investment**, without needing to repeatedly price custom requests.

### How It Works

#### For Customers
- Customers create an event and select the services they need
- Pricing is calculated automatically using:
  - Guest numbers
  - Duration
  - Packages
  - Optional extras
  - Location and coverage rules
- Customers can:
  - Build a full package of services
  - See clear, upfront pricing
  - Pay in **one single payment** or via **monthly instalments**

This removes uncertainty, delays, and the need to chase multiple vendors.

#### For Vendors
- Vendors define their pricing once using structured rules
- Quotes are generated automatically when a customer builds an event
- Vendors receive a complete event request with:
  - Clear pricing
  - Event details
  - Coverage validation already applied
- Vendors simply **accept or decline** the booking

This avoids:
- Time-consuming enquiries
- Repeated custom quotes
- Non-viable leads outside coverage or budget

---

## Why This Matters

Traditional event platforms focus on listings and enquiries.
This platform focuses on **decision efficiency**.

- Customers get instant clarity and fewer surprises
- Vendors only engage with realistic, fully-priced requests
- The platform scales without increasing admin burden

Automated quotes are the foundation that enables:
- Fair vendor exposure
- Algorithm-friendly pricing
- Consolidated payments
- Reduced operational friction on both sides
