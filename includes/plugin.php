<?php

namespace RTBS;

require_once RTBS_PLUGIN_DIR . 'includes/admin.php';
require_once RTBS_PLUGIN_DIR . 'includes/tool.php';



class Plugin {
    const COMPONENTS_PAGE = 'components';
    const COMPONENT_POST_TYPE = 'component';
    const ORGANISM_POST_TYPE = 'organism';
    const MOLECULE_POST_TYPE = 'molecule';

    /**
     * Initializes the plugin.
     *
     * @return void
     */
    public static function init() {
        self::loadTextDomain();
        add_action('init', [self::class, 'registerCustomObjects']);
        add_filter('template_include', [self::class, 'renderComponentsPage']);
        self::registerComponentAjaxActions();

        if (is_admin()) Admin::init();
    }

    /**
     * Loads the text domain for the plugin.
     *
     * This method is responsible for loading the translation files for the plugin.
     * It uses the `load_plugin_textdomain()` function to load the translation files
     * from the 'languages' directory of the plugin.
     *
     * @return void
     */
    private static function loadTextDomain() {
        load_plugin_textdomain('rt-build-system', false, dirname(RTBS_PLUGIN_BASENAME) . '/languages');
    }

    /**
     * Registers the custom post types for the plugin.
     *
     * @return void
     */
    public static function registerCustomObjects() {
        register_taxonomy('tag', self::COMPONENT_POST_TYPE, [
            'labels'            => [
                'name'              => __('Tags', 'rt-build-system'),
                'singular_name'     => __('Tag', 'rt-build-system'),
                'search_items'      => __('Search tags', 'rt-build-system'),
                'all_items'         => __('All tags', 'rt-build-system'),
                'edit_item'         => __('Edit tag', 'rt-build-system'),
                'update_item'       => __('Update tag', 'rt-build-system'),
                'add_new_item'      => __('Add new tag', 'rt-build-system'),
                'new_item_name'     => __('New tag name', 'rt-build-system'),
                'menu_name'         => __('Tags', 'rt-build-system'),
            ],
            'hierarchical'      => false,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
        ]);

        register_post_type(self::COMPONENT_POST_TYPE, [
            'labels' => [
                'name' => __('Components', 'rt-build-system'),
                'singular_name' => __('Component', 'rt-build-system'),
                'add_new' => __('Add new', 'rt-build-system'),
                'add_new_item' => __('Add new component', 'rt-build-system'),
                'edit_item' => __('Edit component', 'rt-build-system'),
                'new_item' => __('New component', 'rt-build-system'),
                'view_item' => __('View component', 'rt-build-system'),
                'view_items' => __('View components', 'rt-build-system'),
                'search_items' => __('Search components', 'rt-build-system'),
                'not_found' => __('No components found', 'rt-build-system'),
                'not_found_in_trash' => __('No components found in trash', 'rt-build-system'),
            ],
            'public' => true,
            'has_archive' => 'components',
            'show_in_menu' => RTBS_PLUGIN_DOMAIN,
            'supports' => ['title', 'editor', 'thumbnail'],
        ]);
    }

    /**
     * Creates existing component posts for components already defined in the components directory.
     * 
     * We couldn't use current component detection because AJAX URI requests don't have any information about the current component.
     * 
     * @return void
     */
    public static function createExistingComponentsPosts() {
        $components = glob(RTBS_PLUGIN_DIR . 'components/*', GLOB_ONLYDIR);

        foreach ($components as $componentDir) {
            $componentName = basename($componentDir);
            $existingPost = get_page_by_path($componentName, OBJECT, self::COMPONENT_POST_TYPE);
            $title = ucfirst($componentName);
            $description = sprintf(__('This is the %s component.', 'rt-build-system'), ucfirst($componentName));
            $thumbnail = null;

            if (!$existingPost) {
                $xmlFile = $componentDir . '/component.xml';

                if (file_exists($xmlFile)) {
                    $xml = simplexml_load_file($xmlFile);

                    if ($xml) {
                        if (isset($xml->title)) $title = (string)$xml->title;
                        if (isset($xml->description)) $description = (string)$xml->description;
                    } else {
                        error_log("Failed to load XML file: $xmlFile");
                    }
                }

                $postData = [
                    'post_title' => $title,
                    'post_content' => $description,
                    'post_status' => 'publish',
                    'post_type' => self::COMPONENT_POST_TYPE,
                    'post_name' => $componentName,
                ];
                $postID = wp_insert_post($postData);

                foreach (['jpg', 'jpeg', 'png', 'gif', 'webp'] as $ext) {
                    $candidate = $componentDir . "/thumbnail.$ext";
                    if (file_exists($candidate)) {
                        $thumbnail = $candidate;
                        break;
                    }
                }

                if ($thumbnail && $postID) {
                    $uploadDir = wp_upload_dir();
                    $filename = basename($thumbnail);
                    $targetPath = trailingslashit($uploadDir['path']) . $filename;

                    if (!file_exists($targetPath)) copy($thumbnail, $targetPath);

                    $filetype = wp_check_filetype($filename, null);
                    $attachment = [
                        'post_mime_type' => $filetype['type'],
                        'post_title'     => sanitize_file_name($filename),
                        'post_content'   => '',
                        'post_status'    => 'inherit'
                    ];
                    $attachID = wp_insert_attachment($attachment, $targetPath, $postID);

                    require_once(ABSPATH . 'wp-admin/includes/image.php');
                    $attachData = wp_generate_attachment_metadata($attachID, $targetPath);
                    wp_update_attachment_metadata($attachID, $attachData);

                    set_post_thumbnail($postID, $attachID);
                }
            }
        }
    }

    /**
     * Renders the components page for the plugin.
     *
     * @return string
     */
    public static function renderComponentsPage($template) {
        if (is_singular(self::COMPONENT_POST_TYPE)) {
            return RTBS_PLUGIN_DIR . 'templates/component.php';
        } else if (is_post_type_archive(self::COMPONENT_POST_TYPE)) {
            return RTBS_PLUGIN_DIR . 'templates/components.php';
        }

        return $template;
    }

    /**
     * Registers AJAX actions for all component classes.
     *
     * @return void
     */
    public static function registerComponentAjaxActions() {
        foreach (glob(RTBS_PLUGIN_DIR . 'components/*/class.php') as $componentClassFile) {
            require_once $componentClassFile;

            $componentSlug = basename(dirname($componentClassFile));
            $componentClass = Component::getComponentClass($componentSlug);

            if (class_exists($componentClass) && method_exists($componentClass, 'registerAjaxActions')) {
                $componentClass::registerAjaxActions();
            }
        }
    }

    /**
     * Create the necessary database table at plugin activation, and flush links after custom post type creation.
     *
     * @return void
     *
     * @see https://developer.wordpress.org/reference/functions/register_post_type/#flushing-rewrite-on-activation
     */
    public static function activate() {
        self::registerCustomObjects();
        self::createExistingComponentsPosts();
        flush_rewrite_rules();
    }

    /**
     * Drop the database table at plugin uninstallation.
     *
     * @return void
     */
    public static function uninstall() {
    }
}
