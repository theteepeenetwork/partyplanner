<?= $this->include('header') ?>

<main class="page-main">
<div class="dashboard-wrapper">
    <div class="container">
        <?= $user['role'] === 'vendor' ? $this->include('dashboard/_vendor_tabs') : $this->include('dashboard/_customer_tabs') ?>

        <div class="mb-4">
            <h4 class="mb-2">Messages</h4>
            <?php if (($user['role'] ?? '') === 'vendor'): ?>
                <p class="dash-page-lead mb-0">One inbox for customer questions tied to your services. Prompt replies build trust and help secure bookings.</p>
            <?php else: ?>
                <p class="dash-page-lead mb-0">One inbox for every vendor conversation. Replies stay threaded by booking so context never gets lost.</p>
            <?php endif; ?>
        </div>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger"><?= esc(session()->getFlashdata('error')) ?></div>
        <?php endif; ?>

        <?php if (!empty($rooms)): ?>
            <?php foreach ($rooms as $room): ?>
                <a href="/profile/messages/<?= $room['id'] ?>" class="text-decoration-none">
                    <div class="dash-card mb-2 <?= $room['unread_count'] > 0 ? 'border-start border-primary border-3' : '' ?>">
                        <div class="d-flex align-items-center">
                            <div class="message-avatar">
                                <i class="fas fa-store"></i>
                            </div>
                            <div class="message-content flex-grow-1">
                                <div class="d-flex justify-content-between">
                                    <div class="message-sender"><?= esc($room['peer_name'] ?? $room['vendor_name']) ?></div>
                                    <span class="message-time"><?= $room['last_message_time'] ? date('d M H:i', strtotime($room['last_message_time'])) : '' ?></span>
                                </div>
                                <?php if (!empty($room['service_name'])): ?>
                                    <div class="small text-primary"><?= esc($room['service_name']) ?></div>
                                <?php endif; ?>
                                <div class="message-snippet"><?= esc(substr($room['last_message'], 0, 80)) ?><?= strlen($room['last_message']) > 80 ? '...' : '' ?></div>
                            </div>
                            <?php if ($room['unread_count'] > 0): ?>
                                <span class="badge bg-primary rounded-pill ms-2"><?= $room['unread_count'] ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="dash-card text-center py-5 px-3">
                <div class="dash-empty-state">
                    <i class="fas fa-comments fa-3x text-muted mb-3 d-block" aria-hidden="true"></i>
                    <h5 class="fw-semibold">No messages yet</h5>
                    <p class="text-muted mb-4"><?= ($user['role'] ?? '') === 'vendor'
                        ? 'When customers message you about a booking, threads appear here. List a service and respond to requests to unlock conversations.'
                        : 'After you request or confirm a booking, you and the vendor can chat here. Start by finding a service or checking an existing booking.' ?></p>
                    <div class="d-flex flex-column flex-sm-row gap-2 justify-content-center">
                        <?php if (($user['role'] ?? '') === 'vendor'): ?>
                            <a href="/service/create" class="btn btn-primary"><i class="fas fa-plus me-1"></i>Add a service</a>
                            <a href="/profile/bookings" class="btn btn-outline-secondary">Booking requests</a>
                        <?php else: ?>
                            <a href="/browse-services" class="btn btn-primary">Browse services</a>
                            <a href="/profile/my-bookings" class="btn btn-outline-secondary">My bookings</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
</main>

<?= $this->include('footer') ?>
