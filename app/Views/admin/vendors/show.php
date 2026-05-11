<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">Vendor #<?= (int) $user['id'] ?></h1>
    <div class="btn-group">
        <a class="btn btn-outline-secondary" href="<?= site_url('/admin/vendors') ?>">Back</a>
        <a class="btn btn-outline-primary" href="<?= site_url('/admin/vendors/' . $user['id'] . '/edit') ?>">Edit</a>
        <a class="btn btn-outline-danger" href="<?= site_url('/admin/vendors/' . $user['id'] . '/delete') ?>">Delete</a>
    </div>
</div>
<div class="row g-3">
    <div class="col-md-4">
        <div class="card shadow-sm"><div class="card-body">
            <h2 class="h6">Account</h2>
            <p class="mb-1"><strong>Name:</strong> <?= esc($user['name']) ?></p>
            <p class="mb-1"><strong>Email:</strong> <?= esc($user['email']) ?></p>
            <p class="mb-0"><strong>Username:</strong> <?= esc($user['username']) ?></p>
        </div></div>
    </div>
    <div class="col-md-8">
        <div class="card shadow-sm mb-3">
            <div class="card-header bg-white fw-bold">Services</div>
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead><tr><th>ID</th><th>Title</th><th>Status</th><th>Price</th></tr></thead>
                    <tbody>
                    <?php foreach ($services as $s): ?>
                        <tr>
                            <td><a href="<?= site_url('/admin/services/' . $s['id']) ?>"><?= (int) $s['id'] ?></a></td>
                            <td><?= esc($s['title']) ?></td>
                            <td><?= esc($s['status'] ?? '') ?></td>
                            <td><?= esc((string) ($s['price'] ?? '')) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($services)): ?><tr><td colspan="4" class="text-muted">None</td></tr><?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card shadow-sm mb-3">
            <div class="card-header bg-white fw-bold">Bookings (via services)</div>
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead><tr><th>Booking</th><th>Customer</th><th>Service</th><th>Status</th><th>Created</th></tr></thead>
                    <tbody>
                    <?php foreach ($bookings as $b): ?>
                        <tr>
                            <td><a href="<?= site_url('/admin/bookings/' . $b['booking_id']) ?>"><?= (int) $b['booking_id'] ?></a></td>
                            <td><?= esc($b['customer_name'] ?? '') ?></td>
                            <td><?= esc($b['service_title'] ?? '') ?></td>
                            <td><?= esc($b['booking_status'] ?? '') ?></td>
                            <td><?= esc($b['booking_created'] ?? '') ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($bookings)): ?><tr><td colspan="5" class="text-muted">None</td></tr><?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card shadow-sm">
            <div class="card-header bg-white fw-bold">Conversations</div>
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead><tr><th>Room</th><th>Customer</th><th>Started</th></tr></thead>
                    <tbody>
                    <?php foreach ($rooms as $r): ?>
                        <tr>
                            <td><a href="<?= site_url('/admin/messages/' . $r['id']) ?>">#<?= (int) $r['id'] ?></a></td>
                            <td><?= esc($r['customer_name']) ?></td>
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
