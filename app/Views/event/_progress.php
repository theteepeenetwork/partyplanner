<div class="d-flex justify-content-between mb-4">
    <?php
    $steps = ['Event Basics', 'Location', 'Preferences', 'Review'];
    foreach ($steps as $i => $label):
        $stepNum = $i + 1;
        $isActive = ($currentStep ?? 1) == $stepNum;
        $isCompleted = ($currentStep ?? 1) > $stepNum;
    ?>
        <div class="text-center flex-fill">
            <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-1 <?= $isActive ? 'bg-primary text-white' : ($isCompleted ? 'bg-success text-white' : 'bg-light text-muted') ?>" style="width:36px;height:36px;font-size:0.85rem;font-weight:600;">
                <?= $isCompleted ? '<i class="fas fa-check"></i>' : $stepNum ?>
            </div>
            <div class="small <?= $isActive ? 'fw-bold text-primary' : 'text-muted' ?>"><?= $label ?></div>
        </div>
    <?php endforeach; ?>
</div>
