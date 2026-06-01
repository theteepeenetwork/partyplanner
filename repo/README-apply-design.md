# Applying the new brand to the Create-a-Service flow

This rebrands the existing **Create Service** steps (1–6 + review) to the
forest-green / terracotta system from the prototype — **without touching any
PHP markup or JavaScript**. It's a pure stylesheet swap.

## What to drop in

Replace these two files in your repo with the versions in this folder:

| This folder | Copy to (repo path) |
|---|---|
| `public/assets/css/service-form.css` | `public/assets/css/service-form.css` |
| `app/Views/service_create/css.php`   | `app/Views/service_create/css.php`   |

That's it. Both files are already loaded by every step view
(`<?= $this->include('service_create/css.php') ?>` and the
`<link … service-form.css>` tag), so the look changes everywhere at once.

## Why two files

The step views load these two stylesheets in different orders, so **both**
carry the brand palette to stay consistent regardless of order. `service-form.css`
does the heavy lifting and uses a few `!important` rules to win over Bootstrap 5;
`css.php` keeps the modal / tag / tooltip structural rules your JS relies on,
recoloured to match.

## What changes (visually)

- **Sections → cards**: white, rounded, soft shadow on the cream page.
- **Headings**: Fraunces display face in deep green, with a short terracotta accent bar (no more blue underline).
- **Inputs / selects / textareas**: rounded, warm borders, green focus ring.
- **`input-group` prefixes** (From / to / GBP / %): cream "affix caps".
- **Tags**: green pills.
- **Checkboxes / radios**: green when checked (incl. the BS4 `custom-control` radios on step 2).
- **Buttons**: primary = green; "Add another…" = dashed ghost; remove/danger = subtle until hover.
- **Info icons, alerts, popovers, image previews**: all rebranded.

Fonts (DM Sans + Fraunces) are already loaded by `header.php` on non-home pages,
so nothing new is requested.

## Not included (needs markup, ask if you want it)

The prototype's **left step-rail / progress indicator** and the **sticky footer
nav** aren't here — those need markup added to each step view (and a shared
layout). The CSS above is the safe, zero-risk first pass. Happy to do the rail
as a follow-up if you want it.

## Verify

Open `preview/step1.html` and `preview/step3.html` (in the project) — those use
the **real view markup** (PHP stripped, sample data filled) with these exact
stylesheets, so they show what your live pages will look like.
