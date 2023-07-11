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

// make sure the basic setup has been completed
$up_running = true;

/**
 * @wponly - check if some shortcodes have been defined before showing the Dashboard
 */
$model 		= JModel::getInstance('vikrentcar', 'shortcodes');
$shortcodes = $model->all('post_id');
//

if ($this->arrayfirst['totprices'] < 1 || $this->arrayfirst['totcars'] < 1 || $this->arrayfirst['totdailyfares'] < 1 || count($shortcodes) < 1) {
	// first setup helper needed
	$up_running = false;

	?>
<div class="vrc-dashboard-firstsetup-wrap">
	<div class="vrc-dashboard-firstsetup-container">
		<div class="vrc-dashboard-firstsetup-head">
			<h3><?php echo JText::_('VRCDASHFIRSTSETTITLE'); ?></h3>
			<h4><?php echo JText::_('VRCDASHFIRSTSETSUBTITLE'); ?></h4>
		</div>
		<?php
		/**
		 * Load sampledata template.
		 * 
		 * @since 	1.2.0
		 */
		echo $this->loadTemplate('sampledata');
		//
		?>
		<div class="vrc-dashboard-firstsetup-body">
			<div class="vrc-dashboard-firstsetup-task vrc-dashboard-firstsetup-task-<?php echo $this->arrayfirst['totprices'] < 1 ? 'incomplete' : 'completed'; ?>">
				<div class="vrc-dashboard-firstsetup-task-wrap">
					<div class="vrc-dashboard-firstsetup-task-number">
						<span>1.</span>
					</div>
					<div class="vrc-dashboard-firstsetup-task-details">
						<div class="vrc-dashboard-firstsetup-task-name"><?php echo JText::_('VRCDASHNOPRICES'); ?></div>
						<div class="vrc-dashboard-firstsetup-task-count">
							<span class="vrc-dashboard-firstsetup-task-val"><?php echo $this->arrayfirst['totprices']; ?></span>
						<?php
						if ($this->arrayfirst['totprices'] > 0) {
							?>
							<span class="vrc-dashboard-firstsetup-done"><?php VikRentCarIcons::e('check-circle'); ?></span>
							<?php
						}
						?>
						</div>
					</div>
				<?php
				if ($this->arrayfirst['totprices'] < 1) {
					?>
					<div class="vrc-dashboard-firstsetup-task-action">
						<a href="index.php?option=com_vikrentcar&task=prices" class="button button-secondary"><?php echo JText::_('VRCCONFIGURETASK'); ?></a>
					</div>
					<?php
				}
				?>
					<div class="vrc-dashboard-firstsetup-task-description">
						<p><?php echo JText::_('VRCWIZARDRPLANSMESS'); ?></p>
					</div>
				</div>
			</div>
			<div class="vrc-dashboard-firstsetup-task vrc-dashboard-firstsetup-task-<?php echo $this->arrayfirst['totcars'] < 1 ? 'incomplete' : 'completed'; ?>">
				<div class="vrc-dashboard-firstsetup-task-wrap">
					<div class="vrc-dashboard-firstsetup-task-number">
						<span>2.</span>
					</div>
					<div class="vrc-dashboard-firstsetup-task-details">
						<div class="vrc-dashboard-firstsetup-task-name"><?php echo JText::_('VRCDASHNOCARS'); ?></div>
						<div class="vrc-dashboard-firstsetup-task-count">
							<span class="vrc-dashboard-firstsetup-task-val"><?php echo $this->arrayfirst['totcars']; ?></span>
						<?php
						if ($this->arrayfirst['totcars'] > 0) {
							?>
							<span class="vrc-dashboard-firstsetup-done"><?php VikRentCarIcons::e('check-circle'); ?></span>
							<?php
						}
						?>
						</div>
					</div>
					<?php
				if ($this->arrayfirst['totcars'] < 1) {
					?>
					<div class="vrc-dashboard-firstsetup-task-action">
						<a href="index.php?option=com_vikrentcar&task=cars" class="button button-secondary"><?php echo JText::_('VRCCONFIGURETASK'); ?></a>
					</div>
					<?php
				}
				?>
					<div class="vrc-dashboard-firstsetup-task-description">
						<p><?php echo JText::_('VRCDASHFIRSTSETUPCARS'); ?></p>
					</div>
				</div>
			</div>
			<div class="vrc-dashboard-firstsetup-task vrc-dashboard-firstsetup-task-<?php echo $this->arrayfirst['totdailyfares'] < 1 ? 'incomplete' : 'completed'; ?>">
				<div class="vrc-dashboard-firstsetup-task-wrap">
					<div class="vrc-dashboard-firstsetup-task-number">
						<span>3.</span>
					</div>
					<div class="vrc-dashboard-firstsetup-task-details">
						<div class="vrc-dashboard-firstsetup-task-name"><?php echo JText::_('VRCDASHNODAILYFARES'); ?></div>
						<div class="vrc-dashboard-firstsetup-task-count">
							<span class="vrc-dashboard-firstsetup-task-val"><?php echo $this->arrayfirst['totdailyfares'] < 1 ? '0' : ''; ?></span>
						<?php
						if ($this->arrayfirst['totdailyfares'] > 0) {
							?>
							<span class="vrc-dashboard-firstsetup-done"><?php VikRentCarIcons::e('check-circle'); ?></span>
							<?php
						}
						?>
						</div>
					</div>
					<?php
				if ($this->arrayfirst['totdailyfares'] < 1) {
					?>
					<div class="vrc-dashboard-firstsetup-task-action">
						<a href="index.php?option=com_vikrentcar&task=tariffs" class="button button-secondary"><?php echo JText::_('VRCCONFIGURETASK'); ?></a>
					</div>
					<?php
				}
				?>
					<div class="vrc-dashboard-firstsetup-task-description">
						<p><?php echo JText::_('VRCDASHFIRSTSETUPTARIFFS'); ?></p>
					</div>
				</div>
			</div>
			<div class="vrc-dashboard-firstsetup-task vrc-dashboard-firstsetup-task-<?php echo count($shortcodes) < 1 ? 'incomplete' : 'completed'; ?>">
				<div class="vrc-dashboard-firstsetup-task-wrap">
					<div class="vrc-dashboard-firstsetup-task-number">
						<span>4.</span>
					</div>
					<div class="vrc-dashboard-firstsetup-task-details">
						<div class="vrc-dashboard-firstsetup-task-name"><?php echo JText::_('VRCFIRSTSETSHORTCODES'); ?></div>
						<div class="vrc-dashboard-firstsetup-task-count">
							<span class="vrc-dashboard-firstsetup-task-val"><?php echo count($shortcodes); ?></span>
						<?php
						if (count($shortcodes) > 0) {
							?>
							<span class="vrc-dashboard-firstsetup-done"><?php VikRentCarIcons::e('check-circle'); ?></span>
							<?php
						}
						?>
						</div>
					</div>
					<?php
				if (count($shortcodes) < 1) {
					?>
					<div class="vrc-dashboard-firstsetup-task-action">
						<a href="index.php?option=com_vikrentcar&view=shortcodes" class="button button-secondary"><?php echo JText::_('VRCCONFIGURETASK'); ?></a>
					</div>
					<?php
				}
				?>
					<div class="vrc-dashboard-firstsetup-task-description">
						<p><?php echo JText::_('VRCDASHFIRSTSETUPSHORTCODES'); ?></p>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
	<?php
}

if ($up_running === true) {
	// load the admin widgets
	?>
<div class="vrc-dashboard-fullcontainer vrc-admin-widgets-container">
	<?php
	/**
	 * Load the template file for the admin widgets when the first setup is complete.
	 * 
	 * @since 	1.2.0
	 */
	echo $this->loadTemplate('widgets');
	//
	?>
</div>
	<?php
}
