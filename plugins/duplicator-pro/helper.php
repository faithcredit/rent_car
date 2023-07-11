<?php

defined('ABSPATH') || exit;

use Duplicator\Libs\Snap\SnapIO;
use Duplicator\Libs\Snap\SnapWP;

/**
 * Return home path
 *
 * @return string
 */
function duplicator_pro_get_home_path()
{
    static $homePath = null;
    if (is_null($homePath)) {
        $homePath = SnapIO::safePathUntrailingslashit(SnapWP::getHomePath(), true);
    }
    return $homePath;
}

if (!function_exists('wp_doing_ajax')) {

    /**
     * Determines whether the current request is a WordPress Ajax request.
     *
     * @since 4.7.0
     *
     * @return bool True if it's a WordPress Ajax request, false otherwise.
     */
    function wp_doing_ajax()
    {
        /**
         * Filters whether the current request is a WordPress Ajax request.
         *
         * @since 4.7.0
         *
         * @param bool $wp_doing_ajax Whether the current request is a WordPress Ajax request.
         */
        return apply_filters('wp_doing_ajax', defined('DOING_AJAX') && DOING_AJAX);
    }
}

if (!function_exists('wp_normalize_path')) {

    /**
     * Normalize a filesystem path.
     *
     * On windows systems, replaces backslashes with forward slashes
     * and forces upper-case drive letters.
     * Allows for two leading slashes for Windows network shares, but
     * ensures that all other duplicate slashes are reduced to a single.
     *
     * @since 3.9.0
     * @since 4.4.0 Ensures upper-case drive letters on Windows systems.
     * @since 4.5.0 Allows for Windows network shares.
     * @since 4.9.7 Allows for PHP file wrappers.
     *
     * @param string $path Path to normalize.
     *
     * @return string Normalized path.
     */
    function wp_normalize_path($path)
    {
        $wrapper = '';
        if (wp_is_stream($path)) {
            list( $wrapper, $path ) = explode('://', $path, 2);
            $wrapper               .= '://';
        }

        // Standardise all paths to use /
        $path = str_replace('\\', '/', $path);

        // Replace multiple slashes down to a singular, allowing for network shares having two slashes.
        $path = preg_replace('|(?<=.)/+|', '/', $path);

        // Windows paths should uppercase the drive letter
        if (':' === substr($path, 1, 1)) {
            $path = ucfirst($path);
        }

        return $wrapper . $path;
    }
}
