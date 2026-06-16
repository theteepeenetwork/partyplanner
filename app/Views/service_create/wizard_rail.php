<?php
/**
 * Create-a-Service wizard — left step rail + content-column opener.
 *
 * Self-contained: it derives the current step from the request URI and the
 * per-step session data, so the step views can include it without the
 * controller passing anything. Pair with wizard_nav.php, which closes the
 * markup this opens and renders the sticky footer nav.
 */
$ppUri = uri_string(); // e.g. "service/step3", "service/review", "service/create"

$ppSteps = [
    1 => ['url' => '/service/step1',  'name' => 'Service info',          'key' => 'step1_data'],
    2 => ['url' => '/service/step2',  'name' => 'Availability',          'key' => 'step2_data'],
    3 => ['url' => '/service/step3',  'name' => 'Pricing',               'key' => 'step3_data'],
    4 => ['url' => '/service/step4',  'name' => 'Delivery & logistics',  'key' => 'step4_data'],
    5 => ['url' => '/service/step5',  'name' => 'Optional extras',       'key' => 'step5_data'],
    6 => ['url' => '/service/step6',  'name' => 'Cancellation',          'key' => 'step6_data'],
    7 => ['url' => '/service/review', 'name' => 'Review & publish',      'key' => null],
];

$ppCurrent = 1;
if (strpos($ppUri, 'review') !== false) {
    $ppCurrent = 7;
} elseif (preg_match('#step([1-6])#', $ppUri, $m)) {
    $ppCurrent = (int) $m[1];
}

$ppSaveExitUrl = (session()->get('role') === 'vendor') ? '/profile/services' : '/';
?>
<div class="pp-wizard">
    <aside class="pp-rail" aria-label="Create a service progress">
        <div class="pp-rail-head">
            <div class="pp-rail-eyebrow">Partysmith</div>
            <div class="pp-rail-title">Create a service</div>
        </div>

        <ol class="pp-steps">
            <?php foreach ($ppSteps as $n => $s):
                $hasData = $s['key'] && session($s['key']);
                if ($n === $ppCurrent) {
                    $state = 'active';
                } elseif ($n < $ppCurrent || $hasData) {
                    $state = 'done';
                } else {
                    $state = 'upcoming';
                }
                // the review screen is only reachable once step 6 is complete
                if ($n === 7 && $state !== 'active' && ! session('step6_data')) {
                    $state = 'upcoming';
                }
                $clickable = ($state !== 'upcoming');
                $tag  = $clickable ? 'a' : 'span';
                $href = $clickable ? ' href="' . esc($s['url'], 'attr') . '"' : '';
                $aria = ($state === 'active') ? ' aria-current="step"' : '';
            ?>
                <li class="pp-step <?= $state ?>">
                    <<?= $tag ?> class="pp-step-link"<?= $href ?><?= $aria ?>>
                        <span class="pp-dot"><?= $state === 'done' ? '<i class="fas fa-check" aria-hidden="true"></i>' : $n ?></span>
                        <span class="pp-step-meta">
                            <span class="pp-step-label">Step <?= $n ?></span>
                            <span class="pp-step-name"><?= esc($s['name']) ?></span>
                        </span>
                    </<?= $tag ?>>
                </li>
            <?php endforeach; ?>
        </ol>

        <div class="pp-rail-foot">
            <a class="pp-save-exit" href="<?= esc($ppSaveExitUrl, 'attr') ?>">
                <i class="fas fa-arrow-left" aria-hidden="true"></i> Save &amp; exit
            </a>
            <div class="pp-autosave"><span class="pp-pulse"></span> Progress saved as you go</div>
        </div>
    </aside>

    <div class="pp-wcol">
