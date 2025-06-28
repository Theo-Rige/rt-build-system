<?php

use RTBS\Tool;
?>

<?php foreach ($libraries as $index => $library) : ?>
    <div class="rtbs-postbox-item rtbs-postbox-item--library">
        <label for="rtbs_libraries[<?= $index ?>][name]">
            <?php _e('Name', 'rt-build-system'); ?>
            <input type="text" name="rtbs_libraries[<?= $index ?>][name]" id="rtbs_libraries[<?= $index ?>][name]" value="<?= esc_attr($library['name'] ?? '') ?>" minlength="2" />
        </label>
        <label for="rtbs_libraries[<?= $index ?>][repository]">
            <?php _e('Repository', 'rt-build-system'); ?>
            <input type="url" name="rtbs_libraries[<?= $index ?>][repository]" id="rtbs_libraries[<?= $index ?>][repository]" value="<?= esc_attr($library['repository'] ?? '') ?>" minlength="2" />
        </label>
        <label class="rtbs-library-date" for="rtbs_libraries[<?= $index ?>][date]">
            <?php _e('Date of last release', 'rt-build-system'); ?>
            <?= Tool::loadSVG('checkmark'); ?>
            <?= Tool::loadSVG('exclamation'); ?>
            <?= Tool::loadSVG('cross'); ?>
            <input type="text" name="rtbs_libraries[<?= $index ?>][date]" id="rtbs_libraries[<?= $index ?>][date]" value="<?= esc_attr($library['date'] ?? '') ?>" placeholder="<?= __('No data', 'rt-build-system'); ?>" readonly />
        </label>
    </div>
<?php endforeach; ?>