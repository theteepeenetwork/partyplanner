<?= $this->include('header') ?>

<main class="page-main">
<div class="dashboard-wrapper">
    <div class="container">
        <?= $user['role'] === 'vendor' ? $this->include('dashboard/_vendor_tabs') : $this->include('dashboard/_customer_tabs') ?>

        <div class="d-flex align-items-center mb-4">
            <a href="/profile/messages" class="btn btn-sm btn-outline-secondary me-3"><i class="fas fa-arrow-left"></i></a>
            <div>
                <h5 class="mb-0"><?= esc($peer_name ?? $vendor_name) ?></h5>
                <small class="text-muted"><?= esc($service_name) ?></small>
            </div>
        </div>

        <div class="dash-card" style="max-height: 500px; overflow-y: auto;" id="message-container">
            <?php if ($modWarn = session()->getFlashdata('moderation_warning')): ?>
                <div class="alert alert-warning"><?= esc($modWarn) ?></div>
            <?php endif; ?>
            <?php if (!empty($messages)): ?>
                <?php foreach ($messages as $msg): ?>
                    <?php $isMe = ($msg['sender_id'] == $user['id']); ?>
                    <div class="d-flex mb-3 <?= $isMe ? 'justify-content-end' : 'justify-content-start' ?>">
                        <div class="p-2 px-3 rounded-3 <?= $isMe ? 'bg-primary text-white' : 'bg-light' ?>" style="max-width: 70%;">
                            <div class="small"><?= nl2br(esc($msg['message'])) ?></div>
                            <?= view('partials/chat_moderation_meta', ['msg' => $msg]) ?>
                            <div class="<?= $isMe ? 'text-white-50' : 'text-muted' ?> small mt-1" style="font-size:0.7rem;">
                                <?= date('d M H:i', strtotime($msg['created_at'])) ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text-center text-muted">No messages yet. Start the conversation below.</p>
            <?php endif; ?>
        </div>

        <!-- Send message form -->
        <form action="/profile/messages/send" method="post" class="mt-3">
            <?= csrf_field() ?>
            <input type="hidden" name="chat_room_id" value="<?= $room['id'] ?>">
            <div class="input-group">
                <input type="text" class="form-control" name="message" placeholder="Type your message..." required>
                <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane"></i></button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var container = document.getElementById('message-container');
    container.scrollTop = container.scrollHeight;
});
</script>

</main>

<?= $this->include('footer') ?>
