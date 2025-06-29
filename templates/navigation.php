<?php

use RTBS\Plugin;

?>

<nav class="rtbs-navigation">
    <div class="rtbs-navigation__header">
        <span class="rtbs-navigation__header_title"><?= __('Build System', 'rt-build-system') ?></span>
        <span class="rtbs-navigation__header_version">v<?= RTBS_PLUGIN_VERSION ?></span>
    </div>
    <span class="rtbs-navigation__subtitle"><?= __('Documentation', 'rt-build-system') ?></span>
    <ul class="rtbs-navigation__list">
        <?php foreach ($docs as $doc) :
            $docSlug = get_post_field('post_name', $doc);
        ?>
            <li>
                <a href="<?= esc_url(get_permalink($doc)) ?>" class="<?= $slug === $docSlug ? 'active' : '' ?>">
                    <?= esc_html(get_the_title($doc)) ?>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
    <span class="rtbs-navigation__subtitle"><?= __('Components', 'rt-build-system') ?></span>
    <ul class="rtbs-navigation__list">
        <?php foreach ($components as $post) :
            $componentSlug = get_post_field('post_name', $post);
        ?>
            <li>
                <a href="<?= esc_url(get_permalink($post)) ?>" class="<?= $slug === $componentSlug ? 'active' : '' ?>">
                    <?= esc_html(get_the_title($post)) ?>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
</nav>