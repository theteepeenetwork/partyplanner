<?= $this->include('header') ?>

<main class="container mt-4">
    <h2>Vendor Registration</h2>
    <p class="text-muted mb-4">Register as a vendor to list your event services on our platform. Once registered, you'll be able to create service listings and receive bookings from customers.</p>

    <?php if (session()->has('errors')): ?>
        <div class="alert alert-danger">
            <ul>
                <?php foreach (session('errors') as $error): ?>
                    <li><?= esc($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form action="/register/vendor/create" method="post">
        <?= csrf_field() ?>
        <div class="form-group mb-3">
            <label for="name">Business / Full Name:</label>
            <input type="text" class="form-control" id="name" name="name" value="<?= old('name') ?>" required>
        </div>
        <div class="form-group mb-3">
            <label for="username">Username:</label>
            <input type="text" class="form-control" id="username" name="username" value="<?= old('username') ?>" required>
        </div>
        <div class="form-group mb-3">
            <label for="email">Email:</label>
            <input type="email" class="form-control" id="email" name="email" value="<?= old('email') ?>" required>
        </div>
        <div class="form-group mb-3">
            <label for="password">Password:</label>
            <input type="password" class="form-control" id="password" name="password" required>
        </div>
        <div class="form-group mb-3">
            <label for="confirm_password">Confirm Password:</label>
            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
        </div>
        <button type="submit" class="btn btn-primary">Submit Vendor Application</button>
    </form>

    <hr class="mt-4">
    <p class="text-muted">Looking to book services instead? <a href="/register">Register as a customer</a></p>
</main>

<?= $this->include('footer') ?>
