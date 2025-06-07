<?php

/**
 * Search engine component class file.
 *
 * @package RT_Build_System
 */

namespace RTBS;

if (! defined('ABSPATH')) exit;

use RTBS\Plugin;
use RTBS\Component;

require_once RTBS_PLUGIN_DIR . 'includes/plugin.php';
require_once RTBS_PLUGIN_DIR . 'includes/component.php';

/**
 * SearchEngine class to handle search engine component loading and rendering.
 */
class SearchEngine extends Component {
    /**
     * Constructor.
     *
     * @param string $name Component name.
     * @param array  $data Component data to be passed to the template.
     */
    public function __construct($name = 'search-engine', $data = []) {
        $data['categories'] = get_object_taxonomies(Plugin::COMPONENT_POST_TYPE);
        $data['posts'] = $this->getPosts();

        parent::__construct($name, $data);
    }

    /**
     * Register AJAX actions for the component.
     */
    public static function registerAjaxActions() {
        add_action('wp_ajax_rtbs_get_posts', [self::class, 'ajaxGetPosts']);
        add_action('wp_ajax_nopriv_rtbs_get_posts', [self::class, 'ajaxGetPosts']);
    }

    private function getPosts() {
        $posts = get_posts([
            'post_type' => Plugin::COMPONENT_POST_TYPE,
            'numberposts' => -1
        ]);

        return $posts;
    }

    /**
     * AJAX handler to get posts based on search criteria.
     */
    public static function ajaxGetPosts() {
        check_ajax_referer('rtbs_nonce');

        $keyword = sanitize_text_field($_POST['keyword'] ?? '');

        $args = [
            'post_type' => Plugin::COMPONENT_POST_TYPE,
            's' => $keyword,
            'tax_query' => []
        ];

        $categories = get_object_taxonomies(Plugin::COMPONENT_POST_TYPE);
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
        $instance = new self();

        if (empty($posts)) {
            $html = '<div class="rtbs-no-results">No components found matching your criteria.</div>';
        } else {
            ob_start();
            foreach ($posts as $post) {
                echo $instance->loadTemplate('_partials/card', ['post' => $post]);
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
