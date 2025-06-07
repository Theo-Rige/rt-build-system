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
    protected $name;

    /**
     * Component data.
     *
     * @var array
     */
    private $data;

    /**
     * Constructor.
     *
     * @param string $name Component name.
     * @param array  $data Component data to be passed to the template.
     */
    public function __construct($name, $data = []) {
        $this->name = $name;
        $this->data = $data;

        add_action('wp_enqueue_scripts', [$this, 'enqueueAssets']);
        add_filter('script_loader_tag', [$this, 'addModuleType'], 10, 3);
    }

    /**
     * Enqueue component scripts and styles.
     */
    public function enqueueAssets() {
        wp_enqueue_script('rtbs-component-script', RTBS_PLUGIN_URL . 'assets/js/component.min.js', [], RTBS_PLUGIN_VERSION, true);
        wp_enqueue_style('rtbs-component-style', RTBS_PLUGIN_URL . 'assets/css/component.min.css', [], RTBS_PLUGIN_VERSION);
        wp_enqueue_style('rtbs-style', RTBS_PLUGIN_URL . 'assets/css/style.min.css', [], RTBS_PLUGIN_VERSION);

        $scriptPath = plugin_dir_path(dirname(__FILE__)) . 'components/' . $this->name . '/script.js';
        $stylePath = plugin_dir_path(dirname(__FILE__)) . 'components/' . $this->name . '/style.css';

        if (file_exists($scriptPath)) {
            $scriptURL = plugin_dir_url(dirname(__FILE__)) . 'components/' . $this->name . '/script.js';
            wp_enqueue_script('rtbs-' . $this->name . '-script', $scriptURL, [], RTBS_PLUGIN_VERSION, true);

            wp_localize_script('rtbs-' . $this->name . '-script', 'RTBS', [
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('rtbs_nonce')
            ]);
        }

        if (file_exists($stylePath)) {
            $styleURL = plugin_dir_url(dirname(__FILE__)) . 'components/' . $this->name . '/style.css';
            wp_enqueue_style('rtbs-' . $this->name . '-style', $styleURL, [], RTBS_PLUGIN_VERSION);
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
    public function addModuleType($tag, $handle, $src) {
        if ($handle === 'rtbs-component-script') {
            return str_replace('src=', 'type="module" src=', $tag);
        }

        return $tag;
    }

    /**
     * Load component template.
     *
     * @return string|false The template content or false if template not found.
     */
    public function loadTemplate($name = 'template', $data = []) {
        $templatePath = plugin_dir_path(dirname(__FILE__)) . 'components/' . $this->name . '/' . $name . '.php';

        if (!file_exists($templatePath)) {
            error_log("Template file not found: $templatePath for component: " . $this->name);
            return false;
        }

        if (empty($data) && !empty($this->data) && is_array($this->data)) {
            extract($this->data);
        } else {
            extract($data);
        }

        $instance = $this;

        ob_start();
        include $templatePath;
        return ob_get_clean();
    }
}
