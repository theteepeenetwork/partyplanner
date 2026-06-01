<?= $this->include('header') ?>

<style>
    .select-event-wrap {
        max-width: 680px;
    }
    .adding-service-card {
        background: var(--surface-warm, #F5EFE6);
        border: 1px solid rgba(196, 149, 106, 0.25);
        border-radius: 14px;
        padding: 1.1rem 1.25rem;
    }
    .adding-service-thumb {
        width: 60px;
        height: 60px;
        object-fit: cover;
        border-radius: 10px;
        flex-shrink: 0;
    }
    .adding-service-thumb--placeholder {
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(196, 149, 106, 0.15);
        border: 1px solid rgba(196, 149, 106, 0.2);
    }
    .event-option-card {
        border: 1.5px solid rgba(0, 0, 0, 0.08);
        border-radius: 12px;
        padding: 1rem 1.25rem;
        background: var(--surface-elevated, #fff);
        transition: border-color 0.2s ease, box-shadow 0.2s ease, transform 0.15s ease;
    }
    .event-option-card.is-selectable {
        cursor: pointer;
    }
    .event-option-card.is-selectable:hover,
    .event-option-card.is-selectable:focus-within {
        border-color: var(--accent-orange, #C4956A);
        box-shadow: 0 4px 18px rgba(196, 149, 106, 0.18);
        transform: translateY(-2px);
    }
    .event-option-card.is-unavailable {
        opacity: 0.65;
        background: var(--surface-warm, #f7f3ec);
        cursor: default;
    }
    .event-option-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 0.3rem 0.9rem;
        margin-top: 0.3rem;
        font-size: 0.82rem;
    }
    .select-divider {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        margin: 1.5rem 0 1rem;
        color: var(--text-muted-custom, #6B6560);
        font-size: 0.78rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.07em;
    }
    .select-divider::before,
    .select-divider::after {
        content: '';
        flex: 1;
        height: 1px;
        background: rgba(0,0,0,0.08);
    }
</style>

<main class="page-main">
<div class="dashboard-wrapper">
    <div class="container select-event-wrap">

        <h3 class="mb-1">Choose an event</h3>
        <p class="text-muted mb-4">Select the event you'd like to add this service to.</p>

        <!-- Service being added -->
        <div class="adding-service-card mb-2">
            <div class="d-flex align-items-center gap-3">
                <?php if (!empty($serviceThumbnail)): ?>
                    <img src="<?= base_url($serviceThumbnail) ?>"
                         alt="<?= esc($service['title']) ?>"
                         class="adding-service-thumb">
                <?php else: ?>
                    <div class="adding-service-thumb adding-service-thumb--placeholder">
                        <i class="fas fa-concierge-bell" style="color:var(--accent-orange,#C4956A);"></i>
                    </div>
                <?php endif; ?>
                <div>
                    <div class="text-muted" style="font-size:0.72rem;text-transform:uppercase;letter-spacing:0.08em;font-weight:600;">Adding</div>
                    <div class="fw-semibold" style="color:var(--dark-blue,#1A2E27);"><?= esc($service['title']) ?></div>
                    <?php if (!empty($service['price'])): ?>
                        <div class="small" style="color:var(--accent-orange,#C4956A);">From £<?= number_format($service['price'], 2) ?></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="select-divider">Your events</div>

        <?php foreach ($events as $event): ?>
            <?php
            $eventId = (int) $event['id'];
            $serviceAlreadyAdded = in_array($eventId, $eventsWithService ?? [], true);
            $vendorAlreadyBooked = !$serviceAlreadyAdded && in_array($eventId, $eventsWithVendor ?? [], true);
            $isUnavailable = $serviceAlreadyAdded || $vendorAlreadyBooked;
            ?>

            <?php if ($isUnavailable): ?>
                <div class="event-option-card is-unavailable mb-3">
                    <div class="d-flex align-items-center justify-content-between gap-3">
                        <div>
                            <div class="fw-semibold"><?= esc($event['title']) ?></div>
                            <div class="event-option-meta text-muted">
                                <?php if (!empty($event['event_type'])): ?>
                                    <span class="badge" style="background:var(--accent-orange,#C4956A);color:#fff;"><?= esc($event['event_type']) ?></span>
                                <?php endif; ?>
                                <?php if (!empty($event['guest_count'])): ?>
                                    <span><i class="fas fa-users me-1"></i><?= (int) $event['guest_count'] ?> guests</span>
                                <?php endif; ?>
                                <?php if (!empty($event['date'])): ?>
                                    <span><i class="fas fa-calendar-alt me-1"></i><?= date('d M Y', strtotime($event['date'])) ?></span>
                                <?php endif; ?>
                                <?php if (!empty($event['location'])): ?>
                                    <span><i class="fas fa-map-marker-alt me-1"></i><?= esc($event['location']) ?></span>
                                <?php endif; ?>
                            </div>
                            <?php if ($serviceAlreadyAdded): ?>
                                <div class="small text-muted mt-1"><i class="fas fa-check-circle me-1" style="color:var(--accent-orange,#C4956A);"></i>This service is already in this event's basket.</div>
                            <?php else: ?>
                                <div class="small text-muted mt-1"><i class="fas fa-lock me-1"></i>This vendor is already booked for this event.</div>
                            <?php endif; ?>
                        </div>
                        <a href="/event/basket/<?= $eventId ?>" class="btn btn-sm btn-outline-secondary flex-shrink-0">
                            View basket
                        </a>
                    </div>
                </div>

            <?php else: ?>
                <form method="post" action="/event/add-to-basket/<?= $service['id'] ?>" class="mb-3">
                    <?= csrf_field() ?>
                    <input type="hidden" name="event_id" value="<?= $eventId ?>">
                    <?php if (!empty($selectedOptions['pricing_option'])): ?>
                        <input type="hidden" name="pricing_option" value="<?= esc($selectedOptions['pricing_option']) ?>">
                    <?php endif; ?>
                    <?php if (!empty($selectedOptions['extras']) && is_array($selectedOptions['extras'])): ?>
                        <?php foreach ($selectedOptions['extras'] as $extra): ?>
                            <input type="hidden" name="extras[]" value="<?= esc($extra) ?>">
                        <?php endforeach; ?>
                    <?php endif; ?>
                    <?php if (!empty($selectedOptions['extra_qty']) && is_array($selectedOptions['extra_qty'])): ?>
                        <?php foreach ($selectedOptions['extra_qty'] as $eid => $qty): ?>
                            <?php if ((int) $qty <= 0) { continue; } ?>
                            <input type="hidden" name="extra_qty[<?= (int) $eid ?>]" value="<?= (int) $qty ?>">
                        <?php endforeach; ?>
                    <?php endif; ?>

                    <div class="event-option-card is-selectable" onclick="this.closest('form').submit();">
                        <div class="d-flex align-items-center justify-content-between gap-3">
                            <div>
                                <div class="fw-semibold"><?= esc($event['title']) ?></div>
                                <div class="event-option-meta text-muted">
                                    <?php if (!empty($event['event_type'])): ?>
                                        <span class="badge" style="background:var(--accent-orange,#C4956A);color:#fff;"><?= esc($event['event_type']) ?></span>
                                    <?php endif; ?>
                                    <?php if (!empty($event['guest_count'])): ?>
                                        <span><i class="fas fa-users me-1"></i><?= (int) $event['guest_count'] ?> guests</span>
                                    <?php endif; ?>
                                    <?php if (!empty($event['date'])): ?>
                                        <span><i class="fas fa-calendar-alt me-1"></i><?= date('d M Y', strtotime($event['date'])) ?></span>
                                    <?php endif; ?>
                                    <?php if (!empty($event['location'])): ?>
                                        <span><i class="fas fa-map-marker-alt me-1"></i><?= esc($event['location']) ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-sm btn-primary flex-shrink-0">
                                <i class="fas fa-plus me-1"></i>Add to this event
                            </button>
                        </div>
                    </div>
                </form>
            <?php endif; ?>

        <?php endforeach; ?>

        <div class="text-center mt-4 pt-2" style="border-top:1px solid rgba(0,0,0,0.07);">
            <a href="/event/create" class="btn btn-outline-primary">
                <i class="fas fa-plus me-1"></i>Create New Event
            </a>
        </div>

    </div>
</div>
</main>

<?= $this->include('footer') ?>
