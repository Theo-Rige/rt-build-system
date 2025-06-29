<?php

/**
 * {title} component class file.
 *
 * @package RT_Build_System
 */

namespace RTBS;

if (! defined('ABSPATH')) exit;

require_once RTBS_PLUGIN_DIR . 'includes/component.php';

/**
 * {title} component class.
 */
class NewComponent extends Component {
    /**
     * Component name.
     */
    const NAME = '{slug}';
}
