<header class="admin-page-header">
    <h1 class="admin-page-title">Public pages</h1>
    <p class="admin-page-subtitle">Marketing and legal content for public routes. Set status to <strong>published</strong> to make a page visible on the site.</p>
</header>
<?php if (empty($pages)): ?>
    <div class="card shadow-sm">
        <div class="admin-empty">
            <div class="admin-empty-icon"><i class="fas fa-file-lines" aria-hidden="true"></i></div>
            <p class="admin-empty-title">No CMS pages in the database</p>
            <p class="admin-empty-text">Run migrations and seed default pages so marketing content appears here and on the public site.</p>
            <div class="admin-empty-actions">
                <span class="d-inline-block small text-muted font-monospace bg-light border rounded px-2 py-1">php spark db:seed CmsPagesSeeder</span>
            </div>
            <div class="admin-empty-actions">
                <a class="btn btn-outline-secondary" href="<?= site_url('/admin') ?>">Back to dashboard</a>
            </div>
        </div>
    </div>
<?php else: ?>
    <div class="card shadow-sm admin-table-card">
        <div class="table-responsive">
            <table class="table mb-0 align-middle">
                <thead><tr><th>Slug</th><th>Title</th><th>Status</th><th>Updated</th><th></th></tr></thead>
                <tbody>
                <?php foreach ($pages as $p): ?>
                    <tr>
                        <td><code><?= esc($p['slug']) ?></code></td>
                        <td><?= esc($p['title']) ?></td>
                        <td><?= esc($p['status']) ?></td>
                        <td class="text-nowrap small"><?= esc($p['updated_at'] ?? '') ?></td>
                        <td class="text-end"><a class="btn btn-sm btn-outline-primary" href="<?= site_url('/admin/pages/edit/' . $p['slug']) ?>">Edit</a></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>
