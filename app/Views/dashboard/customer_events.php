<?= $this->include('header') ?>

<main class="page-main">
<div class="dashboard-wrapper">
    
    <div class="container">
        <?= $this->include('dashboard/_customer_tabs') ?>

        <div class="d-flex flex-column flex-md-row justify-content-md-between align-items-md-start gap-3 mb-4">
            <div>
                <h4 class="mb-2">My Events</h4>
                <p class="dash-page-lead mb-0">Each celebration has its own basket, deposits, and bookings. Pay separately per event.</p>
            </div>
            <a href="/event/create" class="btn btn-primary flex-shrink-0"><i class="fas fa-plus me-1"></i>Create New Event</a>
        </div>

        <?php if (!empty($events)): ?>
            <?php foreach ($events as $event): ?>
                <?= view('partials/event_planning_card', ['event' => $event]) ?>
            <?php endforeach; ?>
        <?php else: ?>
            
            <div class="dash-card text-center py-5 px-3">
                <div class="dash-empty-state">
                    <i class="fas fa-calendar-plus fa-3x text-muted mb-3 d-block" aria-hidden="true"></i>
                    <h5 class="fw-semibold">No events yet</h5>
                    <p class="text-muted mb-4">Set up your first event to save the basics, then browse and shortlist services with confidence.</p>
                    <div class="d-flex flex-column flex-sm-row gap-2 justify-content-center">
                        <a href="/event/create" class="btn btn-primary"><i class="fas fa-plus me-1"></i>Create your first event</a>
                        <a href="/browse-services" class="btn btn-outline-primary">Browse services</a>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
</main>

<?= $this->include('footer') ?>
