<?php

$slug = get_post_field('post_name');

require_once RTBS_PLUGIN_DIR . 'components/' . $slug . '/class.php';

$className = 'RTBS\\' . str_replace('-', '', ucwords($slug, '-'));
$component = new $className();

wp_head();
?>

<body <?php body_class(); ?>>
    <main>
        <article>
            <h1><?= get_the_title() ?></h1>
            <p><?= get_the_content() ?></p>
            <div id="preview">
                <?= $component->loadTemplate() ?>
            </div>
            <section>
                <h2>Code</h2>
                <div id="code" class="tabs">
                    <div class="tabs-list">
                        <?php foreach ($component->codes as $key => $code) : ?>
                            <button type="button" id="code-tabs-<?= $key ?>-trigger" class="tabs-trigger"><?= $code['label'] ?></button>
                        <?php endforeach; ?>
                        <div class="tabs-indicator"></div>
                    </div>
                    <?php foreach ($component->codes as $key => $code) : ?>
                        <div id="code-tabs-<?= $key ?>-panel" class="tabs-panel">
                            <?php $scriptPath = plugin_dir_path(dirname(__FILE__)) . 'components/' . $slug . '/' . $code['file'];
                            if (file_exists($scriptPath)) : ?>
                                <pre><code><?= trim(htmlspecialchars(file_get_contents($scriptPath))) ?></code></pre>
                            <?php else : ?>
                                <p><?= $code['label'] ?> file not found.</p>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>

        </article>
    </main>

    <?php wp_footer(); ?>
</body>