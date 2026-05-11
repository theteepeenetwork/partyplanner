<?= $this->include('header') ?>

<main class="page-main">
<div class="dashboard-wrapper">
    <div class="container" style="max-width: 700px;">
        <h3 class="mb-2">Create Your Event</h3>
        <p class="text-muted mb-4">Tell us about your event so we can help you find the perfect services.</p>

        <?php $currentStep = 1; ?>
        <?= $this->include('event/_progress') ?>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul class="mb-0">
                    <?php foreach ($errors as $err): ?><li><?= esc($err) ?></li><?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('info')): ?>
            <div class="alert alert-info"><?= session()->getFlashdata('info') ?></div>
        <?php endif; ?>

        <div class="dash-card">
            <h5><i class="fas fa-calendar-alt text-primary me-2"></i>Event Basics</h5>

            <form method="post" action="/event/create/step1">
                <div class="mb-3">
                    <label for="title" class="form-label">Event Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="title" name="title"
                           value="<?= esc($old['title'] ?? '') ?>" placeholder="e.g. Sarah & Tom's Wedding" required>
                </div>

                <div class="mb-3">
                    <label for="event_type" class="form-label">Event Type <span class="text-danger">*</span></label>
                    <select class="form-select" id="event_type" name="event_type" required>
                        <option value="" disabled <?= empty($old['event_type'] ?? '') ? 'selected' : '' ?>>Select event type...</option>
                        <?php
                        $types = ['Wedding', 'Birthday', 'Christening', 'Corporate Event', 'Conference', 'Summer Fair', 'Private Party', 'Community Event', 'Funeral', 'Graduation', 'Anniversary', 'Other'];
                        foreach ($types as $type):
                        ?>
                            <option value="<?= $type ?>" <?= ($old['event_type'] ?? '') === $type ? 'selected' : '' ?>><?= $type ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="date" class="form-label">Event Date <span class="text-danger">*</span></label>
                    <input type="date" class="form-control" id="date" name="date"
                           value="<?= esc($old['date'] ?? '') ?>" min="<?= date('Y-m-d') ?>" required>
                </div>

                <div class="mb-3">
                    <label for="guest_count" class="form-label">Estimated Guest Count <span class="text-danger">*</span></label>
                    <input type="number" class="form-control" id="guest_count" name="guest_count"
                           value="<?= esc($old['guest_count'] ?? '') ?>" min="1" placeholder="e.g. 100" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Event format <span class="text-danger">*</span></label>
                    <p class="small text-muted">We use this to match vendor pricing (private hire vs public / pitch events).</p>
                    <?php $es = $old['event_setting'] ?? 'private'; ?>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="event_setting" id="es_private" value="private" <?= $es === 'private' ? 'checked' : '' ?> required>
                        <label class="form-check-label" for="es_private">Private event (wedding, party, venue hire, corporate at a site)</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="event_setting" id="es_public" value="public" <?= $es === 'public' ? 'checked' : '' ?>>
                        <label class="form-check-label" for="es_public">Public / trade event (fair, festival, market — pitch or stand)</label>
                    </div>
                </div>

                <div class="d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary">Next: Location <i class="fas fa-arrow-right ms-1"></i></button>
                </div>
            </form>
        </div>
    </div>
</div>
</main>

<?= $this->include('footer') ?>
