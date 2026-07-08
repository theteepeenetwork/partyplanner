<?php
/**
 * Confirmation — "Booking Confirmation Redesign", frame 1a (manage-first split).
 *
 * Hero (celebratory tick + honest "date held" framing) → horizontal
 * "what happens next" → two columns: the account-creation funnel as the primary
 * column (claim the guest account created at checkout by setting a password),
 * with the booking summary + actions as the aside.
 *
 * The account form only renders when this session may claim the account
 * ($canCreateAccount, gated in the controller). Otherwise the column shows a
 * "created" success state or a sign-in prompt — never a claimable form for an
 * account that isn't this session's to claim.
 */
?>
<?= $this->include('tenant_header') ?>
<?php
$bn        = $site['business_name'] ?? 'Storefront';
$firstWord = strtok($bn, ' ');
$paidToday = $payment !== null ? (float) $payment['amount_paid'] : 0.0;
$balance   = (float) ($booking['balance_due'] ?? 0);
$dateLabel = ! empty($event['date']) ? date('D j M Y', strtotime($event['date'])) : 'Date to be agreed';
$dayLabel  = ! empty($event['date']) ? date('D j M', strtotime($event['date'])) : 'On the day';
$where     = trim((string) ($event['postcode'] ?? $event['location'] ?? ''));
$firstName = trim((string) (session()->get('tenant_guest_name') ?? ''));
$phone     = trim((string) ($site['phone'] ?? ''));
$phoneHref = $phone !== '' ? 'tel:' . preg_replace('/[^0-9+]/', '', $phone) : '';
$titleLine = implode(' + ', array_map(static fn ($i) => $i['service_title'], $items));
$paid      = '£' . number_format($paidToday, 2);
$bal       = '£' . number_format($balance, 2);
$formEmail = $guestEmail !== '' ? $guestEmail : trim((string) ($account['email'] ?? ''));
?>

<div class="sf-shell sf-confirm" style="padding-bottom: 44px;">
    <?php if (session()->getFlashdata('error')): ?>
        <div class="sf-flash error" role="alert" style="margin-top: 18px;"><?= esc(session()->getFlashdata('error')) ?></div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('info')): ?>
        <div class="sf-flash info" style="margin-top: 18px;"><?= esc(session()->getFlashdata('info')) ?></div>
    <?php endif; ?>

    <div class="sf-confirm-hero">
        <div class="sf-tick"><i class="fas fa-check" aria-hidden="true"></i></div>
        <h1 class="sf-confirm-h">Date held — you're nearly booked<?= $firstName !== '' ? ', ' . esc($firstName) : '' ?></h1>
        <p class="sf-confirm-sub">
            Ref <b><?= esc($reference) ?></b> · <?= esc($paid) ?> paid<?= $guestEmail !== '' ? ' · receipt sent to ' . esc($guestEmail) : '' ?>
        </p>
    </div>

    <div class="sf-panel sf-nextcard" style="box-shadow: var(--sf-sh-card);">
        <h2 class="sf-sec-h" style="text-align: center; margin: 0;">What happens next</h2>
        <ol class="sf-next-steps">
            <li class="sf-step is-now">
                <span class="disc">1</span>
                <span class="when">Within 24 hrs</span>
                <b><?= esc($firstWord) ?> confirms</b>
                <p><?= esc($firstWord) ?> reviews and emails you a confirmation. If they can't make it, your <?= esc($paid) ?> comes straight back — guaranteed.</p>
            </li>
            <li class="sf-step">
                <span class="disc">2</span>
                <span class="when">Week before</span>
                <b>Reminder &amp; balance</b>
                <p>We email a reminder with your arrival time and the <?= esc($bal) ?> balance — due before your event date.</p>
            </li>
            <li class="sf-step">
                <span class="disc">3</span>
                <span class="when"><?= esc($dayLabel) ?></span>
                <b>On the day</b>
                <p><?= esc($firstWord) ?> arrives with time to set up. Enjoy the party.</p>
            </li>
        </ol>
    </div>

    <div class="sf-confirm-cols">
        <?php if ($canCreateAccount): ?>
            <div class="sf-panel sf-account">
                <p class="sf-eyebrow">Recommended next step</p>
                <h2 class="sf-account-h">Create an account to manage your booking</h2>
                <p class="sf-account-sub">Everything about your <?= esc($dayLabel) ?> booking — and every booking after — kept in one place. Takes about 20 seconds.</p>

                <div class="sf-benefits">
                    <div class="sf-benefit"><span class="ck"><i class="fas fa-check"></i></span>Manage &amp; view this booking anytime</div>
                    <div class="sf-benefit"><span class="ck"><i class="fas fa-check"></i></span>Pay the remaining <?= esc($bal) ?> online</div>
                    <div class="sf-benefit"><span class="ck"><i class="fas fa-check"></i></span>Message <?= esc($firstWord) ?> directly</div>
                    <div class="sf-benefit"><span class="ck"><i class="fas fa-check"></i></span>Reschedule or cancel in a tap</div>
                    <div class="sf-benefit"><span class="ck"><i class="fas fa-check"></i></span>Rebook &amp; see your booking history</div>
                    <div class="sf-benefit"><span class="ck"><i class="fas fa-check"></i></span>Save your details for next time</div>
                </div>

                <form class="sf-formhead" action="/account/create" method="post">
                    <?= csrf_field() ?>
                    <input type="hidden" name="booking_id" value="<?= (int) $booking['id'] ?>">
                    <div class="sf-form-2">
                        <label class="sf-field"><span>Full name</span>
                            <input class="sf-input" type="text" name="name" value="<?= esc((string) ($account['name'] ?? $firstName), 'attr') ?>" maxlength="100" required>
                        </label>
                        <label class="sf-field"><span>Username</span>
                            <input class="sf-input" type="text" name="username" value="<?= esc((string) ($account['username'] ?? ''), 'attr') ?>" pattern="[a-zA-Z0-9_.]{3,50}" required>
                        </label>
                    </div>
                    <label class="sf-field"><span>Email</span>
                        <span class="sf-prefill">
                            <input class="sf-input" type="email" value="<?= esc($formEmail, 'attr') ?>" readonly>
                            <span class="tag"><i class="fas fa-circle-check"></i>Receipt sent here</span>
                        </span>
                    </label>
                    <div class="sf-form-2">
                        <label class="sf-field"><span>Create password</span>
                            <span class="sf-inputwrap">
                                <input class="sf-input" type="password" name="password" placeholder="At least 8 characters" minlength="8" required>
                                <button type="button" class="reveal" data-reveal>Show</button>
                            </span>
                        </label>
                        <label class="sf-field"><span>Confirm password</span>
                            <span class="sf-inputwrap">
                                <input class="sf-input" type="password" name="confirm_password" placeholder="Re-enter password" minlength="8" required>
                                <button type="button" class="reveal" data-reveal>Show</button>
                            </span>
                        </label>
                    </div>
                    <label class="sf-agree"><input type="checkbox" name="agree_terms" value="1" required><span>I agree to the <a href="<?= esc($mainSiteUrl, 'attr') ?>/contact">Terms of Service</a> and <a href="<?= esc($mainSiteUrl, 'attr') ?>/contact">Privacy Policy</a>.</span></label>
                    <button type="submit" class="sf-btn block">Create account &amp; save my booking <i class="fas fa-arrow-right" aria-hidden="true"></i></button>
                    <p class="sf-signin">Rather not? Your booking is safe — the confirmation link is in your email.</p>
                </form>
            </div>
        <?php elseif ($accountCreated): ?>
            <div class="sf-panel sf-account">
                <div class="sf-tick" style="width: 44px; height: 44px; font-size: 18px; margin: 0 0 12px;"><i class="fas fa-check" aria-hidden="true"></i></div>
                <h2 class="sf-account-h">Your account is ready</h2>
                <p class="sf-account-sub">Sign in on the main site with <b><?= esc($formEmail) ?></b> to manage this booking, pay your balance and message <?= esc($firstWord) ?>.</p>
                <a class="sf-btn" href="<?= esc($mainSiteUrl, 'attr') ?>/login">Sign in to manage <i class="fas fa-arrow-right" aria-hidden="true"></i></a>
            </div>
        <?php else: ?>
            <div class="sf-panel sf-account">
                <p class="sf-eyebrow">Manage your booking</p>
                <h2 class="sf-account-h">You already have an account</h2>
                <p class="sf-account-sub">This email is already registered. Sign in on the main site to manage this booking, pay your balance and message <?= esc($firstWord) ?>.</p>
                <a class="sf-btn" href="<?= esc($mainSiteUrl, 'attr') ?>/login">Sign in to manage <i class="fas fa-arrow-right" aria-hidden="true"></i></a>
            </div>
        <?php endif; ?>

        <aside class="sf-panel" style="box-shadow: var(--sf-sh-card);">
            <p class="sf-aside-h">Your booking</p>
            <div class="sf-svc">
                <div class="sf-thumb"><i class="fas fa-image" aria-hidden="true"></i></div>
                <div>
                    <p class="sf-svc-t"><?= esc($titleLine) ?></p>
                    <p class="sf-svc-s"><?= esc($dateLabel) ?><?= $where !== '' ? ' · ' . esc($where) : '' ?></p>
                </div>
            </div>
            <div class="sf-quote-card" style="margin: 0 0 14px;">
                <div class="row"><span class="l">Paid today</span><span class="a"><?= esc($paid) ?></span></div>
                <div class="row total"><span class="l">Balance due before the event</span><span class="a"><?= esc($bal) ?></span></div>
            </div>
            <div class="sf-actions2">
                <a class="sf-btn-outline" href="/booked/<?= (int) $booking['id'] ?>/calendar.ics">
                    <i class="fas fa-calendar-plus" aria-hidden="true"></i>Add to calendar
                </a>
                <?php if ($phone !== ''): ?>
                    <a class="sf-btn-outline" href="<?= esc($phoneHref, 'attr') ?>">
                        <i class="fas fa-phone" aria-hidden="true"></i>Contact <?= esc($firstWord) ?>
                    </a>
                <?php endif; ?>
            </div>
            <p class="sf-microcopy" style="text-align: left; margin-top: 14px;">
                <i class="fas fa-lock" style="color: var(--sf-good);" aria-hidden="true"></i>
                No account needed to keep this booking — the confirmation link is in your email.
            </p>
        </aside>
    </div>
</div>

<script>
/* Show/Hide password toggles on the account form. */
(function () {
    document.querySelectorAll('[data-reveal]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var input = btn.closest('.sf-inputwrap').querySelector('input');
            if (!input) return;
            var show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            btn.textContent = show ? 'Hide' : 'Show';
        });
    });
})();
</script>

<?= $this->include('tenant_footer') ?>
