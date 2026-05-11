<h1 class="h3 mb-3">Public pages</h1>
<p class="text-muted">Edit content for marketing routes. Set status to <strong>published</strong> to show on the site.</p>
<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table mb-0">
            <thead><tr><th>Slug</th><th>Title</th><th>Status</th><th>Updated</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($pages as $p): ?>
                <tr>
                    <td><code><?= esc($p['slug']) ?></code></td>
                    <td><?= esc($p['title']) ?></td>
                    <td><?= esc($p['status']) ?></td>
                    <td><?= esc($p['updated_at'] ?? '') ?></td>
                    <td><a class="btn btn-sm btn-outline-primary" href="<?= site_url('/admin/pages/edit/' . $p['slug']) ?>">Edit</a></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php if (empty($pages)): ?>
        <div class="card-body text-muted">No pages yet. Run migrations and <code>php spark db:seed CmsPagesSeeder</code>.</div>
    <?php endif; ?>
</div>
