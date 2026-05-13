<?= $this->include('header') ?>

<?php if (!empty($cmsHome)): ?>
<section class="border-bottom bg-white py-3">
    <div class="container cms-intro">
        <?= $cmsHome['content'] ?>
    </div>
</section>
<?php endif; ?>



<main class="full-width-container">
    <!-- Hero Section -->
    <section class="hero-section d-flex flex-column">
        <div class="hero-image" style="background-image: url('<?= base_url('assets/images/hero.png') ?>');">
            <div class="overlay d-flex align-items-center justify-content-center">
                <div class="text-center text-white">
                    <div class="display-4-container">
                        <h1 class="display-4">
                            <div class="flex-wrapper">
                                <span class="static-text">For Your</span>
                                <span id="rotating-words" class="rotating-words">Event</span>
                            </div>
                        </h1>
                    </div>

                    <!-- Search Form (Visible on larger screens) -->
                    <form class="search-form mt-4 d-none d-lg-flex justify-content-center" action="/search"
                        method="get">
                        <div class="input-group" style="max-width: 600px;">
                            <select class="form-control select2-searchable" name="category" required>
                                <option value="" disabled selected>Select Service Category</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?= esc($category['id']) ?>"><?= esc($category['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <button class="btn btn-primary" type="submit">Search</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <!-- Search Form (Visible on smaller screens) -->
        <div class="search-form-container d-lg-none mt-3 text-center">
            <form class="search-form" action="/search" method="get">
                <div class="input-group" style="max-width: 600px; margin: 0 auto;">
                    <select class="form-control select2-searchable" name="category" required>
                        <option value="" disabled selected>Select Service Category</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?= esc($category['id']) ?>"><?= esc($category['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button class="btn btn-primary" type="submit">Search</button>
                </div>
            </form>
        </div>
    </section>






    <!-- Featured Categories Section -->


    <!-- Example Card Structure -->
    <section class="py-5 bg-light text-center">
        <h1>Popular Categories</h1>
        <div class="category-card-container">
            <div class="category-card">
                <img src="<?= base_url('assets/images/photo1.png') ?>" alt="Photographers" class="category-card-image">
                <div class="category-card-category">Photographers</div>
            </div>
            <div class="category-card">
                <img src="<?= base_url('assets/images/caterer.png') ?>" alt="Caterers" class="category-card-image">
                <div class="category-card-category">Caterers</div>
            </div>
            <div class="category-card">
                <img src="<?= base_url('assets/images/planner.png') ?>" alt="Caterers" class="category-card-image">
                <div class="category-card-category">Event Planners</div>
            </div>
            <div class="category-card">
                <img src="<?= base_url('assets/images/florist.png') ?>" alt="Caterers" class="category-card-image">
                <div class="category-card-category">Florists</div>
            </div>
            <div class="category-card">
                <img src="<?= base_url('assets/images/dj.png') ?>" alt="Caterers" class="category-card-image">
                <div class="category-card-category">Music and DJs</div>
            </div>
            <div class="category-card">
                <img src="<?= base_url('assets/images/makeup.png') ?>" alt="Caterers" class="category-card-image">
                <div class="category-card-category">Hair and Makeup</div>
            </div>
            <div class="category-card">
                <img src="<?= base_url('assets/images/car.png') ?>" alt="Caterers" class="category-card-image">
                <div class="category-card-category">Transport</div>
            </div>
            <div class="category-card">
                <img src="<?= base_url('assets/images/venues.png') ?>" alt="Caterers" class="category-card-image">
                <div class="category-card-category">Event Venues</div>
            </div>
            <div class="category-card">
                <img src="<?= base_url('assets/images/supplies.png') ?>" alt="Caterers" class="category-card-image">
                <div class="category-card-category">Party Supplies</div>
            </div>
            <div class="category-card">
                <img src="<?= base_url('assets/images/cakes.png') ?>" alt="Caterers" class="category-card-image">
                <div class="category-card-category">Cakes & Desserts</div>
            </div>
            <!-- Add more cards as needed -->
        </div>
    </section>


    <section class="how-it-works py-5 bg-light">
        <div class="container text-center">
            <h1 class="mb-5">How It Works</h1>
            <div class="row justify-content-center">
                <!-- Card 1: Search Services -->
                <div class="col-md-4">
                    <div class="how-it-works-card">
                        <div class="icon-wrapper">
                            <i class="fas fa-search fa-3x text-primary"></i>
                        </div>
                        <h5 class="card-title mt-3">Create Your Event</h5>
                        <p class="card-description">Plan your event details including dates, location, and type of
                            celebration.</p>
                    </div>
                </div>

                <!-- Card 2: Get Quotes -->
                <div class="col-md-4">
                    <div class="how-it-works-card">
                        <div class="icon-wrapper">
                            <i class="fas fa-envelope fa-3x text-primary"></i>
                        </div>
                        <h5 class="card-title mt-3">Find & Add Services
                        </h5>
                        <p class="card-description">Get instant quotes on a wide range of vendors, including catering,
                            venues, DJs.</p>
                    </div>
                </div>

                <!-- Card 3: Book Your Event -->
                <div class="col-md-4">
                    <div class="how-it-works-card">
                        <div class="icon-wrapper">
                            <i class="fas fa-check-circle fa-3x text-primary"></i>
                        </div>
                        <h5 class="card-title mt-3">Book Services</h5>
                        <p class="card-description">Confirm and book all your services with one seamless checkout
                            process.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>




    <section class="py-5 bg-light text-center">
        <h1 class="py-5">Popular Services</h1>
        <div class="service-card-container">
            <?php foreach ($services as $service): ?>
                <div class="service-card">
                    <?php if (!empty($service['images'])): ?>
                        <!-- Display the first image as the card's main image -->
                        <img src="<?= base_url(esc($service['images'][0]['thumbnail_path'])) ?>"
                            alt="<?= esc($service['title']) ?> Thumbnail" class="service-card-image">
                    <?php else: ?>
                        <!-- Fallback image -->
                        <img src="<?= base_url('assets/images/no-image.png') ?>" alt="No Image Available"
                            class="service-card-image">
                    <?php endif; ?>

                    <div class="service-card-content">
                        <h3 class="service-card-title"><?= esc($service['title']) ?></h3>
                        <p class="service-card-description">
                            <?= esc($service['short_description'] ?? 'No description available.') ?>
                        </p>
                        <a href="/service/view/<?= esc($service['id']) ?>" class="service-card-button">Learn More</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>






    <!-- Call-to-Action Section -->
    <section class="cta-section text-white py-5" style="background-color: #2c3e50;">
        <div class="container text-center">
            <h2>Start Planning Your Event Today!</h2>
            <p>Join thousands of happy event planners.</p>
            <a href="/register" class="btn btn-light btn-lg">Get Started</a>
        </div>
    </section>

</main>

    <?= $this->include('footer') ?>

    <!-- Initialize Select2 -->
    <script>
        $(document).ready(function () {
            $('.select2-searchable').select2({
                placeholder: "Select Service Category",
                allowClear: true,
                width: '100%'
            });
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const words = ["Wedding", "Birthday", "Christening", "Bat Mitzvah", "Meeting", "Events"]; // Words to rotate
            const rotatingWords = document.getElementById('rotating-words');
            let index = 0; // Track the current word
            let wordCount = 0; // Track how many words have been displayed
            let interval = 3000;

            const updateWord = () => {
                rotatingWords.textContent = words[index]; // Update the displayed word
                index++;
                wordCount++;

                // Stop updating if all words have been displayed


                // Adjust interval after the first word
                if (wordCount > 1) {
                    interval = 3000;
                }

                if (wordCount >= words.length) {
                    delay = 5000;
                    rotatingWords.style.animation = 'none';
                    return; // Exit the function and stop further updates
                }


                // Schedule the next word update
                setTimeout(updateWord, interval);
            };

            // Start the word rotation
            updateWord();
        });




    </script>