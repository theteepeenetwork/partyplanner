<?= $this->include('header') ?>

<main class="container mt-4">
    <h2>Your Cart</h2>

    <?php if (!empty($events)): ?>
        <?php foreach ($events as $eventId => $event): ?>
            <div class="event-section">
                <h3>Event: <?= esc($event['title']) ?> (Date: <?= esc($event['date']) ?>)</h3>

                <table class="table">
                    <thead>
                        <tr>
                            <th>Service</th>
                            <th>Price</th>
                            <th>Start Time</th>
                            <th>End Time</th>
                            <th>Remove</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($event['services'] as $service): ?>
                            <tr>
                                <td><?= esc($service['title']) ?></td>
                                <td>£<?= esc($service['price']) ?></td>
                                <td><?= esc($service['start_time']) ?></td>
                                <td><?= esc($service['end_time']) ?></td>
                                <td>
                                    <a href="<?= base_url('cart/remove/' . esc($service['id'])) ?>" class="btn btn-danger btn-sm">
                                        Remove
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endforeach; ?>

        <div class="text-right">

            <form method="post" action="<?= site_url('cart/submitToVendors') ?>">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-success">Submit All Events to Vendors</button>
            </form>
        </div>
    <?php else: ?>
        <p>Your cart is empty.</p>
    <?php endif; ?>
</main>

<?= $this->include('footer') ?>