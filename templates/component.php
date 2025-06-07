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
                        <button type="button" id="code-tabs-php-trigger" class="tabs-trigger">PHP</button>
                        <button type="button" id="code-tabs-js-trigger" class="tabs-trigger">JavaScript</button>
                        <div class="tabs-indicator"></div>
                    </div>
                    <div id="code-tabs-php-panel" class="tabs-panel">
                        <pre><code><?php
                                    $templatePath = plugin_dir_path(dirname(__FILE__)) . 'components/' . $slug . '/class.php';
                                    if (file_exists($templatePath)) {
                                        echo htmlspecialchars(file_get_contents($templatePath));
                                    } else {
                                        echo 'Template file not found.';
                                    } ?>
                        </code></pre>
                    </div>
                    <div id="code-tabs-js-panel" class="tabs-panel">
                        <pre><code><?php
                                    $scriptPath = plugin_dir_path(dirname(__FILE__)) . 'components/' . $slug . '/script.js';
                                    if (file_exists($scriptPath)) {
                                        echo trim(htmlspecialchars(file_get_contents($scriptPath)));
                                    } else {
                                        echo 'Script file not found.';
                                    }
                                    ?>
                        </code></pre>
                    </div>
                </div>
            </section>

        </article>
    </main>

    <?php wp_footer(); ?>
</body>