<?= $this->include('header') ?>

<main class="page-main">
<div class="dashboard-wrapper">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="mb-0">Event Basket</h3>
                <p class="text-muted mb-0"><?= esc($event['title']) ?> — <?= !empty($event['date']) ? date('d M Y', strtotime($event['date'])) : '' ?></p>
            </div>
            <a href="/browse-services" class="btn btn-outline-primary"><i class="fas fa-plus me-1"></i>Add More Services</a>
        </div>

        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
        <?php endif; ?>
        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
        <?php endif; ?>

        <?php if (!empty($basketItems)): ?>
            <?php foreach ($basketItems as $item): ?>
                <div class="dash-card mb-3">
                    <div class="row align-items-center">
                        <div class="col-md-7">
                            <h6 class="mb-1"><?= esc($item['service_title']) ?></h6>
                            <div class="text-muted small">
                                <i class="fas fa-store me-1"></i><?= esc($item['vendor_name']) ?>
                                <?php if (!empty($item['package_name'])): ?>
                                    <span class="ms-2"><i class="fas fa-box me-1"></i><?= esc($item['package_name']) ?></span>
                                <?php endif; ?>
                            </div>
                            <?php if (!empty($item['service_description'])): ?>
                                <p class="small text-muted mt-1 mb-0"><?= esc(substr($item['service_description'], 0, 100)) ?></p>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-3 text-md-end mt-2 mt-md-0">
                            <div class="fw-bold text-primary">£<?= number_format($item['estimated_total'], 2) ?></div>
                            <div class="small text-muted">Deposit: £<?= number_format($item['deposit_amount'], 2) ?></div>
                        </div>
                        <div class="col-md-2 text-md-end mt-2 mt-md-0">
                            <a href="/event/basket/remove/<?= $item['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Remove this service?');">
                                <i class="fas fa-trash"></i>
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>

            <!-- Totals -->
            <div class="dash-card bg-light">
                <div class="row">
                    <div class="col-md-6">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Estimated Total</span>
                            <span class="fw-bold">£<?= number_format($totalEstimated, 2) ?></span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Deposit Due Today (15%)</span>
                            <span class="fw-bold text-primary">£<?= number_format($totalDeposit, 2) ?></span>
                        </div>
                    </div>
                    <div class="col-md-6 text-md-end mt-3 mt-md-0 d-flex align-items-center justify-content-end">
                        <a href="/event/checkout/<?= $event['id'] ?>" class="btn btn-success btn-lg">
                            <i class="fas fa-lock me-1"></i>Proceed to Checkout
                        </a>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="dash-card text-center py-5">
                <i class="fas fa-shopping-basket fa-3x text-muted mb-3"></i>
                <h5>Your basket is empty</h5>
                <p class="text-muted">Browse services and add them to this event.</p>
                <a href="/browse-services" class="btn btn-primary">Browse Services</a>
            </div>
        <?php endif; ?>
    </div>
</div>
</main>

<?= $this->include('footer') ?>
