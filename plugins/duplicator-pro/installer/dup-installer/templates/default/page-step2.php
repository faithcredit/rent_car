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
    'paramView'   => 'step2',
    'bodyId'      => 'page-step2',
    'bodyClasses' => $bodyClasses
));
?>
<div id="content-inner">
    <?php dupxTplRender('pages-parts/step2/step-title'); ?>
    <div id="main-content-wrapper" >
        <?php dupxTplRender('pages-parts/step2/main'); ?>
    </div>
    <?php
    dupxTplRender('parts/ajax-error');
    dupxTplRender('parts/progress-bar');
    ?>
</div>
<?php
dupxTplRender('scripts/step2-init');
dupxTplRender('pages-parts/page-footer');
