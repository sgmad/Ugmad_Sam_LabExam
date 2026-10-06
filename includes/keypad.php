<?php /* On-screen PIN keypad, shown on phones only. $biometric: show fingerprint key. */ ?>
<div class="keypad" aria-label="PIN keypad">
    <?php for ($i = 1; $i <= 9; $i++): ?>
        <button type="button" class="key" data-key="<?= $i ?>"><?= $i ?></button>
    <?php endfor; ?>
    <?php if (!empty($biometric)): ?>
        <button type="button" class="key key-icon" data-soon="Biometric login isn't available in this demo." aria-label="Use biometrics"><?= icon('fingerprint') ?></button>
    <?php else: ?>
        <span></span>
    <?php endif; ?>
    <button type="button" class="key" data-key="0">0</button>
    <button type="button" class="key key-icon" data-key="del" aria-label="Delete"><?= icon('backspace') ?></button>
</div>
