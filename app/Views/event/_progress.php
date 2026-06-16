<?php
$steps = ['Event Basics', 'Location', 'Preferences', 'Review'];
$current = (int) ($currentStep ?? 1);
$last = count($steps);
?>
<div class="stepper-head">
    <?php foreach ($steps as $i => $label):
        $stepNum = $i + 1;
        $cls = '';
        if ($stepNum === $current) {
            $cls = ' on';
        } elseif ($stepNum < $current) {
            $cls = ' done';
        }
    ?>
        <div class="sdot<?= $cls ?>">
            <span class="sn"><?= $stepNum < $current ? '<i class="fas fa-check"></i>' : $stepNum ?></span>
            <span class="sl"><?= esc($label) ?></span>
        </div>
        <?php if ($stepNum < $last): ?>
            <span class="sline"></span>
        <?php endif; ?>
    <?php endforeach; ?>
</div>
