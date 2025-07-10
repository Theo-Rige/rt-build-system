<?php

use RTBS\Tool;
?>

<span class="rtbs-status rtbs-status--risky">
    <?= Tool::loadSVG('exclamation'); ?>
    <?= __('Risk of not being maintained', 'rt-build-system') ?>
</span>