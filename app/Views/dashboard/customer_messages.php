<?= $this->include('header') ?>

<div class="dashboard-wrapper">
    <div class="container">
        <?= $this->include('dashboard/_customer_tabs') ?>

        <h4 class="mb-4">Messages</h4>

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
                                    <div class="message-sender"><?= esc($room['vendor_name']) ?></div>
                                    <span class="message-time"><?= date('d M H:i', strtotime($room['last_message_time'])) ?></span>
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
            <div class="dash-card text-center py-5">
                <i class="fas fa-comments fa-3x text-muted mb-3"></i>
                <h5>No messages yet</h5>
                <p class="text-muted">Messages from vendors will appear here after you book services.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?= $this->include('footer') ?>
