<?= $this->include('header') ?>

<main class="container mt-4">
    <h2>Add New Service</h2>

    <?php if (session()->has('success')): ?>
        <div class="alert alert-success">
            <?= session('success') ?>
        </div>

        <p>You can now <a href="/profile">view your services</a> in your profile.</p>
    <?php else: ?>
        <div class="alert alert-danger">
            An error occurred while adding the service.

            <?php if (isset($error)): ?>
                <p><strong>Error Details:</strong></p>
                <pre><?= esc($error) ?></pre> 
            <?php endif; ?>

            <?php if (isset($validation)): ?>
                <p><strong>Validation Errors:</strong></p>
                <ul>
                    <?php foreach ($validation->getErrors() as $error): ?>
                        <li><?= esc($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>

            <?php if (isset($db_error)): ?>
                <p><strong>Database Error:</strong></p>
                <pre><?= esc($db_error) ?></pre>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</main>

<footer class="footer mt-5 py-3 bg-light">
    </footer>
