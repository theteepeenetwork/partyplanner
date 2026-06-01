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
      <div style="display:flex;flex-wrap:wrap;gap:10px">
        <?php foreach ($services as $svc): ?>
          <a href="<?= site_url('service/view/' . (int) $svc['id']) ?>" class="sv-tag" style="text-decoration:none">
            <?= esc($svc['title']) ?>
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
