<?= $this->include('header') ?>

<main class="page-main">
<div class="dashboard-wrapper">
    <div class="container" style="max-width: 700px;">
        <h3 class="mb-2">Add to Event</h3>
        <p class="text-muted mb-4">Which event would you like to add <strong>"<?= esc($service['title']) ?>"</strong> to?</p>

        <div class="dash-card mb-3">
            <div class="d-flex align-items-center mb-3">
                <div class="me-3">
                    <i class="fas fa-concierge-bell fa-2x text-primary"></i>
                </div>
                <div>
                    <h6 class="mb-0"><?= esc($service['title']) ?></h6>
                    <?php if (!empty($service['price'])): ?>
                        <span class="text-muted small">From £<?= number_format($service['price'], 2) ?></span>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <?php foreach ($events as $event): ?>
            <form method="post" action="/event/add-to-basket/<?= $service['id'] ?>">
                <?= csrf_field() ?>
                <input type="hidden" name="event_id" value="<?= $event['id'] ?>">
                <?php if (!empty($selectedOptions['pricing_option'])): ?>
                    <input type="hidden" name="pricing_option" value="<?= esc($selectedOptions['pricing_option']) ?>">
                <?php endif; ?>
                <?php if (!empty($selectedOptions['extras']) && is_array($selectedOptions['extras'])): ?>
                    <?php foreach ($selectedOptions['extras'] as $extra): ?>
                        <input type="hidden" name="extras[]" value="<?= esc($extra) ?>">
                    <?php endforeach; ?>
                <?php endif; ?>

                <div class="dash-card mb-2" style="cursor:pointer;" onclick="this.closest('form').submit();">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h6 class="mb-0"><?= esc($event['title']) ?></h6>
                            <div class="text-muted small">
                                <?php if (!empty($event['event_type'])): ?>
                                    <span class="badge bg-primary me-1"><?= esc($event['event_type']) ?></span>
                                <?php endif; ?>
                                <?php if (!empty($event['date'])): ?>
                                    <i class="fas fa-calendar-alt me-1"></i><?= date('d M Y', strtotime($event['date'])) ?>
                                <?php endif; ?>
                                <?php if (!empty($event['location'])): ?>
                                    <span class="ms-2"><i class="fas fa-map-marker-alt me-1"></i><?= esc($event['location']) ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-plus me-1"></i>Add</button>
                    </div>
                </div>
            </form>
        <?php endforeach; ?>

        <div class="text-center mt-4">
            <a href="/event/create" class="btn btn-outline-primary"><i class="fas fa-plus me-1"></i>Create New Event</a>
        </div>
    </div>
</div>
</main>

<?= $this->include('footer') ?>
