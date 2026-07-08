<?php
/**
 * Service page + live itemised quote (frames 1f/1g/1h).
 *
 * The price card and both sticky-bar figures re-render on every option
 * change via GET /quote-live — all numbers come from EventQuoteBuilder
 * server-side (travel included; never "call us"). "Book this date" POSTs
 * the same inputs to /quote, which stores the server-priced session quote
 * and goes straight to checkout.
 */
?>
<?= $this->include('tenant_header') ?>
<?php
$bn        = $site['business_name'] ?? 'Storefront';
$rating    = $trust['rating'] ?? null;
$bookCnt   = (int) ($trust['bookings'] ?? 0);
$catShort  = trim(explode('·', (string) ($categoryName ?? ''))[0] ?? '');
$urls      = $photos['urls'];
$dateLabel = $ctxDate !== '' ? date('D j M', strtotime($ctxDate)) : '';
$ctxBits   = array_filter([$catShort, $dateLabel, $ctxPostcode]);
?>

<div class="sf-shell" style="padding-top: 10px;">
    <?php if (! empty($isMultiService)): ?>
        <a href="/" style="font-size: 13px; font-weight: 600;"><i class="fas fa-arrow-left" aria-hidden="true"></i> All services</a>
    <?php endif; ?>
</div>

<!-- Gallery, per the design FRAMES (1f mobile swipe / 1g laptop mosaic /
     1m framed hero): 1 photo → framed-on-tint hero; 2+ → swipe strip on
     mobile + 2fr-1fr mosaic on laptop. At exactly 2 photos the mosaic gets
     the `two` variant (both tiles full height) so the grid is never sparse.
     Strip and mosaic render the SAME $urls; only one is in the a11y tree per
     breakpoint (the other is display:none), so numbered alt text is not
     double-announced. Counter, dots and "+N" all derive from count($urls). -->
<?php
$galAlt = static fn (int $i): string => $i === 0
    ? (string) ($service['title'] ?? 'Service photo')
    : ($service['title'] ?? 'Service') . ' — photo ' . ($i + 1);
?>
<?php if ($urls !== []): ?>
    <?php if ($photos['mode'] === 'framed'): ?>
        <div class="sf-framed-hero"><img src="<?= esc($urls[0], 'attr') ?>" alt="<?= esc($galAlt(0), 'attr') ?>"></div>
    <?php else: ?>
        <div class="sf-shell" style="padding-top: 10px;">
            <div class="sf-gal">
                <div class="sf-gal-strip" id="sfGal">
                    <?php foreach ($urls as $i => $u): ?><img src="<?= esc($u, 'attr') ?>" alt="<?= esc($galAlt($i), 'attr') ?>"><?php endforeach; ?>
                </div>
                <span class="sf-counter-chip" aria-hidden="true"><span id="sfGalN">1</span>/<?= count($urls) ?></span>
                <div class="sf-gal-dots" id="sfGalDots" aria-hidden="true">
                    <?php foreach ($urls as $i => $u): ?><span<?= $i === 0 ? ' class="on"' : '' ?>></span><?php endforeach; ?>
                </div>
                <div class="sf-mosaic<?= count($urls) === 2 ? ' two' : '' ?>">
                    <div class="cell big"><img src="<?= esc($urls[0], 'attr') ?>" alt="<?= esc($galAlt(0), 'attr') ?>"></div>
                    <?php if (isset($urls[1])): ?><div class="cell" role="button" tabindex="0" aria-label="Show this photo larger"><img src="<?= esc($urls[1], 'attr') ?>" alt="<?= esc($galAlt(1), 'attr') ?>"></div><?php endif; ?>
                    <?php if (isset($urls[2])): ?>
                        <div class="cell" role="button" tabindex="0" aria-label="Show this photo larger">
                            <img src="<?= esc($urls[2], 'attr') ?>" alt="<?= esc($galAlt(2), 'attr') ?>">
                            <?php if ($photos['extra'] > 0): ?><span class="more"><span aria-hidden="true">+<?= (int) $photos['extra'] ?> photos</span><span class="sf-sr-only"><?= (int) $photos['extra'] ?> more photos of <?= esc($service['title']) ?></span></span><?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>
<?php endif; ?>

<div class="sf-shell">
    <div class="sf-cols" style="margin-top: 14px;">
        <div>
            <?php if ($ctxBits !== []): ?><p class="sf-eyebrow"><?= esc(implode(' · ', $ctxBits)) ?></p><?php endif; ?>
            <h1 style="font-size: 19px; font-weight: 700; margin: 0 0 6px; letter-spacing: -0.01em;"><?= esc($service['title']) ?></h1>
            <p style="margin: 0 0 12px; display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                <?php if ($rating !== null): ?>
                    <span class="sf-rating-chip" style="color: var(--sf-ink);"><i class="fas fa-star" aria-hidden="true"></i><?= esc(number_format($rating, 1)) ?> · <?= $bookCnt ?> booking<?= $bookCnt === 1 ? '' : 's' ?></span>
                <?php endif; ?>
                <?php if ($available === true): ?>
                    <span class="sf-avail-chip"><i class="fas fa-check" aria-hidden="true"></i>Available <?= esc($dateLabel) ?></span>
                <?php endif; ?>
            </p>

            <?php if (session()->getFlashdata('error')): ?>
                <div class="sf-flash error" role="alert"><?= esc(session()->getFlashdata('error')) ?></div>
            <?php endif; ?>
            <?php if (session()->getFlashdata('info')): ?>
                <div class="sf-flash info"><?= esc(session()->getFlashdata('info')) ?></div>
            <?php endif; ?>

            <?php if ($available === false): ?>
                <!-- 1h: honest no + alternatives -->
                <div class="sf-card sf-unavail" style="margin: 14px 0;">
                    <div class="disc"><i class="fas fa-calendar-xmark" aria-hidden="true"></i></div>
                    <h3><?= esc($dateLabel) ?> is already booked</h3>
                    <p>Sorry — that one's gone. Nearest free dates:</p>
                    <div class="sf-altdates">
                        <?php foreach ($nearestDates as $alt): ?>
                            <a class="sf-altdate" href="/service/<?= (int) $service['id'] ?>?<?= esc(http_build_query(array_filter(['date' => $alt['date'], 'postcode' => $ctxPostcode, 'guests' => $ctxGuests])), 'attr') ?>">
                                <span><?= esc($alt['label']) ?></span><span class="pick">Pick &rarr;</span>
                            </a>
                        <?php endforeach; ?>
                        <?php if ($nearestDates === []): ?>
                            <p class="sf-nudge">Nothing free nearby — try another week.</p>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (! empty($service['description'])): ?>
                <p style="font-size: 13.5px; color: var(--sf-muted); max-width: 60ch; margin: 0 0 16px;"><?= esc($service['description']) ?></p>
            <?php endif; ?>

            <?php if ($reviews !== []): ?>
                <section class="sf-sec" style="padding-top: 4px;">
                    <h2 class="sf-sec-h">Reviews</h2>
                    <?php foreach ($reviews as $r): ?>
                        <div class="sf-card sf-review" style="margin-bottom: 10px;">
                            <span class="sf-stars"><?php for ($i = 1; $i <= 5; $i++): ?><i class="fas fa-star<?= $i > (int) $r['rating'] ? ' off' : '' ?>" aria-hidden="true"></i><?php endfor; ?></span>
                            <span class="ctx"><?= ! empty($r['created_at']) ? esc(date('M Y', strtotime($r['created_at']))) : '' ?></span>
                            <blockquote>&ldquo;<?= esc($r['comment'] ?? $r['title'] ?? '') ?>&rdquo; — <?= esc($r['reviewer'] ?? 'Verified customer') ?></blockquote>
                        </div>
                    <?php endforeach; ?>
                </section>
            <?php endif; ?>
        </div>

        <aside>
            <div class="sf-panel<?= ' sticky' ?>">
                <div style="display: flex; align-items: center; justify-content: space-between; gap: 10px; margin-bottom: 12px;">
                    <h2 style="font-size: 15.5px; font-weight: 700; margin: 0;">Your quote</h2>
                    <?php if ($available === true): ?>
                        <span class="sf-avail-chip"><i class="fas fa-check" aria-hidden="true"></i><?= esc($dateLabel) ?> available</span>
                    <?php endif; ?>
                </div>

                <form id="sfQuoteForm" method="post" action="/quote">
                    <?= csrf_field() ?>
                    <input type="hidden" name="service_id" value="<?= (int) $service['id'] ?>">

                    <div class="sf-2col">
                        <label class="sf-field">
                            <span>Date</span>
                            <input class="sf-input" type="date" name="event_date" required min="<?= date('Y-m-d') ?>" value="<?= esc($ctxDate, 'attr') ?>">
                        </label>
                        <label class="sf-field">
                            <span>Postcode</span>
                            <input class="sf-input" type="text" name="postcode" maxlength="10" placeholder="e.g. SE12" autocomplete="postal-code" value="<?= esc($ctxPostcode, 'attr') ?>">
                        </label>
                    </div>

                    <?php // Time-based services book a slot — start time is required; setup and
                          // pack-down are added around it automatically for availability. ?>
                    <?php if (! empty($pricing['needsStartTime'])): ?>
                        <label class="sf-field">
                            <span>Start time</span>
                            <input class="sf-input" type="time" name="start_time" required step="900" value="<?= esc($ctxTime ?? '', 'attr') ?>">
                        </label>
                    <?php endif; ?>

                    <?php if (! empty($pricing['needsGuests'])): ?>
                        <label class="sf-field">
                            <span>Guests</span>
                            <input class="sf-input" type="number" name="guest_count" min="1" max="10000" required placeholder="e.g. 45" value="<?= $ctxGuests > 0 ? (int) $ctxGuests : '' ?>">
                        </label>
                    <?php endif; ?>

                    <?php if (! empty($pricing['needsQuantity'])): ?>
                        <label class="sf-field">
                            <span>How many?</span>
                            <input class="sf-input" type="number" name="order_quantity" min="<?= (int) $pricing['minQuantity'] ?>" max="100000" value="<?= (int) $pricing['minQuantity'] ?>" required>
                        </label>
                    <?php endif; ?>

                    <?php if (! empty($pricing['options'])): ?>
                        <fieldset class="sf-optgroup">
                            <legend>Session length</legend>
                            <?php foreach ($pricing['options'] as $i => $opt): ?>
                                <label class="sf-opt">
                                    <input type="radio" name="pricing_option" value="<?= esc($opt['token'], 'attr') ?>" <?= $i === 0 ? 'checked' : '' ?>>
                                    <span class="lbl"><?= esc($opt['label']) ?></span>
                                    <span class="prc"><?= esc($opt['sub']) ?></span>
                                </label>
                            <?php endforeach; ?>
                        </fieldset>
                    <?php endif; ?>

                    <?php if (! empty($extras)): ?>
                        <fieldset class="sf-optgroup">
                            <legend>Extras</legend>
                            <?php foreach ($extras as $x):
                                $xp      = isset($x['price']) && $x['price'] !== null ? (float) $x['price'] : null;
                                $perItem = strtolower((string) ($x['pricing_type'] ?? 'flat')) === 'per_item';
                            ?>
                                <label class="sf-opt">
                                    <input type="checkbox" name="extras[]" value="<?= (int) $x['id'] ?>">
                                    <span class="lbl"><?= esc($x['name'] ?? 'Extra') ?></span>
                                    <?php if ($perItem): ?>
                                        <input class="qty" type="number" name="extra_qty[<?= (int) $x['id'] ?>]"
                                            min="<?= max(1, (int) ($x['min_quantity'] ?? 1)) ?>" value="<?= max(1, (int) ($x['min_quantity'] ?? 1)) ?>" aria-label="Quantity">
                                    <?php endif; ?>
                                    <?php if ($xp !== null): ?>
                                        <span class="prc">+£<?= esc(number_format($xp, $xp == (int) $xp ? 0 : 2)) ?><?= $perItem && ! empty($x['unit_label']) ? '/' . esc($x['unit_label']) : '' ?></span>
                                    <?php endif; ?>
                                </label>
                            <?php endforeach; ?>
                        </fieldset>
                    <?php endif; ?>

                    <!-- Itemised price (server-rendered by /quote-live) -->
                    <div class="sf-quote-card" id="sfLines" style="display: none;"></div>
                    <p class="sf-microcopy" id="sfQuoteNote" style="text-align: left; display: none;"></p>

                    <button type="submit" class="sf-btn block" id="sfBookBtn">Book this date</button>
                    <p class="sf-microcopy" id="sfBalanceNote"></p>
                </form>
            </div>
        </aside>
    </div>
</div>

<!-- Mobile sticky bar: live figures -->
<div class="sf-stickybar" id="sfBar" style="display: none;">
    <div class="figures">
        <div class="t" id="sfBarTotal">—</div>
        <div class="d" id="sfBarDeposit"></div>
    </div>
    <button class="sf-btn" type="button" onclick="document.getElementById('sfQuoteForm').requestSubmit()">Book this date</button>
</div>

<script>
(function () {
    var form = document.getElementById('sfQuoteForm');
    var lines = document.getElementById('sfLines');
    var note = document.getElementById('sfQuoteNote');
    var bar = document.getElementById('sfBar');
    var barTotal = document.getElementById('sfBarTotal');
    var barDeposit = document.getElementById('sfBarDeposit');
    var bookBtn = document.getElementById('sfBookBtn');
    var balanceNote = document.getElementById('sfBalanceNote');
    var timer = null;
    var seq = 0;

    function gbp(n) { return '£' + Number(n).toFixed(2); }

    function params() {
        var fd = new FormData(form);
        var p = new URLSearchParams();
        p.set('service_id', fd.get('service_id'));
        if (fd.get('event_date')) p.set('event_date', fd.get('event_date'));
        if (fd.get('postcode')) p.set('postcode', fd.get('postcode'));
        if (fd.get('guest_count')) p.set('guest_count', fd.get('guest_count'));
        if (fd.get('order_quantity')) p.set('order_quantity', fd.get('order_quantity'));
        if (fd.get('start_time')) p.set('start_time', fd.get('start_time'));
        if (fd.get('pricing_option')) p.set('pricing_option', fd.get('pricing_option'));
        fd.getAll('extras[]').forEach(function (id) {
            p.append('extras[]', id);
            var q = fd.get('extra_qty[' + id + ']');
            if (q) p.set('extra_qty[' + id + ']', q);
        });
        return p;
    }

    function render(data) {
        if (!data.ok) {
            lines.style.display = 'none';
            bar.style.display = 'none';
            note.textContent = data.error || '';
            note.style.display = data.error ? '' : 'none';
            balanceNote.textContent = '';
            bookBtn.textContent = 'Book this date';
            return;
        }
        var html = '';
        var hasTravel = false;
        (data.lines || []).forEach(function (l) {
            if (l.code === 'platform_commission') return;
            if (l.code === 'travel') hasTravel = true;
            html += '<div class="row"><span class="l">' + esc(l.label) + '</span><span class="a">'
                + ((Number(l.amount) > 0) ? gbp(l.amount) : 'Free') + '</span></div>';
        });
        // Travel is always a visible, calculated answer (handoff 1f): when the
        // postcode is inside the included radius the engine adds no line, so
        // say "Included" rather than nothing.
        var pc = new FormData(form).get('postcode');
        if (pc && !hasTravel && data.distance_km !== null) {
            html += '<div class="row"><span class="l">Travel to ' + esc(String(pc).toUpperCase())
                + ' <small>(' + (Number(data.distance_km) * 0.621371).toFixed(0) + ' mi)</small></span><span class="a">Included</span></div>';
        }
        html += '<div class="row total"><span class="l">Total for your date</span><span class="a">' + gbp(data.total) + '</span></div>';
        html += '<div class="row deposit"><span class="l">Deposit due today (<?= (int) $depositPercent ?>%)</span><span class="a">' + gbp(data.deposit) + '</span></div>';
        lines.innerHTML = html;
        lines.style.display = '';
        note.textContent = (data.warnings || []).join(' ');
        note.style.display = note.textContent ? '' : 'none';
        barTotal.textContent = 'Total ' + gbp(data.total) + ' · today';
        barDeposit.textContent = gbp(data.deposit);
        bar.style.display = '';
        bookBtn.textContent = 'Book — pay ' + gbp(data.deposit) + ' today';
        balanceNote.textContent = 'Balance of ' + gbp(Math.max(0, data.total - data.deposit))
            + ' due after the event · free cancellation for 14 days';
    }

    function esc(s) { var d = document.createElement('div'); d.textContent = String(s); return d.innerHTML; }

    function requote() {
        clearTimeout(timer);
        timer = setTimeout(function () {
            var fd = new FormData(form);
            if (!fd.get('event_date')) return; // need a date before pricing
            var mySeq = ++seq;
            fetch('/quote-live?' + params().toString(), { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(function (r) { return r.json(); })
                .then(function (data) { if (mySeq === seq) render(data); })
                .catch(function () {});
        }, 350);
    }

    form.addEventListener('input', requote);
    form.addEventListener('change', requote);
    requote(); // price immediately when arriving with ?date=&postcode=
})();
</script>

<?php if ($photos['mode'] === 'gallery'): ?>
<script>
(function () {
    var strip = document.getElementById('sfGal') ? document.getElementById('sfGal') : null;
    if (!strip) return;
    var dots = document.querySelectorAll('#sfGalDots span');
    var n = document.getElementById('sfGalN');
    strip.addEventListener('scroll', function () {
        var i = Math.round(strip.scrollLeft / strip.clientWidth);
        n.textContent = i + 1;
        dots.forEach(function (d, k) { d.classList.toggle('on', k === i); });
    }, { passive: true });
})();
</script>
<?php endif; ?>

<?= $this->include('tenant_footer') ?>
