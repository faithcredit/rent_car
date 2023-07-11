<?php

/**
 *
 * @package templates/default
 */

use Duplicator\Installer\ViewHelpers\Resources;

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

$versionDup = DUPX_ArchiveConfig::getInstance()->version_dup;
$baseUrl    = Resources::getAssetsBaseUrl();
$cssList    =  array(
    'assets/normalize.css',
    'assets/font-awesome/css/all.min.css',
    'assets/fonts/dots/dots-font.css',
    'assets/js/password-strength/password.css',
    'assets/js/tippy/dup-pro-tippy.css',
    'vendor/select2/css/select2.css'
);

$jsList = array(
    'assets/inc.libs.js',
    'assets/js/popper/popper.min.js',
    'assets/js/tippy/tippy-bundle.umd.min.js',
    'assets/js/duplicator-tooltip.js',
    'assets/js/select2/js/select2.js',
    'assets/js/password-strength/password.js'
);

// CSS
foreach ($cssList as $css) {
    ?>
    <link rel="stylesheet" href="<?php echo $baseUrl . '/' . $css . '?ver=' . $versionDup; ?>" type="text/css" media="all" >
    <?php
}
require(DUPX_INIT . '/assets/inc.libs.css.php');
require(DUPX_INIT . '/assets/inc.css.php');

// JAVASCRIPT
foreach ($jsList as $js) {
    ?>
    <script src="<?php echo $baseUrl . '/' . $js . '?ver=' . $versionDup; ?>" ></script>
    <?php
}
require(DUPX_INIT . '/assets/inc.js.php');
dupxTplRender('scripts/dupx-functions');