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

$vrc_app = new VrcApplication();
$vrc_app->prepareModalBox();

?>
<div class="vrc-admin-body vrc-config-body">
	
	<form name="adminForm" id="adminForm" action="index.php" method="post" enctype="multipart/form-data">

		<div class="vrc-config-tabs-wrap">

			<dl class="tabs" id="tab_group_id">
				<dt style="display:none;"></dt>
				<dd style="display:none;"></dd>
				<dt class="tabs <?php echo $this->curtabid == 1 ? 'open' : 'closed'; ?>" data-ptid="1" style="cursor: pointer;">
					<span>
						<h3>
							<?php VikRentCarIcons::e('sliders-h'); ?>
							<a href="javascript:void(0);"><?php echo JText::_('VRPANELONE'); ?></a>
						</h3>
					</span>
				</dt><dt class="tabs <?php echo $this->curtabid == 2 ? 'open' : 'closed'; ?>" data-ptid="2" style="cursor: pointer;">
					<span>
						<h3>
							<?php VikRentCarIcons::e('funnel-dollar'); ?>
							<a href="javascript:void(0);"><?php echo JText::_('VRPANELTWO'); ?></a>
						</h3>
					</span>
				</dt><dt class="tabs <?php echo $this->curtabid == 3 ? 'open' : 'closed'; ?>" data-ptid="3" style="cursor: pointer;">
					<span>
						<h3>
							<?php VikRentCarIcons::e('pencil-alt'); ?>
							<a href="javascript:void(0);"><?php echo JText::_('VRPANELTHREE'); ?></a>
						</h3>
					</span>
				</dt><dt class="tabs <?php echo $this->curtabid == 4 ? 'open' : 'closed'; ?>" data-ptid="4" style="cursor: pointer;">
					<span>
						<h3>
							<?php VikRentCarIcons::e('user-cog'); ?>
							<a href="javascript:void(0);"><?php echo JText::_('VRPANELFOUR'); ?></a>
						</h3>
					</span>
				</dt><dt class="tabs <?php echo $this->curtabid == 5 ? 'open' : 'closed'; ?>" data-ptid="5" style="cursor: pointer;">
					<span>
						<h3>
							<?php VikRentCarIcons::e('quote-right'); ?>
							<a href="javascript:void(0);"><?php echo JText::_('VRC_COND_TEXTS'); ?></a>
						</h3>
					</span>
				</dt>
				<dt class="vrc-renewsession-dt">
					<a href="javascript: void(0);" class="vrcflushsession" onclick="vrcFlushSession();"><?php echo JText::_('VRCONFIGFLUSHSESSION'); ?></a>
				</dt>
			</dl>

		</div>

		<div class="current">
			<dd class="tabs" id="pt1" style="display: <?php echo $this->curtabid == 1 ? 'block' : 'none'; ?>;">
				<div class="vrc-admin-container vrc-config-tab-container">
					<?php echo $this->loadTemplate('one'); ?>
				</div>
			</dd>
			<dd class="tabs" id="pt2" style="display: <?php echo $this->curtabid == 2 ? 'block' : 'none'; ?>;">
				<div class="vrc-admin-container vrc-config-tab-container">
					<?php echo $this->loadTemplate('two'); ?>
				</div>
			</dd>
			<dd class="tabs" id="pt3" style="display: <?php echo $this->curtabid == 3 ? 'block' : 'none'; ?>;">
				<div class="vrc-admin-container vrc-config-tab-container">
					<?php echo $this->loadTemplate('three'); ?>
				</div>
			</dd>
			<dd class="tabs" id="pt4" style="display: <?php echo $this->curtabid == 4 ? 'block' : 'none'; ?>;">
				<div class="vrc-admin-container vrc-config-tab-container">
					<?php echo $this->loadTemplate('four'); ?>
				</div>
			</dd>
			<dd class="tabs" id="pt5" style="display: <?php echo $this->curtabid == 5 ? 'block' : 'none'; ?>;">
				<div class="vrc-admin-container vrc-config-tab-container">
					<?php echo $this->loadTemplate('five'); ?>
				</div>
			</dd>
		</div>

		<input type="hidden" name="task" value="">
		<input type="hidden" name="option" value="com_vikrentcar"/>
		<?php echo JHtml::_('form.token'); ?>
	</form>
	
</div>

<script type="text/javascript">
function vrcFlushSession() {
	if (confirm('<?php echo addslashes(JText::_('VRCONFIGFLUSHSESSIONCONF')); ?>')) {
		location.href='index.php?option=com_vikrentcar&task=renewsession';
	} else {
		return false;
	}
}
jQuery(document).ready(function() {
	jQuery('dt.tabs').click(function() {
		var ptid = jQuery(this).attr('data-ptid');
		jQuery('dt.tabs').removeClass('open').addClass('closed');
		jQuery(this).removeClass('closed').addClass('open');
		jQuery('dd.tabs').hide();
		jQuery('dd#pt'+ptid).show();
		var nd = new Date();
		nd.setTime(nd.getTime() + (365*24*60*60*1000));
		document.cookie = "vrcConfPt="+ptid+"; expires=" + nd.toUTCString() + "; path=/; SameSite=Lax";
	});
});
</script>
