<?= $this->include('header') ?>

<div class="ps-app">
<main data-screen-label="Help & FAQ">
    <section class="page-head page-head--tall">
        <div class="container">
            <div class="breadcrumb"><a href="/">Home</a><span class="sep">/</span><span class="cur">Help centre</span></div>
            <p class="eyebrow">Help centre</p>
            <h1>How can we help?</h1>
            <p class="ph-lead">Answers on planning, payments, cancellations and vetting — for hosts and suppliers alike.</p>
            <form class="browse-search" action="/faq" method="get" style="grid-template-columns:1fr auto;max-width:620px">
                <div class="control"><i class="fas fa-magnifying-glass"></i><input type="text" name="q" placeholder="Search help articles…"></div>
                <button class="btn btn-primary" type="submit">Search</button>
            </form>
        </div>
    </section>

    <section class="section" style="padding-bottom:0">
        <div class="container">
            <div class="feature-grid cols-4">
                <a href="#hosts" class="fcard" style="text-decoration:none;color:inherit"><div class="fic"><i class="fas fa-calendar"></i></div><h3>Planning an event</h3><p>Searching, quotes and booking suppliers.</p></a>
                <a href="#payments" class="fcard" style="text-decoration:none;color:inherit"><div class="fic"><i class="fas fa-lock"></i></div><h3>Payments &amp; protection</h3><p>How holding, payouts and refunds work.</p></a>
                <a href="#suppliers" class="fcard" style="text-decoration:none;color:inherit"><div class="fic"><i class="fas fa-store"></i></div><h3>For suppliers</h3><p>Vetting, enquiries and getting paid.</p></a>
                <a href="#trust" class="fcard" style="text-decoration:none;color:inherit"><div class="fic"><i class="fas fa-shield-halved"></i></div><h3>Trust &amp; safety</h3><p>Vetting standards and dispute help.</p></a>
            </div>
        </div>
    </section>

    <section class="section">
        <div class="container">
            <div class="faq-grid" id="hosts">
                <div class="faq-aside">
                    <h3>Planning an event</h3>
                    <p>The basics of finding and booking the right suppliers for your day.</p>
                    <a href="/event/create" class="btn btn-gold">Start planning</a>
                </div>
                <div class="faq-list">
                    <div class="faq-item open"><button class="faq-q" type="button">How do I get quotes?<span class="ic"><i class="fas fa-plus"></i></span></button><div class="faq-a"><div class="faq-a-inner">Describe your event once — occasion, date, location and the suppliers you need. Matched suppliers reply with structured quotes, usually within one business day, which you can compare side by side.</div></div></div>
                    <div class="faq-item"><button class="faq-q" type="button">Does it cost anything to get quotes?<span class="ic"><i class="fas fa-plus"></i></span></button><div class="faq-a"><div class="faq-a-inner">No. Searching, requesting quotes and comparing them is completely free, with no booking fees added on top.</div></div></div>
                    <div class="faq-item"><button class="faq-q" type="button">Can I book more than one supplier at once?<span class="ic"><i class="fas fa-plus"></i></span></button><div class="faq-a"><div class="faq-a-inner">Yes. Add several suppliers to your event and check out together — each booking is tracked under the same event in your dashboard.</div></div></div>
                </div>
            </div>
        </div>
    </section>

    <section class="section" style="padding-top:0" id="payments">
        <div class="container">
            <div class="faq-grid">
                <div class="faq-aside">
                    <h3>Payments &amp; protection</h3>
                    <p>Your money is held securely and only released after your event.</p>
                    <a href="/how-it-works" class="btn btn-gold">How protection works</a>
                </div>
                <div class="faq-list">
                    <div class="faq-item open"><button class="faq-q" type="button">How does payment protection work?<span class="ic"><i class="fas fa-plus"></i></span></button><div class="faq-a"><div class="faq-a-inner">When you book, your payment is held securely and only released to the supplier 48 hours after your event. If a supplier cancels, we'll help you re-match or refund you in full.</div></div></div>
                    <div class="faq-item"><button class="faq-q" type="button">What's your cancellation policy?<span class="ic"><i class="fas fa-plus"></i></span></button><div class="faq-a"><div class="faq-a-inner">Each supplier sets a cancellation window shown on their quote. Cancel within it and you're refunded per those terms; because funds are held, refunds are straightforward.</div></div></div>
                    <div class="faq-item"><button class="faq-q" type="button">Which payment methods can I use?<span class="ic"><i class="fas fa-plus"></i></span></button><div class="faq-a"><div class="faq-a-inner">All major debit and credit cards, plus Apple Pay and Google Pay at checkout. Payments are processed securely by our payment partner.</div></div></div>
                </div>
            </div>
        </div>
    </section>

    <section class="section dark" style="padding-block:clamp(60px,7vw,96px)" id="suppliers">
        <div class="container">
            <div class="faq-grid">
                <div class="faq-aside">
                    <h3>For suppliers</h3>
                    <p>Joining, getting vetted, and how enquiries and payouts work.</p>
                    <a href="/vendor-info" class="btn btn-gold">For vendors</a>
                </div>
                <div class="faq-list">
                    <div class="faq-item open"><button class="faq-q" type="button">How do I join as a supplier?<span class="ic"><i class="fas fa-plus"></i></span></button><div class="faq-a"><div class="faq-a-inner">Apply through "List your business", tell us about your services and complete vetting. Once approved you can build packages and start receiving matched enquiries — free during our founding period.</div></div></div>
                    <div class="faq-item"><button class="faq-q" type="button">When do I get paid?<span class="ic"><i class="fas fa-plus"></i></span></button><div class="faq-a"><div class="faq-a-inner">Payouts are released to your nominated account 48 hours after each event takes place. Funds are confirmed at the point of booking, so there's nothing to chase.</div></div></div>
                    <div class="faq-item"><button class="faq-q" type="button">How are enquiries matched to me?<span class="ic"><i class="fas fa-plus"></i></span></button><div class="faq-a"><div class="faq-a-inner">By your categories, service area and availability — you only see jobs that fit, and you're never charged to receive or reply.</div></div></div>
                </div>
            </div>
        </div>
    </section>

    <section class="section" style="text-align:center" id="trust">
        <div class="container">
            <p class="eyebrow centered">Still stuck?</p>
            <h2 class="heading">We're a quick message away</h2>
            <p class="lead" style="margin:0 auto 26px">Our team replies within one business day — usually much sooner.</p>
            <a href="/contact" class="btn btn-primary btn-lg">Contact support</a>
        </div>
    </section>
</main>
</div>

<?= $this->include('footer') ?>
