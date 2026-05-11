<?= $this->include('header') ?>

<main class="page-main">
<div class="dashboard-wrapper">
    <div class="container" style="max-width: 700px;">
        <h3 class="mb-2">Create Your Event</h3>
        <p class="text-muted mb-4">Where is your event taking place?</p>

        <?php $currentStep = 2; ?>
        <?= $this->include('event/_progress') ?>

        <div class="dash-card">
            <h5><i class="fas fa-map-marker-alt text-primary me-2"></i>Location Details</h5>

            <form method="post" action="/event/create/step2">
                <div class="mb-3">
                    <label for="venue_name" class="form-label">Venue Name <span class="text-muted">(optional)</span></label>
                    <input type="text" class="form-control" id="venue_name" name="venue_name"
                           value="<?= esc($old['venue_name'] ?? '') ?>" placeholder="e.g. The Grand Hotel">
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="town_city" class="form-label">Town / City</label>
                        <input type="text" class="form-control" id="town_city" name="town_city"
                               value="<?= esc($old['town_city'] ?? '') ?>" placeholder="e.g. Durham">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="postcode" class="form-label">Postcode</label>
                        <input type="text" class="form-control" id="postcode" name="postcode"
                               value="<?= esc($old['postcode'] ?? '') ?>" placeholder="e.g. DH1 3EL">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Indoor / Outdoor</label>
                    <div class="d-flex gap-3">
                        <?php $io = $old['indoor_outdoor'] ?? ''; ?>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="indoor_outdoor" id="indoor" value="indoor" <?= $io === 'indoor' ? 'checked' : '' ?>>
                            <label class="form-check-label" for="indoor">Indoor</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="indoor_outdoor" id="outdoor" value="outdoor" <?= $io === 'outdoor' ? 'checked' : '' ?>>
                            <label class="form-check-label" for="outdoor">Outdoor</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="indoor_outdoor" id="both" value="both" <?= $io === 'both' ? 'checked' : '' ?>>
                            <label class="form-check-label" for="both">Both</label>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-between">
                    <a href="/event/create/step1" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back</a>
                    <button type="submit" class="btn btn-primary">Next: Preferences <i class="fas fa-arrow-right ms-1"></i></button>
                </div>
            </form>
        </div>
    </div>
</div>
</main>

<?= $this->include('footer') ?>
