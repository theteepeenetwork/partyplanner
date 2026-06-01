<?php
/** @var array $msg */
$st = $msg['moderation_status'] ?? 'clean';
$note = trim((string) ($msg['admin_note'] ?? ''));
$P = \App\Libraries\ChatModeration::class;
?>
<?php if ($st === \App\Libraries\ChatModeration::STATUS_PENDING): ?>
    <div class="small text-warning mt-1">
        <i class="fas fa-exclamation-triangle"></i> Flagged for language review (masked version shown).
    </div>
<?php elseif ($st === \App\Libraries\ChatModeration::STATUS_APPROVED): ?>
    <div class="small text-success mt-1">
        <i class="fas fa-check-circle"></i> Reviewed and accepted as sent.
        <?php if ($note !== ''): ?><span class="text-muted">— <?= esc($note) ?></span><?php endif; ?>
    </div>
<?php elseif ($st === \App\Libraries\ChatModeration::STATUS_REJECTED): ?>
    <div class="small text-danger mt-1">
        <i class="fas fa-ban"></i> Reviewed and not sent.
        <?php if ($note !== ''): ?><span class="text-muted">— <?= esc($note) ?></span><?php endif; ?>
    </div>
<?php endif; ?>
