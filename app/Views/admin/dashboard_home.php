<?php
// Map booking status -> design badge colour.
$bookingBadge = static function (string $status): string {
    $s = strtolower(trim($status));
    return match (true) {
        in_array($s, ['confirmed', 'paid', 'completed', 'paid_out'], true) => 'green',
        in_array($s, ['pending', 'awaiting', 'held', 'hold'], true)         => 'gold',
        in_array($s, ['cancelled', 'canceled', 'declined', 'dispute', 'disputed', 'refunded'], true) => 'red',
        default => 'grey',
    };
};

// Initials avatar from a display name.
$initials = static function (?string $name): string {
    $name = trim((string) $name);
    if ($name === '') {
        return '—';
    }
    $parts = preg_split('/\s+/', $name);
    $first = mb_substr($parts[0] ?? '', 0, 1);
    $last  = count($parts) > 1 ? mb_substr(end($parts), 0, 1) : '';
    return mb_strtoupper($first . $last);
};

$attentionCount = (int) $pendingBookings + (int) $flaggedRooms + (int) ($pendingLanguage ?? 0);
?>
<main class="dash">
  <section class="dash-head dark">
    <div class="container">
      <div class="dash-hello">
        <div>
          <p class="eyebrow on-dark">Partysmith HQ</p>
          <h1 class="heading">Marketplace overview</h1>
          <p class="dh-sub">Activity, queues that need attention, and quick navigation.</p>
        </div>
        <div style="display:flex;gap:10px;flex-wrap:wrap">
          <a href="<?= site_url('/admin/bookings') ?>" class="btn btn-ghost-light"><i class="fas fa-calendar-check"></i> Bookings</a>
          <a href="<?= site_url('/admin/vendors') ?>" class="btn btn-gold"><i class="fas fa-user-check"></i> Vendors</a>
        </div>
      </div>
    </div>
  </section>

  <div class="dash-body">
    <div class="container">

      <?php if (!empty($cmsNavIssues)): ?>
        <div class="form-alert error" role="alert" style="margin-bottom:22px">
          <div style="font-weight:700;margin-bottom:8px"><i class="fas fa-link-slash me-2"></i>Public navigation would show broken pages (404 or missing CMS)</div>
          <p style="margin:0 0 8px">These URLs are linked from the site header or footer. Each needs a <strong>published</strong> row in Admin → Pages.</p>
          <ul style="margin:0 0 12px">
            <?php foreach ($cmsNavIssues as $issue): ?>
              <?php if (($issue['type'] ?? '') === 'table_missing'): ?>
                <li>The <code>cms_pages</code> table is missing. Import <code class="user-select-all">database_update.sql</code>, then reload.</li>
              <?php elseif (($issue['type'] ?? '') === 'missing'): ?>
                <li><strong><?= esc($issue['label']) ?></strong> — no row for slug <code><?= esc($issue['slug']) ?></code>. <a href="<?= esc($issue['public_url']) ?>" target="_blank" rel="noopener noreferrer">Would 404</a></li>
              <?php else: ?>
                <li><strong><?= esc($issue['label']) ?></strong> — slug <code><?= esc($issue['slug']) ?></code> is <span class="badge grey"><?= esc($issue['status'] ?? '') ?></span>. <a href="<?= esc($issue['edit_url'] ?? '') ?>">Edit and publish</a></li>
              <?php endif; ?>
            <?php endforeach; ?>
          </ul>
          <a class="btn btn-ghost" href="<?= site_url('/admin/pages') ?>">Open Public pages</a>
        </div>
      <?php endif; ?>

      <div class="stat-grid">
        <a class="stat" href="<?= site_url('/admin/customers') ?>" style="text-decoration:none">
          <div class="st-top"><span class="st-ic"><i class="fas fa-users"></i></span></div>
          <div class="st-n"><?= number_format((int) ($stats['customers'] ?? 0)) ?></div>
          <div class="st-l">Customers</div>
        </a>
        <a class="stat" href="<?= site_url('/admin/vendors') ?>" style="text-decoration:none">
          <div class="st-top"><span class="st-ic"><i class="fas fa-store"></i></span></div>
          <div class="st-n"><?= number_format((int) ($stats['vendors'] ?? 0)) ?></div>
          <div class="st-l">Vendors</div>
        </a>
        <a class="stat" href="<?= site_url('/admin/services') ?>" style="text-decoration:none">
          <div class="st-top"><span class="st-ic"><i class="fas fa-briefcase"></i></span></div>
          <div class="st-n"><?= number_format((int) ($stats['services'] ?? 0)) ?></div>
          <div class="st-l">Services (active)</div>
        </a>
        <a class="stat" href="<?= site_url('/admin/bookings') ?>" style="text-decoration:none">
          <div class="st-top"><span class="st-ic"><i class="fas fa-calendar-check"></i></span></div>
          <div class="st-n"><?= number_format((int) ($stats['bookings'] ?? 0)) ?></div>
          <div class="st-l">Bookings</div>
        </a>
      </div>

      <div data-tabs>
        <div class="dash-tabbar">
          <div class="tabnav">
            <button data-panel="overview" class="on"><i class="fas fa-gauge"></i> Overview</button>
            <button data-panel="bookings"><i class="fas fa-calendar-check"></i> Bookings</button>
            <button data-panel="messages"><i class="fas fa-comments"></i> Messages
              <?php if ($attentionCount > 0): ?><span class="badge gold" style="margin-left:2px"><?= (int) $attentionCount ?></span><?php endif; ?>
            </button>
          </div>
        </div>

        <!-- overview -->
        <div class="tabpanel show" data-panel="overview">
          <div class="admin-grid">
            <div class="panel">
              <div class="panel-head">
                <h2>Recent bookings</h2>
                <a href="<?= site_url('/admin/bookings') ?>" class="ph-meta">View all</a>
              </div>
              <div class="table-wrap">
                <table class="ps-table">
                  <thead><tr><th>ID</th><th>Customer</th><th>Event</th><th>Status</th><th>When</th></tr></thead>
                  <tbody>
                  <?php foreach (array_slice($recentBookings, 0, 6) as $b): ?>
                    <tr>
                      <td class="t-primary"><a href="<?= site_url('/admin/bookings/' . $b['id']) ?>" style="text-decoration:none">#<?= (int) $b['id'] ?></a></td>
                      <td>
                        <div class="td-user">
                          <span class="td-av"><?= esc($initials($b['customer_name'] ?? '')) ?></span>
                          <span class="t-primary"><?= esc($b['customer_name'] ?? '—') ?></span>
                        </div>
                      </td>
                      <td><?= esc($b['event_title'] ?? '—') ?></td>
                      <td><span class="badge <?= esc($bookingBadge((string) ($b['status'] ?? ''))) ?>"><?= esc($b['status'] ?? '') ?></span></td>
                      <td class="text-nowrap"><?= esc($b['created_at'] ?? '') ?></td>
                    </tr>
                  <?php endforeach; ?>
                  <?php if (empty($recentBookings)): ?>
                    <tr><td colspan="5" style="text-align:center;padding:28px 12px;color:var(--ink-faint)">
                      No bookings recorded yet. <a href="<?= site_url('/admin/bookings') ?>">Go to bookings</a>
                    </td></tr>
                  <?php endif; ?>
                  </tbody>
                </table>
              </div>
            </div>

            <div>
              <div class="widget">
                <h3>Quick links</h3>
                <div style="display:flex;flex-wrap:wrap;gap:8px">
                  <a class="btn btn-ghost" href="<?= site_url('/admin/customers') ?>">Customers</a>
                  <a class="btn btn-ghost" href="<?= site_url('/admin/vendors') ?>">Vendors</a>
                  <a class="btn btn-ghost" href="<?= site_url('/admin/bookings') ?>">Bookings</a>
                  <a class="btn btn-ghost" href="<?= site_url('/admin/services') ?>">Services</a>
                  <a class="btn btn-ghost" href="<?= site_url('/admin/events') ?>">Events</a>
                  <a class="btn btn-ghost" href="<?= site_url('/admin/messages') ?>">Messages</a>
                  <a class="btn btn-ghost" href="<?= site_url('/admin/pages') ?>">Public pages</a>
                </div>
              </div>

              <div class="widget dark">
                <h3>Needs attention</h3>
                <div class="task-line"><span class="tk" style="border:none;background:rgba(185,140,42,0.16);color:#e7c878"><i class="fas fa-clock"></i></span><span>Pending bookings: <strong><?= (int) $pendingBookings ?></strong></span></div>
                <div class="task-line"><span class="tk" style="border:none;background:rgba(251,248,241,0.12);color:var(--cream)"><i class="fas fa-pause-circle"></i></span><span>Non-active services: <strong><?= (int) $inactiveServices ?></strong></span></div>
                <div class="task-line"><span class="tk" style="border:none;background:rgba(192,71,62,0.22);color:#f0a8a1"><i class="fas fa-flag"></i></span><span>Flagged conversations: <strong><?= (int) $flaggedRooms ?></strong></span></div>
                <div class="task-line"><span class="tk" style="border:none;background:rgba(185,140,42,0.16);color:#e7c878"><i class="fas fa-language"></i></span>
                  <span>Chat pending language review: <strong><?= (int) ($pendingLanguage ?? 0) ?></strong>
                    <?php if (!empty($pendingLanguage)): ?>
                      <a href="<?= site_url('/admin/messages?moderation=pending') ?>" style="color:var(--gold-bright)">Open queue</a>
                    <?php endif; ?>
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- bookings -->
        <div class="tabpanel" data-panel="bookings">
          <div class="panel">
            <div class="panel-head">
              <h2>Recent bookings</h2>
              <a href="<?= site_url('/admin/bookings') ?>" class="ph-meta">View all</a>
            </div>
            <div class="table-wrap">
              <table class="ps-table">
                <thead><tr><th>ID</th><th>Customer</th><th>Event</th><th>Status</th><th>When</th></tr></thead>
                <tbody>
                <?php foreach ($recentBookings as $b): ?>
                  <tr>
                    <td class="t-primary"><a href="<?= site_url('/admin/bookings/' . $b['id']) ?>" style="text-decoration:none">#<?= (int) $b['id'] ?></a></td>
                    <td>
                      <div class="td-user">
                        <span class="td-av"><?= esc($initials($b['customer_name'] ?? '')) ?></span>
                        <span class="t-primary"><?= esc($b['customer_name'] ?? '—') ?></span>
                      </div>
                    </td>
                    <td><?= esc($b['event_title'] ?? '—') ?></td>
                    <td><span class="badge <?= esc($bookingBadge((string) ($b['status'] ?? ''))) ?>"><?= esc($b['status'] ?? '') ?></span></td>
                    <td class="text-nowrap"><?= esc($b['created_at'] ?? '') ?></td>
                  </tr>
                <?php endforeach; ?>
                <?php if (empty($recentBookings)): ?>
                  <tr><td colspan="5" style="text-align:center;padding:28px 12px;color:var(--ink-faint)">
                    No bookings recorded yet. <a href="<?= site_url('/admin/bookings') ?>">Go to bookings</a>
                  </td></tr>
                <?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- messages -->
        <div class="tabpanel" data-panel="messages">
          <div class="panel">
            <div class="panel-head">
              <h2>Recent messages</h2>
              <a href="<?= site_url('/admin/messages') ?>" class="ph-meta">View all</a>
            </div>
            <div class="table-wrap">
              <table class="ps-table">
                <thead><tr><th>Room</th><th>Preview</th><th>When</th></tr></thead>
                <tbody>
                <?php foreach ($recentMessages as $m): ?>
                  <tr>
                    <td class="t-primary">
                      <a href="<?= site_url('/admin/messages/' . $m['room_id']) ?>" style="text-decoration:none">#<?= (int) $m['room_id'] ?></a>
                      <?php if (!empty($m['flagged_for_review'])): ?><span class="badge red">flagged</span><?php endif; ?>
                      <?php if (($m['moderation_status'] ?? '') === \App\Libraries\ChatModeration::STATUS_PENDING): ?>
                        <span class="badge gold">language</span>
                      <?php endif; ?>
                    </td>
                    <td><?php $msg = (string) ($m['message'] ?? ''); echo esc(strlen($msg) > 80 ? substr($msg, 0, 80) . '…' : $msg); ?></td>
                    <td class="text-nowrap"><?= esc($m['created_at'] ?? '') ?></td>
                  </tr>
                <?php endforeach; ?>
                <?php if (empty($recentMessages)): ?>
                  <tr><td colspan="3" style="text-align:center;padding:28px 12px;color:var(--ink-faint)">
                    No messages yet. <a href="<?= site_url('/admin/messages') ?>">Open messages</a>
                  </td></tr>
                <?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>
</main>
