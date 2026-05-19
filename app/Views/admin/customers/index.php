<header class="admin-page-header">
    <h1 class="admin-page-title">Customers</h1>
    <p class="admin-page-subtitle">Customer accounts registered on the marketplace. Search by name, email, or username.</p>
</header>
<form class="row g-2 admin-filters" method="get">
    <div class="col-md-6">
        <input type="text" name="q" value="<?= esc($q) ?>" class="form-control" placeholder="Search name, email, username">
    </div>
    <div class="col-auto d-flex flex-wrap align-items-center">
        <button class="btn btn-primary me-2 mb-2 mb-md-0" type="submit">Search</button>
        <a class="btn btn-outline-secondary mb-2 mb-md-0" href="<?= site_url('/admin/customers') ?>">Reset</a>
    </div>
</form>
<?php if (empty($customers)): ?>
    <div class="card shadow-sm">
        <div class="admin-empty">
            <div class="admin-empty-icon"><i class="fas fa-users" aria-hidden="true"></i></div>
            <p class="admin-empty-title"><?= !empty($q) ? 'No customers match your search' : 'No customers yet' ?></p>
            <p class="admin-empty-text">
                <?= !empty($q)
                    ? 'Try different keywords or clear the search to see the full list.'
                    : 'New customers will appear here after they sign up on the site.' ?>
            </p>
            <div class="admin-empty-actions">
                <?php if (!empty($q)): ?>
                    <a class="btn btn-primary" href="<?= site_url('/admin/customers') ?>">Clear search</a>
                <?php endif; ?>
                <a class="btn btn-outline-secondary<?= empty($q) ? '' : ' ms-2' ?>" href="<?= site_url('/admin') ?>">Back to dashboard</a>
            </div>
        </div>
    </div>
<?php else: ?>
    <div class="card shadow-sm admin-table-card">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Username</th><th></th></tr></thead>
                <tbody>
                <?php foreach ($customers as $c): ?>
                    <tr>
                        <td class="text-muted"><?= (int) $c['id'] ?></td>
                        <td><?= esc($c['name']) ?></td>
                        <td><?= esc($c['email']) ?></td>
                        <td><?= esc($c['username'] ?? '') ?></td>
                        <td class="text-end"><a class="btn btn-sm btn-outline-primary" href="<?= site_url('/admin/customers/' . $c['id']) ?>">View</a></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="card-body border-top"><?= $pager->links() ?></div>
    </div>
<?php endif; ?>
