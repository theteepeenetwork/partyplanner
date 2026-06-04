<?php
$footerClass = 'site-footer mt-0';
if (! empty($isHomePage)) {
    $footerClass .= ' site-footer--home';
}
$vendorDashboardUrl = '/register/vendor';
if (session()->has('user_id') && session()->get('role') === 'vendor') {
    $vendorDashboardUrl = '/profile/services';
}
?>
</div>
<footer class="<?= $footerClass ?>">
    <div class="container p-4 py-lg-5">
        <div class="row g-4">
            <div class="col-lg-4 col-md-6">
                <a class="brand d-inline-flex mb-3" href="/" style="color:inherit;text-decoration:none">
                    <span class="ps">P<span class="dot">.</span>S<span class="dot">.</span></span>
                    <span class="name">Partysmith</span>
                </a>
                <p class="site-footer-muted">
                    A UK marketplace to discover event services, request quotes and manage bookings—from weddings and christenings to birthdays and corporate occasions.
                </p>
                <p class="site-footer-script mt-2 mb-0">P.S. leave the planning to us.</p>
            </div>

            <div class="col-lg-2 col-md-6">
                <h5>Quick links</h5>
                <ul class="list-unstyled site-footer-links">
                    <li class="mb-2"><a href="/browse-services">Find suppliers</a></li>
                    <li class="mb-2"><a href="/how-it-works">How it works</a></li>
                    <li class="mb-2"><a href="/browse-services">Inspiration</a></li>
                    <li class="mb-2"><a href="/login">My account</a></li>
                </ul>
            </div>

            <div class="col-lg-3 col-md-6">
                <h5>Popular services</h5>
                <ul class="list-unstyled site-footer-links">
                    <li class="mb-2"><a href="/browse-services?q=photography">Photography &amp; video</a></li>
                    <li class="mb-2"><a href="/browse-services?q=catering">Catering &amp; drinks</a></li>
                    <li class="mb-2"><a href="/browse-services?q=venue">Venues</a></li>
                    <li class="mb-2"><a href="/browse-services?q=entertainment">Entertainment</a></li>
                </ul>
            </div>

            <div class="col-lg-3 col-md-6">
                <h5>For vendors</h5>
                <ul class="list-unstyled site-footer-links">
                    <li class="mb-2"><a href="/register/vendor">Become a vendor</a></li>
                    <li class="mb-2"><a href="<?= esc($vendorDashboardUrl) ?>">Vendor dashboard</a></li>
                    <li class="mb-2"><a href="/vendor-info">Supplier support</a></li>
                </ul>
            </div>
        </div>

        <div class="row g-4 mt-2 pt-3 site-footer-divider">
            <div class="col-md-8">
                <h5>Contact</h5>
                <p class="site-footer-muted mb-2">
                    <a href="/contact" class="site-footer-link">Get in touch</a> for help with services, quotes or listing your business.
                </p>
            </div>
            <div class="col-md-4">
                <h5>Follow us</h5>
                <div class="site-footer-social">
                    <a href="https://www.instagram.com/" class="site-footer-social-link" rel="noopener noreferrer" target="_blank" aria-label="Instagram">
                        <i class="fab fa-instagram" aria-hidden="true"></i>
                    </a>
                    <a href="https://www.facebook.com/" class="site-footer-social-link" rel="noopener noreferrer" target="_blank" aria-label="Facebook">
                        <i class="fab fa-facebook-f" aria-hidden="true"></i>
                    </a>
                    <a href="https://www.pinterest.com/" class="site-footer-social-link" rel="noopener noreferrer" target="_blank" aria-label="Pinterest">
                        <i class="fab fa-pinterest-p" aria-hidden="true"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
    <div class="site-footer-bottom text-center p-3">
        <p class="mb-0 small">&copy; <?= date('Y') ?> Partysmith. All rights reserved.</p>
    </div>
</footer>
