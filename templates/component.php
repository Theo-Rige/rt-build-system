<?php

namespace RTBS;

$slug = get_post_field('post_name');

require_once RTBS_PLUGIN_DIR . 'components/' . $slug . '/class.php';

$component = Component::getComponentClass($slug);
$component::init();

$docs = Plugin::getDocs();
$components = Component::getAll();

$content = get_the_content();
$figma = get_post_meta(get_the_ID(), 'rtbs-figma', true);
$preview = $component::loadTemplate('template', $dummydata);
$libraries = $component::getLibraries();
$references = $component::getReferences();

wp_head();
?>

<body <?php body_class(); ?>>
    <?= Tool::loadTemplate('navigation', compact('docs', 'components', 'slug')) ?>
    <main>
        <div class="lines">
            <div class="line"></div>
            <div class="line"></div>
            <div class="line"></div>
        </div>
        <article>
            <header>
                <h1><?= get_the_title() ?></h1>
            </header>
            <?php if (!empty($content)) : ?>
                <section id="description" class="rtbs-content">
                    <?= $content ?>
                </section>
            <?php endif; ?>
            <section id="actions">
                <a href="<?= admin_url('admin-ajax.php') . '?action=rtbs_download_zip&slug=' . $slug ?>" class="rtbs-button rtbs-button--download" download>
                    <?= Tool::loadSVG('folder-download') ?>
                    <?= __('Download ZIP folder', 'rt-build-system') ?>
                </a>
                <?php if ($figma) : ?>
                    <a target="_blank" class="rtbs-button rtbs-button--secondary rtbs-button--copy-design">
                        <?= Tool::loadSVG('figma') ?>
                        <?= __('Get design', 'rt-build-system') ?>
                    </a>
                <?php endif; ?>
                <button type="button" class="rtbs-button rtbs-button--secondary rtbs-button--copy-share">
                    <?= Tool::loadSVG('up-right-arrow') ?>
                    <?= __('Share', 'rt-build-system') ?>
                </button>
            </section>
            <section id="preview">
                <?php if ($preview) : ?>
                    <button type="button" class="rtbs-button rtbs-button--secondary rtbs-button--icon rtbs-button--expand" aria-label="<?= __('Expand preview', 'rt-build-system') ?>">
                        <?= Tool::loadSVG('expand') ?>
                        <?= Tool::loadSVG('collapse') ?>
                    </button>
                    <?= $preview ?>
                <?php else : ?>
                    <p><?= __('No preview available for this component.', 'rt-build-system') ?></p>
                <?php endif; ?>
            </section>
            <section id="libraries">
                <h2><?= __('Libraries', 'rt-build-system') ?></h2>
                <div class="grid">
                    <?php if (empty($libraries)) : ?>
                        <p class="grid__cell grid__cell--empty"><?= __('This component does not use any libraries.', 'rt-build-system') ?></p>
                    <?php else: ?>
                        <span class="grid__cell grid__cell--header"><?= __('Name', 'rt-build-system') ?></span>
                        <span class="grid__cell grid__cell--header"><?= __('Last update', 'rt-build-system') ?></span>
                        <span class="grid__cell grid__cell--header"><?= __('Status', 'rt-build-system') ?></span>
                        <span class="grid__cell grid__cell--header"><?= __('Repository', 'rt-build-system') ?></span>
                        <?php foreach ($libraries as $library) : ?>
                            <div class="grid__cell"><?= esc_html($library['name']) ?></div>
                            <div class="grid__cell"><?= esc_html($library['date']) ?></div>
                            <div class="grid__cell"><?= $component::getLibraryStatus($library['date']) ?></div>
                            <a class="grid__cell" href="<?= esc_url($library['repository']) ?>" target="_blank">GitHub</a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </section>
            <section>
                <h2><?= __('Fichiers', 'rt-build-system') ?></h2>
                <div id="code" class="tabs">
                    <div class="tabs-list">
                        <?php foreach ($component::CODES as $key => $code) : ?>
                            <button type="button" id="code-tabs-<?= $key ?>-trigger" class="tabs-trigger"><?= $code['label'] ?></button>
                        <?php endforeach; ?>
                        <div class="tabs-indicator"></div>
                    </div>
                    <?php foreach ($component::CODES as $key => $code) : ?>
                        <div id="code-tabs-<?= $key ?>-panel" class="tabs-panel">
                            <?php $filePath = plugin_dir_path(dirname(__FILE__)) . 'components/' . $slug . '/' . $code['file'];
                            if (file_exists($filePath)) :
                                $fileContent = file_get_contents($filePath);
                                if (empty($fileContent)) : ?>
                                    <p>
                                        <?php
                                        /* translators: %s: Name of the language */
                                        printf(__('%s file is empty.', 'rt-build-system'), esc_html($code['label'])); ?>
                                    </p>
                                <?php else : ?>
                                    <button type="button" class="rtbs-button rtbs-button--secondary rtbs-button--icon rtbs-button--copy" aria-label="<?= __('Copy code', 'rt-build-system') ?>">
                                        <?= Tool::loadSVG('copy') ?>
                                        <?= Tool::loadSVG('check') ?>
                                    </button>
                                    <div class="code" data-lang="<?= $code['lang'] ?>">
                                        <pre><code><?= trim(htmlspecialchars($fileContent)) ?></code></pre>
                                    </div>
                                <?php endif; ?>
                            <?php else : ?>
                                <p>
                                    <?php
                                    /* translators: %s: Name of the language */
                                    printf(__('%s file not found.', 'rt-build-system'), esc_html($code['label'])); ?>
                                </p>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
            <section id="references">
                <h2><?= __('References', 'rt-build-system') ?></h2>
                <div class="grid">
                    <?php if (empty($references)) : ?>
                        <p class="grid__cell grid__cell--empty"><?= __('This component does not have any references.', 'rt-build-system') ?></p>
                    <?php else: ?>
                        <span class="grid__cell grid__cell--header"><?= __('Name', 'rt-build-system') ?></span>
                        <span class="grid__cell grid__cell--header"><?= __('Date', 'rt-build-system') ?></span>
                        <span class="grid__cell grid__cell--header"><?= __('Link', 'rt-build-system') ?></span>
                        <?php foreach ($references as $reference) : ?>
                            <div class="grid__cell"><?= esc_html($reference['name']) ?></div>
                            <div class="grid__cell"><?= esc_html($reference['date']) ?></div>
                            <a class="grid__cell" href="<?= esc_url($reference['url']) ?>" target="_blank"><?= esc_html($reference['title']) ?></a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </section>
        </article>
    </main>
    <?php wp_footer(); ?>
</body>