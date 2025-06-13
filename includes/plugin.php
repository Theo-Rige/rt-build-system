<?php

namespace RTBS;

require_once RTBS_PLUGIN_DIR . 'includes/admin.php';
require_once RTBS_PLUGIN_DIR . 'includes/tool.php';

/**
 * Dynamically require all component class files to register AJAX actions.
 */
foreach (glob(RTBS_PLUGIN_DIR . 'components/*/class.php') as $componentClassFile) {
    require_once $componentClassFile;
}

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
        load_plugin_textdomain(RTBS_PLUGIN_DOMAIN, false, dirname(plugin_basename(__FILE__)) . '/languages');
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
                'not_found_in_trash' => __('No components found in Trash', 'rt-build-system'),
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
     * @return void
     */
    public static function createExistingComponentsPosts() {
        $components = glob(RTBS_PLUGIN_DIR . 'components/*', GLOB_ONLYDIR);
        foreach ($components as $componentDir) {
            $componentName = basename($componentDir);
            $existingPost = get_page_by_path($componentName, OBJECT, self::COMPONENT_POST_TYPE);

            if (!$existingPost) {
                $postData = [
                    'post_title' => ucfirst($componentName),
                    'post_content' => '',
                    'post_status' => 'publish',
                    'post_type' => self::COMPONENT_POST_TYPE,
                    'post_name' => $componentName,
                ];
                wp_insert_post($postData);
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
