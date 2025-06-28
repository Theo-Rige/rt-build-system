<?php foreach ($references as $index => $reference) : ?>
    <div class="rtbs-postbox-item rtbs-postbox-item--reference">
        <label for="rtbs_references[<?= $index ?>][title]">
            <?php _e('Title', 'rt-build-system'); ?>
            <input type="text" name="rtbs_references[<?= $index ?>][title]" id="rtbs_references[<?= $index ?>][title]" value="<?= esc_attr($reference['title'] ?? '') ?>" minlength="2" />
        </label>
        <label for="rtbs_references[<?= $index ?>][url]">
            <?php _e('URL', 'rt-build-system'); ?>
            <input type="url" name="rtbs_references[<?= $index ?>][url]" id="rtbs_references[<?= $index ?>][url]" value="<?= esc_attr($reference['url'] ?? '') ?>" minlength="2" />
        </label>
    </div>
<?php endforeach; ?>