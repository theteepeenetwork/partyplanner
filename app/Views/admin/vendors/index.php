<h1 class="h3 mb-3">Vendors</h1>
<form class="row g-2 mb-3" method="get">
    <div class="col-md-6">
        <input type="text" name="q" value="<?= esc($q) ?>" class="form-control" placeholder="Search name, email, username">
    </div>
    <div class="col-auto">
        <button class="btn btn-primary" type="submit">Search</button>
        <a class="btn btn-outline-secondary" href="<?= site_url('/admin/vendors') ?>">Reset</a>
    </div>
</form>
<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Username</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($vendors as $v): ?>
                <tr>
                    <td><?= (int) $v['id'] ?></td>
                    <td><?= esc($v['name']) ?></td>
                    <td><?= esc($v['email']) ?></td>
                    <td><?= esc($v['username']) ?></td>
                    <td><a class="btn btn-sm btn-outline-primary" href="<?= site_url('/admin/vendors/' . $v['id']) ?>">View</a></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="card-body"><?= $pager->links() ?></div>
</div>
