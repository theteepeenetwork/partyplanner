<header class="admin-page-header">
    <h1 class="admin-page-title">Dashboard</h1>
    <p class="admin-page-subtitle">Overview of marketplace activity, queues that need attention, and quick navigation.</p>
</header>

<?php if (!empty($cmsNavIssues)): ?>
    <div class="alert alert-danger border-0 shadow-sm mb-4" role="alert">
        <div class="fw-semibold mb-2"><i class="fas fa-link-slash me-2"></i>Public navigation would show broken pages (404 or missing CMS)</div>
        <p class="small mb-2">These URLs are linked from the site header or footer and are served from <strong>Admin → Pages</strong> (<code>cms_pages</code>). Each needs a <strong>published</strong> row.</p>
        <ul class="small mb-3 ps-3">
            <?php foreach ($cmsNavIssues as $issue): ?>
                <?php if (($issue['type'] ?? '') === 'table_missing'): ?>
                    <li>The <code>cms_pages</code> table is missing. Import <code class="user-select-all">database_update.sql</code> into MySQL, then reload.</li>
                <?php elseif (($issue['type'] ?? '') === 'missing'): ?>
                    <li><strong><?= esc($issue['label']) ?></strong> — no row for slug <code><?= esc($issue['slug']) ?></code>. Public URL: <a href="<?= esc($issue['public_url']) ?>" target="_blank" rel="noopener noreferrer"><?= esc($issue['public_url']) ?></a> (404). Re-import <code class="user-select-all">database_update.sql</code> or create the page in Admin → Pages.</li>
                <?php else: ?>
                    <li><strong><?= esc($issue['label']) ?></strong> — slug <code><?= esc($issue['slug']) ?></code> is <span class="badge bg-secondary"><?= esc($issue['status'] ?? '') ?></span> (not published). <a href="<?= esc($issue['edit_url'] ?? '') ?>">Edit and publish</a> · <a href="<?= esc($issue['public_url']) ?>" target="_blank" rel="noopener noreferrer">Preview URL</a></li>
                <?php endif; ?>
            <?php endforeach; ?>
        </ul>
        <a class="btn btn-sm btn-light" href="<?= site_url('/admin/pages') ?>">Open Public pages</a>
    </div>
<?php endif; ?>

<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card shadow-sm h-100"><div class="card-body">
            <div class="text-muted small text-uppercase" style="font-size:.6875rem;letter-spacing:.06em;">Customers</div>
            <div class="display-6"><?= (int) ($stats['customers'] ?? 0) ?></div>
        </div></div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card shadow-sm h-100"><div class="card-body">
            <div class="text-muted small text-uppercase" style="font-size:.6875rem;letter-spacing:.06em;">Vendors</div>
            <div class="display-6"><?= (int) ($stats['vendors'] ?? 0) ?></div>
        </div></div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card shadow-sm h-100"><div class="card-body">
            <div class="text-muted small text-uppercase" style="font-size:.6875rem;letter-spacing:.06em;">Services (active)</div>
            <div class="display-6"><?= (int) ($stats['services'] ?? 0) ?></div>
        </div></div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card shadow-sm h-100"><div class="card-body">
            <div class="text-muted small text-uppercase" style="font-size:.6875rem;letter-spacing:.06em;">Bookings</div>
            <div class="display-6"><?= (int) ($stats['bookings'] ?? 0) ?></div>
        </div></div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-6">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white fw-semibold">Review-worthy activity</div>
            <div class="card-body">
                <ul class="list-unstyled mb-0">
                    <li class="mb-2"><i class="fas fa-clock text-warning me-2"></i>Pending bookings: <strong><?= (int) $pendingBookings ?></strong></li>
                    <li class="mb-2"><i class="fas fa-pause-circle text-secondary me-2"></i>Non-active services: <strong><?= (int) $inactiveServices ?></strong></li>
                    <li class="mb-2"><i class="fas fa-flag text-danger me-2"></i>Flagged conversations: <strong><?= (int) $flaggedRooms ?></strong></li>
                    <li class="mb-0"><i class="fas fa-language text-warning me-2"></i>Chat messages pending language review: <strong><?= (int) ($pendingLanguage ?? 0) ?></strong>
                        <?php if (!empty($pendingLanguage)): ?>
                            <a href="<?= site_url('/admin/messages?moderation=pending') ?>" class="small ms-1">Open queue</a>
                        <?php endif; ?>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white fw-semibold">Quick links</div>
            <div class="card-body d-flex flex-wrap">
                <a class="btn btn-outline-primary btn-sm me-1 mb-1" href="<?= site_url('/admin/customers') ?>">Customers</a>
                <a class="btn btn-outline-primary btn-sm me-1 mb-1" href="<?= site_url('/admin/vendors') ?>">Vendors</a>
                <a class="btn btn-outline-primary btn-sm me-1 mb-1" href="<?= site_url('/admin/bookings') ?>">Bookings</a>
                <a class="btn btn-outline-primary btn-sm me-1 mb-1" href="<?= site_url('/admin/services') ?>">Services</a>
                <a class="btn btn-outline-primary btn-sm me-1 mb-1" href="<?= site_url('/admin/events') ?>">Events</a>
                <a class="btn btn-outline-primary btn-sm me-1 mb-1" href="<?= site_url('/admin/messages') ?>">Messages</a>
                <a class="btn btn-outline-primary btn-sm me-1 mb-1" href="<?= site_url('/admin/pages') ?>">Public pages</a>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-6">
        <div class="card shadow-sm admin-table-card">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <span class="fw-semibold">Recent bookings</span>
                <a href="<?= site_url('/admin/bookings') ?>" class="small text-decoration-none">View all</a>
            </div>
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0 align-middle">
                    <thead><tr><th>ID</th><th>Customer</th><th>Event</th><th>Status</th><th>When</th></tr></thead>
                    <tbody>
                    <?php foreach ($recentBookings as $b): ?>
                        <tr>
                            <td class="text-muted"><a href="<?= site_url('/admin/bookings/' . $b['id']) ?>" class="text-decoration-none"><?= (int) $b['id'] ?></a></td>
                            <td><?= esc($b['customer_name'] ?? '') ?></td>
                            <td><?= esc($b['event_title'] ?? '—') ?></td>
                            <td><?= esc($b['status'] ?? '') ?></td>
                            <td class="text-nowrap small"><?= esc($b['created_at'] ?? '') ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($recentBookings)): ?>
                        <tr><td colspan="5" class="text-muted text-center py-4">
                            <div class="py-2">No bookings recorded yet.</div>
                            <a href="<?= site_url('/admin/bookings') ?>" class="btn btn-sm btn-outline-primary">Go to bookings</a>
                        </td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card shadow-sm admin-table-card">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <span class="fw-semibold">Recent messages</span>
                <a href="<?= site_url('/admin/messages') ?>" class="small text-decoration-none">View all</a>
            </div>
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0 align-middle">
                    <thead><tr><th>Room</th><th>Preview</th><th>When</th></tr></thead>
                    <tbody>
                    <?php foreach ($recentMessages as $m): ?>
                        <tr>
                            <td>
                                <a href="<?= site_url('/admin/messages/' . $m['room_id']) ?>" class="text-decoration-none">#<?= (int) $m['room_id'] ?></a>
                                <?php if (!empty($m['flagged_for_review'])): ?><span class="badge bg-danger">flagged</span><?php endif; ?>
                                <?php if (($m['moderation_status'] ?? '') === \App\Libraries\ChatModeration::STATUS_PENDING): ?>
                                    <span class="badge bg-warning text-dark">language</span>
                                <?php endif; ?>
                            </td>
                            <td class="small text-truncate" style="max-width:220px"><?php $msg = (string) ($m['message'] ?? ''); echo esc(strlen($msg) > 80 ? substr($msg, 0, 80) . '…' : $msg); ?></td>
                            <td class="text-nowrap small"><?= esc($m['created_at'] ?? '') ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($recentMessages)): ?>
                        <tr><td colspan="3" class="text-muted text-center py-4">
                            <div class="py-2">No messages yet.</div>
                            <a href="<?= site_url('/admin/messages') ?>" class="btn btn-sm btn-outline-primary">Open messages</a>
                        </td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
