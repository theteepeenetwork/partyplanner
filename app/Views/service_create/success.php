<?= $this->include('header') ?>

<?= $this->include('service_create/css.php') ?>

<body>

    <!-- Success and Error Messages -->
    <?php if (session()->has('success')): ?>
        <div class="alert alert-success">
            <?= session('success') ?>
        </div>
    <?php elseif (session()->has('error')): ?>
        <div class="alert alert-danger">
            <?= session('error') ?>
        </div>
    <?php endif; ?>

    <div class="container text-center success-container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow-lg p-4">
                    <div class="card-body">
                        <div class="mb-4">
                            <i class="bi bi-check-circle success-icon"></i>
                        </div>
                        <h1 class="display-4">Success!</h1>
                        <p class="lead">Your service has been successfully submitted.</p>
                        <p>You can now view or manage your service in your dashboard.</p>
                        <a href="/profile#services" class="btn btn-primary btn-lg mt-3">Go to Dashboard</a>
                        <a href="/service/create" class="btn btn-outline-secondary btn-lg mt-3">Add Another Service</a>

                        <?php if (!empty($hostProfileIncomplete)): ?>
                            <div class="alert alert-info mt-4 text-start">
                                <strong>Complete your host profile</strong>
                                <p class="mb-2">Add a photo, bio and tagline so customers can see who's behind this service — it appears in the &ldquo;Meet your host&rdquo; section of every listing.</p>
                                <a href="/profile/host-profile" class="btn btn-outline-primary">
                                    <i class="bi bi-person-badge"></i> Set up host profile
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.js"></script>
</body>

</html>