<div id="search-engine-component">
    <form id="search-engine-filters">
        <input type="text" id="keyword" name="keyword" placeholder="Search..." />
        <?php foreach ($categories as $category) :
            $options = get_terms(['taxonomy' => $category]);

            if (is_wp_error($options) || empty($options)) continue;
        ?>
            <select name="<?= esc_attr($category) ?>" id="<?= esc_attr($category) ?>">
                <option value=""><?= sprintf(esc_html__('Select a %s', 'rt-build-system'), str_replace(['_', '-'], ' ', $category)); ?></option>
                <?php foreach ($options as $option) : ?>
                    <option value="<?= esc_attr($option->slug); ?>"><?= esc_html($option->name); ?></option>
                <?php endforeach; ?>
            </select>
        <?php endforeach; ?>
        <button type="submit"><?= __('Search', 'rt-build-system'); ?></button>
        <button type="reset"><?= __('Reset', 'rt-build-system'); ?></button>
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