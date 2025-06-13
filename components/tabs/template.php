<div id="tabs-component" class="tabs">
    <div class="tabs-list">
        <?php foreach ($component->tabs as $key => $tab) : ?>
            <button type="button" id="tabs-<?= $key ?>-trigger" class="tabs-trigger"><?= $tab['label'] ?></button>
        <?php endforeach; ?>
        <div class="tabs-indicator"></div>
    </div>
    <?php foreach ($component->tabs as $key => $tab) : ?>
        <div id="tabs-<?= $key ?>-panel" class="tabs-panel">
            <?= $tab['content'] ?? '' ?>
        </div>
    <?php endforeach; ?>
</div>