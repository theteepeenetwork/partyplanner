<?= $this->include('header') ?>

<div class="ps-app">
<main>
  <section class="page-head">
    <div class="container">
      <div class="breadcrumb">
        <a href="/profile/events">My events</a><span class="sep">/</span>
        <span class="cur">Account settings</span>
      </div>
      <p class="eyebrow">Your account</p>
      <h1 class="heading">Account settings</h1>
    </div>
  </section>

  <section class="section" style="padding-block:clamp(40px,5vw,64px)">
    <div class="container">

      <?php if (session()->getFlashdata('success')): ?>
        <div class="form-alert ok"><?= esc(session()->getFlashdata('success')) ?></div>
      <?php endif; ?>
      <?php if (session()->getFlashdata('error')): ?>
        <div class="form-alert error"><?= esc(session()->getFlashdata('error')) ?></div>
      <?php endif; ?>
      <?php if (session()->has('errors')): ?>
        <div class="form-alert error">
          <ul>
            <?php foreach ((array) session('errors') as $error): ?>
              <li><?= esc($error) ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
      <?php endif; ?>

      <?php if (isset($user)): ?>
        <div class="settings-grid">
          <!-- side nav -->
          <nav class="settings-nav">
            <a href="#profile" data-section="profile" class="on"><i class="fas fa-user"></i> Profile</a>
            <a href="#security" data-section="security"><i class="fas fa-lock"></i> Security</a>
            <a href="#payments" data-section="payments"><i class="fas fa-credit-card"></i> Payment methods</a>
            <a href="#notifications" data-section="notifications"><i class="fas fa-bell"></i> Notifications</a>
            <a href="/logout" style="color:#a23a32"><i class="fas fa-right-from-bracket"></i> Sign out</a>
          </nav>

          <!-- content -->
          <div>
            <!-- ===== PROFILE (real, functional) ===== -->
            <section class="settings-section" data-section="profile" id="profile">
              <h2>Profile</h2>
              <p class="ss-sub">This is how suppliers see you when you enquire.</p>

              <?php
              $displayName = trim((string) old('name', $user['name'] ?? ''));
              $initials = '';
              foreach (preg_split('/\s+/', $displayName) as $part) {
                  if ($part !== '') {
                      $initials .= strtoupper($part[0]);
                  }
                  if (strlen($initials) >= 2) {
                      break;
                  }
              }
              if ($initials === '') {
                  $initials = strtoupper(substr((string) ($user['email'] ?? '?'), 0, 1));
              }
              ?>
              <div class="avatar-row">
                <span class="avatar-lg"><?= esc($initials) ?></span>
                <div class="field-hint">Profile photos are coming soon.</div>
              </div>

              <form action="/profile/edit" method="post">
                <?= csrf_field() ?>
                <div class="form-grid two">
                  <div class="field-row span2">
                    <label for="name">Name</label>
                    <input class="input" type="text" id="name" name="name" value="<?= esc(old('name', $user['name'] ?? ''), 'attr') ?>">
                  </div>
                  <div class="field-row">
                    <label for="username">Username</label>
                    <div class="input-icon"><i class="fas fa-at"></i>
                      <input class="input" type="text" id="username" name="username" value="<?= esc(old('username', $user['username'] ?? ''), 'attr') ?>">
                    </div>
                  </div>
                  <div class="field-row">
                    <label for="email">Email</label>
                    <div class="input-icon"><i class="fas fa-envelope"></i>
                      <input class="input" type="email" id="email" name="email" value="<?= esc(old('email', $user['email'] ?? ''), 'attr') ?>">
                    </div>
                  </div>
                </div>
                <div style="display:flex;gap:12px;margin-top:22px">
                  <button type="submit" class="btn btn-primary">Save changes</button>
                  <a href="/profile" class="btn btn-oauth">Cancel</a>
                </div>
              </form>
            </section>

            <!-- ===== SECURITY (placeholder) ===== -->
            <section class="settings-section" data-section="security" id="security" style="display:none">
              <h2>Security</h2>
              <p class="ss-sub">Keep your account safe.</p>
              <div class="form-alert">Password management is coming soon — these controls are a preview and don't change your account yet.</div>
              <div class="form-grid two">
                <div class="field-row span2"><label>Current password</label><div class="input-icon"><i class="fas fa-lock"></i><input class="input" type="password" placeholder="&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;" disabled></div></div>
                <div class="field-row"><label>New password</label><input class="input" type="password" placeholder="At least 8 characters" disabled></div>
                <div class="field-row"><label>Confirm new password</label><input class="input" type="password" placeholder="Repeat new password" disabled></div>
              </div>
              <div class="setting-toggle-row" style="margin-top:18px"><div><b>Two-factor authentication</b><span>Add an extra layer of security at sign-in.</span></div><div class="switch" role="switch" aria-checked="false" aria-disabled="true"></div></div>
              <div style="margin-top:18px"><button type="button" class="btn btn-primary" disabled>Update password</button></div>
            </section>

            <!-- ===== PAYMENT METHODS (placeholder) ===== -->
            <section class="settings-section" data-section="payments" id="payments" style="display:none">
              <h2>Payment methods</h2>
              <p class="ss-sub">Used when you book and to receive any refunds.</p>
              <div class="form-alert">Saved payment methods are coming soon. You can still pay securely at checkout.</div>
              <div class="quote-list">
                <div class="quote">
                  <span class="quote-av" style="background:rgba(28,74,54,0.08);color:var(--green);display:flex;align-items:center;justify-content:center"><i class="fas fa-credit-card"></i></span>
                  <div><b>No cards saved yet</b><div class="qmeta"><span>Add one to check out faster</span></div></div>
                  <div class="qprice"></div>
                </div>
              </div>
              <div style="margin-top:16px"><button type="button" class="btn btn-ghost" disabled><i class="fas fa-plus"></i> Add payment method</button></div>
            </section>

            <!-- ===== NOTIFICATIONS (placeholder) ===== -->
            <section class="settings-section" data-section="notifications" id="notifications" style="display:none">
              <h2>Notifications</h2>
              <p class="ss-sub">Choose what we email you about.</p>
              <div class="form-alert">Notification preferences are coming soon — toggles here are a preview and aren't saved yet.</div>
              <div class="setting-toggle-row"><div><b>New quotes</b><span>When a supplier sends a quote for your event.</span></div><div class="switch on" role="switch" aria-checked="true"></div></div>
              <div class="setting-toggle-row"><div><b>Booking updates</b><span>Confirmations, reminders and changes.</span></div><div class="switch on" role="switch" aria-checked="true"></div></div>
              <div class="setting-toggle-row"><div><b>Planning tips</b><span>Occasional ideas and supplier highlights.</span></div><div class="switch" role="switch" aria-checked="false"></div></div>
              <div style="margin-top:18px"><button type="button" class="btn btn-primary" disabled>Save preferences</button></div>
            </section>
          </div>
        </div>
      <?php else: ?>
        <div class="form-alert error">User not found.</div>
      <?php endif; ?>

    </div>
  </section>
</main>
</div>

<?= $this->include('footer') ?>
