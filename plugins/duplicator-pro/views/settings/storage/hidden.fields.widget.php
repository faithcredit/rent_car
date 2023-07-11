<?php
defined("ABSPATH") or die("");

wp_nonce_field(DUP_PRO_CTRL_Storage_Setting::NONCE_ACTION);
?>
<input type="hidden" name="action" value="<?php echo DUP_PRO_CTRL_Storage_Setting::FORM_ACTION; ?>">