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
    'paramView'   => 'help',
    'bodyId'      => 'page-help',
    'bodyClasses' => $bodyClasses
));
?>
<div id="content-inner">
    <div id="main-content-wrapper" >
        <?php dupxTplRender('pages-parts/help/main'); ?>
    </div>
</div>
<?php
dupxTplRender('pages-parts/page-footer');
