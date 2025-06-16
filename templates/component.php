<?php

namespace RTBS;

$slug = get_post_field('post_name');

require_once RTBS_PLUGIN_DIR . 'components/' . $slug . '/class.php';

$component = Component::getComponentClass($slug);
$component::init();

wp_head();
?>

<body <?php body_class(); ?>>
    <main>
        <article>
            <h1><?= get_the_title() ?></h1>
            <p><?= get_the_content() ?></p>
            <div class="actions">
                <a href="<?= admin_url('admin-ajax.php') . '?action=rtbs_download_zip&slug=' . $slug ?>" class="rtbs-button rtbs-button--secondary rtbs-button--download" download><?= __('Download ZIP folder', 'rt-build-system') ?></a>
                <button type="button" class="rtbs-button rtbs-button--secondary rtbs-button--copy-design">
                    <?= Tool::loadSVG('figma') ?>
                    <?= __('Get design', 'rt-build-system') ?>
                </button>
            </div>
            <section id="preview" class="preview">
                <button type="button" class="preview__expand" aria-label="<?= __('Expand preview', 'rt-build-system') ?>">
                    <?= Tool::loadSVG('expand') ?>
                    <?= Tool::loadSVG('collapse') ?>
                </button>
                <?= $component::loadTemplate() ?>
            </section>
            <section>
                <h2>Code</h2>
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
                                    <p><?= $code['label'] ?> file not found.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>

        </article>
    </main>

    <?php wp_footer(); ?>
</body>