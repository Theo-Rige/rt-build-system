<?php

use RTBS\Plugin;
use RTBS\Component;
use RTBS\Tool;

$slug = get_post_field('post_name');
$docs = Plugin::getDocs();
$components = Component::getAll();
$content = get_the_content();

wp_head();
?>

<body <?php body_class(); ?>>
    <?= Tool::loadTemplate('navigation', compact('docs', 'components', 'slug')) ?>
    <main>
        <article>
            <header>
                <h1><?= get_the_title() ?></h1>
            </header>
            <?php if (!empty($content)) : ?>
                <section id="content" class="rtbs-content">
                    <?= $content ?>
                </section>
            <?php endif; ?>
        </article>
    </main>
    <?php wp_footer(); ?>
</body>