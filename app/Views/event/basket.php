<?= $this->include('header') ?>

<div class="ps-app">
<main>
  <section class="page-head">
    <div class="container">
      <div class="breadcrumb">
        <a href="/profile/events">My events</a><span class="sep">/</span>
        <a href="#"><?= esc($event['title']) ?></a><span class="sep">/</span>
        <span class="cur">Basket</span>
      </div>
      <p class="eyebrow">Review &amp; book</p>
      <h1 class="heading">Your basket</h1>
      <p class="ph-lead">
        <?php $count = count($basketItems ?? []); ?>
        <?= $count ?> <?= $count === 1 ? 'supplier' : 'suppliers' ?> for
        <b style="color:var(--cream)"><?= esc($event['title']) ?></b><?php if (!empty($event['date'])): ?> · <?= esc(date('D d M Y', strtotime($event['date']))) ?><?php endif; ?>.
        Your payment stays protected until 48 hours after the day.
      </p>
    </div>
  </section>

  <section class="section" style="padding-block:clamp(40px,5vw,64px)">
    <div class="container">

      <?php if (session()->getFlashdata('success')): ?>
        <div class="form-alert ok"><?= session()->getFlashdata('success') ?></div>
      <?php endif; ?>
      <?php if (session()->getFlashdata('info')): ?>
        <div class="form-alert"><?= esc(session()->getFlashdata('info')) ?></div>
      <?php endif; ?>
      <?php if (session()->getFlashdata('error')): ?>
        <div class="form-alert error"><?= session()->getFlashdata('error') ?></div>
      <?php endif; ?>

      <?php if (!empty($basketItems)): ?>
        <div class="basket-grid">

          <!-- items -->
          <div class="panel">
            <div class="panel-head">
              <h2><?= $count ?> <?= $count === 1 ? 'supplier' : 'suppliers' ?></h2>
              <a href="/browse-services" class="linkmore">Add another <i class="fas fa-plus"></i></a>
            </div>
            <div class="panel-pad">
              <?php foreach ($basketItems as $item): ?>
                <div class="basket-item">
                  <?php if (!empty($item['thumbnail_path'])): ?>
                    <img src="<?= base_url($item['thumbnail_path']) ?>" alt="<?= esc($item['service_title']) ?>">
                  <?php else: ?>
                    <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='1' height='1'%3E%3C/svg%3E" alt="" style="display:flex;align-items:center;justify-content:center;background:var(--surface-warm,#f1ece4);">
                  <?php endif; ?>
                  <div>
                    <div class="bi-cat"><i class="fas fa-store"></i> <?= esc($item['vendor_name']) ?></div>
                    <h4><?= esc($item['service_title']) ?></h4>
                    <div class="bi-meta">
                      <?php if (!empty($item['option_label'])): ?>
                        <span><i class="fas fa-tag"></i> <?= esc($item['option_label']) ?></span>
                      <?php endif; ?>
                      <?php if (!empty($item['service_description'])): ?>
                        <span><?= esc(substr($item['service_description'], 0, 100)) ?></span>
                      <?php endif; ?>
                    </div>
                    <?php if (!empty($item['quote_detail']['lines'])): ?>
                      <div class="bi-meta" style="flex-direction:column;align-items:flex-start;gap:2px;margin-top:6px">
                        <?php foreach ($item['quote_detail']['lines'] as $line): ?>
                          <span><?= esc($line['label'] ?? '') ?> — £<?= number_format((float) ($line['amount'] ?? 0), 2) ?></span>
                        <?php endforeach; ?>
                        <?php if (isset($item['quote_detail']['distance_km']) && $item['quote_detail']['distance_km'] !== null): ?>
                          <span>Road distance (approx.): <?= esc($item['quote_detail']['distance_km']) ?> km</span>
                        <?php endif; ?>
                      </div>
                    <?php endif; ?>
                    <div class="bi-actions">
                      <a href="/event/basket/remove/<?= $item['id'] ?>" class="del" onclick="return confirm('Remove this service?');"><i class="fas fa-trash"></i> Remove</a>
                    </div>
                  </div>
                  <div class="bi-price">
                    <?php if (($item['package_name'] ?? '') === 'Price on request' || ((float) $item['estimated_total'] <= 0 && (float) $item['deposit_amount'] <= 0)): ?>
                      <div class="n">Price on request</div>
                      <span style="font-size:12.5px;color:var(--ink-faint)">Supplier will quote</span>
                    <?php else: ?>
                      <div class="n">£<?= number_format($item['estimated_total'], 2) ?></div>
                      <span style="font-size:12.5px;color:var(--ink-faint)">Deposit £<?= number_format($item['deposit_amount'], 2) ?></span>
                    <?php endif; ?>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          </div>

          <!-- summary -->
          <div class="summary">
            <div class="summary-body">
              <h3>Order summary</h3>
              <?php foreach ($basketItems as $item): ?>
                <div class="sum-row">
                  <span><?= esc($item['service_title']) ?></span>
                  <?php if (($item['package_name'] ?? '') === 'Price on request' || ((float) $item['estimated_total'] <= 0 && (float) $item['deposit_amount'] <= 0)): ?>
                    <b>Price on request</b>
                  <?php else: ?>
                    <b>£<?= number_format($item['estimated_total'], 2) ?></b>
                  <?php endif; ?>
                </div>
              <?php endforeach; ?>
              <div class="sum-row"><span>Booking fee</span><b style="color:var(--green-bright)">£0.00</b></div>
              <div class="sum-row total"><span>Estimated total</span><span class="n">£<?= number_format($totalEstimated, 2) ?></span></div>
              <div class="sum-row"><span>Deposit due today (<?= (int) ($depositPercent ?? 10) ?>%)</span><b style="color:var(--green-bright)">£<?= number_format($totalDeposit, 2) ?></b></div>
              <a href="/event/checkout/<?= $event['id'] ?>" class="btn btn-primary btn-block btn-lg" style="margin-top:16px"><i class="fas fa-lock"></i> Proceed to checkout</a>
              <p style="text-align:center;font-size:12.5px;color:var(--ink-faint);margin:12px 0 0">Cards, Apple Pay &amp; Google Pay accepted</p>
            </div>
            <div class="summary-foot">
              <i class="fas fa-shield-halved"></i>
              <span>Your deposit is held securely and only released to suppliers 48 hours after your event. Full refund if a supplier cancels.</span>
            </div>
          </div>

        </div>
      <?php else: ?>
        <div class="panel">
          <div class="panel-pad" style="text-align:center;padding-block:clamp(40px,6vw,72px)">
            <i class="fas fa-shopping-basket" style="font-size:42px;color:var(--ink-faint);margin-bottom:16px"></i>
            <h2>Your basket is empty</h2>
            <p class="lead">Browse services and add them to this event.</p>
            <a href="/browse-services" class="btn btn-primary btn-lg" style="margin-top:8px"><i class="fas fa-plus"></i> Browse services</a>
          </div>
        </div>
      <?php endif; ?>

    </div>
  </section>
</main>
</div>

<?= $this->include('footer') ?>
