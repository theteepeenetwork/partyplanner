<?= $this->include('header') ?>

<div class="ps-app">
<main class="auth-wrap" data-screen-label="Create account">
    <aside class="auth-aside">
        <div class="auth-aside-img"><img src="/assets/images/ps-hero-event.jpg" alt="An event in full swing"></div>
        <a class="brand" href="/"><span class="ps">P<span class="dot">.</span>S<span class="dot">.</span></span><span class="name">Partysmith</span></a>
        <div>
            <p class="auth-quote">Create an account and your <em>quotes, bookings and payments</em> live in one tidy place.</p>
            <div class="auth-trust">
                <span><i class="fas fa-file-lines"></i> Compare structured quotes side by side</span>
                <span><i class="fas fa-calendar-check"></i> Track every supplier under one event</span>
                <span><i class="fas fa-shield-halved"></i> Protected payments as standard</span>
            </div>
        </div>
    </aside>

    <section class="auth-main">
        <div class="auth-card">
            <p class="eyebrow">Get started</p>
            <h1>Create your account</h1>
            <p class="auth-sub">Free to join — start comparing quotes in minutes.</p>

            <?php if (session()->has('errors')): ?>
                <div class="form-alert error">
                    <ul>
                        <?php foreach (session('errors') as $error): ?>
                            <li><?= esc($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form class="form-grid" action="/register/create" method="post">
                <?= csrf_field() ?>
                <div class="form-grid two" style="gap:14px">
                    <div class="field-row">
                        <label for="name">Full name</label>
                        <input class="input" type="text" id="name" name="name" value="<?= esc(old('name') ?? '') ?>" placeholder="Sienna Rai" required>
                    </div>
                    <div class="field-row">
                        <label for="username">Username</label>
                        <input class="input" type="text" id="username" name="username" value="<?= esc(old('username') ?? '') ?>" placeholder="sienna" required>
                    </div>
                </div>
                <div class="field-row">
                    <label for="email">Email</label>
                    <div class="input-icon">
                        <i class="fas fa-envelope"></i>
                        <input class="input" type="email" id="email" name="email" value="<?= esc(old('email') ?? '') ?>" placeholder="you@email.com" required>
                    </div>
                </div>
                <div class="field-row">
                    <label for="password">Password</label>
                    <div class="input-icon">
                        <i class="fas fa-lock"></i>
                        <input class="input" type="password" id="password" name="password" placeholder="At least 8 characters" required>
                    </div>
                    <span class="field-hint">Use 8+ characters with a mix of letters and numbers.</span>
                </div>
                <div class="field-row">
                    <label for="confirm_password">Confirm password</label>
                    <div class="input-icon">
                        <i class="fas fa-lock"></i>
                        <input class="input" type="password" id="confirm_password" name="confirm_password" placeholder="Re-enter your password" required>
                    </div>
                </div>
                <label class="check-line"><input type="checkbox" name="agree_terms" required> <span>I agree to the <a href="/contact">Terms of Service</a> and <a href="/contact">Privacy Policy</a>.</span></label>
                <label class="check-line"><input type="checkbox" name="marketing_opt_in" checked> <span>Send me occasional planning tips and supplier highlights.</span></label>
                <button type="submit" class="btn btn-primary btn-block btn-lg">Create account</button>
            </form>

            <p class="auth-foot">Already have an account? <a href="/login">Sign in</a></p>
            <p class="auth-foot" style="margin-top:10px">Want to sell on Partysmith? <a href="/register/vendor">Become a supplier</a></p>
        </div>
    </section>
</main>
</div>

<?= $this->include('footer') ?>
