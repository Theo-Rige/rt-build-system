<?php

namespace RTBS;

require_once RTBS_PLUGIN_DIR . 'includes/tool.php';

class Admin {

    /**
     * Initializes the admin of the plugin.
     *
     * @return void
     */
    public static function init() {
        add_action('admin_menu', [self::class, 'addAdminMenu']);
    }

    /**
     * Adds the admin menu for the plugin.
     *
     * @return void
     */
    public static function addAdminMenu() {
        add_menu_page(
            __('Components', 'rt-build-system'),
            __('Components', 'rt-build-system'),
            'read',
            RTBS_PLUGIN_DOMAIN,
            '',
            'data:image/svg+xml;base64,' . base64_encode(Tool::loadSVG('component')),
            20
        );
    }
}
