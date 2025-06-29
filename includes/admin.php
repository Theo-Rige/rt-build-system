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
        add_action('wp_insert_post', [static::class, 'mergeComponentDirectory'], 10, 3);
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
        add_meta_box('rtbs_component_references', __('References', 'rt-build-system'), [static::class, 'renderReferencesMetaBox'], Plugin::COMPONENT_POST_TYPE, 'normal', 'high');
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
     * Render the references meta box.
     *
     * @param \WP_Post $post The post object.
     */
    public static function renderReferencesMetaBox($post) {
        $references = get_post_meta($post->ID, 'rtbs_references', true) ?: [];
        $references[] = [
            'title' => '',
            'url' => ''
        ];

        Tool::loadTemplate('references', [
            'post' => $post,
            'references' => $references
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

        if (isset($_POST['rtbs_references'])) {
            $_POST['rtbs_references'] = array_filter($_POST['rtbs_references'], function ($reference) {
                return !empty($reference['title']) && !empty($reference['url']);
            });

            update_post_meta($postID, 'rtbs_references', $_POST['rtbs_references']);
        }
    }

    /**
     * Create/delete component directory when a component is created/trashed.
     *
     * @param int $postID The post ID.
     * @param \WP_Post $post The post object.
     * @param bool $update Whether this is an update or not.
     */
    public static function mergeComponentDirectory($postID, $post, $update) {
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;

        if ($post->post_status === 'auto-draft' || empty($post->post_title)) return;


        if ($post->post_type !== Plugin::COMPONENT_POST_TYPE) return;

        $componentSlug = $post->post_name ?: sanitize_title($post->post_title);
        $componentDir = RTBS_PLUGIN_DIR . 'components/' . $componentSlug;

        if ($post->post_status === 'trash') {
            $originalSlug = str_replace('__trashed', '', $componentSlug);
            $originalDir = RTBS_PLUGIN_DIR . 'components/' . $originalSlug;

            if (file_exists($originalDir)) {
                error_log("Deleting component directory: $originalDir");
                array_map('unlink', glob("$originalDir/*.*"));
                rmdir($originalDir);
            }

            return;
        }

        if ($update && file_exists($componentDir)) return;

        if (!file_exists($componentDir)) {
            wp_mkdir_p($componentDir);

            $templateFiles = glob(RTBS_PLUGIN_DIR . 'templates/component/*');
            foreach ($templateFiles as $file) {
                error_log("Copying file: $file to $componentDir");
                if (is_file($file)) {
                    $filename = basename($file);
                    copy($file, $componentDir . '/' . $filename);
                }
            }

            $classFile = $componentDir . '/class.php';
            if (file_exists($classFile)) {
                $classContent = file_get_contents($classFile);

                // Replace component name placeholder
                $classContent = preg_replace('/const NAME = \'{slug}\'/', "const NAME = '$componentSlug'", $classContent);

                // Replace title in comments
                $componentTitle = $post->post_title;
                $classContent = str_replace('{title}', $componentTitle, $classContent);

                // Replace class name from NewComponent to proper component name
                $className = str_replace(' ', '', ucwords(str_replace('-', ' ', $componentSlug)));
                $classContent = preg_replace('/class NewComponent extends Component/', "class $className extends Component", $classContent);

                file_put_contents($classFile, $classContent);
            }
        }
    }
}
