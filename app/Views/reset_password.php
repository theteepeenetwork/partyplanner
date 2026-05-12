<?= $this->include('header') ?>

<main class="container mt-4">
    <h2>Choose a new password</h2>
    <p class="text-muted mb-4">
        Use at least 8 characters. For a strong password, combine words or use a mix of letters, numbers, and symbols.
        Avoid reusing a password from another site.
    </p>

    <?php if (session()->has('errors')): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php foreach (session('errors') as $error): ?>
                    <li><?= esc($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form action="<?= site_url('reset-password') ?>" method="post" class="mb-4">
        <?= csrf_field() ?>
        <input type="hidden" name="token" value="<?= esc(old('token', $token ?? '')) ?>">
        <div class="form-group mb-3">
            <label for="password">New password</label>
            <input type="password" class="form-control" id="password" name="password" required autocomplete="new-password" minlength="8">
        </div>
        <div class="form-group mb-3">
            <label for="password_confirm">Confirm new password</label>
            <input type="password" class="form-control" id="password_confirm" name="password_confirm" required autocomplete="new-password" minlength="8">
        </div>
        <button type="submit" class="btn btn-primary">Update password</button>
    </form>

    <p class="text-muted"><a href="<?= site_url('login') ?>">Back to sign in</a></p>
</main>

<?= $this->include('footer') ?>
