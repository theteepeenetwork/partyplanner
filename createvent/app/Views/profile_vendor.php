<?= $this->include('header') ?>

<main class="container mt-4">
    <h2><?= esc($user['name']) ?> (Vendor)</h2>
    <a href="/service/create" class="btn btn-primary mb-3">Add New Service</a>
    
    <?php if (session()->has('success')): ?>
        <div class="alert alert-success">
            <?= session('success') ?>
        </div>
    <?php elseif (session()->has('error')): ?>
        <div class="alert alert-danger">
            <?= session('error') ?>
        </div>
    <?php endif; ?>


    <h2>Active Listings</h2>

    <?php if (! empty($activeServices)): ?>
        
        <div class="row">
            <?php foreach ($activeServices as $service): ?>
                <div class="col-md-4 mb-4">
                    <div class="card">
                        <img src="<?= base_url('uploads/' . esc($service['image'])) ?>" class="card-img-top" alt="<?= esc($service['title']) ?>">
                        <div class="card-body">
                            <h5 class="card-title"><?= esc($service['title']) ?></h5>
                            <p class="card-text"><?= esc($service['short_description']) ?></p>
                            <p class="card-text">Price: £<?= esc($service['price']) ?></p>
                            <a href="<?= base_url('service/view/' . esc($service['id'])) ?>" class="btn btn-primary btn-sm">View Details</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p>You don't have any active services yet.</p>
    <?php endif; ?>
    
    
    <h2>Inactive Listings</h2>

    <?php if (! empty($inactiveServices)): ?>
        <div class="row">
            <?php foreach ($inactiveServices as $service): ?>
                <div class="col-md-4 mb-4">
                    <div class="card">
                        <img src="<?= base_url('uploads/' . esc($service['image'])) ?>" class="card-img-top" alt="<?= esc($service['title']) ?>">
                        <div class="card-body">
                            <h5 class="card-title"><?= esc($service['title']) ?></h5>
                            <p class="card-text"><?= esc($service['short_description']) ?></p>
                            <p class="card-text">Price: £<?= esc($service['price']) ?></p>
                            <a href="<?= base_url('service/view/' . esc($service['id'])) ?>" class="btn btn-primary btn-sm">View Details</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p>You don't have any inactive services yet.</p>
    <?php endif; ?>

    <h2>Bookings</h2>
    <?php if (! empty($bookingItems)): ?>
        <table class="table">
            <thead>
                <tr>
                    <th>Event Title</th>
                    <th>Event Date</th>
                    <th>Ceremony Type</th>
                    <th>Location</th>
                    <th>Service</th>
                    <th>Price</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($bookingItems as $item): ?>
                    <tr>
                        <td><?= esc($item['event_title']) ?></td>
                        <td><?= esc($item['event_date']) ?></td>
                        <td><?= esc($item['ceremony_type']) ?></td>
                        <td><?= esc($item['location']) ?></td>
                        <td><?= esc($item['service_title']) ?></td>
                        <td>$<?= esc($item['price']) ?></td>
                        <td>
                            <span class="badge badge-pill <?= $item['status'] == 'accepted' ? 'badge-success' : ($item['status'] == 'pending' ? 'badge-warning' : 'badge-danger') ?>">
                                <?= ucfirst($item['status']) ?>
                            </span>
                        </td>
                        <td>
                        <td>
                        <form method="POST" action="<?= base_url('profile/update-booking-status/' . $item['booking_item_id']) ?>">
        <?= csrf_field() ?>
        <select class="form-control" name="status">
            <option value="pending" <?= ($item['status'] == 'pending') ? 'selected' : '' ?>>Pending</option>
            <option value="accepted" <?= ($item['status'] == 'accepted') ? 'selected' : '' ?>>Accept</option>
            <option value="rejected" <?= ($item['status'] == 'rejected') ? 'selected' : '' ?>>Reject</option>
        </select>
        <button type="submit" class="btn btn-primary btn-sm mt-2">Update</button>
    </form>
</td>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No booking requests at this time.</p>
    <?php endif; ?>

    <footer class="footer mt-5 py-3 bg-light">
    </footer>
</main>

