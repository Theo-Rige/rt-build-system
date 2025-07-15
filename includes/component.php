<?php

/**
 * Component class file.
 *
 * @package RT_Build_System
 */

namespace RTBS;

if (! defined('ABSPATH')) exit;

/**
 * Component class to handle component loading and rendering.
 */
class Component {
    /**
     * Component name.
     *
     * @var string
     */
    const NAME = 'component';

    /**
     * List of code tabs.
     *
     * @var array
     */
    const CODES = [
        'template' => [
            'label' => 'Template',
            'file' => 'template.php',
            'lang' => 'php'
        ],
        'php' => [
            'label' => 'PHP',
            'file' => 'class.php',
            'lang' => 'php'
        ],
        'style' => [
            'label' => 'CSS',
            'file' => 'style.css',
            'lang' => 'scss'
        ],
        'js' => [
            'label' => 'JavaScript',
            'file' => 'script.js',
            'lang' => 'js'
        ]
    ];

    /**
     * Get component class by slug.
     *
     * @param string $slug Component slug.
     * @return string|null Component class name or null if not found.
     */
    public static function getComponentClass($slug) {
        $className = 'RTBS\\' . str_replace('-', '', ucwords($slug, '-'));

        if (class_exists($className)) return $className;

        error_log("Component class not found: $className for slug: $slug");
        return null;
    }

    /**
     * Get all components.
     *
     * @return array Component posts.
     */
    public static function getAll() {
        return get_posts([
            'post_type' => Plugin::COMPONENT_POST_TYPE,
            'numberposts' => -1,
            'orderby' => 'title',
            'order' => 'ASC',
        ]);
    }

    /**
     * Initialize component hooks.
     */
    public static function init() {
        add_action('wp_enqueue_scripts', [static::class, 'enqueueAssets']);
        add_filter('script_loader_tag', [static::class, 'addModuleType'], 10, 3);
    }

    /**
     * Register AJAX actions for the component.
     */
    public static function registerAjaxActions() {
        add_action('wp_ajax_rtbs_download_zip', [self::class, 'downloadZip']);
        add_action('wp_ajax_nopriv_rtbs_download_zip', [self::class, 'downloadZip']);
        add_action('wp_ajax_rtbs_update_library_status', [self::class, 'updateLibraryStatus']);
        add_action('wp_ajax_nopriv_rtbs_update_library_status', [self::class, 'updateLibraryStatus']);
        add_action('wp_ajax_rtbs_refresh_library_dates', [self::class, 'refreshLibraryDatesAjax']);
    }

    /**
     * Get component name (should be overridden by child classes).
     * 
     * @return string Component name.
     */
    public static function getName() {
        return static::NAME ?? 'component';
    }

    /**
     * Helper to get component path.
     *
     * @param string $name Component name.
     * @param string $file File name.
     * @return string Full path to component file.
     */
    protected static function getComponentPath($name, $file = '') {
        return plugin_dir_path(dirname(__FILE__)) . 'components/' . $name . '/' . $file;
    }

    /**
     * Helper to get component URL.
     *
     * @param string $name Component name.
     * @param string $file File name.
     * @return string Full URL to component file.
     */
    protected static function getComponentUrl($name, $file = '') {
        return plugin_dir_url(dirname(__FILE__)) . 'components/' . $name . '/' . $file;
    }

    /**
     * Enqueue component scripts and styles.
     */
    public static function enqueueAssets() {
        $name = static::getName();

        wp_enqueue_script('rtbs-component-script', RTBS_PLUGIN_URL . 'assets/js/component.min.js', [], RTBS_PLUGIN_VERSION, true);
        wp_enqueue_style('rtbs-component-style', RTBS_PLUGIN_URL . 'assets/css/component.min.css', [], RTBS_PLUGIN_VERSION);
        wp_enqueue_style('rtbs-base', RTBS_PLUGIN_URL . 'assets/css/base.min.css', [], RTBS_PLUGIN_VERSION);
        wp_enqueue_style('rtbs-theme', RTBS_PLUGIN_URL . 'assets/css/theme.min.css', [], RTBS_PLUGIN_VERSION);
        wp_enqueue_style('rtbs-style', RTBS_PLUGIN_URL . 'assets/css/style.min.css', [], RTBS_PLUGIN_VERSION);
        wp_enqueue_style('rtbs-unstyle', RTBS_PLUGIN_URL . 'assets/css/unstyle.min.css', [], RTBS_PLUGIN_VERSION);

        wp_localize_script('rtbs-component-script', 'RTBS', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('rtbs_nonce'),
            'id' => get_the_ID()
        ]);

        $scriptPath = self::getComponentPath($name, 'script.js');
        $stylePath = self::getComponentPath($name, 'style.css');

        if (file_exists($scriptPath)) {
            $scriptURL = self::getComponentUrl($name, 'script.js');
            wp_enqueue_script('rtbs-' . $name . '-script', $scriptURL, [], RTBS_PLUGIN_VERSION, true);
        }

        if (file_exists($stylePath)) {
            $styleURL = self::getComponentUrl($name, 'style.css');
            wp_enqueue_style('rtbs-' . $name . '-style', $styleURL, [], RTBS_PLUGIN_VERSION);
        }
    }

    /**
     * Add module type to script tags.
     *
     * @param string $tag The script tag.
     * @param string $handle The script handle.
     * @param string $src The script source URL.
     * @return string Modified script tag.
     */
    public static function addModuleType($tag, $handle, $src) {
        if ($handle === 'rtbs-component-script') {
            return str_replace('src=', 'type="module" src=', $tag);
        }

        return $tag;
    }

    /**
     * Load component template.
     *
     * @param string $name Template name.
     * @param array $data Template data.
     * @return string|false The template content or false if template not found.
     */
    public static function loadTemplate($name = 'template', $data = []) {
        $componentName = static::getName();
        $templatePath = self::getComponentPath($componentName, $name . '.php');

        if (!file_exists($templatePath)) {
            error_log("Template file not found: $templatePath for component: " . $componentName);
            return false;
        }

        extract($data);

        // Make component class available to template
        $component = static::class;

        ob_start();
        include $templatePath;
        return ob_get_clean();
    }

    /**
     * Get component libraries.
     *
     */
    public static function getLibraries($id = null) {
        $id = $id ?: get_the_ID();
        return get_post_meta($id, 'rtbs-libraries', true) ?: [];
    }

    /**
     * Get component references.
     *
     */
    public static function getReferences($id = null) {
        $id = $id ?: get_the_ID();
        return get_post_meta($id, 'rtbs-references', true) ?: [];
    }

    /**
     * Get library status based on date.
     *
     * @param string $date Date string.
     * @return string HTML template for library status.
     */
    public static function getLibraryStatus($date) {
        $date = strtotime($date);
        $currentDate = time();
        $sixMonthsAgo = strtotime('-6 months', $currentDate);
        $oneYearAgo = strtotime('-1 year', $currentDate);
        if ($date < $oneYearAgo) {
            return Tool::loadTemplate('status/deprecated');
        } elseif ($date < $sixMonthsAgo) {
            return Tool::loadTemplate('status/risky');
        } else {
            return Tool::loadTemplate('status/maintained');
        }
    }

    /**
     * Download component as ZIP file.
     *
     * @return void
     */
    public static function downloadZip() {
        if (!isset($_GET['slug'])) {
            http_response_code(400);
            exit('Missing slug');
        }

        $slug = sanitize_text_field($_GET['slug']);
        $component_dir = RTBS_PLUGIN_DIR . 'components/' . $slug . '/';

        if (!is_dir($component_dir)) {
            http_response_code(404);
            exit('Component not found');
        }

        $zip_name = $slug . '-' . time() . '.zip';
        $zip_path = sys_get_temp_dir() . '/' . $zip_name;

        $zip = new \ZipArchive();
        if ($zip->open($zip_path, \ZipArchive::CREATE) !== true) {
            http_response_code(500);
            exit('Could not create ZIP');
        }

        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($component_dir),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($files as $name => $file) {
            if (!$file->isDir()) {
                $filePath = $file->getRealPath();
                $relativePath = substr($filePath, strlen($component_dir));
                $zip->addFile($filePath, $relativePath);
            }
        }

        $zip->close();

        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="' . basename($zip_path) . '"');
        header('Content-Length: ' . filesize($zip_path));
        readfile($zip_path);
        unlink($zip_path);
        exit;
    }

    /**
     * Fetch latest release date from GitHub API.
     *
     * @param string $repository_url GitHub repository URL.
     * @return string|null Release date in ISO format or null if not found.
     */
    public static function fetchGitHubReleaseDate($repository_url) {
        if (empty($repository_url)) {
            return null;
        }

        // Extract repo owner/name from GitHub URL
        $repo = self::extractRepoFromUrl($repository_url);
        if (!$repo) {
            return null;
        }

        $api_url = "https://api.github.com/repos/{$repo}/releases/latest";
        
        // Use WordPress HTTP API for the request
        $response = wp_remote_get($api_url, [
            'timeout' => 10,
            'user-agent' => 'RT-Build-System-WordPress-Plugin'
        ]);

        if (is_wp_error($response)) {
            error_log('GitHub API request failed: ' . $response->get_error_message());
            return null;
        }

        $status_code = wp_remote_retrieve_response_code($response);
        if ($status_code !== 200) {
            error_log("GitHub API returned status code: {$status_code}");
            return null;
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log('Failed to decode GitHub API response');
            return null;
        }

        return isset($data['published_at']) ? $data['published_at'] : null;
    }

    /**
     * Extract repository owner/name from GitHub URL.
     *
     * @param string $url GitHub repository URL.
     * @return string|null Repository in "owner/name" format or null if invalid.
     */
    public static function extractRepoFromUrl($url) {
        if (preg_match('/github\.com\/([^\/]+)\/([^\/]+)/', $url, $matches)) {
            $repo_name = $matches[2];
            // Remove .git extension if present
            $repo_name = preg_replace('/\.git$/', '', $repo_name);
            return $matches[1] . '/' . $repo_name;
        }
        return null;
    }

    /**
     * Update library release dates for a component.
     *
     * @param int $post_id The component post ID.
     * @return array Updated libraries array.
     */
    public static function updateLibraryReleaseDates($post_id) {
        $libraries = self::getLibraries($post_id);
        
        foreach ($libraries as $index => &$library) {
            if (!empty($library['repository'])) {
                $release_date = self::fetchGitHubReleaseDate($library['repository']);
                if ($release_date) {
                    // Convert to readable date format
                    $library['date'] = date('n/j/Y', strtotime($release_date));
                }
            }
        }
        
        // Update the post meta with the new dates
        update_post_meta($post_id, 'rtbs-libraries', $libraries);
        
        return $libraries;
    }

    /**
     * Update library status via AJAX.
     *
     * @return void
     */
    public static function updateLibraryStatus() {
        if (!isset($_POST['id']) || !isset($_POST['index']) || !isset($_POST['date'])) {
            wp_send_json_error(['message' => __('Missing parameters', 'rt-build-system')], 400);
        }

        $id = intval($_POST['id']);
        $index = intval($_POST['index']);
        $date = sanitize_text_field($_POST['date']);
        $libraries = self::getLibraries($id);

        if (!isset($libraries[$index])) {
            wp_send_json_error(['message' => __('Library not found', 'rt-build-system')], 404);
        }

        $libraries[$index]['date'] = $date;

        $statusHTML = self::getLibraryStatus($date);

        wp_send_json_success($statusHTML);
    }

    /**
     * Refresh library release dates via AJAX.
     *
     * @return void
     */
    public static function refreshLibraryDatesAjax() {
        // Verify nonce for security
        if (!wp_verify_nonce($_POST['_ajax_nonce'] ?? '', 'rtbs_nonce')) {
            wp_send_json_error(['message' => __('Security check failed', 'rt-build-system')], 403);
        }

        if (!isset($_POST['id'])) {
            wp_send_json_error(['message' => __('Missing post ID', 'rt-build-system')], 400);
        }

        $post_id = intval($_POST['id']);

        // Check if user can edit this post
        if (!current_user_can('edit_post', $post_id)) {
            wp_send_json_error(['message' => __('Permission denied', 'rt-build-system')], 403);
        }

        $updated_libraries = self::updateLibraryReleaseDates($post_id);

        wp_send_json_success([
            'libraries' => $updated_libraries,
            'message' => __('Library dates updated successfully', 'rt-build-system')
        ]);
    }
}
