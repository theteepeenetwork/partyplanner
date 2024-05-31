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




    <h2>My Events and bookings</h2>


    <?php if (!empty($events)): ?>
        <div id="accordion">
            <?php foreach ($events as $event): ?>
                <div class="card">
                    <div class="card-header" id="heading<?= esc($event['id']) ?>">
                        <h5 class="mb-0">
                            <button class="btn btn-link" data-toggle="collapse" data-target="#collapse<?= esc($event['id']) ?>"
                                aria-expanded="true" aria-controls="collapse<?= esc($event['id']) ?>">
                                <?= esc($event['title']) ?>
                            </button>
                        </h5>
                    </div>

                    <div id="collapse<?= esc($event['id']) ?>" class="collapse"
                        aria-labelledby="heading<?= esc($event['id']) ?>" data-parent="#accordion">
                        <div class="card-body">
                            <?php if (!empty($event['bookingItems'])): ?>
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Service</th>
                                            <th>Price</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($event['bookingItems'] as $bookingItem): ?>
                                            <tr>
                                                <td><?= esc($bookingItem['title']) ?></td>
                                                <td>$<?= esc($bookingItem['price']) ?></td>
                                                <td>
                                                    <span
                                                        class="badge badge-pill <?= $bookingItem['status'] == 'accepted' ? 'badge-success' : ($bookingItem['status'] == 'pending' ? 'badge-warning' : 'badge-danger') ?>">
                                                        <?= ucfirst($bookingItem['status']) ?>
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php else: ?>
                                <p>No services booked for this event yet.</p>
                            <?php endif; ?>
                            <div class="card-body">
                                <a href="<?= base_url('/service/search?q=') ?>" class="btn btn-primary">Add Services to this
                                    event</a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p>You don't have any events yet.</p>
    <?php endif; ?>

</main>

<footer class="footer mt-5 py-3 bg-light">
</footer>


</main>