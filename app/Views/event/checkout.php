<?= $this->include('header') ?>

<div class="dashboard-wrapper">
    <div class="container" style="max-width: 700px;">
        <h3 class="mb-2">Checkout</h3>
        <p class="text-muted mb-4">Pay your deposit to submit booking requests to vendors.</p>

        <div class="dash-card mb-3">
            <h5><i class="fas fa-calendar text-primary me-2"></i><?= esc($event['title']) ?></h5>
            <div class="text-muted small">
                <?php if (!empty($event['date'])): ?><i class="fas fa-calendar-alt me-1"></i><?= date('d M Y', strtotime($event['date'])) ?><?php endif; ?>
                <?php if (!empty($event['location'])): ?><span class="ms-2"><i class="fas fa-map-marker-alt me-1"></i><?= esc($event['location']) ?></span><?php endif; ?>
            </div>
        </div>

        <!-- Order summary -->
        <div class="dash-card mb-3">
            <h6 class="mb-3">Order Summary</h6>
            <?php foreach ($basketItems as $item): ?>
                <div class="d-flex justify-content-between py-2 border-bottom">
                    <div>
                        <span class="fw-bold"><?= esc($item['service_title']) ?></span>
                        <?php if (!empty($item['package_name'])): ?>
                            <span class="text-muted small ms-1">(<?= esc($item['package_name']) ?>)</span>
                        <?php endif; ?>
                    </div>
                    <span>£<?= number_format($item['deposit_amount'], 2) ?></span>
                </div>
            <?php endforeach; ?>
            <div class="d-flex justify-content-between pt-3">
                <span class="fw-bold">Total Deposit</span>
                <span class="fw-bold text-primary fs-5">£<?= number_format($totalDeposit, 2) ?></span>
            </div>
        </div>

        <!-- Payment form (simulated) -->
        <div class="dash-card mb-3">
            <h6 class="mb-3"><i class="fas fa-credit-card text-success me-2"></i>Payment Details</h6>
            <p class="text-muted small">Payment processing is simulated. No real payment will be taken.</p>

            <form method="post" action="/event/checkout/process/<?= $event['id'] ?>">
                <div class="mb-3">
                    <label class="form-label">Card Number</label>
                    <input type="text" class="form-control" value="4242 4242 4242 4242" disabled>
                </div>
                <div class="row">
                    <div class="col-6 mb-3">
                        <label class="form-label">Expiry</label>
                        <input type="text" class="form-control" value="12/28" disabled>
                    </div>
                    <div class="col-6 mb-3">
                        <label class="form-label">CVC</label>
                        <input type="text" class="form-control" value="123" disabled>
                    </div>
                </div>

                <div class="alert alert-info small">
                    <i class="fas fa-info-circle me-1"></i>
                    By paying the deposit, your booking request will be sent to each vendor for approval.
                    Vendors typically respond within 24-48 hours.
                </div>

                <div class="d-flex justify-content-between">
                    <a href="/event/basket/<?= $event['id'] ?>" class="btn btn-outline-secondary">Back to Basket</a>
                    <button type="submit" class="btn btn-success btn-lg">
                        <i class="fas fa-lock me-1"></i>Pay Deposit £<?= number_format($totalDeposit, 2) ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?= $this->include('footer') ?>
