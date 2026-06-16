<?= $this->include('header') ?>

<div class="ps-app">
<main data-screen-label="Plan an event">
    <section class="page-head">
        <div class="container">
            <div class="breadcrumb"><a href="/">Home</a><span class="sep">/</span><a href="/event/create/step1">Plan an event</a><span class="sep">/</span><span class="cur">Location</span></div>
            <p class="eyebrow">Start planning</p>
            <h1>Where is your event?</h1>
            <p class="ph-lead">Tell us the location so we can match you with vetted suppliers who cover the area.</p>
        </div>
    </section>

    <div class="container">
        <div class="flow-wrap">
            <?php $this->setData(['currentStep' => 2]); ?>
            <?= $this->include('event/_progress') ?>

            <div class="flow-card">
                <h2>Location details</h2>
                <p class="flow-sub">Where and what kind of space — venue is optional.</p>

                <form class="form-grid" method="post" action="/event/create/step2">
                    <?= csrf_field() ?>

                    <div class="field-row">
                        <label for="venue_name">Venue name <span class="opt">— optional</span></label>
                        <div class="input-icon">
                            <i class="fas fa-building"></i>
                            <input class="input" type="text" id="venue_name" name="venue_name"
                                   value="<?= esc($old['venue_name'] ?? '') ?>" placeholder="e.g. The Grand Hotel">
                        </div>
                    </div>

                    <div class="form-grid two" style="gap:14px">
                        <div class="field-row">
                            <label for="town_city">Town / City</label>
                            <div class="input-icon">
                                <i class="fas fa-location-dot"></i>
                                <input class="input" type="text" id="town_city" name="town_city"
                                       value="<?= esc($old['town_city'] ?? '') ?>" placeholder="e.g. Durham">
                            </div>
                        </div>
                        <div class="field-row">
                            <label for="postcode">Postcode</label>
                            <input class="input" type="text" id="postcode" name="postcode"
                                   value="<?= esc($old['postcode'] ?? '') ?>" placeholder="e.g. DH1 3EL">
                        </div>
                    </div>

                    <?php if (($eventSetting ?? 'private') === 'public'): ?>
                    <div class="field-row">
                        <label for="organiser_pitch_fee">Organiser pitch / stand fee (£)</label>
                        <input class="input" type="number" id="organiser_pitch_fee" name="organiser_pitch_fee"
                               value="<?= esc($old['organiser_pitch_fee'] ?? '') ?>" min="0" step="0.01" placeholder="e.g. 150.00">
                        <span class="field-hint">If the organiser has quoted a pitch or stand fee, enter it here for an accurate total. Leave blank to estimate using each vendor's maximum pitch for your expected attendance.</span>
                    </div>
                    <?php endif; ?>

                    <div class="field-row">
                        <label>Indoor / Outdoor</label>
                        <?php $io = $old['indoor_outdoor'] ?? ''; ?>
                        <label class="check-line">
                            <input type="radio" name="indoor_outdoor" id="indoor" value="indoor" <?= $io === 'indoor' ? 'checked' : '' ?>>
                            <span>Indoor</span>
                        </label>
                        <label class="check-line">
                            <input type="radio" name="indoor_outdoor" id="outdoor" value="outdoor" <?= $io === 'outdoor' ? 'checked' : '' ?>>
                            <span>Outdoor</span>
                        </label>
                        <label class="check-line">
                            <input type="radio" name="indoor_outdoor" id="both" value="both" <?= $io === 'both' ? 'checked' : '' ?>>
                            <span>Both</span>
                        </label>
                    </div>

                    <div class="flow-actions">
                        <a href="/event/create/step1" class="btn btn-ghost btn-lg"><i class="fas fa-arrow-left"></i> Back</a>
                        <button type="submit" class="btn btn-primary btn-lg">Next: Preferences <i class="fas fa-arrow-right"></i></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>
</div>

<?= $this->include('footer') ?>
