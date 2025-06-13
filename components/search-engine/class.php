<?php

/**
 * Search engine component class file.
 *
 * @package RT_Build_System
 */

namespace RTBS;

if (! defined('ABSPATH')) exit;

require_once RTBS_PLUGIN_DIR . 'includes/plugin.php';
require_once RTBS_PLUGIN_DIR . 'includes/component.php';

/**
 * SearchEngine class to handle search engine component loading and rendering.
 */
class SearchEngine extends Component {
    /**
     * Component name.
     */
    const NAME = 'search-engine';

    /**
     * Post type to search.
     */
    const POST_TYPE = Plugin::COMPONENT_POST_TYPE;

    /**
     * Register AJAX actions for the component.
     */
    public static function registerAjaxActions() {
        add_action('wp_ajax_rtbs_get_posts', [self::class, 'ajaxGetPosts']);
        add_action('wp_ajax_nopriv_rtbs_get_posts', [self::class, 'ajaxGetPosts']);
    }

    /**
     * Get posts.
     * 
     * @return array Array of posts.
     */
    public static function getPosts() {
        $posts = get_posts([
            'post_type' => self::POST_TYPE,
            'numberposts' => -1
        ]);

        return $posts;
    }

    /**
     * Load template with component-specific data.
     *
     * @param string $name Template name.
     * @param array $data Template data.
     * @return string|false The template content or false if template not found.
     */
    public static function loadTemplate($name = 'template', $data = []) {
        $data['categories'] = get_object_taxonomies(self::POST_TYPE);
        $data['posts'] = self::getPosts();

        return parent::loadTemplate($name, $data);
    }

    /**
     * AJAX handler to get posts based on search criteria.
     */
    public static function ajaxGetPosts() {
        check_ajax_referer('rtbs_nonce');

        $keyword = sanitize_text_field($_POST['keyword'] ?? '');

        $args = [
            'post_type' => self::POST_TYPE,
            's' => $keyword,
            'tax_query' => []
        ];

        $categories = get_object_taxonomies(self::POST_TYPE);
        $categories = array_filter($categories, function ($category) {
            return isset($_POST[$category]) && is_array($_POST[$category]) && !empty($_POST[$category]);
        });

        if (!empty($categories)) {
            $args['tax_query'] = [
                'relation' => 'OR',
                array_map(function ($category) {
                    return [
                        'taxonomy' => $category,
                        'field' => 'slug',
                        'terms' => $_POST[$category]
                    ];
                }, $categories)
            ];
        }

        $posts = get_posts($args);

        if (is_wp_error($posts)) {
            wp_send_json_error($posts->get_error_message());
        }

        $html = '';

        if (empty($posts)) {
            $html = '<div class="rtbs-no-results">No components found matching your criteria.</div>';
        } else {
            ob_start();
            foreach ($posts as $post) {
                echo self::loadTemplate('_partials/card', ['post' => $post]);
            }
            $html = ob_get_clean();
        }

        wp_send_json_success([
            'html' => $html,
            'count' => count($posts)
        ]);
    }
}

SearchEngine::registerAjaxActions();
