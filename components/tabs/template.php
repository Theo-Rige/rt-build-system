<div class="rtbs-tabs">
    <div class="rtbs-tabs__list">
        <?php foreach ($tabs as $key => $tab) : ?>
            <button type="button" id="tabs-<?= $key ?>-trigger" class="rtbs-tabs-trigger rtbs-unstyled-button rtbs-unstyled-button--secondary">
                <?= $tab['label'] ?>
            </button>
        <?php endforeach; ?>
        <div class="rtbs-tabs-indicator"></div>
    </div>
    <?php
    $index = 0;
    foreach ($tabs as $key => $tab) : ?>
        <div id="tabs-<?= $key ?>-panel" class="rtbs-tabs-panel" <?= $index === 0 ? 'hidden' : '' ?>>
            <?= $tab['content'] ?? '' ?>
        </div>
    <?php endforeach; ?>
</div>