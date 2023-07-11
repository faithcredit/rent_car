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
$name = $this->carrows['name'];
$currencysymb = VikRentCar::getCurrencySymb(true);

?>
<div class="vrc-info-overlay-block vrc-info-overlay-block-animation">
	<a class="vrc-info-overlay-close" href="javascript: void(0);"></a>
	<div class="vrc-info-overlay-content vrc-info-overlay-content-wizard vrc-info-overlay-content-hidden">
		<h3><?php echo "{$name} - " . JText::_('VRINSERTFEE'); ?></h3>
		<div class="vrc-overlay-wizard-wrap">
			<form method="post" action="index.php?option=com_vikrentcar">
				<div class="vrc-tariffs-wizard-help-wrap">
					<p>
						<span><?php echo JText::_('VRCWIZARDTARIFFSMESS'); ?></span>
						<?php echo $vrc_app->createPopover(array('title' => JText::_('VRINSERTFEE'), 'content' => JText::_('VRCWIZARDTARIFFSHELP'), 'placement' => 'bottom')); ?>
					</p>
					<h4><?php echo JText::_('VRCWIZARDTARIFFSWHTC'); ?></h4>
				</div>
				<div class="vrc-tariffs-wizard-prices-wrap">
				<?php
				foreach ($this->prices as $pr) {
					?>
					<div class="vrc-tariffs-wizard-price">
						<span class="vrc-tariffs-wizard-price-name"><?php echo $pr['name']; ?></span>
						<span class="vrc-tariffs-wizard-price-cost">
							<span class="vrc-tariffs-wizard-price-cost-currency"><?php echo $currencysymb; ?></span>
							<span class="vrc-tariffs-wizard-price-cost-amount">
								<input type="number" min="1" step="any" name="dprice<?php echo $pr['id']; ?>" value=""/>
							</span>
						</span>
					</div>
					<?php
				}
				?>
				</div>
				<div class="vrc-tariffs-wizard-prices-submit">
					<input type="submit" class="btn btn-success" name="newdispcost" value="<?php echo JHtml::_('esc_attr', JText::_('VRINSERTFEE')); ?>"/>
				</div>
				<input type="hidden" name="task" value="tariffs" />
				<input type="hidden" name="ddaysfrom" value="1" />
				<input type="hidden" name="ddaysto" value="30" />
				<input type="hidden" name="cid[]" value="<?php echo (int)$this->carrows['id']; ?>" />
			</form>
		</div>
	</div>
</div>

<script type="text/javascript">
var vrcdialog_on = false;
function hideVrcWizard() {
	if (vrcdialog_on === true) {
		jQuery(".vrc-info-overlay-block").fadeOut(400, function () {
			jQuery(".vrc-info-overlay-content").hide().addClass("vrc-info-overlay-content-hidden").removeClass("vrc-info-overlay-content-animated");
		});
		vrcdialog_on = false;
	}
}
function showVrcWizard() {
	jQuery(".vrc-info-overlay-block").fadeIn(400, function () {
		jQuery(".vrc-info-overlay-content").show().addClass("vrc-info-overlay-content-animated").removeClass("vrc-info-overlay-content-hidden");
	});
	vrcdialog_on = true;
}
jQuery(document).ready(function() {
<?php
if (empty($this->rows)) {
	?>
	showVrcWizard();
	<?php
}
?>
	// modal handling
	jQuery(document).keydown(function(e) {
		if (e.keyCode == 27) {
			hideVrcWizard();
		}
	});
	jQuery(document).mouseup(function(e) {
		if (!vrcdialog_on) {
			return false;
		}
		var vrc_overlay_cont = jQuery(".vrc-info-overlay-content");
		if (!vrc_overlay_cont.is(e.target) && vrc_overlay_cont.has(e.target).length === 0) {
			hideVrcWizard();
		}
	});
});
</script>
