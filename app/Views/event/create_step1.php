<?= $this->include('header') ?>

<div class="ps-app">
<main data-screen-label="Plan an event">
    <section class="page-head">
        <div class="container">
            <div class="breadcrumb"><a href="/">Home</a><span class="sep">/</span><span class="cur">Plan an event</span></div>
            <p class="eyebrow">Start planning</p>
            <h1>Tell us about your event</h1>
            <p class="ph-lead">Answer a few quick questions and we'll match you with vetted suppliers who can quote. Free, with no commitment.</p>
        </div>
    </section>

    <div class="container">
        <div class="flow-wrap">
            <?php $this->setData(['currentStep' => 1]); ?>
            <?= $this->include('event/_progress') ?>

            <?php if (!empty($errors)): ?>
                <div class="form-alert error" style="margin-bottom:18px">
                    <ul class="mb-0">
                        <?php foreach ($errors as $err): ?><li><?= esc($err) ?></li><?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php if (session()->getFlashdata('info')): ?>
                <div class="form-alert" style="margin-bottom:18px"><?= session()->getFlashdata('info') ?></div>
            <?php endif; ?>

            <div class="flow-card">
                <h2>The basics</h2>
                <p class="flow-sub">Name your event, pick the occasion, and tell us when and how big.</p>

                <form class="form-grid" method="post" action="/event/create/step1">
                    <?= csrf_field() ?>

                    <div class="field-row">
                        <label for="title">Event name</label>
                        <input class="input" type="text" id="title" name="title"
                               value="<?= esc($old['title'] ?? '') ?>" placeholder="e.g. Sarah &amp; Tom's Wedding" required>
                    </div>

                    <div class="form-grid two" style="gap:14px">
                        <div class="field-row">
                            <label for="event_type">Event type</label>
                            <select class="select-full" id="event_type" name="event_type" required>
                                <option value="" disabled <?= empty($old['event_type'] ?? '') ? 'selected' : '' ?>>Select event type...</option>
                                <?php
                                $types = ['Wedding', 'Birthday', 'Christening', 'Corporate Event', 'Conference', 'Summer Fair', 'Private Party', 'Community Event', 'Funeral', 'Graduation', 'Anniversary', 'Other'];
                                foreach ($types as $type):
                                ?>
                                    <option value="<?= $type ?>" <?= ($old['event_type'] ?? '') === $type ? 'selected' : '' ?>><?= $type ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="field-row">
                            <label for="guest_count">Estimated guest count</label>
                            <input class="input" type="number" id="guest_count" name="guest_count"
                                   value="<?= esc($old['guest_count'] ?? '') ?>" min="1" placeholder="e.g. 100" required>
                        </div>
                    </div>

                    <div class="field-row">
                        <label for="date">Event date</label>
                        <div class="input-icon">
                            <i class="fas fa-calendar"></i>
                            <input class="input" type="date" id="date" name="date"
                                   value="<?= esc($old['date'] ?? '') ?>" min="<?= date('Y-m-d') ?>" required>
                        </div>
                    </div>

                    <div class="field-row">
                        <label>Event format</label>
                        <span class="field-hint">We use this to match vendor pricing (private hire vs public / pitch events).</span>
                        <?php $es = $old['event_setting'] ?? 'private'; ?>
                        <label class="check-line">
                            <input type="radio" name="event_setting" id="es_private" value="private" <?= $es === 'private' ? 'checked' : '' ?> required>
                            <span>Private event (wedding, party, venue hire, corporate at a site)</span>
                        </label>
                        <label class="check-line">
                            <input type="radio" name="event_setting" id="es_public" value="public" <?= $es === 'public' ? 'checked' : '' ?>>
                            <span>Public / trade event (fair, festival, market — pitch or stand)</span>
                        </label>
                    </div>

                    <div class="flow-actions">
                        <span class="field-hint">Step 1 of 4</span>
                        <button type="submit" class="btn btn-primary btn-lg">Next: Location <i class="fas fa-arrow-right"></i></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>
</div>

<?= $this->include('footer') ?>
