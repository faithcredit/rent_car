<?php

/**
 * @package Duplicator
 */

use Duplicator\Addons\ProBase\LicensingController;
use Duplicator\Controllers\SettingsPageController;
use Duplicator\Core\Controllers\ControllersManager;

defined("ABSPATH") or die("");

/**
 * Variables
 *
 * @var Duplicator\Core\Controllers\ControllersManager $ctrlMng
 * @var Duplicator\Core\Views\TplMng $tplMng
 * @var array<string, mixed> $tplData
 */

$img_url           = plugins_url('duplicator-pro/assets/img/warning.png');
$problem_text      = $tplData['problem'];
$licensing_tab_url = ControllersManager::getMenuLink(ControllersManager::SETTINGS_SUBMENU_SLUG, LicensingController::L2_SLUG_LICENSING);
?>
<div class='update-nag dpro-admin-notice'>
    <p>
        <img src="<?php echo esc_url($img_url); ?>" style='float:left; padding:0 10px 0 5px' /> 
        <b>Warning!</b> Your Duplicator Pro license key is <?php echo $problem_text; ?>... <br/>
        This means this plugin does not have access to <b>security updates</b>, <i>bug fixes</i>, <b>support request</b> or <i>new features</i>.<br/>
        <b>Please <a href="<?php echo esc_url($licensing_tab_url); ?>">
            Activate Your License
        </a></b>.&nbsp;
        If you do not have a license key go to <a target='_blank' href='https://duplicator.com/dashboard'>
            duplicator.com
        </a> to get it.
    </p>
</div>