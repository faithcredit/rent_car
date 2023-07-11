<?php

use Duplicator\Controllers\DebugPageController;

 defined("ABSPATH") or die(""); ?>

<div class="section-tests">
    <div class="section-hdr">TOOLS CTRLS</div>

    <!-- METHOD TEST -->
    <form>
        <?php
            $CTRL['Title']  = 'DUP_PRO_CTRL_Tools_runScanValidator';
            $CTRL['Action'] = 'DUP_PRO_CTRL_Tools_runScanValidator';
            $CTRL['Test']   = true;
            DebugPageController::testSetup($CTRL);
        ?>
        <div class="params">
            <label>Allow Recursion:</label>
            <input type="checkbox" name="scan-recursive" /><br/>
            <label>Search Path:</label> 
            <input type="text" name="scan-path" value="<?php echo esc_attr(duplicator_pro_get_home_path()); ?>" /> <br/>
        </div>
    </form>
    
    <!-- METHOD TEST -->
    <form>
        <?php
            $CTRL['Title']  = 'DUP_PRO_CTRL_Tools_runScanValidatorFull';
            $CTRL['Action'] = 'DUP_PRO_CTRL_Tools_runScanValidator';
            $CTRL['Test']   = true;
            DebugPageController::testSetup($CTRL);
        ?>
        <div class="params">
            <label>Recursion:</label> True
            <input type="hidden" name="scan-recursive" value="true" /><br/>
            <label>Search Path:</label> 
            <input type="text" name="scan-path" value="<?php echo esc_attr(duplicator_pro_get_home_path()); ?>" /> <br/>
        </div>
    </form>

</div>
