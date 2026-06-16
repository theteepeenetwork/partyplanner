<?= $this->include('header') ?>
<div class="ps-app">
<main class="dash" data-screen-label="My events dashboard">
<?php
    // ---- Derive real aggregates from the enriched $events ----
    $events = $events ?? [];

    $activeEvents = [];
    $pastEvents   = [];
    foreach ($events as $ev) {
        $d = $ev['days'] ?? null;
        if ($d !== null && $d < 0) {
            $pastEvents[] = $ev;
        } else {
            $activeEvents[] = $ev;
        }
    }

    $activeCount    = count($activeEvents);
    $suppliersBooked = 0;
    $pendingQuotes   = 0;
    $protectedHolding = 0.0;
    foreach ($events as $ev) {
        $suppliersBooked  += (int) ($ev['services_booked'] ?? $ev['servicesBooked'] ?? 0);
        $pendingQuotes    += (int) ($ev['pending_count'] ?? 0);
        $protectedHolding += (float) ($ev['total_cost'] ?? $ev['totalCost'] ?? 0);
    }

    $firstName = trim((string) ($user['name'] ?? ''));
    $firstName = $firstName !== '' ? explode(' ', $firstName)[0] : 'there';

    $maxSuppliers = 8; // planning target per event for the progress meter

    $badgeFor = static function (array $ev): array {
        $booked  = (int) ($ev['services_booked'] ?? $ev['servicesBooked'] ?? 0);
        $pending = (int) ($ev['pending_count'] ?? 0);
        if ($booked > 0) {
            return ['green', 'fa-circle-check', 'Booking confirmed'];
        }
        if ($pending > 0) {
            return ['gold', 'fa-clock', 'Quotes to review'];
        }
        return ['grey', 'fa-file-lines', 'Planning'];
    };
?>
  <section class="dash-head">
    <div class="container">
      <div class="dash-hello">
        <div>
          <p class="eyebrow on-dark">Your dashboard</p>
          <h1>Welcome back, <?= esc($firstName) ?></h1>
          <p class="dh-sub">
            <?= $activeCount ?> <?= $activeCount === 1 ? 'event' : 'events' ?> in planning
            <?php if ($pendingQuotes > 0): ?>
              &middot; <?= $pendingQuotes ?> <?= $pendingQuotes === 1 ? 'quote' : 'quotes' ?> to review
            <?php endif; ?>
          </p>
        </div>
        <a href="/event/create" class="btn btn-gold btn-lg"><i class="fas fa-plus"></i> Plan a new event</a>
      </div>
    </div>
  </section>

  <div class="dash-body">
    <div class="container">
      <?= $this->include('dashboard/_customer_tabs') ?>
      <?= $this->include('dashboard/_flash_alerts') ?>

      <!-- stats -->
      <div class="stat-grid">
        <div class="stat">
          <div class="st-top"><span class="st-ic"><i class="fas fa-calendar"></i></span><span class="st-delta flat">planning</span></div>
          <div class="st-n"><?= $activeCount ?></div>
          <div class="st-l">Active events</div>
        </div>
        <div class="stat">
          <div class="st-top"><span class="st-ic"><i class="fas fa-file-lines"></i></span>
            <?php if ($pendingQuotes > 0): ?><span class="st-delta up"><?= $pendingQuotes ?> new</span><?php else: ?><span class="st-delta flat">all clear</span><?php endif; ?>
          </div>
          <div class="st-n"><?= $pendingQuotes ?></div>
          <div class="st-l">Quotes to review</div>
        </div>
        <div class="stat">
          <div class="st-top"><span class="st-ic"><i class="fas fa-circle-check"></i></span><span class="st-delta flat">on track</span></div>
          <div class="st-n"><?= $suppliersBooked ?></div>
          <div class="st-l">Suppliers booked</div>
        </div>
        <div class="stat">
          <div class="st-top"><span class="st-ic"><i class="fas fa-shield-halved"></i></span><span class="st-delta flat">held</span></div>
          <div class="st-n">£<?= number_format($protectedHolding) ?></div>
          <div class="st-l">Protected in holding</div>
        </div>
      </div>

      <div class="dash-cols">
        <!-- main column -->
        <div data-tabs>
          <div class="dash-tabbar">
            <div class="tabnav">
              <button data-panel="active" class="on"><i class="fas fa-calendar"></i> Active <span class="badge green" style="margin-left:2px"><?= $activeCount ?></span></button>
              <button data-panel="past"><i class="fas fa-clock"></i> Past <span class="badge grey" style="margin-left:2px"><?= count($pastEvents) ?></span></button>
            </div>
          </div>

          <!-- active events -->
          <div class="tabpanel show" data-panel="active">
            <?php if (!empty($activeEvents)): ?>
              <?php foreach ($activeEvents as $i => $event):
                  $booked = (int) ($event['services_booked'] ?? $event['servicesBooked'] ?? 0);
                  $pct    = $maxSuppliers > 0 ? min(100, (int) round($booked / $maxSuppliers * 100)) : 0;
                  $days   = $event['days'] ?? null;
                  $cost   = (float) ($event['total_cost'] ?? $event['totalCost'] ?? 0);
                  [$bClass, $bIcon, $bLabel] = $badgeFor($event);
                  $dd = !empty($event['date']) ? date('d', strtotime($event['date'])) : '--';
                  $mm = !empty($event['date']) ? date('M', strtotime($event['date'])) : '';
                  $guests = (int) ($event['guest_count'] ?? 0);
              ?>
                <div class="panel"<?= $i < count($activeEvents) - 1 ? ' style="margin-bottom:22px"' : '' ?>>
                  <div class="panel-head">
                    <div style="display:flex;align-items:center;gap:14px">
                      <span class="erow-date"><span class="d"><?= esc($dd) ?></span><span class="m"><?= esc($mm) ?></span></span>
                      <div>
                        <h2 style="font-size:22px"><?= esc($event['title']) ?></h2>
                        <div class="ph-meta" style="margin-top:3px">
                          <i class="fas fa-location-dot" style="color:var(--green)"></i>
                          <?= esc($event['location'] ?? 'Location TBC') ?>
                          <?php if ($guests > 0): ?> &middot; <?= $guests ?> guests<?php endif; ?>
                        </div>
                      </div>
                    </div>
                    <span class="badge <?= $bClass ?>"><i class="fas <?= $bIcon ?>"></i> <?= esc($bLabel) ?></span>
                  </div>
                  <div class="panel-body">
                    <div style="display:flex;align-items:center;justify-content:space-between;gap:14px;margin:10px 0 16px;flex-wrap:wrap">
                      <div style="flex:1;min-width:200px">
                        <div style="font-size:13px;color:var(--ink-soft);margin-bottom:7px"><?= $booked ?> of <?= $maxSuppliers ?> suppliers sorted</div>
                        <div class="meter"><div class="fill" style="width:<?= $pct ?>%"></div></div>
                      </div>
                      <?php if ($cost > 0): ?>
                        <div style="text-align:right">
                          <div style="font-size:12px;color:var(--ink-faint);text-transform:uppercase;letter-spacing:.06em">Booked</div>
                          <div style="font-size:20px;font-weight:700;color:var(--green)">£<?= number_format($cost) ?></div>
                        </div>
                      <?php endif; ?>
                    </div>
                    <div style="display:flex;gap:12px;margin-top:6px;flex-wrap:wrap">
                      <a href="/profile/events/<?= (int) $event['id'] ?>" class="btn btn-primary">Open event</a>
                      <a href="/browse-services" class="btn btn-ghost">Add another supplier</a>
                      <?php if ($days !== null): ?>
                        <span style="margin-left:auto;align-self:center;font-size:13px;font-weight:700;color:var(--ink-soft)">
                          <?= $days === 0 ? 'Today' : ($days . ' ' . ($days === 1 ? 'day' : 'days') . ' to go') ?>
                        </span>
                      <?php endif; ?>
                    </div>
                  </div>
                </div>
              <?php endforeach; ?>
            <?php else: ?>
              <div class="panel"><div class="panel-body" style="text-align:center;padding:48px 24px">
                <i class="fas fa-calendar-plus" style="font-size:34px;color:var(--ink-faint);margin-bottom:14px"></i>
                <h2 style="font-size:22px;margin:0 0 8px">No events yet</h2>
                <p style="color:var(--ink-soft);font-size:14.5px;margin:0 0 20px">Set up your first event to save the basics, then browse and shortlist suppliers.</p>
                <div style="display:flex;gap:10px;justify-content:center;flex-wrap:wrap">
                  <a href="/event/create" class="btn btn-primary"><i class="fas fa-plus"></i> Create your first event</a>
                  <a href="/browse-services" class="btn btn-ghost">Browse suppliers</a>
                </div>
              </div></div>
            <?php endif; ?>
          </div>

          <!-- past events -->
          <div class="tabpanel" data-panel="past">
            <div class="panel"><div class="panel-body" style="padding-top:22px">
              <?php if (!empty($pastEvents)): ?>
                <div class="elist">
                  <?php foreach ($pastEvents as $event):
                      $booked = (int) ($event['services_booked'] ?? $event['servicesBooked'] ?? 0);
                  ?>
                    <div class="erow">
                      <div class="erow-main">
                        <b><?= esc($event['title']) ?></b>
                        <div class="esub">
                          <?php if (!empty($event['date'])): ?>
                            <span><i class="fas fa-calendar"></i> <?= date('d M Y', strtotime($event['date'])) ?></span>
                          <?php endif; ?>
                          <?php if (!empty($event['location'])): ?>
                            <span><i class="fas fa-location-dot"></i> <?= esc($event['location']) ?></span>
                          <?php endif; ?>
                        </div>
                      </div>
                      <div class="erow-side">
                        <span class="badge <?= $booked > 0 ? 'green' : 'grey' ?>"><i class="fas fa-circle-check"></i> <?= $booked > 0 ? 'Completed' : 'Closed' ?></span>
                        <a href="/profile/events/<?= (int) $event['id'] ?>" class="linkmore">View <i class="fas fa-arrow-right"></i></a>
                      </div>
                    </div>
                  <?php endforeach; ?>
                </div>
              <?php else: ?>
                <p style="color:var(--ink-soft);font-size:14.5px;margin:0;text-align:center;padding:24px 0">No past events yet. Once an event date passes it will appear here.</p>
              <?php endif; ?>
            </div></div>
          </div>
        </div>

        <!-- side column -->
        <div>
          <div class="widget">
            <h3>Planning checklist</h3>
            <?php
                $hasEvent    = !empty($events);
                $hasQuote    = $pendingQuotes > 0 || $suppliersBooked > 0;
                $hasBooked   = $suppliersBooked > 0;
            ?>
            <div class="task-line<?= $hasEvent ? ' done' : '' ?>"><span class="tk"><?= $hasEvent ? '<i class="fas fa-check"></i>' : '' ?></span><span>Create your event brief</span></div>
            <div class="task-line<?= $hasQuote ? ' done' : '' ?>"><span class="tk"><?= $hasQuote ? '<i class="fas fa-check"></i>' : '' ?></span><span>Receive your first quotes</span></div>
            <div class="task-line<?= $hasBooked ? ' done' : '' ?>"><span class="tk"><?= $hasBooked ? '<i class="fas fa-check"></i>' : '' ?></span><span>Book your first supplier</span></div>
            <div class="task-line"><span class="tk"></span><span>Confirm final guest numbers</span></div>
            <div class="task-line"><span class="tk"></span><span>Complete your line-up</span></div>
          </div>
          <div class="widget dark">
            <h3 style="color:var(--cream)"><i class="fas fa-shield-halved" style="color:var(--gold-bright);margin-right:8px"></i> Payment protection</h3>
            <p style="margin:0 0 6px;font-size:14px">
              <?php if ($protectedHolding > 0): ?>
                £<?= number_format($protectedHolding) ?> is held securely and released to your suppliers 48 hours after each event.
              <?php else: ?>
                Every payment you make is held securely and released to your suppliers 48 hours after each event.
              <?php endif; ?>
            </p>
            <a href="/how-it-works" class="linkmore" style="color:var(--gold-bright)">How it works <i class="fas fa-arrow-right"></i></a>
          </div>
          <div class="widget">
            <h3>Quick links</h3>
            <div class="quote-list">
              <a href="/browse-services" class="quote" style="text-decoration:none">
                <span class="quote-av" style="display:flex;align-items:center;justify-content:center;background:var(--green);color:var(--cream)"><i class="fas fa-magnifying-glass"></i></span>
                <div><b>Browse suppliers</b><div class="qmeta"><span>Find your line-up</span></div></div>
                <div class="qprice"><span class="icon-act"><i class="fas fa-arrow-right"></i></span></div>
              </a>
              <a href="/profile/favourites" class="quote" style="text-decoration:none">
                <span class="quote-av" style="display:flex;align-items:center;justify-content:center;background:var(--gold-bright);color:var(--green-darkest)"><i class="fas fa-heart"></i></span>
                <div><b>Saved suppliers</b><div class="qmeta"><span>Your shortlist</span></div></div>
                <div class="qprice"><span class="icon-act"><i class="fas fa-arrow-right"></i></span></div>
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</main>
</div>
<?= $this->include('footer') ?>
