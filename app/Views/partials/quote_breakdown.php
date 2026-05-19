<?php
/** @var array{lines?: list<array{label?: string, amount?: float, code?: string}>, warnings?: list<string>}|null $quoteDetail */
$quoteDetail = $quoteDetail ?? null;
if (!$quoteDetail || empty($quoteDetail['lines'])) {
    return;
}
$cid = (int) ($collapseId ?? 0);
?>
<div class="quote-breakdown small mt-2">
    <button class="btn btn-link btn-sm p-0" type="button" data-bs-toggle="collapse" data-bs-target="#quote-detail-<?= $cid ?>">
        View quote breakdown
    </button>
    <div class="collapse mt-2" id="quote-detail-<?= $cid ?>">
        <table class="table table-sm mb-0">
            <tbody>
                <?php foreach ($quoteDetail['lines'] as $line): ?>
                    <?php if (($line['code'] ?? '') === 'platform_commission') {
                        continue;
                    } ?>
                    <tr>
                        <td><?= esc($line['label'] ?? '') ?></td>
                        <td class="text-end">£<?= number_format((float) ($line['amount'] ?? 0), 2) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php if (!empty($quoteDetail['warnings'])): ?>
            <ul class="text-warning mb-0 ps-3">
                <?php foreach ($quoteDetail['warnings'] as $w): ?>
                    <li><?= esc($w) ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
</div>
