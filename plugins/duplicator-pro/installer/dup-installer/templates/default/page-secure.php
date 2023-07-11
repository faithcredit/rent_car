<?php

/**
 * @package   Duplicator
 * @copyright (c) 2022, Snap Creek LLC
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

/**
 * Variables
 *
 * @var string $bodyClasses
 */

dupxTplRender('pages-parts/page-header', array(
    'paramView'       => 'secure',
    'bodyId'          => 'page-secure',
    'bodyClasses'     => $bodyClasses,
    'skipTopMessages' => true
));
?>
<div id="content-inner">
    <?php
    dupxTplRender('pages-parts/head/header-main', array(
        'htmlTitle' => 'Installer Security'
    ));
    ?>
    <div id="main-content-wrapper" >
        <?php dupxTplRender('pages-parts/secure/main'); ?>
    </div>
</div>
<?php
dupxTplRender('scripts/secure-init');
dupxTplRender('pages-parts/page-footer');
