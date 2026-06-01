<?= $this->include('header') ?>

<main class="container mt-4">
    <h2><?= esc($user['name']) ?>'s Profile</h2>

    <!-- Navigation Tabs -->
    <ul class="nav nav-tabs" id="profileTabs" role="tablist">
        <li class="nav-item">
            <a class="nav-link active" href="<?= base_url('/profile') ?>">Main</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="<?= base_url('/profile/services') ?>">Services</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="<?= base_url('/profile/bookings') ?>">Bookings</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="<?= base_url('/profile/calendar') ?>">Calendar</a>
        </li>
    </ul>

    <!-- Dynamic Content -->
    <div class="mt-4">
        <p>Welcome to your profile! Use the tabs above to manage your services, bookings, and calendar.</p>
    </div>
</main>

<?= $this->include('footer') ?>