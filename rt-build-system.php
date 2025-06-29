<?php
/*
Plugin Name: Build System
Plugin URI: https://github.com/Theo-Rige/wp-build-system
Description: A simple WordPress plugin for building web systems.
Version: 0.1.0
Author: Theo Rige
Author URI: rigetheo.netlify.app
Developer: Theo Rige
Developer URI: rigetheo.netlify.app
Text Domain: rt-build-system
Domain Path: /languages
*/

use RTBS\Plugin;

if (!defined('ABSPATH')) exit;

define('RTBS_PLUGIN_URL', plugin_dir_url(__FILE__));
define('RTBS_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('RTBS_PLUGIN_BASENAME', plugin_basename(__FILE__));
define('RTBS_PLUGIN_DOMAIN', 'rt-build-system');
define('RTBS_PLUGIN_PREFIX', 'rtbs');
define('RTBS_PLUGIN_VERSION', '0.1.0');

require_once RTBS_PLUGIN_DIR . 'includes/plugin.php';

register_activation_hook(__FILE__, [Plugin::class, 'activate']);
register_uninstall_hook(__FILE__, [Plugin::class, 'uninstall']);

Plugin::init();
