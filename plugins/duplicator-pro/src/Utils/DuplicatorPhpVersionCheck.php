<?php

/**
 * These functions are performed before including any other Duplicator file so
 * do not use any Duplicator library or feature and use code compatible with PHP 5.2
 */

defined('ABSPATH') || exit;
// In the future it will be included on both PRO and LITE so you need to check if the define exists.
if (!class_exists('DuplicatorPhpVersionCheck')) {

    /**
     * PHP Version
     */
    class DuplicatorPhpVersionCheck // phpcs:ignore 
    {
        /** @var string */
        protected static $minVer = '';
        /** @var string */
        protected static $suggestedVer = '';

        /**
         * Check PHP version
         *
         * @param string $minVer       minumum version
         * @param string $suggestedVer suggested version
         *
         * @return bool
         */
        public static function check($minVer, $suggestedVer)
        {
            self::$minVer       = $minVer;
            self::$suggestedVer = $suggestedVer;

            if (version_compare(PHP_VERSION, self::$minVer, '<')) {
                if (is_multisite()) {
                    add_action('network_admin_notices', array(__CLASS__, 'notice'));
                } else {
                    add_action('admin_notices', array(__CLASS__, 'notice'));
                }
                return false;
            } else {
                return true;
            }
        }

        /**
         * Display notice
         *
         * @return void
         */
        public static function notice()
        {
            ?>
            <div class="error notice">
                <p>
                    <b>Duplicator Pro:</b>
                    <?php
                        $str = __('This server is running a very old version of PHP %s no longer supported by Duplicator Pro.', 'duplicator-pro');
                        printf($str, PHP_VERSION);
                        echo '<br/>';

                        $str = __('Please ask your host or server admin to update to PHP %1s or greater on this server.', 'duplicator-pro');
                        printf($str, DUPLICATOR_PRO_PHP_MINIMUM_VERSION);
                        echo '<br/>';

                        echo sprintf(
                            "%s <a href='https://duplicator.com/my-account/support/' target='blank'>%s</a> %s %s.",
                            __('If this is not possible, open a', 'duplicator-pro'),
                            __('help ticket', 'duplicator-pro'),
                            __('and request a previous version of Duplicator Pro compatible with PHP', 'duplicator-pro'),
                            PHP_VERSION
                        );
                    ?>
                </p>
            </div>
            <?php
        }
    }

}
