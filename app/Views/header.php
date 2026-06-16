<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <?= csrf_meta() ?>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#143729">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <?php
    $ogTitle       = $pageTitle ?? 'Partysmith';
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
    <!-- Hanken Grotesk (UI/body) + Newsreader (serif headings) + Caveat (the "P.S." script voice). -->
    <!-- Fonts load async (media swap) so a slow/unreachable Google Fonts never blocks render. -->
    <link href="https://fonts.googleapis.com/css2?family=Hanken+Grotesk:wght@400;500;600;700;800&family=Newsreader:ital,opsz,wght@0,16..72,400;0,16..72,500;0,16..72,600;1,16..72,400;1,16..72,500&family=Caveat:wght@600&display=swap" rel="stylesheet" media="print" onload="this.media='all'">
    <noscript>
        <link href="https://fonts.googleapis.com/css2?family=Hanken+Grotesk:wght@400;500;600;700;800&family=Newsreader:ital,opsz,wght@0,16..72,400;0,16..72,500;0,16..72,600;1,16..72,400;1,16..72,500&family=Caveat:wght@600&display=swap" rel="stylesheet">
    </noscript>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">

    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="/assets/css/dashboard.css">
    <?php if (! empty($isHomePage)): ?>
    <link rel="stylesheet" href="/assets/css/home.css">
    <?php endif; ?>

    <!-- Partysmith front-of-house interactions (occasion tabs, FAQ accordion, filter
         pills, toggles, favourites). Harmless on pages without those elements. -->
    <script src="/assets/js/partysmith.js" defer></script>

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
                <a class="navbar-brand brand" href="/">
                    <span class="ps">P<span class="dot">.</span>S<span class="dot">.</span></span>
                    <span class="name">Partysmith</span>
                </a>

                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                    aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <i class="fas fa-bars"></i>
                </button>

                <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                    <ul class="navbar-nav ms-auto align-items-lg-center">
                        <?php
                        $navRole = session()->get('role');
                        if ($navRole === 'customer'): ?>
                            <li class="nav-item">
                                <a class="nav-link text-end" href="/browse-services">Find Suppliers</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link text-end" href="/profile/events">My Events</a>
                            </li>
                        <?php elseif ($navRole === 'vendor'): ?>
                            <li class="nav-item">
                                <a class="nav-link text-end" href="/profile/services">My Services</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link text-end" href="/profile/bookings">My Bookings</a>
                            </li>
                        <?php else: ?>
                            <li class="nav-item">
                                <a class="nav-link text-end" href="/browse-services">Find Suppliers</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link text-end" href="/vendor-info">For Vendors</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link text-end" href="/browse-services">Inspiration</a>
                            </li>
                        <?php endif; ?>

                        <?php if (session()->has('user_id')): ?>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle text-end" href="#" id="accountDropdown" role="button"
                                    data-bs-toggle="dropdown" aria-expanded="false">
                                    My Account
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="accountDropdown">
                                    <li><a class="dropdown-item" href="/profile">My Profile</a></li>
                                    <?php if ($navRole === 'customer'): ?>
                                        <li><a class="dropdown-item" href="/profile/events">Basket</a></li>
                                        <li><a class="dropdown-item" href="/event/create">Create Event</a></li>
                                    <?php elseif ($navRole === 'vendor'): ?>
                                        <li><a class="dropdown-item" href="/profile/services">My Services</a></li>
                                        <li><a class="dropdown-item" href="/profile/bookings">Bookings</a></li>
                                    <?php elseif ($navRole === 'admin'): ?>
                                        <li><a class="dropdown-item" href="/admin">Admin</a></li>
                                    <?php endif; ?>
                                    <li><a class="dropdown-item" href="/faq">FAQs</a></li>
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
                            <?php if ($navRole === 'vendor'): ?>
                                <a class="btn btn-nav-cta ms-lg-2 mt-2 mt-lg-0" href="/service/list">Add a Service</a>
                            <?php elseif ($navRole === 'customer'): ?>
                                <a class="btn btn-nav-cta ms-lg-2 mt-2 mt-lg-0" href="/event/create">Start Planning</a>
                            <?php else: ?>
                                <a class="btn btn-nav-cta ms-lg-2 mt-2 mt-lg-0" href="/register">Start Planning</a>
                            <?php endif; ?>
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
