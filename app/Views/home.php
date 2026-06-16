<?php
$fallbackImage = base_url('assets/images/' . esc($serviceFallbackImage ?? 'fallback-service-card.jpg'));
$heroBg        = base_url('assets/images/ps-hero-party.png');
$browseUrl     = base_url('browse-services');
$planUrl       = session()->has('user_id') ? base_url('event/create') : base_url('register');
$occasions     = ['Wedding', 'Birthday', 'Corporate', 'Christening'];
$catOptions    = ['Venues', 'Photography & video', 'Catering & drinks', 'Entertainment', 'Flowers & styling', 'Cakes & desserts', 'Beauty', 'Transport'];
$featured      = array_slice($services ?? [], 0, 3);
?>
<?= $this->include('header') ?>

<main class="home-marketplace">

    <!-- ============ HERO ============ -->
    <section class="hero" id="search" aria-labelledby="home-hero-heading">
        <div class="hero-bg">
            <img src="<?= $heroBg ?>" alt="Guests laughing together at a warmly-lit evening celebration"
                onerror="this.onerror=null;this.src='<?= base_url('assets/images/hero-event-planning.jpg') ?>';">
        </div>
        <div class="container">
            <div class="hero-stack">
                <p class="eyebrow hero-eyebrow">The UK event marketplace · expertly made</p>
                <h1 id="home-hero-heading">Find trusted suppliers<br>for <em>every kind of event.</em></h1>
                <p class="hero-sublead">Compare structured quotes from vetted local suppliers and book in one place — with your payment protected until after the event.</p>

                <form class="search" action="<?= $browseUrl ?>" method="get" role="search" aria-label="Find event suppliers">
                    <input type="hidden" name="occasion" id="ps-occasion-input" value="Wedding">
                    <div class="search-tabs" role="tablist">
                        <?php foreach ($occasions as $i => $occasion): ?>
                            <button type="button" class="tab<?= $i === 0 ? ' on' : '' ?>" data-occasion="<?= esc($occasion, 'attr') ?>"><?= esc($occasion) ?>s</button>
                        <?php endforeach; ?>
                        <button type="button" class="tab tab-more" data-occasion="more">+ more occasions</button>
                    </div>
                    <div class="search-fields">
                        <div class="field">
                            <label for="f-cat">What you need</label>
                            <div class="control"><i class="fas fa-layer-group" aria-hidden="true"></i>
                                <select id="f-cat" name="q" aria-label="Category">
                                    <option value="">Any supplier</option>
                                    <?php foreach ($catOptions as $opt): ?>
                                        <option value="<?= esc($opt, 'attr') ?>"><?= esc($opt) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="field">
                            <label for="f-loc">Location</label>
                            <div class="control"><i class="fas fa-location-dot" aria-hidden="true"></i><input id="f-loc" type="text" name="location" placeholder="Town or postcode" autocomplete="off"></div>
                        </div>
                        <div class="field">
                            <label for="f-date">Date</label>
                            <div class="control"><i class="fas fa-calendar" aria-hidden="true"></i><input id="f-date" type="text" name="date" placeholder="If you have one" onfocus="this.type='date'" onblur="if(!this.value)this.type='text'"></div>
                        </div>
                    </div>
                    <div class="search-submit">
                        <button type="submit" class="btn btn-primary btn-lg btn-block"><i class="fas fa-magnifying-glass" aria-hidden="true"></i> Search suppliers</button>
                    </div>
                    <p class="craftline"><i class="fas fa-hammer" aria-hidden="true"></i> Hand-picked, vetted suppliers — structured quotes in minutes, no obligation.</p>
                </form>

                <div class="hero-reassure">
                    <span><i class="fas fa-circle-check" aria-hidden="true"></i> Vetted suppliers</span>
                    <span><i class="fas fa-shield-halved" aria-hidden="true"></i> Payment protected</span>
                    <span><i class="fas fa-tag" aria-hidden="true"></i> No booking fees</span>
                </div>
            </div>
        </div>
    </section>

    <!-- ============ TRUST STRIP ============ -->
    <section class="trust" aria-label="Why Partysmith">
        <div class="container">
            <div class="trust-item">
                <span class="trust-ic" aria-hidden="true"><i class="fas fa-clipboard-check"></i></span>
                <span><b>Vetted suppliers</b><span class="trust-text">Insurance, ID and recent work checked before any listing goes live.</span></span>
            </div>
            <div class="trust-item">
                <span class="trust-ic" aria-hidden="true"><i class="fas fa-shield-halved"></i></span>
                <span><b>Payment protection</b><span class="trust-text">We hold your money securely and release it 48 hours after your event.</span></span>
            </div>
            <div class="trust-item">
                <span class="trust-ic" aria-hidden="true"><i class="fas fa-tag"></i></span>
                <span><b>No booking fees</b><span class="trust-text">Free to enquire, compare and book. You pay the supplier, not us.</span></span>
            </div>
        </div>
    </section>

    <?php if (! empty($cmsHome['content'])): ?>
    <section class="section">
        <div class="container">
            <div class="lead" style="margin-inline:auto;text-align:center;max-width:60ch">
                <?= $cmsHome['content'] ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- ============ FEATURED CATEGORIES ============ -->
    <section class="section" id="categories" aria-labelledby="home-categories-heading">
        <div class="container">
            <div class="sec-head-row">
                <div>
                    <p class="eyebrow">Browse by category</p>
                    <h2 id="home-categories-heading" class="heading" style="margin-bottom:0">Every supplier your event needs</h2>
                </div>
                <a class="linkmore" href="<?= $browseUrl ?>">View all categories <i class="fas fa-arrow-right" aria-hidden="true"></i></a>
            </div>
            <?php if (! empty($homeCategoryTiles)): ?>
            <div class="cat-grid">
                <?php foreach ($homeCategoryTiles as $tile): ?>
                    <a class="cat" href="<?= esc($tile['href']) ?>" aria-label="Browse <?= esc($tile['name']) ?>">
                        <img src="<?= base_url('assets/images/' . esc($tile['image'])) ?>" alt="<?= esc($tile['name']) ?>" loading="lazy"
                            onerror="this.onerror=null;this.src='<?= $fallbackImage ?>';">
                        <span class="cat-arrow" aria-hidden="true"><i class="fas fa-arrow-right"></i></span>
                        <span class="cat-label"><b><?= esc($tile['name']) ?></b></span>
                    </a>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- ============ HOW IT WORKS ============ -->
    <section class="section surface-2" aria-labelledby="home-how-heading">
        <div class="container">
            <div class="section-head center">
                <p class="eyebrow centered">How Partysmith works</p>
                <h2 id="home-how-heading" class="heading">Booked in three calm steps</h2>
                <p class="lead">No endless emails, no chasing. Tell us once and let vetted suppliers come to you.</p>
            </div>
            <div class="steps">
                <div class="step">
                    <span class="step-line" aria-hidden="true"></span>
                    <div class="step-num">1</div>
                    <h3>Tell us about your event</h3>
                    <p>Share the occasion, date, location and what you need. It takes a couple of minutes.</p>
                </div>
                <div class="step">
                    <span class="step-line" aria-hidden="true"></span>
                    <div class="step-num">2</div>
                    <h3>Compare structured quotes</h3>
                    <p>Vetted suppliers respond with clear, like-for-like quotes you can line up side by side.</p>
                </div>
                <div class="step">
                    <div class="step-num">3</div>
                    <h3>Book with confidence</h3>
                    <p>Choose your favourite and pay securely through Partysmith — protected until after the day.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- ============ FEATURED SUPPLIERS ============ -->
    <?php if (! empty($featured)): ?>
    <section class="section" aria-labelledby="home-suppliers-heading">
        <div class="container">
            <div class="sec-head-row">
                <div>
                    <p class="eyebrow">The founding cohort</p>
                    <h2 id="home-suppliers-heading" class="heading" style="margin-bottom:8px">Hand-picked suppliers, vetted by us</h2>
                    <p class="lead">A selection of suppliers ready to help with weddings, private parties and corporate occasions — each one interviewed, insured and reference-checked before joining.</p>
                </div>
                <a class="linkmore" href="<?= $browseUrl ?>">Browse all suppliers <i class="fas fa-arrow-right" aria-hidden="true"></i></a>
            </div>
            <div class="sup-grid">
                <?php foreach ($featured as $service):
                    $serviceUrl = base_url('service/view/' . (int) $service['id']);
                    $thumb      = ! empty($service['images'])
                        ? base_url(esc($service['images'][0]['thumbnail_path'] ?? $service['images'][0]['image_path'] ?? ''))
                        : $fallbackImage;
                    $location = trim((string) ($service['service_location'] ?? '')) ?: 'UK-wide';
                    $price    = isset($service['price']) ? (float) $service['price'] : null;
                    $category = trim((string) ($service['category_label'] ?? '')) ?: 'Event services';
                    $isVerified = ! empty($service['license']);
                ?>
                    <article class="sup-card">
                        <a class="sup-media" href="<?= $serviceUrl ?>">
                            <img src="<?= $thumb ?>" alt="<?= esc($service['title']) ?>" loading="lazy"
                                onerror="this.onerror=null;this.src='<?= $fallbackImage ?>';">
                            <?php if ($isVerified): ?><span class="sup-stamp">Founding supplier</span><?php endif; ?>
                        </a>
                        <div class="sup-body">
                            <span class="sup-cat"><?= esc($category) ?></span>
                            <h3><a href="<?= $serviceUrl ?>"><?= esc($service['title']) ?></a></h3>
                            <div class="sup-meta"><i class="fas fa-location-dot" aria-hidden="true"></i> <?= esc($location) ?></div>
                            <div class="creds">
                                <span class="cred"><i class="fas fa-shield-halved" aria-hidden="true"></i> Vetted by Partysmith</span>
                            </div>
                        </div>
                        <div class="sup-foot">
                            <span class="sup-from"><?= ($price !== null && $price > 0) ? 'From £' . number_format($price, 0) : 'Request a quote' ?></span>
                            <a class="linkmore" href="<?= $serviceUrl ?>">View details <i class="fas fa-arrow-right" aria-hidden="true"></i></a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
            <p class="sup-footnote"><i class="fas fa-circle-info" aria-hidden="true"></i>Every Partysmith supplier is interviewed, insurance-checked and reference-checked before they can take a booking.</p>
        </div>
    </section>
    <?php endif; ?>

    <!-- ============ WHY BOOK ============ -->
    <section class="section surface-white" aria-labelledby="home-why-heading">
        <div class="container">
            <div class="why-grid">
                <div>
                    <p class="eyebrow">Why book through Partysmith</p>
                    <h2 id="home-why-heading" class="heading">One trusted place, from first quote to final payment</h2>
                    <p class="lead" style="margin-bottom:30px">We bring the suppliers, the structure and the safety net — so you can plan any event without the guesswork.</p>
                    <div class="why-list">
                        <div class="why-row"><span class="why-ic" aria-hidden="true"><i class="fas fa-user-check"></i></span><div><b>Trusted suppliers</b><p>Every supplier is vetted, insured and reference-checked before they can take a booking.</p></div></div>
                        <div class="why-row"><span class="why-ic" aria-hidden="true"><i class="fas fa-file-lines"></i></span><div><b>Structured quotes</b><p>Like-for-like quotes with clear inclusions — no more comparing apples with oranges.</p></div></div>
                        <div class="why-row"><span class="why-ic" aria-hidden="true"><i class="fas fa-layer-group"></i></span><div><b>Easy comparison</b><p>Line up suppliers, prices and availability side by side in one tidy place.</p></div></div>
                        <div class="why-row"><span class="why-ic" aria-hidden="true"><i class="fas fa-shield-halved"></i></span><div><b>Secure payments</b><p>Pay through Partysmith and we hold it safely until 48 hours after your event.</p></div></div>
                        <div class="why-row"><span class="why-ic" aria-hidden="true"><i class="fas fa-map-location-dot"></i></span><div><b>UK-wide coverage</b><p>From village halls to city venues — local suppliers wherever your event is.</p></div></div>
                    </div>
                </div>
                <figure class="why-figure">
                    <img src="<?= base_url('assets/images/ps-hero-event.jpg') ?>" alt="Guests talking and laughing at a warmly-lit evening celebration"
                        onerror="this.onerror=null;this.src='<?= $fallbackImage ?>';">
                    <figcaption class="why-badge"><div class="n">48 hrs</div><div class="t">payment held after every event</div></figcaption>
                </figure>
            </div>
        </div>
    </section>

    <!-- ============ FOUNDER NOTE ============ -->
    <section class="section surface-2" aria-label="A note from the founder">
        <div class="container">
            <div class="note-grid">
                <img class="founder-photo" src="<?= base_url('assets/images/ps-founder-mark.jpg') ?>" alt="The founder of Partysmith"
                    onerror="this.onerror=null;this.src='<?= $fallbackImage ?>';">
                <div class="note-body">
                    <p class="eyebrow">A note from the founder</p>
                    <p>Partysmith is new, and we'd rather say so than pretend otherwise. We launched this year with one promise: every supplier checked by a person, every payment held until after your event, every question answered by someone who can actually fix things.</p>
                    <p class="muted">If anything ever falls short of that, email me directly — my inbox is at the other end of the address below.</p>
                    <div class="note-sig">
                        <span class="sig-mark">Mark Pearson</span>
                        <span class="sig-role"><b>Founder, Partysmith</b>hello@partysmith.co.uk</span>
                    </div>
                    <p class="note-ps">P.S. leave the planning to us.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- ============ SUPPLIER CTA ============ -->
    <section class="section" id="supplier-cta" aria-labelledby="home-supcta-heading">
        <div class="container">
            <div class="supcta">
                <div class="supcta-inner">
                    <div>
                        <p class="eyebrow on-dark">For suppliers</p>
                        <h2 id="home-supcta-heading">Grow your event business — without the admin</h2>
                        <p class="lead">Whether you're a freelancer, a small team or an established supplier, list your services once and let structured enquiries come to you. Set your pricing, manage bookings from one dashboard, and get paid securely.</p>
                        <div class="supcta-actions">
                            <a href="<?= base_url('register/vendor') ?>" class="btn btn-gold btn-lg">List your business</a>
                            <a href="<?= base_url('vendor-info') ?>" class="btn btn-ghost-light btn-lg">How it works for suppliers</a>
                        </div>
                    </div>
                    <div class="supcta-stats">
                        <div class="supcta-stat"><div class="n">0%</div><div class="t">commission during our founding period</div></div>
                        <div class="supcta-stat"><div class="n">1 dashboard</div><div class="t">quotes, messages &amp; bookings in one place</div></div>
                        <div class="supcta-stat"><div class="n">Vetted</div><div class="t">a quality bar that protects your reputation</div></div>
                        <div class="supcta-stat"><div class="n">Secure</div><div class="t">payments handled and protected for you</div></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============ FAQ ============ -->
    <section class="section surface-2" aria-labelledby="home-faq-heading">
        <div class="container">
            <div class="faq-grid">
                <div class="faq-aside">
                    <p class="eyebrow on-dark">Good to know</p>
                    <h3 id="home-faq-heading">Questions, answered</h3>
                    <p>Everything you need to know about planning and booking through Partysmith. Can't find it here?</p>
                    <a href="<?= base_url('faq') ?>" class="btn btn-ghost-light btn-block">Visit the help centre</a>
                </div>
                <div class="faq-list">
                    <?php
                    $faqs = [
                        ['How does Partysmith make money if there are no booking fees?', 'Customers pay no booking fee — you pay the supplier\'s quoted price, nothing more. Suppliers pay a small subscription to list and manage their business with us. That keeps our incentives aligned with finding you the right supplier, not the most expensive one.'],
                        ['What does it mean that a supplier is "vetted"?', 'Before any supplier can take a booking we check their public liability insurance, confirm their identity against the registered business, review their portfolio and take two recent client references. Suppliers are re-checked every 12 months.'],
                        ['How does payment protection work?', 'When you book, your payment is held securely and only released to the supplier 48 hours after your event has taken place. If a supplier cancels, we\'ll help you re-match or refund you in full.'],
                        ['What kinds of events can I plan here?', 'Weddings, birthdays, christenings, corporate events, baby showers, anniversaries, school and community events — anything that needs trusted suppliers. Pick your occasion in the search and we\'ll tailor the results.'],
                        ['How quickly will I hear back from suppliers?', 'Most suppliers respond with a structured quote within one business day. You\'ll get a notification as each quote arrives, so you can compare them as they come in.'],
                    ];
                    foreach ($faqs as $i => $faq): ?>
                        <div class="faq-item<?= $i === 0 ? ' open' : '' ?>">
                            <button class="faq-q" type="button"><?= esc($faq[0]) ?><span class="ic" aria-hidden="true"><i class="fas fa-plus"></i></span></button>
                            <div class="faq-a"><div class="faq-a-inner"><?= esc($faq[1]) ?></div></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </section>
</main>

<?= $this->include('footer') ?>
