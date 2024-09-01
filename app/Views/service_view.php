<?= $this->include('header') ?>

<main class="container mt-4">
    <?php if (isset($service)): ?>
        <div class="card">
            <div class="card-body">
                <?= $this->include('components/gallery_view', ['images' => $images]) ?>
                <!-- Service Details -->
                <h2 class="card-title"><?= esc($service['title']) ?></h2>
                <h5 class="card-subtitle mb-2 text-muted"><?= esc($service['short_description']) ?></h5>

                <p class="card-text"><?= nl2br(esc($service['description'])) ?></p>
                <p class="card-text"><strong>Price:</strong> $<?= esc($service['price']) ?></p>

                <!-- Display Edit Button for Vendor -->
                <?php if (session()->has('user_id') && session()->get('role') == 'vendor' && $service['vendor_id'] == session()->get('user_id')): ?>
                    <a href="<?= base_url('service/edit/' . $service['id']) ?>" class="btn btn-secondary btn-sm">Edit</a>
                <?php endif; ?>

                <!-- Event and Availability Information -->
                <?php if (!empty($availability_statuses)): ?>
                    <h5>Your Events:</h5>
                    <ul class="list-group mb-3">
                        <?php foreach ($availability_statuses as $availability): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span><?= esc($availability['event_title']) ?> - <?= esc($availability['date']) ?></span>
                                <?php if ($availability['status'] === 'Available'): ?>
                                    <span class="badge badge-success">Available</span>
                                    <!-- Dropdown for selecting duration and start time -->
                                    <form action="<?= base_url('cart/add/' . $service['id']) ?>" method="post">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="service_id" value="<?= $service['id'] ?>">
                                        <input type="hidden" name="event_id" value="<?= esc($availability['event_id']) ?>">
                                        <input type="hidden" name="event_date" value="<?= esc($availability['date']) ?>">
                                        <div class="form-group">
                                            <label for="duration">Select Duration:</label>
                                            <select name="duration" class="form-control">
                                                <?php foreach ($service['time_blocks'] as $timeBlock): ?>
                                                    <option value="<?= esc($timeBlock['time_length']) ?>"><?= esc($timeBlock['time_length']) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="start_time">Select Start Time:</label>
                                            <input type="time" name="start_time" class="form-control" required>
                                        </div>
                                        <button type="submit" class="btn btn-success">Add</button>
                                    </form>
                                <?php else: ?>
                                    <span class="badge badge-<?= $availability['status'] === 'Limited Availability' ? 'warning' : 'danger' ?>">
                                        <?= esc($availability['status']) ?>
                                    </span>
                                    <button class="btn btn-secondary" disabled>Add</button>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    <?php else: ?>
        <p>Service not found.</p>
    <?php endif; ?>
</main>

<footer class="footer mt-5 py-3 bg-light">
</footer>

<?= $this->include('footer') ?>
