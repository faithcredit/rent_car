<?php

/**
 *
 * @package templates/default
 */

use Duplicator\Installer\ViewHelpers\Resources;

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

$baseUrl       = Resources::getAssetsBaseUrl();
$escBaseUrl    = DUPX_U::esc_url($baseUrl);
$archiveConfig = DUPX_ArchiveConfig::getInstance();
?>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="robots" content="noindex,nofollow">
<?php if ($archiveConfig->brand->isDefault) : ?>
<link rel="apple-touch-icon" sizes="180x180" href="<?php echo $escBaseUrl; ?>/favicon/pro01_apple-touch-icon.png">
<link rel="icon" type="image/png" sizes="32x32" href="<?php echo $escBaseUrl; ?>/favicon/pro01_favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="<?php echo $escBaseUrl; ?>/favicon/pro01_favicon-16x16.png">
<link rel="manifest" href="<?php echo $escBaseUrl; ?>/favicon/site.webmanifest">
<link rel="mask-icon" href="<?php echo $escBaseUrl; ?>/favicon/pro01_safari-pinned-tab.svg" color="#5bbad5">
<link rel="shortcut icon" href="<?php echo $escBaseUrl; ?>/favicon/pro01_favicon.ico">
<meta name="msapplication-TileColor" content="#00aba9">
<meta name="msapplication-config" content="favicon/browserconfig.xml">
<meta name="theme-color" content="#ffffff">
<?php endif; ?>
