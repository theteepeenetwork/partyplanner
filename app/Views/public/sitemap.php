<?php
$pageTitle       = 'Site map — Partysmith';
$metaDescription = 'Every page on Partysmith in one place — public and marketing, accounts, customer planning, supplier tools and admin.';

/*
 * Real-route map of the prototype "Partysmith Site Map" hub. Each card points at a
 * live route in the app (not the prototype HTML filenames). Pages that need a record
 * id in the prototype (a specific supplier profile, basket or service edit) point at
 * their generic index instead, so every link here is reachable without context.
 */
$groups = [
    [
        'eyebrow' => 'Public &amp; marketing',
        'title'   => 'For everyone',
        'cards'   => [
            ['Public', 'Homepage', '/', 'Hero search, categories, how it works, featured suppliers and FAQ.'],
            ['Public', 'Browse suppliers', '/browse-services', 'Category results with a filter rail and supplier cards.'],
            ['Public', 'How it works', '/how-it-works', 'The 3-step process, payment protection and vetting.'],
            ['Public', 'For vendors', '/vendor-info', 'Supplier value proposition and pricing.'],
            ['Public', 'About us', '/about', 'Who we are and how we vet the marketplace.'],
            ['Public', 'Help &amp; FAQ', '/faq', 'Grouped help centre for hosts and suppliers.'],
            ['Public', 'Contact', '/contact', 'Contact form and support details.'],
        ],
    ],
    [
        'eyebrow' => 'Accounts',
        'title'   => 'Sign in &amp; sign up',
        'cards'   => [
            ['Auth', 'Sign in', '/login', 'Sign in with your email and password.'],
            ['Auth', 'Create account', '/register', 'Customer registration.'],
            ['Auth', 'Become a vendor', '/register/vendor', 'Supplier application and onboarding.'],
        ],
    ],
    [
        'eyebrow' => 'Logged in &middot; customer',
        'title'   => 'Hosting an event',
        'cards'   => [
            ['Customer', 'Plan an event', '/event/create', 'Guided brief: occasion, details, suppliers, review.'],
            ['Customer', 'My events', '/profile/events', 'Events, baskets, quotes to compare and your planning checklist.'],
            ['Customer', 'My bookings', '/profile/my-bookings', 'Confirmed bookings and protected payments.'],
            ['Customer', 'Account settings', '/profile', 'Profile, security, payments and notifications.'],
        ],
    ],
    [
        'eyebrow' => 'Logged in &middot; supplier &amp; admin',
        'title'   => 'Running the marketplace',
        'cards'   => [
            ['Vendor', 'Supplier dashboard', '/profile/services', 'Enquiries, services, bookings and payouts.'],
            ['Vendor', 'Bookings', '/profile/bookings', 'Incoming bookings and your calendar.'],
            ['Vendor', 'Add a service', '/service/list', 'Create a new listing with inclusions and portfolio.'],
            ['Admin', 'Admin overview', '/admin', 'Approvals, suppliers and bookings at HQ.'],
        ],
    ],
];
?>
<?= $this->include('header') ?>

<style>
/* ============================================================
   SITE MAP HUB — recreates the prototype "Partysmith Site Map"
   on the live deep-green / gold tokens already in style.css.
   Scoped under .sitemap-hub so nothing leaks into other views.
   ============================================================ */
.sitemap-hub { background: var(--paper); }
.sitemap-hub .sm-wrap { max-width: 1200px; margin: 0 auto; padding-inline: clamp(24px, 5vw, 64px); width: 100%; }

/* deep-green intro band with a soft gold glow */
.sitemap-hub .sm-head { position: relative; overflow: hidden; background: var(--green-deep); color: var(--cream);
    padding: clamp(46px, 6vw, 86px) 0 clamp(46px, 6vw, 82px); }
.sitemap-hub .sm-head::before { content: ""; position: absolute; right: -160px; top: -160px; width: 460px; height: 460px;
    border-radius: 50%; background: radial-gradient(circle, rgba(226,184,96,0.16) 0%, rgba(226,184,96,0) 70%); }
.sitemap-hub .sm-head .sm-wrap { position: relative; z-index: 1; }
.sitemap-hub .sm-head h1 { font-family: var(--serif); font-size: clamp(30px, 4vw, 48px); line-height: 1.06;
    letter-spacing: -0.018em; font-weight: 500; margin: 0 0 12px; color: var(--cream); text-wrap: balance; }
.sitemap-hub .sm-lead { font-size: clamp(15px, 1.6vw, 18px); color: rgba(251,248,241,0.82); margin: 0;
    max-width: 60ch; line-height: 1.55; }

/* shared eyebrow */
.sitemap-hub .sm-eyebrow { font-size: 12.5px; font-weight: 700; letter-spacing: 0.2em; text-transform: uppercase;
    color: var(--gold); margin: 0 0 14px; display: inline-flex; align-items: center; gap: 10px; }
.sitemap-hub .sm-eyebrow::before { content: ""; width: 24px; height: 1.5px; background: var(--gold); display: inline-block; }
.sitemap-hub .sm-head .sm-eyebrow { color: var(--gold-bright); }
.sitemap-hub .sm-head .sm-eyebrow::before { background: var(--gold-bright); }

/* body sections */
.sitemap-hub .sm-section { padding: clamp(40px, 6vw, 72px) 0 0; }
.sitemap-hub .sm-section:last-child { padding-bottom: clamp(56px, 8vw, 104px); }
.sitemap-hub .sm-section-head { margin-bottom: clamp(22px, 3vw, 32px); }
.sitemap-hub .sm-section-head h2 { font-family: var(--serif); font-weight: 500; font-size: clamp(24px, 3vw, 33px);
    line-height: 1.1; letter-spacing: -0.015em; margin: 0; color: var(--ink); }

/* card grid */
.sitemap-hub .sm-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 18px; }
.sitemap-hub .sm-card { display: block; text-decoration: none; color: inherit; background: #fff;
    border: 1px solid var(--line-soft); border-radius: 18px; padding: 22px 24px; box-shadow: var(--shadow-soft);
    transition: transform .2s ease, box-shadow .2s ease, border-color .2s ease; }
.sitemap-hub .sm-card:hover { transform: translateY(-4px); box-shadow: var(--shadow-lift); border-color: var(--line); }
.sitemap-hub .sm-card .sm-role { font-size: 11px; font-weight: 700; letter-spacing: 0.1em; text-transform: uppercase;
    color: var(--gold); }
.sitemap-hub .sm-card h3 { font-family: var(--serif); font-weight: 500; font-size: 21px; margin: 7px 0 6px;
    letter-spacing: -0.01em; color: var(--ink); }
.sitemap-hub .sm-card p { margin: 0; font-size: 13.5px; color: var(--ink-soft); line-height: 1.5; }

@media (max-width: 1000px) { .sitemap-hub .sm-grid { grid-template-columns: 1fr 1fr; } }
@media (max-width: 640px)  { .sitemap-hub .sm-grid { grid-template-columns: 1fr; } }
</style>

<main class="sitemap-hub" aria-label="Site map">
    <section class="sm-head">
        <div class="sm-wrap">
            <p class="sm-eyebrow">Site map</p>
            <h1>The Partysmith brand, across the whole site</h1>
            <p class="sm-lead">Every screen in the marketplace — public, customer, supplier and admin — built on one shared design system. Choose any page to open it.</p>
        </div>
    </section>

    <?php foreach ($groups as $group): ?>
        <section class="sm-section">
            <div class="sm-wrap">
                <div class="sm-section-head">
                    <p class="sm-eyebrow"><?= $group['eyebrow'] ?></p>
                    <h2><?= $group['title'] ?></h2>
                </div>
                <div class="sm-grid">
                    <?php foreach ($group['cards'] as [$cardRole, $name, $href, $desc]): ?>
                        <a class="sm-card" href="<?= esc($href, 'attr') ?>">
                            <div class="sm-role"><?= esc($cardRole) ?></div>
                            <h3><?= $name ?></h3>
                            <p><?= $desc ?></p>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
    <?php endforeach; ?>
</main>

<?= $this->include('footer') ?>
