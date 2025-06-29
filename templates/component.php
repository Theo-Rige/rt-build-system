<?php

namespace RTBS;

$slug = get_post_field('post_name');

require_once RTBS_PLUGIN_DIR . 'components/' . $slug . '/class.php';

$component = Component::getComponentClass($slug);
$component::init();

$components = Component::getAll();
$content = get_the_content();
$libraries = $component::getLibraries();
$references = $component::getReferences();

wp_head();
?>

<body <?php body_class(); ?>>
    <?= Tool::loadTemplate('navigation', compact('components', 'slug')) ?>
    <main>
        <article>
            <h1><?= get_the_title() ?></h1>
            <?php if (!empty($content)) : ?>
                <section id="description">
                    <?= $content ?>
                </section>
            <?php endif; ?>
            <section id="actions" class="actions">
                <a href="<?= admin_url('admin-ajax.php') . '?action=rtbs_download_zip&slug=' . $slug ?>" class="rtbs-button rtbs-button--secondary rtbs-button--download" download><?= __('Download ZIP folder', 'rt-build-system') ?></a>
                <button type="button" class="rtbs-button rtbs-button--secondary rtbs-button--copy-design">
                    <?= Tool::loadSVG('figma') ?>
                    <?= __('Get design', 'rt-build-system') ?>
                </button>
            </section>
            <section id="preview" class="preview">
                <button type="button" class="rtbs-button rtbs-button--secondary rtbs-button--icon preview__expand" aria-label="<?= __('Expand preview', 'rt-build-system') ?>">
                    <?= Tool::loadSVG('expand') ?>
                    <?= Tool::loadSVG('collapse') ?>
                </button>
                <?= $component::loadTemplate() ?>
            </section>
            <section id="libraries">
                <h2><?= __('Libraries', 'rt-build-system') ?></h2>
                <div class="libraries-list">
                    <?php if (empty($libraries)) : ?>
                        <p><?= __('This component does not use any libraries.', 'rt-build-system') ?></p>
                    <?php else: ?>
                        <?php foreach ($libraries as $library) : ?>
                            <div class="library">
                                <h3><?= esc_html($library['name']) ?></h3>
                                <div class="library__date">
                                    <?php
                                    $label = '';
                                    $date = strtotime($library['date']);
                                    $currentDate = time();
                                    $sixMonthsAgo = strtotime('-6 months', $currentDate);
                                    $oneYearAgo = strtotime('-1 year', $currentDate);
                                    if ($date < $oneYearAgo) {
                                        $label = 'Not maintained';
                                        echo Tool::loadSVG('cross');
                                    } elseif ($date < $sixMonthsAgo) {
                                        $label = 'Risk of not being maintained';
                                        echo Tool::loadSVG('exclamation');
                                    } else {
                                        $label = 'Maintained';
                                        echo Tool::loadSVG('checkmark');
                                    }
                                    ?>
                                    <span><?= esc_html($library['date']) ?></span>
                                    <span class="library__status"><?= esc_html($label) ?></span>
                                </div>
                                <a href="<?= esc_url($library['repository']) ?>" target="_blank"><?= __('View on GitHub', 'rt-build-system') ?></a>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </section>
            <section>
                <h2><?= __('Code', 'rt-build-system') ?></h2>
                <div id="code" class="tabs">
                    <div class="tabs-list">
                        <?php foreach ($component::CODES as $key => $code) : ?>
                            <button type="button" id="code-tabs-<?= $key ?>-trigger" class="tabs-trigger"><?= $code['label'] ?></button>
                        <?php endforeach; ?>
                        <div class="tabs-indicator"></div>
                    </div>
                    <?php foreach ($component::CODES as $key => $code) : ?>
                        <div id="code-tabs-<?= $key ?>-panel" class="tabs-panel">
                            <button type="button" class="copy" aria-label="<?= __('Copy code', 'rt-build-system') ?>">
                                <?= Tool::loadSVG('copy') ?>
                                <?= Tool::loadSVG('check') ?>
                            </button>
                            <div class="code" data-lang="<?= $code['lang'] ?>">
                                <?php $scriptPath = plugin_dir_path(dirname(__FILE__)) . 'components/' . $slug . '/' . $code['file'];
                                if (file_exists($scriptPath)) : ?>
                                    <pre><code><?= trim(htmlspecialchars(file_get_contents($scriptPath))) ?></code></pre>
                                <?php else : ?>
                                    <p>
                                        <?php
                                        /* translators: %s: Name of the language */
                                        printf(__('%s file not found.', 'rt-build-system'), esc_html($code['label'])); ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
            <section id="references">
                <h2><?= __('References', 'rt-build-system') ?></h2>
                <div class="references-list">
                    <?php if (empty($references)) : ?>
                        <p><?= __('This component does not have any references.', 'rt-build-system') ?></p>
                    <?php else: ?>
                        <?php foreach ($references as $reference) : ?>
                            <div class="reference">
                                <h3><a href="<?= esc_url($reference['url']) ?>" target="_blank"><?= esc_html($reference['title']) ?></a></h3>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </section>
        </article>
    </main>
    <?php wp_footer(); ?>
</body>