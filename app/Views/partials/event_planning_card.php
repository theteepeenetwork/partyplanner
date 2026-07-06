<?php
/**
 * @var array<string,mixed> $event
 */
$eid = (int) ($event['id'] ?? 0);
$basketCount = (int) ($event['basket_count'] ?? 0);
$basketEst = (float) ($event['basket_estimated'] ?? 0);
$basketDep = (float) ($event['basket_deposit'] ?? 0);
$servicesBooked = (int) ($event['services_booked'] ?? $event['servicesBooked'] ?? 0);
$totalCost = (float) ($event['total_cost'] ?? $event['totalCost'] ?? 0);
$pendingCount = (int) ($event['pending_count'] ?? 0);
$acceptedCount = (int) ($event['accepted_count'] ?? 0);
?>
<div class="event-overview-card">
    
    <div class="row">
        <div class="col-md-8">
            
            <div class="event-title"><?= esc($event['title'] ?? '') ?></div>
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
                <?php if ($basketCount > 0): ?>
                    <span class="badge bg-warning text-dark">
                        <?= $basketCount ?> in basket · Est. £<?= number_format($basketEst, 2) ?>
                    </span>
                <?php endif; ?>
                <?php if ($servicesBooked > 0): ?>
                    <span class="badge bg-info"><?= $servicesBooked ?> booked</span>
                <?php endif; ?>
                <?php if ($pendingCount > 0): ?>
                    <span class="badge bg-secondary"><?= $pendingCount ?> pending vendor</span>
                <?php endif; ?>
                <?php if ($totalCost > 0): ?>
                    <span class="badge bg-light text-dark border">Booked est. £<?= number_format($totalCost, 2) ?></span>
                <?php endif; ?>
            </div>

            <?php if ($basketCount > 0): ?>
                <p class="small text-muted mt-2 mb-0">
                    Deposit for this event: <strong>£<?= number_format($basketDep, 2) ?></strong> (<?= \App\Libraries\DepositCalculator::percentDisplay() ?>% of basket estimate)
                </p>
            <?php endif; ?>
        </div>
        <div class="col-md-4 text-md-end mt-3 mt-md-0 d-flex flex-column justify-content-center gap-2">
            <a href="/browse-services?event_id=<?= $eid ?>" class="btn btn-sm btn-outline-primary">
                <i class="fas fa-plus me-1"></i>Add services
            </a>
            <?php if ($basketCount > 0): ?>
                <a href="/event/basket/<?= $eid ?>" class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-shopping-basket me-1"></i>Review basket (<?= $basketCount ?>)
                </a>
                <a href="/event/checkout/<?= $eid ?>" class="btn btn-sm btn-success">
                    <i class="fas fa-lock me-1"></i>Pay deposit · £<?= number_format($basketDep, 2) ?>
                </a>
            <?php endif; ?>
            <?php if ($servicesBooked > 0): ?>
                <a href="/profile/my-bookings?event_id=<?= $eid ?>" class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-list me-1"></i>Bookings for this event
                </a>
            <?php endif; ?>
        </div>
    </div>


</div>
