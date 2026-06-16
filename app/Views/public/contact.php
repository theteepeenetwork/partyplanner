<?= $this->include('header') ?>

<div class="ps-app">
<main data-screen-label="Contact">
    <section class="page-head page-head--tall">
        <div class="container">
            <div class="breadcrumb"><a href="/">Home</a><span class="sep">/</span><span class="cur">Contact</span></div>
            <p class="eyebrow">Contact us</p>
            <h1>Talk to a real person</h1>
            <p class="ph-lead">Whether you're planning an event or thinking about listing your business, we'd love to hear from you. We reply within one business day.</p>
        </div>
    </section>

    <section class="section">
        <div class="container">
            <div class="dash-cols" style="grid-template-columns:1fr 380px">
                <div class="panel">
                    <div class="panel-head"><h2>Send us a message</h2></div>
                    <div class="panel-pad">
                        <?php if (session()->getFlashdata('contactSuccess')): ?>
                            <div class="form-alert ok"><i class="fas fa-circle-check"></i> <?= esc(session()->getFlashdata('contactSuccess')) ?></div>
                        <?php endif; ?>
                        <?php if (session()->getFlashdata('errors')): ?>
                            <div class="form-alert error">
                                <ul><?php foreach ((array) session()->getFlashdata('errors') as $error): ?><li><?= esc($error) ?></li><?php endforeach; ?></ul>
                            </div>
                        <?php endif; ?>

                        <form class="form-grid two" action="/contact" method="post">
                            <?= csrf_field() ?>
                            <div class="field-row"><label for="first_name">First name</label><input class="input" type="text" id="first_name" name="first_name" value="<?= esc(old('first_name') ?? '') ?>" placeholder="Your first name"></div>
                            <div class="field-row"><label for="last_name">Last name</label><input class="input" type="text" id="last_name" name="last_name" value="<?= esc(old('last_name') ?? '') ?>" placeholder="Your last name"></div>
                            <div class="field-row"><label for="email">Email</label><div class="input-icon"><i class="fas fa-envelope"></i><input class="input" type="email" id="email" name="email" value="<?= esc(old('email') ?? '') ?>" placeholder="you@email.com" required></div></div>
                            <div class="field-row"><label for="i_am">I'm a…</label>
                                <select class="select-full" id="i_am" name="i_am">
                                    <option>Host planning an event</option>
                                    <option>Supplier / vendor</option>
                                    <option>Press or partnerships</option>
                                    <option>Something else</option>
                                </select>
                            </div>
                            <div class="field-row span2"><label for="topic">What's it about?</label>
                                <select class="select-full" id="topic" name="topic">
                                    <option>A booking or quote</option>
                                    <option>Payments &amp; refunds</option>
                                    <option>Becoming a supplier</option>
                                    <option>Trust &amp; safety</option>
                                    <option>General enquiry</option>
                                </select>
                            </div>
                            <div class="field-row span2"><label for="message">Message</label><textarea class="textarea" id="message" name="message" placeholder="Tell us how we can help…" style="min-height:130px" required><?= esc(old('message') ?? '') ?></textarea></div>
                            <div class="field-row span2">
                                <label class="check-line"><input type="checkbox" name="agree_privacy" required> <span>I agree to Partysmith's <a href="/contact">privacy policy</a> and to being contacted about my enquiry.</span></label>
                            </div>
                            <div class="field-row span2"><button class="btn btn-primary btn-lg" type="submit">Send message <i class="fas fa-paper-plane"></i></button></div>
                        </form>
                    </div>
                </div>

                <div>
                    <div class="widget">
                        <h3>Other ways to reach us</h3>
                        <div class="task-line" style="border:none"><span class="tk" style="border:none;background:rgba(28,74,54,0.08);color:var(--green)"><i class="fas fa-envelope"></i></span><span><b style="display:block;color:var(--ink)">hello@partysmith.co.uk</b>General &amp; host enquiries</span></div>
                        <div class="task-line"><span class="tk" style="border:none;background:rgba(28,74,54,0.08);color:var(--green)"><i class="fas fa-store"></i></span><span><b style="display:block;color:var(--ink)">suppliers@partysmith.co.uk</b>Become a supplier</span></div>
                        <div class="task-line"><span class="tk" style="border:none;background:rgba(28,74,54,0.08);color:var(--green)"><i class="fas fa-phone"></i></span><span><b style="display:block;color:var(--ink)">0113 000 0000</b>Mon–Fri, 9am–6pm</span></div>
                    </div>
                    <div class="widget">
                        <h3>Visit</h3>
                        <p style="margin:0 0 6px;color:var(--ink-soft);font-size:14.5px;line-height:1.6">Partysmith Ltd<br>Duncan Street<br>Leeds, LS1 6DQ</p>
                        <p style="margin:0;color:var(--ink-faint);font-size:13px"><i class="fas fa-location-dot" style="color:var(--green);margin-right:6px"></i> Founding home in Yorkshire</p>
                    </div>
                    <div class="widget dark">
                        <h3 style="color:var(--cream)">Looking for a quick answer?</h3>
                        <p style="margin:0 0 16px;font-size:14px">Our help centre covers payments, cancellations and vetting.</p>
                        <a href="/faq" class="btn btn-gold btn-block">Browse the help centre</a>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>
</div>

<?= $this->include('footer') ?>
