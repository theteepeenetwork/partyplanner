<?= $this->include('header') ?>

<main class="page-main">
<div class="dashboard-wrapper">
    <div class="container">
        <?= $this->include('dashboard/_customer_tabs') ?>

        <div class="d-flex flex-column flex-md-row justify-content-md-between align-items-md-start gap-3 mb-4">
            <div>
                <h4 class="mb-2">My Events</h4>
                <p class="dash-page-lead mb-0">Keep dates, venues, and bookings organised per celebration. Nothing is shared publicly until you choose to show it.</p>
            </div>
            <a href="/event/create" class="btn btn-primary flex-shrink-0 align-self-stretch align-self-md-auto"><i class="fas fa-plus me-1"></i>Create New Event</a>
        </div>

        <?php if (!empty($events)): ?>
            <?php foreach ($events as $event): ?>
                <div class="event-overview-card">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="event-title"><?= esc($event['title']) ?></div>
                            <div class="event-meta mt-2">
                                <?php if (!empty($event['event_type'])): ?>
                                    <span class="badge bg-primary me-2"><?= esc($event['event_type']) ?></span>
                                <?php endif; ?>
                                <?php if (!empty($event['date'])): ?>
                                    <span><i class="fas fa-calendar-alt me-1"></i><?= date('d M Y', strtotime($event['date'])) ?></span>
                                <?php endif; ?>
                                <?php if (!empty($event['location'])): ?>
                                    <span class="ms-3"><i class="fas fa-map-marker-alt me-1"></i><?= esc($event['location']) ?></span>
                                <?php endif; ?>
                                <?php if (!empty($event['guest_count'])): ?>
                                    <span class="ms-3"><i class="fas fa-users me-1"></i><?= esc($event['guest_count']) ?> guests</span>
                                <?php endif; ?>
                            </div>

                            <div class="mt-3 d-flex gap-2 flex-wrap small">
                                <span class="badge bg-info"><?= $event['servicesBooked'] ?> service<?= $event['servicesBooked'] != 1 ? 's' : '' ?> booked</span>
                                <?php if ($event['totalCost'] > 0): ?>
                                    <span class="badge bg-secondary">Est. £<?= number_format($event['totalCost'], 2) ?></span>
                                <?php endif; ?>
                            </div>

                            <!-- Progress bar -->
                            <?php
                            $maxServices = 8;
                            $progress = min(100, ($event['servicesBooked'] / $maxServices) * 100);
                            ?>
                            <div class="mt-3">
                                <div class="d-flex justify-content-between small text-muted mb-1">
                                    <span>Planning progress</span>
                                    <span><?= $event['servicesBooked'] ?>/<?= $maxServices ?> key services</span>
                                </div>
                                <div class="progress" style="height: 6px;">
                                    <div class="progress-bar bg-success" style="width: <?= $progress ?>%"></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 text-md-end mt-3 mt-md-0 d-flex flex-column justify-content-center gap-2">
                            <a href="/browse-services" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-plus me-1"></i>Add Services
                            </a>
                            <a href="/profile/my-bookings" class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-list me-1"></i>View Bookings
                            </a>
                        </div>
                    </div>
                </div>
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
