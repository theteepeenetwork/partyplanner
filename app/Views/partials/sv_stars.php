<?php
/**
 * Shared star renderer. Renders 5 stars; floor($rating) filled gold, the rest dimmed.
 * Usage: <?= view('partials/sv_stars', ['rating' => $r]) ?>
 * Wrap in a `.sv-stars` element (gold colour) on the host page, or rely on the inline colour.
 *
 * @var float|int|null $rating
 */
$rating = (float) ($rating ?? 0);
$filled = (int) floor($rating);
$starPath = '<path d="M12 2l2.9 6.3 6.8.7-5.1 4.6 1.4 6.7L12 17.8 6 20.6l1.4-6.7L2.3 9l6.8-.7z"/>';
for ($i = 1; $i <= 5; $i++):
    $isFilled = $i <= $filled;
?><svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor" style="color:#C4956A<?= $isFilled ? '' : ';opacity:.25' ?>" aria-hidden="true"><?= $starPath ?></svg><?php
endfor;
