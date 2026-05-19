<?= $this->include('header') ?>

<main class="page-main">
<div class="dashboard-wrapper">
    <div class="container">
        <?= $this->include('dashboard/_customer_tabs') ?>

        <div class="mb-4">
            <h4 class="mb-2">My Bookings</h4>
            <p class="dash-page-lead mb-0">Every request and confirmation lives here. We surface status so you always know whether a vendor has accepted, needs payment, or has declined.</p>
        </div>

        <?php if (!empty($bookingItems)): ?>
            <?php
            $pending = array_filter($bookingItems, fn($b) => $b['status'] === 'pending');
            $accepted = array_filter($bookingItems, fn($b) => $b['status'] === 'accepted');
            $confirmed = array_filter($bookingItems, fn($b) => $b['status'] === 'confirmed');
            $declined = array_filter($bookingItems, fn($b) => in_array($b['status'], ['rejected', 'cancelled']));
            ?>

            <div class="d-flex gap-2 mb-4 flex-wrap">
                <span class="badge bg-warning text-dark p-2"><?= count($pending) ?> Pending</span>
                <span class="badge bg-success p-2"><?= count($accepted) ?> Accepted</span>
                <span class="badge bg-primary p-2"><?= count($confirmed) ?> Confirmed</span>
                <span class="badge bg-danger p-2"><?= count($declined) ?> Declined</span>
            </div>

            <?php foreach ($bookingItems as $item): ?>
                <div class="dash-card mb-3">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <div class="d-flex align-items-center mb-2 flex-wrap gap-1">
                                <?php
                                $statusClass = match($item['status']) {
                                    'pending' => 'bg-warning text-dark',
                                    'accepted' => 'bg-success',
                                    'confirmed' => 'bg-primary',
                                    'rejected', 'cancelled' => 'bg-danger',
                                    default => 'bg-secondary'
                                };
                                ?>
                                <span class="badge <?= $statusClass ?>"><?= ucfirst($item['status']) ?></span>
                                <?php if ($item['payment_status'] === 'succeeded'): ?>
                                    <span class="badge bg-info">Deposit Paid (£<?= number_format($item['amount_paid'], 2) ?>)</span>
                                <?php elseif ($item['payment_status'] !== 'unpaid'): ?>
                                    <span class="badge bg-light text-dark"><?= ucfirst($item['payment_status']) ?></span>
                                <?php endif; ?>
                            </div>

                            <h6 class="mb-1"><?= esc($item['service_title']) ?></h6>
                            <div class="text-muted small">
                                <i class="fas fa-store me-1"></i><?= esc($item['vendor_name']) ?>
                            </div>
                            <div class="text-muted small mt-1">
                                <i class="fas fa-calendar me-1"></i><?= esc($item['event_title'] ?? '') ?>
                                <?php if (!empty($item['event_date'])): ?>
                                    — <?= date('d M Y', strtotime($item['event_date'])) ?>
                                <?php endif; ?>
                                <?php if (!empty($item['location'])): ?>
                                    <span class="ms-2"><i class="fas fa-map-marker-alt me-1"></i><?= esc($item['location']) ?></span>
                                <?php endif; ?>
                            </div>

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
                                    Vendor sent a revised quote: £<?= number_format((float) $item['pending_vendor_quote']['total'], 2) ?>
                                    <form method="post" action="/profile/vendor-quote/<?= (int) $item['id'] ?>/accept" class="d-inline ms-2">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn-sm btn-success">Accept revised quote</button>
                                    </form>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-4 text-md-end mt-3 mt-md-0">
                            <a href="/service/view/<?= $item['service_id'] ?>" class="btn btn-sm btn-outline-primary me-1">View Service</a>
                            <?php if (!in_array($item['status'], ['rejected', 'cancelled'], true)): ?>
                                <a href="<?= base_url('profile/messages/start/' . (int) $item['service_id']) ?>" class="btn btn-sm btn-outline-secondary">Message vendor</a>
                            <?php else: ?>
                                <span class="btn btn-sm btn-outline-secondary disabled" tabindex="-1">Message vendor</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="dash-card text-center py-5 px-3">
                <div class="dash-empty-state">
                    <i class="fas fa-shopping-bag fa-3x text-muted mb-3 d-block" aria-hidden="true"></i>
                    <h5 class="fw-semibold">No bookings yet</h5>
                    <p class="text-muted mb-4">Browse vendors, add services to an event, and send a booking request. Your thread and status updates will show up here.</p>
                    <div class="d-flex flex-column flex-sm-row gap-2 justify-content-center">
                        <a href="/browse-services" class="btn btn-primary">Browse services</a>
                        <a href="/event/create" class="btn btn-outline-primary"><i class="fas fa-plus me-1"></i>Create an event</a>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
</main>

<?= $this->include('footer') ?>
