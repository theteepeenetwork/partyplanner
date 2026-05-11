<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>For Your Events</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= base_url('assets/css/style.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/dashboard.css') ?>">

    <!-- Slick Carousel CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/slick-carousel/slick/slick.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/slick-carousel/slick/slick-theme.css">

    <!-- Image Uploader CSS -->
    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/gh/christianbayer/image-uploader@master/dist/image-uploader.min.css">

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM"
        crossorigin="anonymous"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">

    <!-- Slick Carousel JS -->
    <script src="https://cdn.jsdelivr.net/npm/slick-carousel/slick/slick.min.js"></script>

    <!-- Image Uploader JS -->
    <script src="https://cdn.jsdelivr.net/gh/christianbayer/image-uploader@master/dist/image-uploader.min.js"></script>

    <!-- Optional JavaScript -->
    <script src="<?= base_url('assets/js/slick/slick.min.js') ?>"></script>
</head>

<body>

    <header>
        <nav class="navbar navbar-expand-lg fixed-top shadow-sm">
            <div class="container">
                <!-- Logo -->
                <a class="navbar-brand text-uppercase logo" href="/">
                    <span class="logo-line">For <span style="color: var(--accent-orange);">Your</span></span>
                    <span class="logo-line" style="color: var(--primary-blue-light);">Events</span>
                </a>

                <!-- Toggle Button for Mobile View -->
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                    aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <i class="fas fa-bars"></i>
                </button>

                <!-- Collapsible Navbar Content -->
                <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                    <!-- Right-Aligned Nav Items -->
                    <ul class="navbar-nav ms-auto">
                        <!-- Browse Services -->
                        <li class="nav-item">
                            <a class="nav-link text-end" href="/browse-services">Browse Services</a>
                        </li>
                        <!-- How It Works -->
                        <li class="nav-item">
                            <a class="nav-link text-end" href="/how-it-works">How It Works</a>
                        </li>

                        <?php if (session()->has('user_id')): ?>
                            <!-- Logged-in: My Account Dropdown -->
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
                            <!-- Not logged-in: Login + Register -->
                            <li class="nav-item">
                                <a class="nav-link text-end" href="/login">Login</a>
                            </li>
                            <li class="nav-item">
                                <a class="btn btn-gradient ms-2" href="/register">Register</a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>

            </div>
        </nav>
    </header>


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
