<?php

namespace RTBS;

class Tool {
    /**
     * Loads a template file and returns its output.
     *
     * @param string $path The path to the template file.
     * @return string The output of the template file.
     */
    public static function loadTemplate($path, $data = null, $echo = false) {
        if ($data) extract($data);

        $themeTemplate = get_stylesheet_directory() . '/' . RTBS_PLUGIN_DOMAIN . "/$path.php";

        ob_start();
        require file_exists($themeTemplate) ? $themeTemplate : RTBS_PLUGIN_DIR . "templates/$path.php";

        if ($echo) {
            echo ob_get_clean();
        } else {
            return ob_get_clean();
        }
    }

    /**
     * Loads an SVG file and returns it as a string.
     *
     * @param string $name The name of the SVG file to load.
     * @param string|null $class Optional. The CSS class to apply to the SVG element. Default is null.
     * @return string The SVG file content as a string.
     */
    public static function loadSVG($name, $class = null) {
        ob_start();

        include RTBS_PLUGIN_DIR . 'assets/svg/' . $name . '.svg';

        $svg = ob_get_clean();
        $svg = str_replace('<svg', '<svg class="icon icon--' . (!empty($class) ? $class : $name) . '"', $svg);

        return $svg;
    }
}
