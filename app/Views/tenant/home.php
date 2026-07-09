<?php
/**
 * Landing mode B — 3D hybrid (booking-first + gallery), themed by the vendor's
 * chosen colour theme (body.sf-theme-*, applied by the header).
 *
 * Booking-first hero with a live instant-quote ESTIMATOR (service + date +
 * guests → ballpark total & 10% deposit) → gallery proof band → trust strip →
 * services grid → reviews. The estimator is a client-side ballpark; "Reserve"
 * and each card hand off to the real quote flow on the service page
 * (/service/{id}?date=&guests=), which produces the exact itemised quote.
 */
$bn        = $site['business_name'] ?? 'Storefront';
$rating    = $trust['rating'] ?? null;
$bookCnt   = (int) ($trust['bookings'] ?? 0);
$coverage  = trim((string) ($coverage ?? ''));
$phone     = trim((string) ($site['phone'] ?? ''));
$phoneHref = $phone !== '' ? 'tel:' . preg_replace('/[^0-9+]/', '', $phone) : '';
$today     = date('Y-m-d');
$ctxDate   = trim((string) ($ctxDate ?? ''));
$ctxTime   = trim((string) ($ctxTime ?? ''));
$depositPct = (int) \App\Libraries\DepositCalculator::percentDisplay();

$perLabel = static fn (string $per): string => match ($per) {
    'guest' => '/guest', 'hour' => '/hr', 'day' => '/day', default => '',
};

// Up to three photos across the vendor's services for the gallery proof band.
$galleryImgs = [];
foreach ($services as $s) {
    foreach ((array) ($s['images'] ?? []) as $img) {
        $p = trim((string) ($img['image_path'] ?? $img['thumbnail_path'] ?? ''));
        if ($p !== '' && count($galleryImgs) < 3) {
            $galleryImgs[] = '/' . ltrim($p, '/');
        }
    }
}

$this->setData(['stickyQuote' => ['href' => '#sf-quote', 'rating' => $rating, 'bookings' => $bookCnt]], 'raw');
?>
<?= $this->include('tenant_header') ?>
<?php $ratingLine = sf_rating_line($rating, $bookCnt); ?>

<section class="sf-lhero">
    <div class="sf-shell sf-lhero-in">
        <div>
            <?php if ($ratingLine !== '' || $coverage !== ''): ?>
                <span class="sf-lhero-pill">
                    <?php if ($ratingLine !== ''): ?><span class="star">★</span><?php endif; ?>
                    <?= $ratingLine !== '' ? esc($ratingLine) : 'Verified vendor' ?><?= $coverage !== '' ? ' · ' . esc($coverage) : '' ?>
                </span>
            <?php endif; ?>
            <h1>Book your event in three clicks.</h1>
            <p class="sf-lhero-sub">Pick a service, choose your date and reserve online with a <?= $depositPct ?>% deposit. No back-and-forth.</p>
            <div class="sf-lhero-btns">
                <a class="sf-lhero-ghost" href="#sf-services" data-sf-scroll-to="sf-services">Browse services</a>
                <?php if ($phone !== ''): ?>
                    <a class="sf-lhero-ghost" href="<?= esc($phoneHref, 'attr') ?>"><i class="fas fa-phone" aria-hidden="true"></i>Call the team</a>
                <?php endif; ?>
            </div>
            <div class="sf-lhero-ticks">
                <span><i class="fas fa-check" aria-hidden="true"></i><?= $depositPct ?>% deposit holds your date</span>
                <span><i class="fas fa-check" aria-hidden="true"></i>Free 14-day cancellation</span>
            </div>
        </div>

        <?php // Client-side estimator → real quote flow on submit. ?>
        <div class="sf-estimator" id="sf-quote">
            <p class="eyebrow">Instant quote</p>
            <label class="sf-est-field">Service
                <select id="est-svc">
                    <?php foreach ($services as $s):
                        $from = $s['from'] ?? ['amount' => 0, 'per' => ''];
                    ?>
                        <option value="<?= (int) $s['id'] ?>" data-amount="<?= esc((string) (float) $from['amount'], 'attr') ?>" data-per="<?= esc($perLabel($from['per'] ?? ''), 'attr') ?>">
                            <?= esc($s['title']) ?><?php if ($from['amount'] > 0): ?> — from £<?= esc(number_format((float) $from['amount'], (float) $from['amount'] == (int) $from['amount'] ? 0 : 2)) ?><?= esc($perLabel($from['per'] ?? '')) ?><?php endif; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>
            <div class="sf-est-row">
                <label class="sf-est-field">Event date
                    <input type="date" id="est-date" min="<?= esc($today, 'attr') ?>" value="<?= esc($ctxDate, 'attr') ?>">
                </label>
                <label class="sf-est-field">Start time <span style="font-weight: 400; text-transform: none;">(optional)</span>
                    <input type="time" id="est-time" step="900" value="<?= esc($ctxTime, 'attr') ?>">
                </label>
            </div>
            <div class="sf-est-total">
                <div>
                    <div class="lbl">From</div>
                    <div class="amt" id="est-total">£0</div>
                </div>
                <div class="dep">Deposit today<b><?= $depositPct ?>%</b></div>
            </div>
            <a class="sf-btn" id="est-reserve" href="#">Reserve your date <i class="fas fa-arrow-right" aria-hidden="true"></i></a>
            <p class="sf-est-note">A "from" guide — you'll get the exact itemised quote on the next step.</p>
        </div>
    </div>
</section>

<div class="sf-shell">
    <?php if (session()->getFlashdata('error')): ?>
        <div class="sf-flash error" role="alert"><?= esc(session()->getFlashdata('error')) ?></div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('info')): ?>
        <div class="sf-flash info"><?= esc(session()->getFlashdata('info')) ?></div>
    <?php endif; ?>

    <div class="sf-chips" style="margin-top: 22px;">
        <span class="sf-chip"><i class="fas fa-shield-halved" aria-hidden="true"></i><?= $depositPct ?>% deposit holds your date</span>
        <span class="sf-chip"><i class="fas fa-lock" aria-hidden="true"></i>Secure card payment</span>
        <span class="sf-chip"><i class="fas fa-rotate-left" aria-hidden="true"></i>Free 14-day cancellation</span>
    </div>

    <?php if ($ctxDate !== ''): ?>
        <p class="sf-datebar-note">Showing availability for <?= esc(date('D j M', strtotime($ctxDate))) ?><?= $ctxTime !== '' ? ' from ' . esc($ctxTime) : '' ?>. Booked services are greyed out.</p>
    <?php endif; ?>
    <?php $ctxQs = $ctxDate === '' ? '' : '?' . http_build_query(array_filter(['date' => $ctxDate, 'time' => $ctxTime])); ?>

    <section class="sf-lsec" id="sf-services">
        <div class="sf-lsec-head">
            <h2>Our services</h2>
        </div>
        <div class="sf-lcards">
            <?php foreach ($services as $service):
                $img  = ! empty($service['images']) ? '/' . ltrim((string) ($service['images'][0]['thumbnail_path'] ?? $service['images'][0]['image_path'] ?? ''), '/') : '';
                $from = $service['from'] ?? ['amount' => 0, 'per' => ''];
                $desc = trim((string) ($service['short_description'] ?? ''));
                $base = '/service/' . (int) $service['id'];
                $href = $base . $ctxQs;
                $unavailable = ($service['available'] ?? null) === false;
                $tag  = $unavailable ? 'div' : 'a';
            ?>
                <<?= $tag ?> class="sf-lcard<?= $unavailable ? ' is-unavailable' : '' ?>"<?= $unavailable ? '' : ' href="' . esc($href, 'attr') . '"' ?> data-sf-svc data-href="<?= esc($base, 'attr') ?>"<?= $unavailable ? ' aria-disabled="true"' : '' ?>>
                    <?php if ($unavailable): ?>
                        <span class="sf-badge-booked">Booked</span>
                    <?php elseif (! empty($mostBookedId) && (int) $service['id'] === (int) $mostBookedId && count($services) > 1): ?>
                        <span class="sf-badge-most">Most booked</span>
                    <?php endif; ?>
                    <span class="img">
                        <?php if ($img !== ''): ?>
                            <img src="<?= esc($img, 'attr') ?>" alt="" loading="lazy">
                        <?php endif; ?>
                    </span>
                    <span class="body">
                        <?php if (! empty($service['category_name'])): ?>
                            <span class="badge"><?= esc(trim(explode('·', $service['category_name'])[0])) ?></span>
                        <?php endif; ?>
                        <h3 class="t"><?= esc($service['title']) ?></h3>
                        <?php if ($desc !== ''): ?>
                            <p class="desc"><?= esc($desc) ?></p>
                        <?php endif; ?>
                        <span class="foot">
                            <?php if ($from['amount'] > 0): ?>
                                <span class="p">from <b>£<?= esc(number_format((float) $from['amount'], (float) $from['amount'] == (int) $from['amount'] ? 0 : 2)) ?></b> <?= esc($perLabel($from['per'] ?? '')) ?></span>
                            <?php else: ?>
                                <span class="p">Priced on request</span>
                            <?php endif; ?>
                            <span class="cta"><?= $unavailable ? 'Booked' : 'Book' ?> <span aria-hidden="true">→</span></span>
                        </span>
                    </span>
                </<?= $tag ?>>
            <?php endforeach; ?>
        </div>
    </section>

    <?php if ($galleryImgs !== []): ?>
        <section class="sf-gallery3 sf-lsec">
            <div class="sf-gallery3-head">
                <h2>Recent events</h2>
            </div>
            <div class="sf-gallery3-grid">
                <?php foreach ($galleryImgs as $g): ?>
                    <div class="cell"><img src="<?= esc($g, 'attr') ?>" alt="" loading="lazy"></div>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>

    <section class="sf-lsec">
        <div class="sf-lsec-head"><h2>Reviews</h2></div>
        <?php if (! empty($reviews)): ?>
            <div class="sf-lreviews-grid">
                <?php foreach ($reviews as $r):
                    $who = trim((string) ($r['reviewer'] ?? 'Verified customer'));
                    $ini = '';
                    foreach (preg_split('/\s+/', $who) as $w) {
                        if ($w !== '' && strlen($ini) < 2) { $ini .= strtoupper(mb_substr($w, 0, 1)); }
                    }
                ?>
                    <div class="sf-lreview">
                        <div class="sf-lreview-head">
                            <span class="avatar"><?= esc($ini !== '' ? $ini : 'V') ?></span>
                            <div>
                                <div class="who"><?= esc($who) ?></div>
                                <span class="stars" aria-label="<?= (int) $r['rating'] ?> out of 5"><?= str_repeat('★', max(0, min(5, (int) $r['rating']))) ?></span>
                            </div>
                            <?php if (! empty($r['created_at'])): ?><span class="date"><?= esc(date('M Y', strtotime($r['created_at']))) ?></span><?php endif; ?>
                        </div>
                        <p>&ldquo;<?= esc($r['comment'] ?? $r['title'] ?? '') ?>&rdquo;</p>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="sf-noreviews">No written reviews yet. <b>Star ratings come only from customers who have booked and had their event</b> — never seeded.</p>
        <?php endif; ?>
    </section>
</div>

<script>
/* 3D hybrid landing: (1) live estimator (service base + per-guest × guests →
   total & 10% deposit) whose "Reserve" hands off to the real quote flow on the
   service page; (2) smooth-scroll helpers; (3) reveal the sticky-header CTA. */
(function () {
    var svc     = document.getElementById('est-svc');
    var dateEl  = document.getElementById('est-date');
    var timeEl  = document.getElementById('est-time');
    var totalEl = document.getElementById('est-total');
    var reserve = document.getElementById('est-reserve');
    var cards   = Array.prototype.slice.call(document.querySelectorAll('[data-sf-svc]'));
    var fmt = function (n) { return '£' + Math.round(n).toLocaleString('en-GB'); };

    // The date + start time entered in the estimator are event context, not
    // tied to one service — thread them onto EVERY booking link (the Reserve
    // button AND each service card) so whichever route the customer takes, the
    // service page pre-fills its booking form with what they typed.
    function ctxQs() {
        var qs = [];
        if (dateEl && dateEl.value) qs.push('date=' + encodeURIComponent(dateEl.value));
        if (timeEl && timeEl.value) qs.push('time=' + encodeURIComponent(timeEl.value));
        return qs.length ? '?' + qs.join('&') : '';
    }

    function update() {
        if (!svc) return;
        var opt = svc.options[svc.selectedIndex];
        var amount = parseFloat(opt.getAttribute('data-amount')) || 0;
        var per = opt.getAttribute('data-per') || '';
        if (totalEl) totalEl.textContent = amount > 0 ? (fmt(amount) + (per ? ' ' + per : '')) : 'On request';
        var q = ctxQs();
        if (reserve) reserve.setAttribute('href', '/service/' + opt.value + q);
        cards.forEach(function (card) {
            var base = card.getAttribute('data-href');
            if (base) card.setAttribute('href', base + q);
        });
    }
    [svc, dateEl, timeEl].forEach(function (el) {
        if (el) { el.addEventListener('input', update); el.addEventListener('change', update); }
    });
    update();

    // Smooth-scroll "Browse services" / any [data-sf-scroll-to].
    document.querySelectorAll('[data-sf-scroll-to]').forEach(function (link) {
        link.addEventListener('click', function (e) {
            var t = document.getElementById(link.getAttribute('data-sf-scroll-to'));
            if (t) { e.preventDefault(); t.scrollIntoView({ behavior: 'smooth', block: 'start' }); }
        });
    });

    // Reveal the sticky-header compact CTA once the hero leaves the viewport.
    var hero = document.querySelector('.sf-lhero');
    if (hero && 'IntersectionObserver' in window) {
        var io = new IntersectionObserver(function (entries) {
            document.body.classList.toggle('sf-scrolled', !entries[0].isIntersecting);
        }, { rootMargin: '-64px 0px 0px 0px', threshold: 0 });
        io.observe(hero);
    }
})();
</script>

<?= $this->include('tenant_footer') ?>
