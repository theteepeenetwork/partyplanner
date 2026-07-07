<?php

use App\Libraries\TenantHost;

/**
 * White-label tenant layout — footer (T4).
 *
 * The one place PartySmith appears on a tenant page: a discreet
 * "Powered by PartySmith" line. No marketplace nav, no browse links.
 */
$site ??= service('tenant')->site() ?? [];

$businessName = trim((string) ($site['business_name'] ?? '')) ?: 'Storefront';
$phone        = trim((string) ($site['phone'] ?? ''));
$phoneHref    = $phone !== '' ? 'tel:' . preg_replace('/[^0-9+]/', '', $phone) : '';

$poweredByUrl = 'https://www.' . TenantHost::baseDomain();
?>
    </div><!-- /.ps-app -->
    </div><!-- /#main-content -->

    <footer class="site-footer mt-0">
        <div class="container py-4">
            <div class="foot-bottom">
                <p>
                    &copy; <?= date('Y') ?> <?= esc($businessName) ?>
                    <?php if ($phone !== ''): ?>
                        &middot; <a href="<?= esc($phoneHref, 'attr') ?>" style="color: inherit;"><?= esc($phone) ?></a>
                    <?php endif; ?>
                </p>
                <div class="foot-legal">
                    <a href="<?= esc($poweredByUrl, 'attr') ?>" rel="nofollow">Powered by PartySmith</a>
                </div>
            </div>
        </div>
    </footer>
</body>

</html>
