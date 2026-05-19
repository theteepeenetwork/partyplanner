<header class="admin-page-header">
    <h1 class="admin-page-title">Public pages</h1>
    <p class="admin-page-subtitle">Marketing content for public routes (header, footer, and <code>PublicPage</code>). Set status to <strong>published</strong> so visitors do not get a 404.</p>
</header>

<?php if (!empty($cmsNavIssues)): ?>
    <div class="alert alert-warning border-0 shadow-sm mb-3" role="alert">
        <div class="fw-semibold mb-1"><i class="fas fa-exclamation-triangle me-2"></i>Site navigation — CMS gaps</div>
        <ul class="small mb-0 ps-3">
            <?php foreach ($cmsNavIssues as $issue): ?>
                <?php if (($issue['type'] ?? '') === 'table_missing'): ?>
                    <li><code>cms_pages</code> table missing — import <code class="user-select-all">database_update.sql</code>.</li>
                <?php elseif (($issue['type'] ?? '') === 'missing'): ?>
                    <li><code><?= esc($issue['slug']) ?></code> (<?= esc($issue['label']) ?>) — missing row · <a href="<?= esc($issue['public_url']) ?>" target="_blank" rel="noopener noreferrer">would 404</a></li>
                <?php else: ?>
                    <li><code><?= esc($issue['slug']) ?></code> — status <strong><?= esc($issue['status'] ?? '') ?></strong> (hidden from public) · <a href="<?= esc($issue['edit_url'] ?? '') ?>">Edit</a></li>
                <?php endif; ?>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<?php if (empty($pages)): ?>
    <div class="card shadow-sm">
        <div class="admin-empty">
            <div class="admin-empty-icon"><i class="fas fa-file-lines" aria-hidden="true"></i></div>
            <p class="admin-empty-title">No CMS pages in the database</p>
            <p class="admin-empty-text">Import <code class="user-select-all">database_update.sql</code> (creates default published pages), or run <span class="font-monospace small">php spark db:seed CmsPagesSeeder</span>.</p>
            <div class="admin-empty-actions">
                <a class="btn btn-outline-secondary" href="<?= site_url('/admin') ?>">Back to dashboard</a>
            </div>
        </div>
    </div>
<?php else: ?>
    <div class="card shadow-sm admin-table-card">
        <div class="table-responsive">
            <table class="table mb-0 align-middle">
                <thead><tr><th>Slug</th><th>Title</th><th>Nav</th><th>Status</th><th>Updated</th><th></th></tr></thead>
                <tbody>
                <?php foreach ($pages as $p): ?>
                    <?php
                    $isNav = isset($cmsNavLabels[$p['slug']]);
                    $navBroken = $isNav && ($p['status'] ?? '') !== 'published';
                    ?>
                    <tr class="<?= $navBroken ? 'table-warning' : '' ?>">
                        <td><code><?= esc($p['slug']) ?></code></td>
                        <td><?= esc($p['title']) ?></td>
                        <td>
                            <?php if ($isNav): ?>
                                <?php if ($navBroken): ?>
                                    <span class="badge bg-warning text-dark" title="Linked from site header/footer — not published">Would 404</span>
                                <?php else: ?>
                                    <span class="badge bg-info text-dark">Site nav</span>
                                <?php endif; ?>
                                <span class="visually-hidden"><?= esc($cmsNavLabels[$p['slug']]) ?></span>
                            <?php else: ?>
                                <span class="text-muted small">—</span>
                            <?php endif; ?>
                        </td>
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
