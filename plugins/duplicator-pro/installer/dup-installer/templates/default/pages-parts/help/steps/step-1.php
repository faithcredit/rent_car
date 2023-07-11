<?php

/**
 * @package   Duplicator
 * @copyright (c) 2022, Snap Creek LLC
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

/**
 * Variables
 *
 * @var bool $open_section;
 */

$sectionId   = 'section-step-1';
$expandClass = $sectionId == $open_section ? 'open' : 'close';
?>
<section id="<?php echo $sectionId; ?>" class="expandable <?php echo $expandClass; ?>" >
    <h2 class="header expand-header">
        Step <span class="step">1</span>: Deployment
    </h2>
    <div class="content" >
        <div id="dup-help-scanner" class="help-page">
            <?php
            dupxTplRender('pages-parts/help/steps/step1-parts/basic-step1-setup');
            dupxTplRender('pages-parts/help/steps/step1-parts/advanced-step1-options');
            dupxTplRender('pages-parts/help/steps/step1-parts/validation-step1');
            ?>
        </div>
    </div>
</section>