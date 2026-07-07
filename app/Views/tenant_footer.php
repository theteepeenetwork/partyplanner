<?php

use App\Libraries\TenantHost;

/**
 * White-label tenant layout — footer.
 *
 * Dark neutral ground. The phone's second (and last) appearance, and the one
 * place PartySmith appears on a tenant page — muted "Powered by" line.
 */
$site ??= service('tenant')->site() ?? [];

$businessName = trim((string) ($site['business_name'] ?? '')) ?: 'Storefront';
$phone        = trim((string) ($site['phone'] ?? ''));
$phoneHref    = $phone !== '' ? 'tel:' . preg_replace('/[^0-9+]/', '', $phone) : '';

$poweredByUrl = 'https://www.' . TenantHost::baseDomain();
?>
    </main>

    <footer class="sf-foot">
        <div class="sf-shell sf-foot-in">
            <p style="margin: 0;">
                &copy; <?= date('Y') ?> <?= esc($businessName) ?>
                <?php if ($phone !== ''): ?>
                    &middot; <a href="<?= esc($phoneHref, 'attr') ?>"><?= esc($phone) ?></a>
                <?php endif; ?>
            </p>
            <p class="powered" style="margin: 0;">
                <a href="<?= esc($poweredByUrl, 'attr') ?>" rel="nofollow">Powered by PartySmith</a>
            </p>
        </div>
    </footer>
</body>

</html>
