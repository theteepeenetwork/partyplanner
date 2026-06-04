<?= $this->include('header') ?>
<main class="page-main">
<div class="dashboard-wrapper">
<div class="container">
    <?= $user['role'] === 'vendor' ? $this->include('dashboard/_vendor_tabs') : $this->include('dashboard/_customer_tabs') ?>
    <?= $this->include('dashboard/_flash_alerts') ?>
    <div class="fye-page">
        <h1 class="fye-page-title" style="margin-bottom:16px">Messages</h1>

        <?php if (!empty($rooms)): ?>
            <div class="fye-thread">
                <!-- Thread list -->
                <div class="thread-list">
                    <?php foreach ($rooms as $room):
                        $peername  = $room['peer_name'] ?? $room['vendor_name'] ?? 'Vendor';
                        $initials  = strtoupper(substr($peername, 0, 2));
                        $isActive  = isset($activeRoom) && (int)$activeRoom['id'] === (int)$room['id'];
                        $preview   = substr($room['last_message'] ?? '', 0, 50);
                    ?>
                        <a href="/profile/messages/<?= (int)$room['id'] ?>" class="tl-item <?= $isActive ? 'on' : '' ?>">
                            <div class="lava" style="border-radius:11px;flex:0 0 auto"><?= esc($initials) ?></div>
                            <div style="min-width:0">
                                <div class="nm"><?= esc($peername) ?></div>
                                <div class="pv"><?= esc($preview) ?></div>
                            </div>
                            <?php if (!empty($room['unread_count']) && $room['unread_count'] > 0): ?>
                                <span class="dot" style="flex:0 0 auto"></span>
                            <?php endif; ?>
                        </a>
                    <?php endforeach; ?>
                </div>
                <!-- Conversation -->
                <div class="thread-conv">
                    <?php if (!empty($activeRoom) && !empty($messages)): ?>
                        <?php
                        $peername = $activeRoom['peer_name'] ?? $activeRoom['vendor_name'] ?? 'Vendor';
                        $initials = strtoupper(substr($peername, 0, 2));
                        ?>
                        <div class="conv-head">
                            <div class="lava" style="border-radius:11px;flex:0 0 auto"><?= esc($initials) ?></div>
                            <div>
                                <div style="font-weight:800;font-size:15px"><?= esc($peername) ?></div>
                                <div class="fye-muted" style="font-size:12px"><?= esc($activeRoom['service_name'] ?? '') ?></div>
                            </div>
                        </div>
                        <div class="conv-body">
                            <?php foreach ($messages as $msg):
                                $isMe = (int)$msg['sender_id'] === (int)session()->get('user_id');
                                $time = !empty($msg['created_at']) ? date('d M H:i', strtotime($msg['created_at'])) : '';
                            ?>
                                <div class="bubble <?= $isMe ? 'me' : 'them' ?>">
                                    <?= esc($msg['message']) ?>
                                    <div class="bt"><?= esc($time) ?></div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="conv-input">
                            <form method="post" action="/profile/messages/send" style="display:flex;gap:10px;flex:1">
                                <?= csrf_field() ?>
                                <input type="hidden" name="room_id" value="<?= (int)$activeRoom['id'] ?>">
                                <input type="text" name="message" class="fake" placeholder="Write a message…" style="border:none;outline:none;font-size:13px;background:var(--fye-paper-2);border-radius:999px;padding:11px 16px;flex:1;color:var(--fye-ink)">
                                <button type="submit" class="fye-btn primary" style="padding:9px 14px"><i class="fa-solid fa-paper-plane"></i></button>
                            </form>
                        </div>
                    <?php elseif (!empty($rooms)): ?>
                        <div style="flex:1;display:grid;place-items:center;color:var(--fye-ink-3);font-size:13.5px">
                            Select a conversation
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php else: ?>
            <div class="icard text-center py-5">
                <i class="fa-solid fa-comments fa-3x mb-3 d-block fye-faint"></i>
                <h5 style="font-family:var(--fye-display)">No messages yet</h5>
                <p class="fye-muted mb-4" style="font-size:13.5px">After you request or confirm a booking, you and the vendor can chat here.</p>
                <div style="display:flex;gap:10px;justify-content:center;flex-wrap:wrap">
                    <?php if (($user['role'] ?? '') === 'vendor'): ?>
                        <a href="/profile/bookings" class="fye-btn primary">Booking requests</a>
                    <?php else: ?>
                        <a href="/browse-services" class="fye-btn primary">Browse services</a>
                        <a href="/profile/my-bookings" class="fye-btn ghost">My bookings</a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
</div>
</main>
<?= $this->include('footer') ?>
