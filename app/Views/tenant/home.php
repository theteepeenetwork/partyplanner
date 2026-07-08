<?php
/**
 * Landing mode B — multi-service vendor (frames 1d/1e).
 *
 * Hero (cover image + built-in bottom scrim, so legibility never depends on the
 * uploaded photo) carries the vendor name, tagline, a trust meta row and the
 * primary "Get an instant quote" CTA. Trust pills sit directly under the hero,
 * then an on-page date field qualifies the lead, the "What we offer" cards, a
 * reviews section and a closing custom-package CTA.
 *
 * The quote action uses ONE label — "Get an instant quote" — on the hero, each
 * card, the date field and the sticky header. All routes hand off to the
 * existing quote flow: the service page (`/service/{id}?date=`) already accepts
 * and validates a pre-filled date.
 */
$bn      = $site['business_name'] ?? 'Storefront';
$rating  = $trust['rating'] ?? null;
$bookCnt = (int) ($trust['bookings'] ?? 0);
$tagline = trim((string) ($aboutLine ?? ''));
$coverage = trim((string) ($coverage ?? ''));
$phone    = trim((string) ($site['phone'] ?? ''));
$phoneHref = $phone !== '' ? 'tel:' . preg_replace('/[^0-9+]/', '', $phone) : '';
$today    = date('Y-m-d');

// Opt the shared header into its compact, scroll-revealed quote CTA + rating.
// Included views only see the shared view data, so merge it in explicitly.
$this->setData(['stickyQuote' => ['href' => '#sf-quote', 'rating' => $rating, 'bookings' => $bookCnt]], 'raw');
?>
<?= $this->include('tenant_header') ?>
<?php // sf_rating_line() is defined by the header include above. ?>
<?php $ratingLine = sf_rating_line($rating, $bookCnt); ?>

<header class="sf-hero sf-hero-lander<?= $heroImage === '' ? ' noimg' : '' ?>">
    <?php if ($heroImage !== ''): ?>
        <img src="<?= esc($heroImage, 'attr') ?>" alt="<?= esc($bn, 'attr') ?> — event services">
    <?php endif; ?>
    <div class="sf-hero-scrim">
        <div class="sf-shell" style="width: 100%;">
            <h1 class="sf-hero-h"><?= esc($bn) ?></h1>
            <?php if ($tagline !== ''): ?>
                <p class="sf-hero-tagline"><?= esc($tagline) ?></p>
            <?php endif; ?>
            <div class="sf-hero-meta">
                <?php if ($ratingLine !== ''): ?>
                    <span class="sf-rating-chip"><i class="fas fa-star" aria-hidden="true"></i><?= esc($ratingLine) ?></span>
                <?php endif; ?>
                <?php if ($coverage !== ''): ?>
                    <span class="sf-hero-fact"><i class="fas fa-location-dot" aria-hidden="true"></i><?= esc($coverage) ?></span>
                <?php endif; ?>
                <span class="sf-hero-fact"><i class="fas fa-bolt" aria-hidden="true"></i>Instant quotes</span>
            </div>
            <div class="sf-hero-cta">
                <a class="sf-hero-btn" href="#sf-quote" data-sf-scroll>Get an instant quote</a>
                <?php if ($phone !== ''): ?>
                    <a class="sf-hero-btn ghost" href="<?= esc($phoneHref, 'attr') ?>">
                        <i class="fas fa-phone" aria-hidden="true"></i><?= esc($phone) ?>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</header>

<div class="sf-shell">
    <?php if (session()->getFlashdata('error')): ?>
        <div class="sf-flash error" role="alert"><?= esc(session()->getFlashdata('error')) ?></div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('info')): ?>
        <div class="sf-flash info"><?= esc(session()->getFlashdata('info')) ?></div>
    <?php endif; ?>

    <div class="sf-chips">
        <span class="sf-chip"><i class="fas fa-shield-halved" aria-hidden="true"></i><?= (int) \App\Libraries\DepositCalculator::percentDisplay() ?>% deposit holds your date</span>
        <span class="sf-chip"><i class="fas fa-lock" aria-hidden="true"></i>Secure card payment</span>
        <span class="sf-chip"><i class="fas fa-rotate-left" aria-hidden="true"></i>Free 14-day cancellation</span>
    </div>

    <section class="sf-sec">
        <h2 class="sf-sec-h">What we offer</h2>
        <p class="sf-sec-sub">Pick a service — you'll get an instant quote for your date.</p>

        <?php // On-page date capture: qualifies the lead, then threads the date onto
              // every service link so the quote page opens pre-filled. ?>
        <form class="sf-datebar" id="sf-quote" novalidate>
            <div class="sf-field" style="margin: 0;">
                <label class="sf-sr-only" for="sf-date">Your event date</label>
                <input class="sf-input" type="date" id="sf-date" name="date" min="<?= esc($today, 'attr') ?>" placeholder="Your event date">
            </div>
            <button class="sf-btn" type="submit">Get an instant quote</button>
        </form>
        <p class="sf-datebar-err" id="sf-date-err" role="alert" hidden>Pick today or a date in the future.</p>

        <div class="sf-svc-list">
            <?php foreach ($services as $service):
                $img  = ! empty($service['images']) ? '/' . ltrim((string) ($service['images'][0]['thumbnail_path'] ?? $service['images'][0]['image_path'] ?? ''), '/') : '';
                $from = $service['from'] ?? ['amount' => 0, 'per' => ''];
                $desc = trim((string) ($service['short_description'] ?? ''));
                $href = '/service/' . (int) $service['id'];
            ?>
                <a class="sf-svc-card" href="<?= esc($href, 'attr') ?>" data-sf-svc data-href="<?= esc($href, 'attr') ?>">
                    <?php if (! empty($mostBookedId) && (int) $service['id'] === (int) $mostBookedId && count($services) > 1): ?>
                        <span class="sf-badge-most">Most booked</span>
                    <?php endif; ?>
                    <span class="img">
                        <?php // Decorative: the card is one link whose text already names the
                              // service, so alt here would double-announce (WCAG H67). ?>
                        <?php if ($img !== ''): ?>
                            <img src="<?= esc($img, 'attr') ?>" alt="" loading="lazy">
                        <?php else: ?>
                            <span style="display: block; width: 100%; height: 100%; min-height: 96px; background: var(--sf-tint-12);"></span>
                        <?php endif; ?>
                    </span>
                    <span class="body">
                        <?php if (! empty($service['category_name'])): ?>
                            <span class="sf-eyebrow"><?= esc(trim(explode('·', $service['category_name'])[0])) ?></span>
                        <?php endif; ?>
                        <h3 class="t"><?= esc($service['title']) ?></h3>
                        <?php if ($desc !== ''): ?>
                            <span class="desc"><?= esc($desc) ?></span>
                        <?php endif; ?>
                        <span class="foot">
                            <?= $this->include('tenant/_price', ['from' => $from]) ?>
                            <span class="sf-svc-cta">Get an instant quote <span aria-hidden="true">→</span></span>
                        </span>
                    </span>
                </a>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="sf-sec">
        <h2 class="sf-sec-h">Reviews</h2>
        <?php if (! empty($reviews)): ?>
            <?php foreach ($reviews as $r): ?>
                <div class="sf-card sf-review" style="margin-bottom: 10px;">
                    <span class="sf-stars"><?php for ($i = 1; $i <= 5; $i++): ?><i class="fas fa-star<?= $i > (int) $r['rating'] ? ' off' : '' ?>" aria-hidden="true"></i><?php endfor; ?></span>
                    <span class="ctx"><?= ! empty($r['created_at']) ? esc(date('M Y', strtotime($r['created_at']))) : '' ?></span>
                    <blockquote>&ldquo;<?= esc($r['comment'] ?? $r['title'] ?? '') ?>&rdquo; — <?= esc($r['reviewer'] ?? 'Verified customer') ?></blockquote>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="sf-noreviews">No written reviews yet. <b>Star ratings come only from customers who have booked and had their event</b> — never seeded.</p>
        <?php endif; ?>
    </section>

    <section class="sf-sec">
        <div class="sf-closing">
            <div>
                <h2 class="sf-sec-h">Can't see what you need?</h2>
                <p class="sf-sec-sub" style="margin: 0;">Ask about a custom package for your event.</p>
            </div>
            <a class="sf-btn-outline" href="<?= $phone !== '' ? esc($phoneHref, 'attr') : '#sf-quote' ?>"<?= $phone === '' ? ' data-sf-scroll' : '' ?>>Send an enquiry</a>
        </div>
    </section>
</div>

<script>
/* Storefront home: (1) date field validates client-side and threads the chosen
   date onto every service link (the quote page accepts ?date=), (2) smooth
   in-page scroll for the quote CTAs, (3) reveal the sticky-header CTA once the
   hero is scrolled past. Vanilla JS, no libraries. */
(function () {
    var dateInput = document.getElementById('sf-date');
    var dateForm  = document.getElementById('sf-quote');
    var dateErr   = document.getElementById('sf-date-err');
    var cards     = Array.prototype.slice.call(document.querySelectorAll('[data-sf-svc]'));
    var today     = <?= json_encode($today) ?>;

    function isValidDate(v) { return !!v && v >= today; }

    function applyDate(v) {
        cards.forEach(function (card) {
            var base = card.getAttribute('data-href');
            card.setAttribute('href', isValidDate(v) ? base + '?date=' + encodeURIComponent(v) : base);
        });
    }

    if (dateInput) {
        dateInput.addEventListener('change', function () {
            if (dateInput.value && !isValidDate(dateInput.value)) {
                dateInput.classList.add('is-invalid');
                if (dateErr) dateErr.hidden = false;
            } else {
                dateInput.classList.remove('is-invalid');
                if (dateErr) dateErr.hidden = true;
                applyDate(dateInput.value);
            }
        });
    }

    if (dateForm) {
        dateForm.addEventListener('submit', function (e) {
            e.preventDefault();
            var v = dateInput ? dateInput.value : '';
            if (v && !isValidDate(v)) {
                dateInput.classList.add('is-invalid');
                if (dateErr) dateErr.hidden = false;
                dateInput.focus();
                return;
            }
            applyDate(v);
            // No single service to quote here — send the qualified lead to the
            // service cards to choose, with their date already carried through.
            var list = document.querySelector('.sf-svc-list');
            if (list) {
                list.scrollIntoView({ behavior: 'smooth', block: 'start' });
                var first = list.querySelector('[data-sf-svc]');
                if (first) first.focus({ preventScroll: true });
            }
        });
    }

    // Smooth-scroll the hero / closing CTAs to the date field and focus it.
    document.querySelectorAll('[data-sf-scroll]').forEach(function (link) {
        link.addEventListener('click', function (e) {
            var target = document.getElementById('sf-quote');
            if (!target) return;
            e.preventDefault();
            target.scrollIntoView({ behavior: 'smooth', block: 'center' });
            if (dateInput) dateInput.focus({ preventScroll: true });
        });
    });

    // Reveal the sticky-header compact CTA once the hero leaves the viewport.
    var hero = document.querySelector('.sf-hero-lander');
    if (hero && 'IntersectionObserver' in window) {
        var io = new IntersectionObserver(function (entries) {
            document.body.classList.toggle('sf-scrolled', !entries[0].isIntersecting);
        }, { rootMargin: '-64px 0px 0px 0px', threshold: 0 });
        io.observe(hero);
    }
})();
</script>

<?= $this->include('tenant_footer') ?>
