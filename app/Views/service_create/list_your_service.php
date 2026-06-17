<?php
/**
 * Partysmith · Vendor onboarding — "List your service"
 *
 * Adaptive, supplier-type-aware listing builder (Coral & Marigold brand).
 * Standalone full-bleed page: the design replaces the sitewide header/footer
 * with its own fixed rail + live-preview column, so this view does not pull in
 * header.php / footer.php. State + behaviour live in /assets/js/onboarding.js;
 * "Publish" POSTs to Service_Controller::publishListing().
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<meta name="theme-color" content="#2A2026" />
<title>Partysmith — List your service</title>
<link rel="icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 64 64'%3E%3Crect width='64' height='64' rx='14' fill='%23D8503C'/%3E%3Ctext x='50%25' y='55%25' text-anchor='middle' dominant-baseline='middle' font-family='Arial' font-weight='700' font-size='30' letter-spacing='-2' fill='%23F6F1E9'%3EP%3Ctspan fill='%232A2026'%3E.%3C/tspan%3ES%3C/text%3E%3C/svg%3E" />
<link rel="preconnect" href="https://fonts.googleapis.com" />
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
<link href="https://fonts.googleapis.com/css2?family=Hanken+Grotesk:wght@400;500;600;700;800&family=Mr+Dafoe&display=swap" rel="stylesheet" />
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet" />
<link rel="stylesheet" href="<?= base_url('assets/css/onboarding.css') ?>" />
</head>
<body>
<div id="root">
  <noscript>
    <div style="max-width:560px;margin:80px auto;padding:0 24px;text-align:center">
      <h1 style="letter-spacing:-0.025em">List your service</h1>
      <p style="color:rgba(42,32,38,0.62)">This guided listing builder needs JavaScript enabled.
      Please turn on JavaScript and reload, or <a href="<?= base_url('profile') ?>">return to your dashboard</a>.</p>
    </div>
  </noscript>
</div>

<script>
  window.PS_ONBOARD = {
    publishUrl: <?= json_encode(base_url('service/publish-listing')) ?>,
    viewBase:   <?= json_encode(base_url('service/view/')) ?>,
    browseUrl:  <?= json_encode(base_url('browse-services')) ?>,
    exitUrl:    <?= json_encode(base_url('profile')) ?>,
    assetsBase: <?= json_encode(base_url('assets/images/')) ?>,
    csrfName:   <?= json_encode(csrf_token()) ?>,
    csrfHash:   <?= json_encode(csrf_hash()) ?>
  };
</script>
<script src="<?= base_url('assets/js/onboarding.js') ?>"></script>
</body>
</html>
