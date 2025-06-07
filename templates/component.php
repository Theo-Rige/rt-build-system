<?php

$slug = get_post_field('post_name');

require_once RTBS_PLUGIN_DIR . 'components/' . $slug . '/class.php';

$className = 'RTBS\\' . str_replace('-', '', ucwords($slug, '-'));
$component = new $className();

wp_head();
?>
<main>
    <article>
        <h1><?= get_the_title() ?></h1>
        <section>
            <h2>Preview</h2>
            <?= $component->loadTemplate() ?>
        </section>
        <section>
            <h2>PHP Code</h2>
            <div id="component-php">
                <pre>
                <code>
                    <?php
                    $templatePath = plugin_dir_path(dirname(__FILE__)) . 'components/' . $slug . '/class.php';
                    if (file_exists($templatePath)) {
                        echo htmlspecialchars(file_get_contents($templatePath));
                    } else {
                        echo 'Template file not found.';
                    }
                    ?>
                </code>
            </pre>
            </div>
        </section>
        <section>
            <h2>JavaScript Code</h2>
            <div id="component-js">
                <pre>
                <code>
                    <?php
                    $scriptPath = plugin_dir_path(dirname(__FILE__)) . 'components/' . $slug . '/script.js';
                    if (file_exists($scriptPath)) {
                        echo trim(htmlspecialchars(file_get_contents($scriptPath)));
                    } else {
                        echo 'Script file not found.';
                    }
                    ?>
                </code>
                </pre>
            </div>
        </section>
    </article>
</main>

<?php wp_footer(); ?>