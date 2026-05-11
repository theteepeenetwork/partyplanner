<h1 class="h3 mb-3">Services</h1>
<form class="row g-2 mb-3 small align-items-end" method="get">
    <div class="col-md-2">
        <label class="form-label mb-0">Vendor ID</label>
        <input class="form-control" name="vendor_id" value="<?= $vendor_id ? (int) $vendor_id : '' ?>">
    </div>
    <div class="col-md-2">
        <label class="form-label mb-0">Category ID</label>
        <input class="form-control" name="category_id" value="<?= $category_id ? (int) $category_id : '' ?>">
    </div>
    <div class="col-md-2">
        <label class="form-label mb-0">Status</label>
        <input class="form-control" name="status" value="<?= esc($status) ?>" placeholder="active / inactive">
    </div>
    <div class="col-md-2 form-check mt-4">
        <input class="form-check-input" type="checkbox" name="deleted" value="1" id="del" <?= $show_deleted ? 'checked' : '' ?>>
        <label class="form-check-label" for="del">Show soft-deleted</label>
    </div>
    <div class="col-auto">
        <button class="btn btn-primary" type="submit">Filter</button>
        <a class="btn btn-outline-secondary" href="<?= site_url('/admin/services') ?>">Reset</a>
    </div>
</form>
<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover mb-0 small">
            <thead><tr><th>ID</th><th>Title</th><th>Vendor</th><th>Category</th><th>Status</th><th>Deleted</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($services as $s): ?>
                <tr>
                    <td><?= (int) $s['id'] ?></td>
                    <td><?= esc($s['title']) ?></td>
                    <td><?= esc($s['vendor_name'] ?? '') ?></td>
                    <td><?= esc($s['category_name'] ?? '—') ?></td>
                    <td><?= esc($s['status'] ?? '') ?></td>
                    <td><?= !empty($s['deleted_at']) ? esc($s['deleted_at']) : '—' ?></td>
                    <td>
                        <a class="btn btn-sm btn-outline-primary" href="<?= site_url('/admin/services/' . $s['id']) ?>">View</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="card-body"><?= $pager->links() ?></div>
</div>
