<?php

/**
 *
 * @package templates/default
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

use Duplicator\Installer\Core\Params\PrmMng;

$paramsManager = PrmMng::getInstance();
$subsite_id    = $paramsManager->getValue(PrmMng::PARAM_SUBSITE_ID);
$safe_mode     = $paramsManager->getValue(PrmMng::PARAM_SAFE_MODE);
?>
<div class="flex-final-button-wrapper" >
    <div class="button-wrapper" >
        <a id="s4-final-btn" class="default-btn" href="<?php echo htmlspecialchars(DUPX_InstallerState::getAdminLogin()); ?>" target="_blank">
            <i class="fab fa-wordpress"></i> Admin Login
        </a>
    </div>
    <div class="content-wrapper" >
        Click the Admin Login button to login and finalize this install.<br />
        <?php $paramsManager->getHtmlFormParam(PrmMng::PARAM_AUTO_CLEAN_INSTALLER_FILES); ?>
    </div>
</div>

<!-- WARN: MU MESSAGES -->
<div class="s4-warn final-step-warn-item" style="display:<?php echo ($subsite_id > 0 ? 'block' : 'none') ?>">
    <b><i class="fas fa-exclamation-triangle"></i> MULTISITE:</b>
    Some plugins may exhibit quirks when switching from subsite to standalone mode, 
    so all plugins have been disabled. Re-activate each plugin one-by-one and test
    the site after each activation. If you experience issues please see the
    <a href="https://snapcreek.com/duplicator/docs/faqs-tech/#faq-trouble-mu" target="_blank">Multisite Network FAQs</a> online.
</div>

<!-- WARN: SAFE MODE MESSAGES -->
<div class="s4-warn final-step-warn-item" style="display:<?php echo ($safe_mode > 0 ? 'block' : 'none') ?>">
    <b><i class="fas fa-exclamation-triangle"></i> SAFE MODE:</b>
    Safe mode has <u>deactivated</u> all plugins. Please be sure to enable your plugins after logging in.
    <i>
        If you notice that problems arise when activating
        the plugins then active them one-by-one to isolate the plugin that could be causing the issue.
    </i>
</div>
