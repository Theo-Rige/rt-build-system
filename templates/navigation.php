<?php

use RTBS\Plugin;

?>

<nav>
    <span><?= __('Components', 'rt-build-system') ?></span>
    <ul>
        <?php foreach ($components as $post) :
            $componentSlug = get_post_field('post_name', $post);
        ?>
            <li>
                <a href="<?= esc_url(get_permalink($post)) ?>" class="<?= $slug === $componentSlug ? 'active' : '' ?>">
                    <?= esc_html(get_the_title($post)) ?>
                </a>
            </li>
        <?php endforeach; ?>
        <li>
            <a href="<?= esc_url(admin_url('post-new.php?post_type=' . Plugin::COMPONENT_POST_TYPE)) ?>" class="rtbs-button rtbs-button--secondary">
                <?= __('Add new component', 'rt-build-system') ?>
            </a>
        </li>
    </ul>
</nav>