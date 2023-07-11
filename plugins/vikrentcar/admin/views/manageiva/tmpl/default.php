<?php
/**
 * @package     VikRentCar
 * @subpackage  com_vikrentcar
 * @author      Alessio Gaggii - e4j - Extensionsforjoomla.com
 * @copyright   Copyright (C) 2018 e4j - Extensionsforjoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 * @link        https://vikwp.com
 */

defined('ABSPATH') or die('No script kiddies please!');

$row = $this->row;

?>
<form name="adminForm" id="adminForm" action="index.php" method="post">
	<div class="vrc-admin-container">
		<div class="vrc-config-maintab-left">
			<fieldset class="adminform">
				<div class="vrc-params-wrap">
					<legend class="adminlegend"><?php echo JText::_('VRCADMINLEGENDDETAILS'); ?></legend>
					<div class="vrc-params-container">
						<div class="vrc-param-container">
							<div class="vrc-param-label"><?php echo JText::_('VRNEWIVAONE'); ?></div>
							<div class="vrc-param-setting"><input type="text" name="aliqname" value="<?php echo count($row) ? htmlspecialchars($row['name']) : ''; ?>" size="30"/></div>
						</div>
						<div class="vrc-param-container">
							<div class="vrc-param-label"><?php echo JText::_('VRNEWIVATWO'); ?></div>
							<div class="vrc-param-setting"><input type="number" step="any" name="aliqperc" value="<?php echo count($row) ? (float)$row['aliq'] : ''; ?>"/> %</div>
						</div>
					</div>
				</div>
			</fieldset>
		</div>
	</div>
	<input type="hidden" name="task" value="">
<?php
if (count($row)) {
?>
	<input type="hidden" name="whereup" value="<?php echo (int)$row['id']; ?>">
<?php
}
?>
	<input type="hidden" name="option" value="com_vikrentcar" />
	<?php echo JHtml::_('form.token'); ?>
</form>
