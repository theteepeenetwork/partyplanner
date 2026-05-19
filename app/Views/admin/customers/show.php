<div class="admin-toolbar d-flex justify-content-between align-items-center flex-wrap">
    <h1 class="admin-page-title mb-0">Customer #<?= (int) $user['id'] ?></h1>
    <div class="btn-group">
        <a class="btn btn-outline-secondary" href="<?= site_url('/admin/customers') ?>">Back</a>
        <a class="btn btn-outline-primary" href="<?= site_url('/admin/customers/' . $user['id'] . '/edit') ?>">Edit</a>
        <a class="btn btn-outline-danger" href="<?= site_url('/admin/customers/' . $user['id'] . '/delete') ?>">Delete</a>
    </div>
</div>
<div class="row g-3">
    <div class="col-md-4">
        <div class="card shadow-sm"><div class="card-body">
            <h2 class="h6">Account</h2>
            <p class="mb-1"><strong>Name:</strong> <?= esc($user['name']) ?></p>
            <p class="mb-1"><strong>Email:</strong> <?= esc($user['email']) ?></p>
            <p class="mb-0"><strong>Username:</strong> <?= esc($user['username'] ?? '') ?></p>
        </div></div>
    </div>
    <div class="col-md-8">
        <div class="card shadow-sm admin-table-card mb-3">
            <div class="card-header bg-white fw-semibold">Events</div>
            <div class="table-responsive">
                <table class="table table-sm mb-0 align-middle">
                    <thead><tr><th>ID</th><th>Title</th><th>Date</th><th>Status</th></tr></thead>
                    <tbody>
                    <?php foreach ($events as $e): ?>
                        <tr>
                            <td><a href="<?= site_url('/admin/events/' . $e['id']) ?>"><?= (int) $e['id'] ?></a></td>
                            <td><?= esc($e['title']) ?></td>
                            <td><?= esc($e['date'] ?? '') ?></td>
                            <td><?= esc($e['status'] ?? '') ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($events)): ?><tr><td colspan="4" class="text-muted">None</td></tr><?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card shadow-sm admin-table-card mb-3">
            <div class="card-header bg-white fw-semibold">Bookings</div>
            <div class="table-responsive">
                <table class="table table-sm mb-0 align-middle">
                    <thead><tr><th>ID</th><th>Event</th><th>Status</th><th>Created</th></tr></thead>
                    <tbody>
                    <?php foreach ($bookings as $b): ?>
                        <tr>
                            <td><a href="<?= site_url('/admin/bookings/' . $b['id']) ?>"><?= (int) $b['id'] ?></a></td>
                            <td><?= esc($b['event_title'] ?? '') ?></td>
                            <td><?= esc($b['status']) ?></td>
                            <td><?= esc($b['created_at'] ?? '') ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($bookings)): ?><tr><td colspan="4" class="text-muted">None</td></tr><?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card shadow-sm admin-table-card">
            <div class="card-header bg-white fw-semibold">Conversations</div>
            <div class="table-responsive">
                <table class="table table-sm mb-0 align-middle">
                    <thead><tr><th>Room</th><th>Vendor</th><th>Started</th></tr></thead>
                    <tbody>
                    <?php foreach ($rooms as $r): ?>
                        <tr>
                            <td><a href="<?= site_url('/admin/messages/' . $r['id']) ?>">#<?= (int) $r['id'] ?></a></td>
                            <td><?= esc($r['vendor_name']) ?></td>
                            <td><?= esc($r['created_at'] ?? '') ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($rooms)): ?><tr><td colspan="3" class="text-muted">None</td></tr><?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
