<?php

/**
 * @package   Duplicator
 * @copyright (c) 2022, Snap Creek LLC
 */

use Duplicator\Libs\Snap\FunctionalityCheck;

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

/**
 * Variables
 *
 * @var int $testResult
 * @var FunctionalityCheck[] $functionalities
 */

?>
<div class="sub-title">STATUS</div>
<?php
switch ($testResult) {
    case DUPX_Validation_abstract_item::LV_PASS:
        ?>
        <p class="green">
            All classes and functions listed below, both essential and non-essential, are enabled on your system.
        </p>
        <?php
        break;
    case DUPX_Validation_abstract_item::LV_HARD_WARNING:
        ?>
        <p class="maroon">
            All required functions and classes are enabled on your system, but some of them that are non-essential are
            disabled or not present on your server.
        </p>
        <?php
        break;
    case DUPX_Validation_abstract_item::LV_FAIL:
        ?>
        <p class="maroon">
            Some of required functions or classes are disabled or not present on your server.
        </p>
        <?php
        break;
}
?>
<div class="sub-title">DETAILS</div>
<p>
    List of functions and classes that should be enabled on your system:
</p>
<div class="s1-validate-flagged-tbl-list margin-bottom-1">
    <table cellspacing="0" class="validation-results">
        <thead>
            <tr>
                <td width="1">Functionality</td>
                <td width="1">Required</td>
                <td width="1">Result</td>
                <td>Quick troubleshoot</td>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($functionalities as $func) : ?>
            <tr>
                <td>
                    <?php echo ($func->getType() == FunctionalityCheck::TYPE_CLASS ? 'Class' : 'Function'); ?>
                    <a href="<?php echo $func->link; ?>" target="_blank">
                        <?php echo $func->getItemKey(); ?>
                    </a>
                </td>
                <td>
                    <?php echo ($func->isRequired() ? 'Yes' : 'No'); ?>
                </td>
                <td>
                    <span class="status-badge right <?php echo ($func->check() ? 'pass' : 'fail'); ?>"></span>
                </td>
                <td style="white-space: normal;">
                    <?php
                        echo $func->troubleshoot;
                    ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<div class="sub-title">TROUBLESHOOT</div>
<ul>
    <li>
        Contact your hosting provider and ask them to enable required functions and classes.
        You can also read specifics related to each function in the
        <a href="https://snapcreek.com/duplicator/docs/faqs-tech/#faq-trouble-120-q"
           target="_blank">FAQ section</a>.
    </li>
</ul>