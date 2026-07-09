<?php

use App\Libraries\TenantHost;

/**
 * White-label tenant layout — footer.
 *
 * Dark neutral ground. The phone's second (and last) appearance, and the one
 * place Partysmith appears on a tenant page — muted "Powered by" line.
 */
$site ??= service('tenant')->site() ?? [];

$businessName = trim((string) ($site['business_name'] ?? '')) ?: 'Storefront';
$phone        = trim((string) ($site['phone'] ?? ''));
$phoneHref    = $phone !== '' ? 'tel:' . preg_replace('/[^0-9+]/', '', $phone) : '';

$poweredByUrl = 'https://www.' . TenantHost::baseDomain();
?>
    </main>

    <script>
    /* Gallery mosaic: clicking (or Enter/Space on) a small tile promotes its
       photo to the big cell by swapping src+alt with the current big image.
       Delegated once here so every tenant page's mosaic gets the behaviour. */
    (function () {
        function swap(cell) {
            var mosaic = cell.closest('.sf-mosaic');
            var bigImg = mosaic && mosaic.querySelector('.cell.big img');
            var img = cell.querySelector('img');
            if (!bigImg || !img || bigImg === img) return;
            var src = bigImg.src, alt = bigImg.alt;
            bigImg.src = img.src; bigImg.alt = img.alt;
            img.src = src; img.alt = alt;
        }
        document.addEventListener('click', function (e) {
            var cell = e.target.closest('.sf-mosaic .cell:not(.big)');
            if (cell) swap(cell);
        });
        document.addEventListener('keydown', function (e) {
            if (e.key !== 'Enter' && e.key !== ' ') return;
            var cell = e.target.closest ? e.target.closest('.sf-mosaic .cell:not(.big)') : null;
            if (cell) { e.preventDefault(); swap(cell); }
        });
    })();
    </script>

    <footer class="sf-foot">
        <div class="sf-shell sf-foot-in">
            <p style="margin: 0;">
                &copy; <?= date('Y') ?> <?= esc($businessName) ?>
                <?php if ($phone !== ''): ?>
                    &middot; <a href="<?= esc($phoneHref, 'attr') ?>"><?= esc($phone) ?></a>
                <?php endif; ?>
            </p>
            <p class="powered" style="margin: 0;">
                <a href="<?= esc($poweredByUrl, 'attr') ?>" rel="nofollow">Powered by Partysmith</a>
            </p>
        </div>
    </footer>
</body>

</html>
