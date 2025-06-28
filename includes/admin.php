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
        add_action('admin_enqueue_scripts', [static::class, 'enqueueAssets']);
        add_action('add_meta_boxes_' . Plugin::COMPONENT_POST_TYPE, [static::class, 'addCustomFields']);
        add_action('save_post_' . Plugin::COMPONENT_POST_TYPE, [static::class, 'saveCustomFields']);
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

    /**
     * Enqueue admin scripts and styles.
     *
     * @return void
     */
    public static function enqueueAssets() {
        wp_enqueue_script('rtbs-admin-script', RTBS_PLUGIN_URL . 'assets/js/admin.min.js', [], RTBS_PLUGIN_VERSION, true);
        wp_enqueue_style('rtbs-admin-style', RTBS_PLUGIN_URL . 'assets/css/admin.min.css', [], RTBS_PLUGIN_VERSION);
    }

    /**
     * Add custom fields to component post type.
     *
     * @param \WP_Post $post The post object.
     */
    public static function addCustomFields($post) {
        add_meta_box('rtbs_component_libraries', __('Libraries', 'rt-build-system'), [static::class, 'renderLibrariesMetaBox'], Plugin::COMPONENT_POST_TYPE, 'normal', 'high');
    }

    /**
     * Render the libraries meta box.
     *
     * @param \WP_Post $post The post object.
     */
    public static function renderLibrariesMetaBox($post) {
        $libraries = get_post_meta($post->ID, 'rtbs_libraries', true) ?: [];
        $libraries[] = [
            'name' => '',
            'repository' => ''
        ];

        Tool::loadTemplate('libraries', [
            'post' => $post,
            'libraries' => $libraries
        ], true);
    }

    /**
     * Save custom fields for component post type.
     *
     * @param int $postID The post ID.
     */
    public static function saveCustomFields($postID) {
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;

        if (!current_user_can('edit_post', $postID)) return;

        if (isset($_POST['rtbs_libraries'])) {
            $_POST['rtbs_libraries'] = array_filter($_POST['rtbs_libraries'], function ($library) {
                return !empty($library['name']) && !empty($library['repository']);
            });

            update_post_meta($postID, 'rtbs_libraries', $_POST['rtbs_libraries']);
        }
    }
}
