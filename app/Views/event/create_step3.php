<?= $this->include('header') ?>

<main class="page-main">
<div class="dashboard-wrapper">
    <div class="container" style="max-width: 700px;">
        <h3 class="mb-2">Create Your Event</h3>
        <p class="text-muted mb-4">Set your preferences to help us recommend services.</p>

        <?php $currentStep = 3; ?>
        <?= $this->include('event/_progress') ?>

        <div class="dash-card">
            <h5><i class="fas fa-sliders-h text-primary me-2"></i>Preferences</h5>

            <form method="post" action="/event/create/step3">
                <div class="mb-3">
                    <label class="form-label">Budget Range <span class="text-muted">(optional)</span></label>
                    <div class="row">
                        <div class="col-6">
                            <div class="input-group">
                                <span class="input-group-text">£</span>
                                <input type="number" class="form-control" name="budget_min" placeholder="Min"
                                       value="<?= esc($old['budget_min'] ?? '') ?>" min="0">
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="input-group">
                                <span class="input-group-text">£</span>
                                <input type="number" class="form-control" name="budget_max" placeholder="Max"
                                       value="<?= esc($old['budget_max'] ?? '') ?>" min="0">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="style_theme" class="form-label">Preferred Style / Theme <span class="text-muted">(optional)</span></label>
                    <input type="text" class="form-control" id="style_theme" name="style_theme"
                           value="<?= esc($old['style_theme'] ?? '') ?>" placeholder="e.g. Rustic, Modern, Boho, Elegant">
                </div>

                <div class="mb-3">
                    <label for="notes" class="form-label">Additional Notes <span class="text-muted">(optional)</span></label>
                    <textarea class="form-control" id="notes" name="notes" rows="3"
                              placeholder="Anything else vendors should know about your event..."><?= esc($old['notes'] ?? '') ?></textarea>
                </div>

                <div class="d-flex justify-content-between">
                    <a href="/event/create/step2" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back</a>
                    <button type="submit" class="btn btn-primary">Next: Review <i class="fas fa-arrow-right ms-1"></i></button>
                </div>
            </form>
        </div>
    </div>
</div>
</main>

<?= $this->include('footer') ?>
