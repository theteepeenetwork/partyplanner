<?php
/**
 * Create-a-Service wizard — sticky footer navigation + content-column closer.
 *
 * Closes the .pp-wcol / .pp-wizard wrappers opened by wizard_rail.php and
 * renders the Back / progress / primary-action bar. The primary button lives
 * outside the step <form> and submits it through the HTML5 `form` attribute,
 * so the existing form-level submit handlers (validation etc.) still fire.
 *
 * Self-contained: step, previous URL, button label and the target form id are
 * all derived from the request URI + session.
 */
$ppUri = uri_string();

$ppMeta = [
    1 => ['name' => 'Service info',          'form' => 'serviceForm',     'prev' => null],
    2 => ['name' => 'Availability',          'form' => 'step2Form',       'prev' => '/service/step1'],
    3 => ['name' => 'Pricing',               'form' => 'publicEventForm', 'prev' => '/service/step2'],
    4 => ['name' => 'Delivery & logistics',  'form' => 'publicEventForm', 'prev' => '/service/step3'],
    5 => ['name' => 'Optional extras',       'form' => 'publicEventForm', 'prev' => '/service/step4'],
    6 => ['name' => 'Cancellation',          'form' => 'publicEventForm', 'prev' => '/service/step5'],
    7 => ['name' => 'Review & publish',      'form' => 'reviewForm',      'prev' => '/service/step6'],
];

$ppCurrent = 1;
if (strpos($ppUri, 'review') !== false) {
    $ppCurrent = 7;
} elseif (preg_match('#step([1-6])#', $ppUri, $m)) {
    $ppCurrent = (int) $m[1];
}
$ppCur = $ppMeta[$ppCurrent];

// primary-button label mirrors the original per-step "Next / Review" logic:
// jumping straight back to review once a later step already has data.
if ($ppCurrent === 7) {
    $ppNextLabel = 'Submit for review';
} elseif ($ppCurrent === 1) {
    $ppNextLabel = session('step2_data') ? 'Review' : 'Continue';
} else {
    $ppNextLabel = session('step' . ($ppCurrent + 1) . '_data') ? 'Review' : 'Continue';
}

$ppIsReview   = ($ppCurrent === 7);
$ppCancelUrl  = (session()->get('role') === 'vendor') ? '/profile/services' : '/';
?>
    </div><!-- /.pp-wcol -->
</div><!-- /.pp-wizard -->

<div class="pp-wizard-nav<?= $ppIsReview ? ' pp-wizard-nav--static' : '' ?>">
    <div class="pp-wizard-nav-inner">
        <?php if ($ppCur['prev']): ?>
            <a class="pp-btn pp-btn-ghost" href="<?= esc($ppCur['prev'], 'attr') ?>">
                <i class="fas fa-arrow-left" aria-hidden="true"></i> Back
            </a>
        <?php else: ?>
            <a class="pp-btn pp-btn-text" href="<?= esc($ppCancelUrl, 'attr') ?>">Cancel</a>
        <?php endif; ?>

        <div class="pp-foot-prog">Step <b><?= $ppCurrent ?></b> of <b>7</b> &middot; <?= esc($ppCur['name']) ?></div>

        <button type="submit" form="<?= esc($ppCur['form'], 'attr') ?>"
            class="pp-btn <?= $ppIsReview ? 'pp-btn-cta' : 'pp-btn-primary' ?>"
            <?= $ppCurrent === 2 ? 'id="step2-next-btn"' : '' ?>>
            <?= esc($ppNextLabel) ?>
            <i class="fas fa-<?= $ppIsReview ? 'check' : 'arrow-right' ?>" aria-hidden="true"></i>
        </button>
    </div>
</div>
