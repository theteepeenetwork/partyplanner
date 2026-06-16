<?= $this->include('header') ?>

<div class="ps-app">
<main data-screen-label="For vendors">
    <section class="hero" style="min-height:560px">
        <div class="hero-bg">
            <img src="/assets/images/ps-hero-event.jpg" alt="A supplier at work at an event">
        </div>
        <div class="container">
            <div class="hero-stack">
                <p class="eyebrow hero-eyebrow">For event suppliers</p>
                <h1>Do more of the work<br>you <em>love</em> — booked.</h1>
                <p class="hero-sublead">Partysmith brings you enquiries that match your craft, structured so you can quote in minutes, and payment you can count on. Join our founding cohort of vetted UK suppliers.</p>
                <div style="display:flex;gap:14px;flex-wrap:wrap;margin-top:6px">
                    <a href="/register/vendor" class="btn btn-gold btn-lg">List your business</a>
                    <a href="#pricing" class="btn btn-ghost-light btn-lg">See pricing</a>
                </div>
                <div class="hero-reassure">
                    <span><i class="fas fa-circle-check"></i> No subscription</span>
                    <span><i class="fas fa-circle-check"></i> No per-lead charges</span>
                    <span><i class="fas fa-circle-check"></i> Paid 48hrs after the event</span>
                </div>
            </div>
        </div>
    </section>

    <section class="section">
        <div class="container">
            <div class="section-head center">
                <p class="eyebrow centered">Why suppliers join</p>
                <h2 class="heading">Built around how you actually work</h2>
            </div>
            <div class="feature-grid cols-3">
                <div class="fcard"><div class="fic"><i class="fas fa-clipboard-check"></i></div><h3>Qualified enquiries</h3><p>Every enquiry comes with the occasion, date, location and budget — so you only spend time on jobs that fit.</p></div>
                <div class="fcard"><div class="fic"><i class="fas fa-file-lines"></i></div><h3>Quote in minutes</h3><p>Reusable packages and a structured quote builder mean you reply fast, look professional, and win more.</p></div>
                <div class="fcard"><div class="fic"><i class="fas fa-sterling"></i></div><h3>Reliable, on-time pay</h3><p>Funds are confirmed up front and released 48 hours after the event. No chasing invoices, ever.</p></div>
                <div class="fcard"><div class="fic"><i class="fas fa-award"></i></div><h3>A trusted badge</h3><p>Founding suppliers carry a verified mark that customers see and value across the marketplace.</p></div>
                <div class="fcard"><div class="fic"><i class="fas fa-map-location-dot"></i></div><h3>Reach further</h3><p>Get discovered by hosts beyond your usual patch — many categories book UK-wide.</p></div>
                <div class="fcard"><div class="fic"><i class="fas fa-gauge"></i></div><h3>One simple dashboard</h3><p>Services, enquiries, bookings and payouts in a single place that takes a day to set up.</p></div>
            </div>
        </div>
    </section>

    <section class="section dark">
        <div class="container">
            <div class="section-head center">
                <p class="eyebrow centered on-dark">Getting started</p>
                <h2 class="heading">Live in four steps</h2>
            </div>
            <div class="feature-grid cols-4">
                <div class="fcard on-dark"><div class="fic"><i class="fas fa-store"></i></div><h3>1 · Apply</h3><p>Tell us about your business and the services you offer.</p></div>
                <div class="fcard on-dark"><div class="fic"><i class="fas fa-shield-check"></i></div><h3>2 · Get vetted</h3><p>We verify identity, insurance and portfolio — usually within a few days.</p></div>
                <div class="fcard on-dark"><div class="fic"><i class="fas fa-images"></i></div><h3>3 · Build packages</h3><p>Add your services, photos and pricing with our guided builder.</p></div>
                <div class="fcard on-dark"><div class="fic"><i class="fas fa-party"></i></div><h3>4 · Start quoting</h3><p>Matched enquiries arrive — reply, win, and get paid.</p></div>
            </div>
        </div>
    </section>

    <section class="section" id="pricing">
        <div class="container">
            <div class="section-head center">
                <p class="eyebrow centered">Pricing</p>
                <h2 class="heading">Honest pricing, no surprises</h2>
                <p class="lead">Founding suppliers join free while we grow. After that, a simple flat commission only when you get booked — never for an enquiry or a quote.</p>
            </div>
            <div class="feature-grid cols-2" style="max-width:760px;margin:0 auto">
                <div class="fcard" style="text-align:center;padding:38px 30px">
                    <p class="eyebrow centered">Founding cohort</p>
                    <div style="font-family:var(--serif);font-size:60px;line-height:1;letter-spacing:-0.02em;margin:6px 0 4px">0%</div>
                    <p style="margin:0 0 20px">commission, no fees — for our launch suppliers</p>
                    <ul style="list-style:none;padding:0;margin:0 0 24px;display:flex;flex-direction:column;gap:11px;text-align:left">
                        <li style="display:flex;gap:11px;align-items:flex-start"><i class="fas fa-check" style="color:var(--green);margin-top:3px"></i> Full profile &amp; unlimited services</li>
                        <li style="display:flex;gap:11px;align-items:flex-start"><i class="fas fa-check" style="color:var(--green);margin-top:3px"></i> Verified founding-supplier badge</li>
                        <li style="display:flex;gap:11px;align-items:flex-start"><i class="fas fa-check" style="color:var(--green);margin-top:3px"></i> All enquiries &amp; quotes, free</li>
                    </ul>
                    <a href="/register/vendor" class="btn btn-primary btn-block btn-lg">Join free</a>
                </div>
                <div class="fcard" style="text-align:center;padding:38px 30px">
                    <p class="eyebrow centered">Standard</p>
                    <div style="font-family:var(--serif);font-size:60px;line-height:1;letter-spacing:-0.02em;margin:6px 0 4px">8%</div>
                    <p style="margin:0 0 20px">per confirmed booking — that's it</p>
                    <ul style="list-style:none;padding:0;margin:0 0 24px;display:flex;flex-direction:column;gap:11px;text-align:left">
                        <li style="display:flex;gap:11px;align-items:flex-start"><i class="fas fa-check" style="color:var(--green);margin-top:3px"></i> Everything in founding</li>
                        <li style="display:flex;gap:11px;align-items:flex-start"><i class="fas fa-check" style="color:var(--green);margin-top:3px"></i> Commission only when you're booked</li>
                        <li style="display:flex;gap:11px;align-items:flex-start"><i class="fas fa-check" style="color:var(--green);margin-top:3px"></i> Payouts 48hrs after each event</li>
                    </ul>
                    <a href="/register/vendor" class="btn btn-ghost btn-block btn-lg">Get started</a>
                </div>
            </div>
        </div>
    </section>

    <section class="section dark" style="padding-block:clamp(60px,7vw,96px)">
        <div class="container">
            <div class="faq-grid">
                <div class="faq-aside">
                    <h3>Questions from suppliers</h3>
                    <p>Everything about vetting, payouts and how enquiries are matched to you.</p>
                    <a href="/faq" class="btn btn-gold">Supplier help centre</a>
                </div>
                <div class="faq-list">
                    <div class="faq-item open"><button class="faq-q" type="button">How are enquiries matched to me?<span class="ic"><i class="fas fa-plus"></i></span></button><div class="faq-a"><div class="faq-a-inner">We match on your categories, service area and availability. You only see enquiries that genuinely fit what you offer — and you're never charged to receive or reply to one.</div></div></div>
                    <div class="faq-item"><button class="faq-q" type="button">When and how do I get paid?<span class="ic"><i class="fas fa-plus"></i></span></button><div class="faq-a"><div class="faq-a-inner">The customer's payment is confirmed at booking and held securely. We release your payout 48 hours after the event has taken place, straight to your nominated account.</div></div></div>
                    <div class="faq-item"><button class="faq-q" type="button">What does vetting involve?<span class="ic"><i class="fas fa-plus"></i></span></button><div class="faq-a"><div class="faq-a-inner">We verify your business identity, in-date insurance and a genuine, recent portfolio, plus references where relevant. Most applications are reviewed within a few days.</div></div></div>
                    <div class="faq-item"><button class="faq-q" type="button">Is there a contract or lock-in?<span class="ic"><i class="fas fa-plus"></i></span></button><div class="faq-a"><div class="faq-a-inner">No. There's no subscription and no minimum term — you can pause or leave whenever you like. Founding suppliers pay nothing during our launch period.</div></div></div>
                </div>
            </div>
        </div>
    </section>
</main>
</div>

<?= $this->include('footer') ?>
