<?= $this->include('header') ?>

<!-- Bootstrap CSS -->
<link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">

<main class="container mt-4">
    <h2><?= esc($user['name']) ?> (Vendor)</h2>

    <!-- Tabs Navigation -->
    <ul class="nav nav-tabs" id="vendorTabs" role="tablist">
        <li class="nav-item">
            <a class="nav-link active" id="main-tab" data-toggle="tab" href="#main" role="tab" aria-controls="main" aria-selected="true">Main</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="services-tab" data-toggle="tab" href="#services" role="tab" aria-controls="services" aria-selected="false">Services</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="bookings-tab" data-toggle="tab" href="#bookings" role="tab" aria-controls="bookings" aria-selected="false">Bookings</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="calendar-tab" data-toggle="tab" href="#calendar" role="tab" aria-controls="calendar" aria-selected="false">Calendar</a>
        </li>
    </ul>

    <!-- Tabs Content -->
    <div class="tab-content mt-4" id="vendorTabsContent">
        <!-- Main Tab -->
        <div class="tab-pane fade show active" id="main" role="tabpanel" aria-labelledby="main-tab">
            <h3>Welcome, <?= esc($user['name']) ?>!</h3>
            <p>Use the tabs above to manage your services, view bookings, or check your calendar.</p>
        </div>

        <!-- Services Tab -->
        <div class="tab-pane fade" id="services" role="tabpanel" aria-labelledby="services-tab">
            <h2>Active Listings</h2>
            <?php if (!empty($activeServices)): ?>
                <div class="row">
                    <?php foreach ($activeServices as $service): ?>
                        <div class="col-md-4 mb-4">
                            <div class="card">
                                <?php if (!empty($service['images'])): ?>
                                    <img src="<?= base_url(esc($service['images'][0]['thumbnail_path'])) ?>" class="card-img-top" alt="<?= esc($service['title']) ?>">
                                <?php endif; ?>
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
            <?php if (!empty($inactiveServices)): ?>
                <div class="row">
                    <?php foreach ($inactiveServices as $service): ?>
                        <div class="col-md-4 mb-4">
                            <div class="card">
                                <?php if (!empty($service['images'])): ?>
                                    <img src="<?= base_url(esc($service['images'][0]['thumbnail_path'])) ?>" class="card-img-top" alt="<?= esc($service['title']) ?>">
                                <?php endif; ?>
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

            <a href="/service/create" class="btn btn-primary mb-3">Add New Service</a>
        </div>

        <!-- Bookings Tab -->
        <div class="tab-pane fade" id="bookings" role="tabpanel" aria-labelledby="bookings-tab">
            <h2>Bookings</h2>
            <?php if (!empty($bookingItems)): ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Event Title</th>
                            <th>Event Date</th>
                            <th>Ceremony Type</th>
                            <th>Location</th>
                            <th>Service</th>
                            <th>Start Time</th>
                            <th>End Time</th>
                            <th>Price</th>
                            <th>Status</th>
                            <th>Actions</th>
                            <th>Chat</th>
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
                                <td><?= esc($item['start_time']) ?></td>
                                <td><?= esc($item['end_time']) ?></td>
                                <td>£<?= esc($item['price']) ?></td>
                                <td>
                                    <span class="badge badge-pill <?= $item['status'] == 'accepted' ? 'badge-success' : ($item['status'] == 'pending' ? 'badge-warning' : 'badge-danger') ?>">
                                        <?= ucfirst($item['status']) ?>
                                    </span>
                                </td>
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
                                <td>
                                    <a href="<?= base_url('chat/start/' . $item['customer_id'] . '/' . $item['service_id']) ?>" class="chat-icon">
                                        <i class="fa fa-comments"></i>
                                        <?php if ($item['has_new_messages']): ?>
                                            <span class="notification-icon"></span>
                                        <?php endif; ?>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No booking requests at this time.</p>
            <?php endif; ?>
        </div>

        <!-- Calendar Tab -->
        <div class="tab-pane fade" id="calendar" role="tabpanel" aria-labelledby="calendar-tab">

            <?= $this->include('vendor_calendar') ?>
        </div>
    </div>
</main>

<footer class="footer mt-5 py-3 bg-light">
</footer>

<?= $this->include('footer') ?>

<!-- Bootstrap JS (for tabs functionality) -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
