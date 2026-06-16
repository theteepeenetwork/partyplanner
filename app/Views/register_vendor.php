<?= $this->include('header') ?>

<div class="ps-app">
<section class="page-head page-head--tall" data-screen-label="Become a vendor">
    <div class="container">
        <nav class="breadcrumb">
            <a href="/vendor-info">For vendors</a><span class="sep">/</span><span class="cur">List your business</span>
        </nav>
        <p class="eyebrow">Become a supplier</p>
        <h1>List your business on Partysmith</h1>
        <p class="ph-lead">Free to join during our founding period. Tell us the essentials and we'll get your account set up — you can add services and photos next.</p>
    </div>
</section>

<div class="container">
    <div class="flow-wrap">
        <?php if (session()->has('errors')): ?>
            <div class="form-alert error">
                <ul>
                    <?php foreach (session('errors') as $error): ?>
                        <li><?= esc($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <div class="flow-card">
            <h2>Your account</h2>
            <p class="flow-sub">We'll use these details to create your supplier login.</p>

            <form class="form-grid" action="/register/vendor/create" method="post">
                <?= csrf_field() ?>
                <div class="field-row">
                    <label for="name">Business name</label>
                    <input class="input" type="text" id="name" name="name" value="<?= esc(old('name') ?? '') ?>" placeholder="Aperture &amp; Co" required>
                </div>
                <div class="form-grid two" style="gap:14px">
                    <div class="field-row">
                        <label for="username">Username</label>
                        <input class="input" type="text" id="username" name="username" value="<?= esc(old('username') ?? '') ?>" placeholder="aperture" required>
                    </div>
                    <div class="field-row">
                        <label for="email">Work email</label>
                        <div class="input-icon">
                            <i class="fas fa-envelope"></i>
                            <input class="input" type="email" id="email" name="email" value="<?= esc(old('email') ?? '') ?>" placeholder="you@business.co.uk" required>
                        </div>
                    </div>
                </div>
                <div class="form-grid two" style="gap:14px">
                    <div class="field-row">
                        <label for="password">Password</label>
                        <div class="input-icon">
                            <i class="fas fa-lock"></i>
                            <input class="input" type="password" id="password" name="password" placeholder="At least 8 characters" required>
                        </div>
                    </div>
                    <div class="field-row">
                        <label for="confirm_password">Confirm password</label>
                        <div class="input-icon">
                            <i class="fas fa-lock"></i>
                            <input class="input" type="password" id="confirm_password" name="confirm_password" placeholder="Re-enter your password" required>
                        </div>
                    </div>
                </div>
                <div class="flow-actions">
                    <a href="/register" class="link-quiet">Looking to book instead? Register as a customer</a>
                    <button type="submit" class="btn btn-primary btn-lg">Submit application</button>
                </div>
            </form>
        </div>

        <p class="auth-foot" style="margin-top:22px">Already have a supplier account? <a href="/login">Sign in</a></p>
    </div>
</div>
</div>

<?= $this->include('footer') ?>
