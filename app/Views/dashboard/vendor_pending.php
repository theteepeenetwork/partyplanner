<?= $this->include('header') ?>

<main class="page-main">
<div class="dashboard-wrapper">
    <div class="container">

        <?= $this->include('dashboard/_flash_alerts') ?>

        <?php $vendorStatus = $user['vendor_status'] ?? 'pending'; ?>

        <?php if ($vendorStatus === 'rejected'): ?>
            <div class="shadow-sm p-4 p-md-5 rounded">
                <h1 class="h3 mb-3">Your application wasn't approved</h1>
                <p class="mb-3">Our team reviewed your vendor application and it wasn't approved this time.</p>
                <?php if (! empty($user['vendor_status_reason'])): ?>
                    <div class="mb-3">
                        <strong>Reason given:</strong>
                        <p class="mb-0"><?= nl2br(esc($user['vendor_status_reason'])) ?></p>
                    </div>
                <?php endif; ?>
                <p>If you have questions about this decision, please <a href="<?= site_url('contact') ?>">contact us</a>.</p>
                <a class="btn btn-outline-secondary" href="<?= site_url('/profile/edit') ?>">Edit your profile</a>
            </div>
        <?php else: ?>
            <div class="shadow-sm p-4 p-md-5 rounded">
                <h1 class="h3 mb-3">Your vendor account is under review</h1>
                <p class="mb-3">Thanks for registering with Partysmith. Our team reviews new vendor accounts before
                    listings and bookings go live. We'll email you as soon as your account is approved.</p>
                <p>Questions in the meantime? <a href="<?= site_url('contact') ?>">Get in touch</a>.</p>
                <a class="btn btn-outline-secondary" href="<?= site_url('/profile/edit') ?>">Edit your profile</a>
            </div>
        <?php endif; ?>

    </div>
</div>
</main>

<?= $this->include('footer') ?>
