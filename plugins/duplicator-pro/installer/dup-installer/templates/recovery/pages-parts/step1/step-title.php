<?php

/**
 *
 * @package templates/default
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

dupxTplRender('pages-parts/head/header-main', array(
    'htmlTitle'        => 'Step <span class="step">1</span> of 2: ' .
        'Recovery <div class="sub-header">Launch the installer to restore the site to the selected Recovery Point.</div>',
    'showSwitchView'    => false,
    'showInstallerLog'  => false
));
