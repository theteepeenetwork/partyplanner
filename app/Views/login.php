<?= $this->include('header') ?>
<link rel="stylesheet" href="<?= base_url('assets/css/service-form.css'); ?>">

<main class="container mt-4">
    <h2>Login</h2>

    <?php if (session()->has('error')): ?>
        <div class="alert alert-danger">
            <?= session('error') ?>
        </div>
    <?php endif; ?>
    <section>
        <form action="/login/attempt" method="post" class="service-form">
            <?= csrf_field() ?>
            <div class="form-group">
                <label for="login">Email:</label>
                <input type="text" class="form-control" id="login" name="login" value="<?= old('login') ?>">
            </div>
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" class="form-control" id="password" name="password">
            </div>
            <button type="submit" class="btn btn-primary">Login</button>
        </form>
</main>
</section>