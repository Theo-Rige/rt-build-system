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
        'php' => [
            'label' => 'PHP',
            'file' => 'class.php',
            'lang' => 'php'
        ],
        'template' => [
            'label' => 'Template',
            'file' => 'template.php',
            'lang' => 'html'
        ],
        'style' => [
            'label' => 'CSS',
            'file' => 'style.css',
            'lang' => 'css'
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
        global $wp_scripts;
        global $wp_styles;

        $name = static::getName();

        foreach ($wp_scripts->queue as $handle) {
            if (preg_match('#rtbs#', $handle)) continue;

            wp_dequeue_script($handle);
            wp_deregister_script($handle);
        }

        foreach ($wp_styles->queue as $handle) {
            if (preg_match('#rtbs#', $handle)) continue;

            wp_dequeue_style($handle);
            wp_deregister_style($handle);
        }

        wp_enqueue_script('rtbs-component-script', RTBS_PLUGIN_URL . 'assets/js/component.min.js', [], RTBS_PLUGIN_VERSION, true);
        wp_enqueue_style('rtbs-component-style', RTBS_PLUGIN_URL . 'assets/css/component.min.css', [], RTBS_PLUGIN_VERSION);
        wp_enqueue_style('rtbs-style', RTBS_PLUGIN_URL . 'assets/css/style.min.css', [], RTBS_PLUGIN_VERSION);
        wp_enqueue_style('rtbs-unstyle', RTBS_PLUGIN_URL . 'assets/css/unstyle.min.css', [], RTBS_PLUGIN_VERSION);

        wp_localize_script('rtbs-component-script', 'RTBS', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('rtbs_nonce')
        ]);

        $scriptPath = self::getComponentPath($name, 'script.js');
        $stylePath = self::getComponentPath($name, 'style.css');

        if (file_exists($scriptPath)) {
            $scriptURL = self::getComponentUrl($name, 'script.js');
            wp_enqueue_script('rtbs-' . $name . '-script', $scriptURL, [], RTBS_PLUGIN_VERSION, true);

            wp_localize_script('rtbs-' . $name . '-script', 'RTBS', [
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('rtbs_nonce')
            ]);
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
    public static function getLibraries() {
        return get_post_meta(get_the_ID(), 'rtbs_libraries', true) ?: [];
    }

    /**
     * Get component references.
     *
     */
    public static function getReferences() {
        return get_post_meta(get_the_ID(), 'rtbs_references', true) ?: [];
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
}
