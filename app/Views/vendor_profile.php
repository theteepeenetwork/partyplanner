<?= $this->include('header') ?>
<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/assets/css/service-view.css">
<style>body { background: #F6F1EB; }</style>

<div class="sv">
  <div class="sv-page-inner" style="max-width:880px;margin:0 auto;padding:34px 40px 80px">

    <div class="sv-crumb">
      <a href="/">Home</a><span class="sep">/</span>
      <a href="/browse-services">Suppliers</a><span class="sep">/</span>
      <b><?= esc($vendor_profile['name']) ?></b>
    </div>

    <!-- Host card -->
    <section style="margin-top:22px">
      <div class="sv-panel sv-host">
        <div class="sv-host-head">
          <?php if (!empty($vendor_profile['photo_path'])): ?>
            <img src="<?= base_url(esc($vendor_profile['photo_path'])) ?>"
                 alt="<?= esc($vendor_profile['name']) ?>"
                 class="sv-host-ava"
                 onerror="this.style.display='none'">
          <?php else: ?>
            <div class="sv-host-ava-initials">
              <?= esc(strtoupper(substr($vendor_profile['name'], 0, 1))) ?>
            </div>
          <?php endif; ?>

          <div class="sv-host-id">
            <div class="sv-host-name"><?= esc($vendor_profile['name']) ?></div>
            <?php if (!empty($vendor_profile['tagline'])): ?>
              <div class="sv-host-role"><?= esc($vendor_profile['tagline']) ?></div>
            <?php endif; ?>
            <?php if (!empty($vendor_rating) && $vendor_rating['count'] > 0): ?>
              <div class="sv-rate-row" style="margin-top:8px">
                <span class="sv-stars"><?= view('partials/sv_stars', ['rating' => $vendor_rating['avg']]) ?></span>
                <b><?= esc(number_format((float) $vendor_rating['avg'], 1)) ?></b>
                <span>·</span>
                <span><?= (int) $vendor_rating['count'] ?> review<?= $vendor_rating['count'] === 1 ? '' : 's' ?></span>
              </div>
            <?php endif; ?>
            <?php if (!empty($vendor_profile['since'])): ?>
              <div class="sv-host-meta">Member since <?= esc((string) $vendor_profile['since']) ?></div>
            <?php endif; ?>
          </div>
        </div>

        <?php if (!empty($vendor_profile['bio'])): ?>
          <p class="sv-host-bio"><?= nl2br(esc($vendor_profile['bio'])) ?></p>
        <?php endif; ?>

        <?php if (!empty($vendor_profile['plays'])): ?>
          <div class="sv-host-plays">
            <span class="sv-host-plays-label">Plays</span>
            <?php foreach ($vendor_profile['plays'] as $playTag): ?>
              <span class="sv-tag"><?= esc($playTag) ?></span>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>

        <?php if (!empty($vendor_profile['quote'])): ?>
          <blockquote class="sv-host-quote"><?= esc($vendor_profile['quote']) ?></blockquote>
        <?php endif; ?>
      </div>
    </section>

    <!-- Services -->
    <?php if (!empty($services)): ?>
    <section style="margin-top:40px">
      <h2 class="sv-section-label">Services</h2>
      <div style="display:flex;flex-direction:column;gap:12px">
        <?php foreach ($services as $svc): ?>
          <a href="<?= site_url('service/view/' . (int) $svc['id']) ?>"
             style="display:flex;align-items:center;gap:16px;background:#fff;border-radius:12px;padding:14px 18px;text-decoration:none;color:inherit;box-shadow:0 1px 4px rgba(0,0,0,.07);transition:box-shadow .15s"
             onmouseover="this.style.boxShadow='0 3px 12px rgba(0,0,0,.13)'"
             onmouseout="this.style.boxShadow='0 1px 4px rgba(0,0,0,.07)'">
            <?php if (!empty($svc['images'])): ?>
              <img src="<?= base_url(esc($svc['images'][0]['thumbnail_path'])) ?>"
                   alt="<?= esc($svc['title']) ?>"
                   style="width:72px;height:72px;object-fit:cover;border-radius:8px;flex-shrink:0"
                   onerror="this.onerror=null;this.src='<?= base_url('assets/images/fallback-service-card.jpg') ?>'">
            <?php else: ?>
              <img src="<?= base_url('assets/images/fallback-service-card.jpg') ?>"
                   alt=""
                   style="width:72px;height:72px;object-fit:cover;border-radius:8px;flex-shrink:0">
            <?php endif; ?>
            <div style="flex:1;min-width:0">
              <div style="font-weight:700;font-size:15px;margin-bottom:3px"><?= esc($svc['title']) ?></div>
              <?php if (!empty($svc['short_description'])): ?>
                <div style="font-size:13px;color:#5F5853;white-space:nowrap;overflow:hidden;text-overflow:ellipsis"><?= esc($svc['short_description']) ?></div>
              <?php endif; ?>
            </div>
            <div style="flex-shrink:0;font-weight:700;font-size:15px;color:#1a1a1a">
              <?php if ((float) ($svc['price'] ?? 0) > 0): ?>
                From &pound;<?= number_format((float) $svc['price'], 2) ?>
              <?php else: ?>
                <span style="color:#888;font-weight:400">Price on request</span>
              <?php endif; ?>
            </div>
          </a>
        <?php endforeach; ?>
      </div>
    </section>
    <?php endif; ?>

    <!-- All reviews across services -->
    <section style="margin-top:40px">
      <h2 class="sv-section-label">Reviews</h2>
      <?php if (empty($reviews)): ?>
        <p class="sv-body" style="color:#5F5853">No reviews yet.</p>
      <?php else: ?>
        <div style="display:flex;flex-direction:column;gap:14px">
          <?php foreach ($reviews as $rv): ?>
            <div class="sv-review">
              <div class="sv-stars"><?= view('partials/sv_stars', ['rating' => (int) $rv['rating']]) ?></div>
              <?php if (!empty($rv['title'])): ?>
                <div style="font-weight:700;margin:8px 0 4px"><?= esc($rv['title']) ?></div>
              <?php endif; ?>
              <p class="sv-review-quote">"<?= esc($rv['comment']) ?>"</p>
              <div class="sv-review-by">
                <b><?= esc($rv['customer_name'] ?? 'Verified customer') ?></b>
                <?php if (!empty($rv['service_title'])): ?>
                  · <?= esc($rv['service_title']) ?>
                <?php endif; ?>
                <?php if (!empty($rv['event_type'])): ?>
                  · <?= esc($rv['event_type']) ?>
                <?php endif; ?>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </section>

  </div>
</div>

<?= $this->include('footer') ?>
