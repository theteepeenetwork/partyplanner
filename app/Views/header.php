<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <?= csrf_meta() ?>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#3A312D">
    <?php
    $ogTitle       = $pageTitle ?? 'For Your Events';
    $ogDescription = $metaDescription ?? 'A UK marketplace to discover event services, request quotes and manage bookings.';
    $ogImage       = $ogImage ?? base_url('assets/images/hero-event-planning.jpg');
    ?>
    <title><?= esc($ogTitle) ?></title>
    <meta name="description" content="<?= esc($ogDescription) ?>">
    <link rel="canonical" href="<?= current_url() ?>">

    <!-- Open Graph -->
    <meta property="og:title" content="<?= esc($ogTitle) ?>">
    <meta property="og:description" content="<?= esc($ogDescription) ?>">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?= current_url() ?>">
    <meta property="og:image" content="<?= esc($ogImage) ?>">

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= esc($ogTitle) ?>">
    <meta name="twitter:description" content="<?= esc($ogDescription) ?>">
    <meta name="twitter:image" content="<?= esc($ogImage) ?>">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <!-- DM Sans powers the sitewide navbar, so it must load on every page -->
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,400&display=swap" rel="stylesheet">
    <?php if (! empty($isHomePage)): ?>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700&family=Playfair+Display:ital,wght@0,500;0,600;0,700;1,500&display=swap" rel="stylesheet">
    <?php else: ?>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,500;0,9..144,600;0,9..144,700;1,9..144,500&display=swap" rel="stylesheet">
    <?php endif; ?>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">

    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="/assets/css/dashboard.css">
    <?php if (! empty($isHomePage)): ?>
    <link rel="stylesheet" href="/assets/css/home.css">
    <?php endif; ?>

    <!-- Slick Carousel CSS (pages with carousels) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/slick-carousel/slick/slick.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/slick-carousel/slick/slick-theme.css">

    <!-- Image Uploader CSS -->
    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/gh/christianbayer/image-uploader@master/dist/image-uploader.min.css">

    <!-- jQuery (loaded synchronously: inline view scripts use $ at parse time) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM"
        crossorigin="anonymous" defer></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">

    <!-- Slick Carousel JS -->
    <script src="https://cdn.jsdelivr.net/npm/slick-carousel/slick/slick.min.js" defer></script>

    <!-- Image Uploader JS -->
    <script src="https://cdn.jsdelivr.net/gh/christianbayer/image-uploader@master/dist/image-uploader.min.js" defer></script>
</head>

<body<?= ! empty($isHomePage) ? ' class="home-page-body"' : '' ?>>

    <a href="#main-content" class="skip-link">Skip to main content</a>

    <header>
        <nav class="navbar navbar-expand-lg fixed-top shadow-sm">
            <div class="container">
                <a class="navbar-brand text-uppercase logo" href="/">
                    <span class="logo-line">For <span class="logo-accent">Your</span></span>
                    <span class="logo-line logo-line--muted">Events</span>
                </a>

                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                    aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <i class="fas fa-bars"></i>
                </button>

                <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                    <ul class="navbar-nav ms-auto align-items-lg-center">
                        <li class="nav-item">
                            <a class="nav-link text-end" href="/browse-services">Find Suppliers</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-end" href="/how-it-works">How It Works</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-end" href="/vendor-info">For Vendors</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-end" href="/browse-services">Inspiration</a>
                        </li>

                        <?php if (session()->has('user_id')): ?>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle text-end" href="#" id="accountDropdown" role="button"
                                    data-bs-toggle="dropdown" aria-expanded="false">
                                    My Account
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="accountDropdown">
                                    <li><a class="dropdown-item" href="/profile">My Profile</a></li>
                                    <?php if (session()->get('role') === 'customer'): ?>
                                        <li><a class="dropdown-item" href="/cart">My Cart</a></li>
                                        <li><a class="dropdown-item" href="/event/create">Create Event</a></li>
                                    <?php elseif (session()->get('role') === 'vendor'): ?>
                                        <li><a class="dropdown-item" href="/profile/services">My Services</a></li>
                                        <li><a class="dropdown-item" href="/profile/bookings">Bookings</a></li>
                                    <?php elseif (session()->get('role') === 'admin'): ?>
                                        <li><a class="dropdown-item" href="/admin">Admin</a></li>
                                    <?php endif; ?>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="/logout">Logout</a></li>
                                </ul>
                            </li>
                        <?php else: ?>
                            <li class="nav-item">
                                <a class="nav-link text-end" href="/login">My Account</a>
                            </li>
                        <?php endif; ?>
                        <li class="nav-item">
                            <?php
                            $navPlanUrl = session()->has('user_id')
                                ? '/event/create'
                                : '/register';
                            ?>
                            <a class="btn btn-nav-cta ms-lg-2 mt-2 mt-lg-0" href="<?= $navPlanUrl ?>">Start planning</a>
                        </li>
                    </ul>
                </div>

            </div>
        </nav>
    </header>

    <div id="main-content" tabindex="-1">

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const tooltipTriggerList = [].slice.call(
                document.querySelectorAll('[data-bs-toggle="tooltip"]')
            );
            tooltipTriggerList.forEach(function (tooltipTriggerEl) {
                new bootstrap.Tooltip(tooltipTriggerEl);
            });

            const popoverTriggerList = [].slice.call(
                document.querySelectorAll('[data-bs-toggle="popover"]')
            );
            popoverTriggerList.forEach(function (popoverTriggerEl) {
                new bootstrap.Popover(popoverTriggerEl);
            });
        });
    </script>
