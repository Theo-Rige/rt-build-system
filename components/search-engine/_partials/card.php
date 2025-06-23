<a href="<?= get_permalink($post->ID); ?>" class="post-card">
    <?= get_the_post_thumbnail($post->ID, 'medium', ['class' => 'post-card__thumbnail']); ?>
    <span class="post-card__title"><?= esc_html($post->post_title); ?></span>
</a>