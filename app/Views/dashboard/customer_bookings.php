<?= $this->include('header') ?>

<main class="page-main">
<div class="dashboard-wrapper">
    <div class="container">
        <?= $this->include('dashboard/_customer_tabs') ?>

        
        <div class="mb-4">
            <h4 class="mb-2">My Bookings</h4>
            <p class="dash-page-lead mb-0">Grouped by event so each celebration stays separate.</p>
        </div>

        <?php if (!empty($filterEventId)): ?>
            <p class="mb-3"><a href="/profile/my-bookings">&larr; All events</a></p>
        <?php endif; ?>

        <?php if (!empty($bookingItems)): ?>
            <?php
            $pending = array_filter($bookingItems, fn($b) => $b['status'] === 'pending');
            $accepted = array_filter($bookingItems, fn($b) => $b['status'] === 'accepted');
            $confirmed = array_filter($bookingItems, fn($b) => $b['status'] === 'confirmed');
            $declined = array_filter($bookingItems, fn($b) => in_array($b['status'], ['rejected', 'cancelled']));
            $groups = $groupedByEvent ?? [];
            ?>

            <div class="d-flex gap-2 mb-4 flex-wrap">
                <span class="badge bg-warning text-dark p-2"><?= count($pending) ?> Pending</span>
                <span class="badge bg-success p-2"><?= count($accepted) ?> Accepted</span>
                <span class="badge bg-primary p-2"><?= count($confirmed) ?> Confirmed</span>
                <span class="badge bg-danger p-2"><?= count($declined) ?> Declined</span>
            </div>

            <?php foreach ($groups as $group): ?>
                <h5 class="mt-4 mb-3">
                    <i class="fas fa-calendar text-primary me-2"></i><?= esc($group['event_title']) ?>
                    <?php if (!empty($group['event_date'])): ?>
                        <span class="text-muted fw-normal small">— <?= date('d M Y', strtotime($group['event_date'])) ?></span>
                    <?php endif; ?>
                </h5>
                <?php foreach ($group['items'] as $item): ?>
                    
                    <div class="dash-card mb-3">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <?php
                                $statusClass = match ($item['status']) {
                                    'pending' => 'bg-warning text-dark',
                                    'accepted' => 'bg-success',
                                    'confirmed' => 'bg-primary',
                                    'rejected', 'cancelled' => 'bg-danger',
                                    default => 'bg-secondary',
                                };
                                ?>
                                <span class="badge <?= $statusClass ?>"><?= ucfirst($item['status']) ?></span>
                                <?php if ($item['payment_status'] === 'succeeded'): ?>
                                    <span class="badge bg-info ms-1">Deposit paid (£<?= number_format($item['amount_paid'], 2) ?>)</span>
                                <?php endif; ?>
                                <h6 class="mb-1 mt-2"><?= esc($item['service_title']) ?></h6>
                                
                                <div class="text-muted small"><i class="fas fa-store me-1"></i><?= esc($item['vendor_name']) ?></div>
                                <?php if (!empty($item['price']) || !empty($item['service_price'])): ?>
                                    <div class="mt-2">
                                        <strong>Total:</strong> £<?= number_format($item['price'] ?? $item['service_price'] ?? 0, 2) ?>
                                        <?php if ($item['outstanding'] > 0): ?>
                                            <span class="text-danger ms-2">Outstanding: £<?= number_format($item['outstanding'], 2) ?></span>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                                <?= view('partials/quote_breakdown', ['quoteDetail' => $item['quote_detail'] ?? null, 'collapseId' => (int) $item['id']]) ?>
                                <?php if (!empty($item['pending_vendor_quote'])): ?>
                                    <div class="alert alert-warning small mt-2 mb-0">
                                        Revised quote: £<?= number_format((float) $item['pending_vendor_quote']['total'], 2) ?>
                                        <form method="post" action="/profile/vendor-quote/<?= (int) $item['id'] ?>/accept" class="d-inline ms-2">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="btn btn-sm btn-success">Accept</button>
                                        </form>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                                <a href="/service/view/<?= $item['service_id'] ?>" class="btn btn-sm btn-outline-primary">View service</a>
                                <?php if (!in_array($item['status'], ['rejected', 'cancelled'], true)): ?>
                                    <a href="<?= base_url('profile/messages/start/' . (int) $item['service_id']) ?>" class="btn btn-sm btn-outline-secondary mt-1">Message vendor</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="dash-card text-center py-5 px-3">
                <div class="dash-empty-state">
                    <h5 class="fw-semibold">No bookings yet</h5>
                    <p class="text-muted mb-4">Add services to an event basket and pay a deposit to send requests.</p>
                    <a href="/profile/events" class="btn btn-primary">My events</a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
</main>

<?= $this->include('footer') ?>
