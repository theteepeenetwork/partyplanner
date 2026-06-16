<?= $this->include('header') ?>

<div class="ps-app">
<main class="auth-wrap" data-screen-label="Sign in">
    <aside class="auth-aside">
        <div class="auth-aside-img"><img src="/assets/images/ps-hero-celebration.jpg" alt="A warmly-lit celebration"></div>
        <a class="brand" href="/"><span class="ps">P<span class="dot">.</span>S<span class="dot">.</span></span><span class="name">Partysmith</span></a>
        <div>
            <p class="auth-quote">Plan the whole thing in <em>one place</em> — and keep your payment protected.</p>
            <div class="auth-trust">
                <span><i class="fas fa-shield-halved"></i> Every supplier vetted before listing</span>
                <span><i class="fas fa-lock"></i> Payment held until 48hrs after your event</span>
                <span><i class="fas fa-tag"></i> No booking fees, ever</span>
            </div>
        </div>
    </aside>

    <section class="auth-main">
        <div class="auth-card">
            <p class="eyebrow">Welcome back</p>
            <h1>Sign in to Partysmith</h1>
            <p class="auth-sub">Pick up your planning, compare quotes, and manage your bookings.</p>

            <?php if (session()->has('error')): ?>
                <div class="form-alert error"><?= esc(session('error')) ?></div>
            <?php endif; ?>
            <?php if (session()->has('success')): ?>
                <div class="form-alert ok"><?= esc(session('success')) ?></div>
            <?php endif; ?>

            <form class="form-grid" action="<?= site_url('login/attempt') ?>" method="post">
                <?= csrf_field() ?>
                <div class="field-row">
                    <label for="login">Email</label>
                    <div class="input-icon">
                        <i class="fas fa-envelope"></i>
                        <input class="input" type="text" id="login" name="login" value="<?= esc(old('login') ?? '') ?>" placeholder="you@email.com" autocomplete="username">
                    </div>
                </div>
                <div class="field-row">
                    <div class="field-between">
                        <label for="password">Password</label>
                        <a href="<?= site_url('forgot-password') ?>" class="link-quiet">Forgot password?</a>
                    </div>
                    <div class="input-icon">
                        <i class="fas fa-lock"></i>
                        <input class="input" type="password" id="password" name="password" placeholder="••••••••" autocomplete="current-password">
                    </div>
                </div>
                <label class="check-line"><input type="checkbox" name="remember" checked> <span>Keep me signed in on this device</span></label>
                <button type="submit" class="btn btn-primary btn-block btn-lg">Sign in</button>
            </form>

            <p class="auth-foot">New to Partysmith? <a href="/register">Create an account</a></p>
            <p class="auth-foot" style="margin-top:10px">Are you a supplier? <a href="/register/vendor">List your business</a></p>
        </div>
    </section>
</main>
</div>

<?= $this->include('footer') ?>
