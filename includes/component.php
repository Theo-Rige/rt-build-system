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
        ],
        'template' => [
            'label' => 'Template',
            'file' => 'template.php',
        ],
        'js' => [
            'label' => 'JavaScript',
            'file' => 'script.js',
        ]
    ];

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
     * Get component name (should be overridden by child classes).
     * 
     * @return string Component name.
     */
    public static function getName() {
        return static::NAME ?? 'component';
    }

    /**
     * Enqueue component scripts and styles.
     */
    public static function enqueueAssets() {
        $name = static::getName();

        wp_enqueue_script('rtbs-component-script', RTBS_PLUGIN_URL . 'assets/js/component.min.js', [], RTBS_PLUGIN_VERSION, true);
        wp_enqueue_style('rtbs-component-style', RTBS_PLUGIN_URL . 'assets/css/component.min.css', [], RTBS_PLUGIN_VERSION);
        wp_enqueue_style('rtbs-style', RTBS_PLUGIN_URL . 'assets/css/style.min.css', [], RTBS_PLUGIN_VERSION);

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
}
