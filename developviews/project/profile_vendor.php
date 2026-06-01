<?= $this->include('header') ?>
<?= $this->include('service_create/css.php') ?>

<main class="container">

    <section>
        <h2><?= esc($user['name']) ?> (Vendor)</h2>

        <!-- Tabs Navigation -->
        <ul class="nav nav-tabs" id="vendorTabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link" id="main-tab" data-toggle="tab" href="#main" role="tab"
                    data-url="<?= base_url('/profile/main') ?>" aria-controls="main" aria-selected="true">Main</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="services-tab" data-toggle="tab" href="#services" role="tab"
                    data-url="<?= base_url('/profile/services') ?>" aria-controls="services"
                    aria-selected="false">Services</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="bookings-tab" data-toggle="tab" href="#bookings" role="tab"
                    data-url="<?= base_url('/profile/bookings') ?>" aria-controls="bookings"
                    aria-selected="false">Bookings</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="calendar-tab" data-toggle="tab" href="#calendar" role="tab"
                    data-url="<?= base_url('/profile/calendar') ?>" aria-controls="calendar"
                    aria-selected="false">Calendar</a>
            </li>
        </ul>
        <?php if (session()->has('success')): ?>
            <div class="alert alert-success">
                <?= session('success') ?>
            </div>
        <?php elseif (session()->has('error')): ?>
            <div class="alert alert-danger">
                <?= session('error') ?>
            </div>
        <?php endif; ?>

        <!-- Tab Content -->
        <div class="tab-content mt-4" id="vendorTabsContent">
            <div class="tab-pane fade show active" id="tabContent"></div>
        </div>
    </section>

</main>



<?= $this->include('footer') ?>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function () {
        function loadTabContent(tab) {
            var tabLink = $('.nav-link[href="#' + tab + '"]');
            if (tabLink.length) {
                var url = tabLink.data('url');
                $('#tabContent').html('<div class="text-center my-4"><span class="spinner-border"></span> Loading...</div>');
                $.get(url, function (data) {
                    $('#tabContent').html(data);
                }).fail(function () {
                    $('#tabContent').html('<div class="text-danger">Failed to load content. Please try again later.</div>');
                });
            }
        }

        // Detect initial tab from URL hash
        var initialTab = window.location.hash ? window.location.hash.substring(1) : 'main';
        $('.nav-link[href="#' + initialTab + '"]').addClass('active');
        loadTabContent(initialTab);

        // Handle tab clicks
        $('.nav-link').click(function (e) {
            e.preventDefault();
            var tab = $(this).attr('href').substring(1);
            $('.nav-link').removeClass('active');
            $(this).addClass('active');
            loadTabContent(tab);
            history.replaceState(null, null, '#' + tab); // Update URL hash
        });
    });
</script>