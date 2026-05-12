<?= $this->include('header') ?>

<main class="container mt-4">
    <h2>Reset your password</h2>
    <p class="text-muted mb-4">
        Enter the email address you use for your account. For security, we never confirm whether an address is registered.
        If we find a matching account, we will send reset instructions.
    </p>

    <?php if (session()->has('success')): ?>
        <div class="alert alert-success">
            <?= esc(session('success')) ?>
        </div>
    <?php endif; ?>

    <?php if (session()->has('error')): ?>
        <div class="alert alert-danger">
            <?= esc(session('error')) ?>
        </div>
    <?php endif; ?>

    <?php if (session()->has('errors')): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php foreach (session('errors') as $error): ?>
                    <li><?= esc($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form action="<?= site_url('forgot-password') ?>" method="post" class="mb-4">
        <?= csrf_field() ?>
        <div class="form-group mb-3">
            <label for="email">Email</label>
            <input type="email" class="form-control" id="email" name="email" value="<?= esc(old('email') ?? '') ?>" required autocomplete="email">
        </div>
        <button type="submit" class="btn btn-primary">Send reset instructions</button>
    </form>

    <p class="text-muted"><a href="<?= site_url('login') ?>">Back to sign in</a></p>
</main>

<?= $this->include('footer') ?>
