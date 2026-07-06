<?php
$statusFilter = $status ?? '';
$pendingCount = $pendingCount ?? 0;

$statusPills = [
    ''          => 'All',
    'pending'   => 'Pending',
    'approved'  => 'Approved',
    'rejected'  => 'Rejected',
];

$pillUrl = static function (string $value) use ($q) {
    $params = [];
    if ($value !== '') {
        $params['status'] = $value;
    }
    if (!empty($q)) {
        $params['q'] = $q;
    }

    return site_url('/admin/vendors') . (!empty($params) ? '?' . http_build_query($params) : '');
};

$statusBadgeClass = static function (string $vendorStatus): string {
    return match ($vendorStatus) {
        'approved' => 'bg-success',
        'rejected' => 'bg-danger',
        default    => 'bg-warning text-dark',
    };
};
?>
<header class="admin-page-header">
    <h1 class="admin-page-title">Vendors</h1>
    <p class="admin-page-subtitle">Vendor accounts and their storefront presence. Search by name, email, or username, and review pending applications.</p>
</header>
<form class="row g-2 admin-filters" method="get">
    <?php if ($statusFilter !== ''): ?>
        <input type="hidden" name="status" value="<?= esc($statusFilter) ?>">
    <?php endif; ?>
    <div class="col-md-6">
        <input type="text" name="q" value="<?= esc($q) ?>" class="form-control" placeholder="Search name, email, username">
    </div>
    <div class="col-auto d-flex flex-wrap align-items-center">
        <button class="btn btn-primary me-2 mb-2 mb-md-0" type="submit">Search</button>
        <a class="btn btn-outline-secondary mb-2 mb-md-0" href="<?= site_url('/admin/vendors') ?>">Reset</a>
    </div>
    <div class="col-12 d-flex flex-wrap gap-2">
        <?php foreach ($statusPills as $value => $label): ?>
            <?php $isActive = $statusFilter === $value; ?>
            <a class="btn btn-sm <?= $isActive ? 'btn-primary' : 'btn-outline-secondary' ?>" href="<?= $pillUrl($value) ?>">
                <?= esc($label) ?><?= $value === 'pending' ? ' (' . (int) $pendingCount . ')' : '' ?>
            </a>
        <?php endforeach; ?>
    </div>
</form>
<?php if (empty($vendors)): ?>
    <div class="card shadow-sm">
        <div class="admin-empty">
            <div class="admin-empty-icon"><i class="fas fa-store" aria-hidden="true"></i></div>
            <p class="admin-empty-title"><?= !empty($q) || $statusFilter !== '' ? 'No vendors match your filters' : 'No vendors yet' ?></p>
            <p class="admin-empty-text">
                <?= !empty($q) || $statusFilter !== ''
                    ? 'Try different keywords, or clear the search and status filter to see the full list.'
                    : 'Vendors appear here once they register as providers.' ?>
            </p>
            <div class="admin-empty-actions">
                <?php if (!empty($q) || $statusFilter !== ''): ?>
                    <a class="btn btn-primary" href="<?= site_url('/admin/vendors') ?>">Clear filters</a>
                <?php endif; ?>
                <a class="btn btn-outline-secondary<?= empty($q) && $statusFilter === '' ? '' : ' ms-2' ?>" href="<?= site_url('/admin/services') ?>">Browse services</a>
            </div>
        </div>
    </div>
<?php else: ?>
    <div class="card shadow-sm admin-table-card">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Username</th><th>Status</th><th>Registered</th><th>Services</th><th></th></tr></thead>
                <tbody>
                <?php foreach ($vendors as $v): ?>
                    <?php $vendorStatus = $v['vendor_status'] ?? 'approved'; ?>
                    <tr>
                        <td class="text-muted"><?= (int) $v['id'] ?></td>
                        <td><?= esc($v['name']) ?></td>
                        <td><?= esc($v['email']) ?></td>
                        <td><?= esc($v['username'] ?? '') ?></td>
                        <td>
                            <span class="badge <?= $statusBadgeClass($vendorStatus) ?>"><?= esc(ucfirst($vendorStatus)) ?></span>
                            <?php if ($vendorStatus === 'rejected' && !empty($v['vendor_status_reason'])): ?>
                                <div class="text-muted small mt-1"><?= esc($v['vendor_status_reason']) ?></div>
                            <?php endif; ?>
                        </td>
                        <td class="text-muted small"><?= !empty($v['created_at']) ? esc($v['created_at']) : '—' ?></td>
                        <td class="text-muted"><?= (int) ($v['services_count'] ?? 0) ?></td>
                        <td class="text-end">
                            <div class="d-flex flex-column align-items-end gap-1">
                                <a class="btn btn-sm btn-outline-primary" href="<?= site_url('/admin/vendors/' . $v['id']) ?>">View</a>
                                <?php if ($vendorStatus === 'pending'): ?>
                                    <form method="post" action="<?= site_url('/admin/vendors/' . $v['id'] . '/approve') ?>" class="d-inline">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn-sm btn-success">Approve</button>
                                    </form>
                                    <form method="post" action="<?= site_url('/admin/vendors/' . $v['id'] . '/reject') ?>" class="d-inline-flex gap-1 align-items-start">
                                        <?= csrf_field() ?>
                                        <input type="text" name="reason" class="form-control form-control-sm" style="width: 10rem;" placeholder="Reason" required maxlength="2000">
                                        <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Reject this vendor application?');">Reject</button>
                                    </form>
                                <?php elseif ($vendorStatus === 'rejected'): ?>
                                    <form method="post" action="<?= site_url('/admin/vendors/' . $v['id'] . '/approve') ?>" class="d-inline">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn-sm btn-success">Re-approve</button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="card-body border-top"><?= $pager->links() ?></div>
    </div>
<?php endif; ?>
