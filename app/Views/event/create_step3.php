<?= $this->include('header') ?>

<div class="ps-app">
<main data-screen-label="Plan an event">
    <section class="page-head">
        <div class="container">
            <div class="breadcrumb"><a href="/">Home</a><span class="sep">/</span><a href="/event/create/step1">Plan an event</a><span class="sep">/</span><span class="cur">Preferences</span></div>
            <p class="eyebrow">Start planning</p>
            <h1>Set your preferences</h1>
            <p class="ph-lead">A few optional details help suppliers tailor their quotes to your vision.</p>
        </div>
    </section>

    <div class="container">
        <div class="flow-wrap">
            <?php $this->setData(['currentStep' => 3]); ?>
            <?= $this->include('event/_progress') ?>

            <div class="flow-card">
                <h2>Preferences</h2>
                <p class="flow-sub">Anything you tell us here is shared with matched suppliers — all optional.</p>

                <form class="form-grid" method="post" action="/event/create/step3">
                    <?= csrf_field() ?>

                    <div class="field-row">
                        <label for="style_theme">Preferred style / theme <span class="opt">— optional</span></label>
                        <input class="input" type="text" id="style_theme" name="style_theme"
                               value="<?= esc($old['style_theme'] ?? '') ?>" placeholder="e.g. Rustic, Modern, Boho, Elegant">
                    </div>

                    <div class="field-row">
                        <label for="notes">Additional notes <span class="opt">— optional</span></label>
                        <textarea class="textarea" id="notes" name="notes" rows="3"
                                  placeholder="Anything else vendors should know about your event..."><?= esc($old['notes'] ?? '') ?></textarea>
                    </div>

                    <div class="flow-actions">
                        <a href="/event/create/step2" class="btn btn-ghost btn-lg"><i class="fas fa-arrow-left"></i> Back</a>
                        <button type="submit" class="btn btn-primary btn-lg">Next: Review <i class="fas fa-arrow-right"></i></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>
</div>

<?= $this->include('footer') ?>
