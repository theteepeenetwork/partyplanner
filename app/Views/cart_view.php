<?= $this->include('header') ?>

<main class="container mt-4">
    <h2>Your Cart</h2>

    <?php if (!empty($cartItems)):
        $total = 0; // Initialize total before the loop
        ?>
        <table class="table">
            <thead>
                <!-- Add table headings -->
            </thead>
            <tbody>
                <?php foreach ($cartItems as $item):
                    $subtotal = $item['service']['price'];
                    $total += $subtotal;
                    ?>
                    <tr>
                        <td><?= esc($item['service']['title']) ?></td>
                        <td>£<?= esc($item['service']['price']) ?></td>
                        <td>
                            <a href="<?= base_url('cart/remove/' . esc($item['id'])) ?>"
                                class="btn btn-danger btn-sm">Remove</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="text-right">
            <h4>Total: £<?= number_format($total, 2) ?></h4>

            <?php if (!empty($events)): ?>
                <form method="post" action="<?= site_url('cart/submitToVendors') ?>">
                    <?= csrf_field() ?>
                    <div class="form-group">
                        <label for="event_id">Select Event:</label>
                        <select class="form-control" id="event_id" name="event_id">
                            <?php foreach ($events as $event): ?>
                                <option value="<?= esc($event['id']) ?>"><?= esc($event['title']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-success">Submit to Vendors</button>
                </form>

            <?php else: ?>
                <p>You need to <a href="<?= site_url('event/create') ?>">create an event</a> before submitting your cart.</p>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <p>Your cart is empty.</p>
    <?php endif; ?>
</main>