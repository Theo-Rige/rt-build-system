<?php

use RTBS\Tool;
?>

<div id="search-engine-component">
    <form id="search-engine-filters">
        <label for="keyword" class="rtbs-label">
            <?= __('Keywords', 'rt-build-system'); ?>
            <input type="text" id="keyword" name="keyword" placeholder="Search..." class="rtbs-input" />
        </label>
        <?php foreach ($categories as $category) :
            $options = get_terms(['taxonomy' => $category]);

            if (is_wp_error($options) || empty($options)) continue;
        ?>
            <label for="<?= esc_attr($category) ?>" class="rtbs-label">
                <?= esc_html(ucfirst(str_replace(['_', '-'], ' ', $category))); ?>
                <select name="<?= esc_attr($category) ?>" id="<?= esc_attr($category) ?>" class="rtbs-select">
                    <button type="button">
                        <selectedcontent></selectedcontent>
                        <?= Tool::loadSVG('chevron-down') ?>
                    </button>
                    <option value=""><?= sprintf(esc_html__('Select a %s', 'rt-build-system'), str_replace(['_', '-'], ' ', $category)); ?></option>
                    <?php foreach ($options as $option) : ?>
                        <option value="<?= esc_attr($option->slug); ?>"><?= esc_html($option->name); ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
        <?php endforeach; ?>
        <button type="submit" class="rtbs-button rtbs-button--secondary"><?= __('Search', 'rt-build-system'); ?></button>
        <button type="reset" class="rtbs-button rtbs-button--secondary"><?= __('Reset', 'rt-build-system'); ?></button>
    </form>
    <div id="search-engine-results">
        <?php if (!empty($posts)) : ?>
            <?php foreach ($posts as $post) : ?>
                <?= $component::loadTemplate('_partials/card', ['post' => $post]); ?>
            <?php endforeach; ?>
        <?php else : ?>
            <p><?= __('No results found.', 'rt-build-system'); ?></p>
        <?php endif; ?>
    </div>
</div>