<?= $this->include('header') ?>
<link rel="stylesheet" href="<?= base_url('assets/css/service-form.css'); ?>">

<main class="container mt-4">
    <h2>Login</h2>

    <?php if (session()->has('error')): ?>
        <div class="alert alert-danger">
            <?= esc(session('error')) ?>
        </div>
    <?php endif; ?>

    <?php if (session()->has('success')): ?>
        <div class="alert alert-success">
            <?= esc(session('success')) ?>
        </div>
    <?php endif; ?>

    <section>
        <form action="<?= site_url('login/attempt') ?>" method="post" class="service-form">
            <?= csrf_field() ?>
            <div class="form-group">
                <label for="login">Email:</label>
                <input type="text" class="form-control" id="login" name="login" value="<?= esc(old('login') ?? '') ?>">
            </div>
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" class="form-control" id="password" name="password">
            </div>
            <button type="submit" class="btn btn-primary">Login</button>
        </form>
        <p class="mt-3 mb-0"><a href="<?= site_url('forgot-password') ?>">Forgot password?</a></p>
    </section>
</main>

<?= $this->include('footer') ?>
