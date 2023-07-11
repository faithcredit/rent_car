<?php

/**
 * Validation object
 *
 * Standard: PSR-2
 *
 * @link http://www.php-fig.org/psr/psr-2 Full Documentation
 *
 * @package SC\DUPX\U
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

use Duplicator\Installer\Core\Params\PrmMng;

class DUPX_Validation_test_multisite_subfolder extends DUPX_Validation_abstract_item
{
    protected function runTest()
    {
        if (DUPX_InstallerState::isRecoveryMode() || !DUPX_InstallerState::isNewSiteIsMultisite()) {
            return self::LV_SKIP;
        }

        if (DUPX_InstallerState::isInstType(DUPX_InstallerState::INSTALL_MULTISITE_SUBDOMAIN) && $this->newUrlIsInSubFolder()) {
            return self::LV_HARD_WARNING;
        }

        return self::LV_PASS;
    }

    /**
     * Check if the new url is in a subfolder
     *
     * @return bool
     */
    private function newUrlIsInSubFolder()
    {
        return parse_url(PrmMng::getInstance()->getValue(PrmMng::PARAM_URL_NEW), PHP_URL_PATH) !== null;
    }

    public function getTitle()
    {
        return 'Subomain multisite installation in subfolder';
    }

    protected function hwarnContent()
    {
        return dupxTplRender('parts/validation/tests/multisite-subfolder', array(
            "isOk" => false,
        ), false);
    }

    protected function passContent()
    {
        return dupxTplRender('parts/validation/tests/multisite-subfolder', array(
            "isOk" => true,
        ), false);
    }
}
