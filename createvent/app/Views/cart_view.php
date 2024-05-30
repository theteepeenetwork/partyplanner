<?= $this->include('header') ?>

<main class="container mt-4">
    <h2>Your Cart</h2>

    <?php if (!empty($cartItems)): ?>
        <table class="table">
            <thead>
                <tr>
                    <th>Service</th>
                    <th>Price</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                    $total = 0; // Initialize total
                    foreach ($cartItems as $item):
                        $total += $item['service']['price']; // Update total 
                ?>
                    <tr>
                        <td><?= esc($item['service']['title']) ?></td>
                        <td>$<?= esc($item['service']['price']) ?></td>
                        <td>
                            <a href="<?= base_url('cart/remove/' . esc($item['id'])) ?>" class="btn btn-danger btn-sm">Remove</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="text-right">
            <h4>Total: $<?= number_format($total, 2) ?></h4>

            <form method="post" action="<?= site_url('cart/submit') ?>">
                <?= csrf_field() ?>
                <input type="hidden" name="event_id" value=""> 
                <button type="submit" class="btn btn-success">Submit to Vendors</button>
            </form>
        </div>
    <?php else: ?>
        <p>Your cart is empty.</p>
    <?php endif; ?>
</main>

<footer class="footer mt-5 py-3 bg-light">
</footer>
