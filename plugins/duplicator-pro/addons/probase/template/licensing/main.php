<?php

/**
 * @package Duplicator
 */

use Duplicator\Addons\ProBase\License\License;
use Duplicator\Addons\ProBase\LicensingController;

defined("ABSPATH") or die("");

/**
 * Variables
 *
 * @var Duplicator\Core\Controllers\ControllersManager $ctrlMng
 * @var Duplicator\Core\Views\TplMng $tplMng
 * @var array<string, mixed> $tplData
 */

$tplMng->render('licensing/license_message');
$tplMng->render('licensing/activation');
$tplMng->render('licensing/visibility');

?>

<script>
    jQuery(document).ready(function($) {
        DupPro.Licensing = new Object();
        DupPro.Licensing.VISIBILITY_ALL = <?php echo License::VISIBILITY_ALL;?>;
        DupPro.Licensing.VISIBILITY_INFO = <?php echo License::VISIBILITY_INFO;?>;
        DupPro.Licensing.VISIBILITY_NONE = <?php echo License::VISIBILITY_NONE;?>;

        $("#_key_password, #_key_password_confirmation").keyup(function(event) {

            if (event.keyCode == 13) {
                $("#show_hide").click();
            }
        });

        DupPro.Licensing.ChangeActivationStatus = function(activate) {
            if (activate) {
                let licenseKey = $('.dup-license-key-input').val();
                window.location.href = 
                    <?php echo json_encode($tplData['actions'][LicensingController::ACTION_ACTIVATE_LICENSE]->getUrl()); ?> + 
                    '&_license_key=' + licenseKey;
            } else {
                window.location.href = <?php echo json_encode($tplData['actions'][LicensingController::ACTION_DEACTIVATE_LICENSE]->getUrl()); ?>;
            }
            return false;
        }

        DupPro.Licensing.ClearActivationStatus = function() {
            window.location.href = <?php echo json_encode($tplData['actions'][LicensingController::ACTION_CLEAR_KEY]->getUrl()); ?>;
        }

        DupPro.Licensing.ChangeKeyVisibility = function(show) {
            $('#dup-license-visibility-form').submit();
        }

        DupPro.Licensing.VisibilityTemporary = function(visibility) {
            switch (visibility) {
                case DupPro.Licensing.VISIBILITY_ALL:
                    $("#dup-tr-license-dashboard").show();
                    $("#dup-tr-license-type").show();
                    $("#dup-tr-license-key-and-description").show();
                    break;
                case DupPro.Licensing.VISIBILITY_INFO:
                    $("#dup-tr-license-dashboard").show();
                    $("#dup-tr-license-type").show();
                    $("#dup-tr-license-key-and-description").hide();
                    break;
                case DupPro.Licensing.VISIBILITY_NONE:
                    $("#dup-tr-license-dashboard").hide();
                    $("#dup-tr-license-type").hide();
                    $("#dup-tr-license-key-and-description").hide();
                    break;
                default:
                    alert("Unexpected visibility value!");
            }
        }
    });
</script>
