<?php
$footerClass = 'site-footer mt-0';
if (! empty($isHomePage)) {
    $footerClass .= ' site-footer--home';
}
$vendorDashboardUrl = '/register/vendor';
if (session()->has('user_id') && session()->get('role') === 'vendor') {
    $vendorDashboardUrl = '/profile/services';
}
$startPlanningUrl = session()->has('user_id') ? '/event/create' : '/register';
?>
</div>
<footer class="<?= $footerClass ?>">
    <div class="container py-5">
        <div class="foot-grid">
            <div class="foot-brandcol">
                <a class="footer-brand" href="/">
                    <span class="ps">P<span class="dot">.</span>S<span class="dot">.</span></span>
                    <span class="name">Partysmith</span>
                </a>
                <p class="foot-about">
                    The UK marketplace to find, compare and book vetted event suppliers — from weddings and christenings to birthdays and corporate occasions.
                </p>
                <p class="foot-contact"><b>hello@partysmith.co.uk</b><br>Replies within one business day</p>
            </div>

            <div class="foot-col">
                <h5>Plan</h5>
                <ul class="foot-links list-unstyled">
                    <li><a href="/browse-services">Browse categories</a></li>
                    <li><a href="/browse-services">Find suppliers</a></li>
                    <li><a href="/how-it-works">How it works</a></li>
                    <li><a href="<?= esc($startPlanningUrl) ?>">Start planning</a></li>
                </ul>
            </div>

            <div class="foot-col">
                <h5>For suppliers</h5>
                <ul class="foot-links list-unstyled">
                    <li><a href="/register/vendor">List your business</a></li>
                    <li><a href="<?= esc($vendorDashboardUrl) ?>">Supplier dashboard</a></li>
                    <li><a href="/vendor-info">Pricing</a></li>
                    <li><a href="/vendor-info">Supplier support</a></li>
                </ul>
            </div>

            <div class="foot-col">
                <h5>Company</h5>
                <ul class="foot-links list-unstyled">
                    <li><a href="/about">About us</a></li>
                    <li><a href="/how-it-works">How we vet</a></li>
                    <li><a href="/faq">Help centre</a></li>
                    <li><a href="/contact">Contact</a></li>
                </ul>
            </div>
        </div>

        <div class="foot-bottom">
            <p>&copy; <?= date('Y') ?> Partysmith Ltd &middot; Registered in England &amp; Wales</p>
            <div class="foot-legal">
                <a href="/contact">Terms</a>
                <a href="/contact">Privacy</a>
                <a href="/contact">Complaints</a>
            </div>
        </div>
    </div>
</footer>
