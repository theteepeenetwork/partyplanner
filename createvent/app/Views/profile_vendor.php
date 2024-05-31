<?= $this->include('header') ?>

    <main class="container mt-4">
        <h2>My Profile</h2>

        <div class="card">
            <div class="card-body">
            <h5 class="card-title"><?= esc($user['name']) ?></h5>    
            <h5 class="card-title"><?= esc($user['username']) ?></h5>
                <p class="card-text">Email: <?= esc($user['email']) ?></p>
                <p class="card-text">Role: <?= esc($user['role']) ?></p>
                <a href="/profile/edit" class="btn btn-primary">Edit Profile</a> 
            </div>
        </div>

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

    <?php if (session()->has('success')): ?>
        <div class="alert alert-success">
            <?= session('success') ?>
        </div>
    <?php elseif (session()->has('error')): ?>
        <div class="alert alert-danger">
            <?= session('error') ?>
        </div>
    <?php endif; ?>

    <main class="container mt-4">
    <h2><?= esc($user['name']) ?> (Vendor)</h2>
    <a href="/service/create" class="btn btn-primary mb-3">Add New Service</a>

    <h3>My Services</h3>

    <?php if (! empty($services)): ?>
        <div class="row">
            <?php foreach ($services as $service): ?>
                <div class="col-md-4 mb-4">
                    <div class="card">
                        <img src="<?= base_url('uploads/' . esc($service['image'])) ?>" class="card-img-top" alt="<?= esc($service['title']) ?>">
                        <div class="card-body">
                            <h5 class="card-title"><?= esc($service['title']) ?></h5>
                            <p class="card-text"><?= esc($service['short_description']) ?></p>
                            <p class="card-text">Price: $<?= esc($service['price']) ?></p>
                            <a href="<?= base_url('service/view/' . esc($service['id'])) ?>" class="btn btn-primary btn-sm">View Details</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p>You don't have any services yet.</p>
    <?php endif; ?>
</main>


    <h2>Bookings</h2>
    <?php if (! empty($bookingItems)): ?>
        <table class="table">
            <thead>
                <tr>
                    
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
                        <td><?= esc($item['event_date']) ?></td>
                        <td><?= esc($item['ceremony_type']) ?></td>
                        <td><?= esc($item['location']) ?></td>
                        <td><?= esc($item['service_title']) ?></td>
                        <td><?= esc($item['price']) ?></td>
                        <td>
                            <span class="badge badge-pill <?= $item['booking_item_status'] == 'accepted' ? 'badge-success' : ($item['booking_item_status'] == 'pending' ? 'badge-warning' : 'badge-danger') ?>">
                                <?= ucfirst($item['booking_item_status']) ?>
                            </span>
                        </td>
                        <td>
                            <?= esc($item['status']) ?> Hello
                            <?= esc($item['ceremony_type']) ?> Hello
                            
                        <td>
                        <form method="POST" action="<?= base_url('profile/update-booking-status/' . $item['booking_item_id']) ?>">
        <?= csrf_field() ?>
        <select class="form-control" name="status">
            <option value="pending" <?= ($item['booking_item_status'] == 'pending') ? 'selected' : '' ?>>Pending</option>
            <option value="accepted" <?= ($item['booking_item_status'] == 'accepted') ? 'selected' : '' ?>>Accept</option>
            <option value="rejected" <?= ($item['booking_item_status'] == 'rejected') ? 'selected' : '' ?>>Reject</option>
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

    <div class="row">
    </div>

    <footer class="footer mt-5 py-3 bg-light">
    </footer>
</main>