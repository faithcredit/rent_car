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

$carrows = $this->carrows;
$rows = $this->rows;
$prices = $this->prices;
$allc = $this->allc;

$vrc_app = new VrcApplication();
$vrc_app->loadSelect2();

$currencysymb = VikRentCar::getCurrencySymb(true);
$idcar = $carrows['id'];
$name = $carrows['name'];
if (is_file(VRC_ADMIN_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.$carrows['img']) && getimagesize(VRC_ADMIN_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.$carrows['img'])) {
	$img = '<img align="middle" class="maxninety" alt="Car Image" src="' . VRC_ADMIN_URI . 'resources/'.$carrows['img'].'" />';
} else {
	$img = '<i class="' . VikRentCarIcons::i('image', 'vrc-enormous-icn') . '"></i>';
}
?>

<div class="vrc-admin-container">
	<div class="vrc-config-maintab-left">
		<fieldset class="adminform">
			<div class="vrc-params-wrap">
				<legend class="adminlegend">
					<div class="vrc-quickres-head">
						<span><?php echo $name . " - " . JText::_('VRINSERTFEE'); ?></span>
						<div class="vrc-quickres-head-right">
							<form name="vrchcar" method="post" action="index.php?option=com_vikrentcar">
								<input type="hidden" name="task" value="tariffshours"/>
								<select name="cid[]" id="vrc-car-selection" onchange="javascript: document.vrchcar.submit();">
								<?php
								foreach ($allc as $cc) {
									?>
									<option value="<?php echo (int)$cc['id']; ?>"<?php echo $cc['id'] == $idcar ? ' selected="selected"' : ''; ?>><?php echo JHtml::_('esc_html', $cc['name']); ?></option>
									<?php
								}
								?>
								</select>
							</form>
						</div>
					</div>
				</legend>
				<div class="vrc-params-container vrc-tariffs-params-container">
					<div class="vrc-param-container">
						<div class="vrc-param-label">
							<div class="vrc-center">
								<?php echo $img; ?>
							</div>
						</div>
						<div class="vrc-param-setting">
							<div class="vrc-fares-tabs">
								<div class="dailyprices">
									<a href="index.php?option=com_vikrentcar&task=tariffs&cid[]=<?php echo $idcar; ?>"><?php echo JText::_('VRCDAILYFARES'); ?></a>
								</div>
								<div class="hourscharges">
									<a href="index.php?option=com_vikrentcar&task=hourscharges&cid[]=<?php echo $idcar; ?>"><?php echo JText::_('VRCHOURSCHARGES'); ?></a>
								</div>
								<div class="hourlypricesactive"><?php echo JText::_('VRCHOURLYFARES'); ?></div>
							</div>
						<?php
						if (empty($prices)) {
							?>
							<p class="err">
								<span><?php echo JText::_('VRMSGONE'); ?></span>
								<a href="index.php?option=com_vikrentcar&task=newprice"><?php echo JText::_('VRHERE'); ?></a>
							</p>
							<?php
						}
						?>
							<form name="newd" method="post" action="index.php?option=com_vikrentcar" onsubmit="javascript: if (!document.newd.hhoursfrom.value.match(/\S/)){alert('<?php echo addslashes(JText::_('VRMSGTWO')); ?>'); return false;} else {return true;}">
								<div class="vrc-insertrates-cont">
									<div class="vrc-insertrates-top">
										<div class="vrc-ratestable-lbl"><?php echo JText::_('VRCHOURS'); ?></div>
										<div class="vrc-ratestable-nights">
											<div class="vrc-ratestable-night-from">
												<span><?php echo JText::_('VRDAYSFROM'); ?></span>
												<input type="number" name="hhoursfrom" id="hhoursfrom" value="<?php echo !is_array($prices) ? '1' : ''; ?>" min="1" />
											</div>
											<div class="vrc-ratestable-night-to">
												<span><?php echo JText::_('VRDAYSTO'); ?></span>
												<input type="number" name="hhoursto" id="hhoursto" value="<?php echo !is_array($prices) ? '30' : ''; ?>" min="1" max="999" />
											</div>
										</div>
									</div>
									<div class="vrc-insertrates-bottom">
										<div class="vrc-ratestable-lbl"><?php echo JText::_('VRCHOURLYPRICES'); ?></div>
										<div class="vrc-ratestable-newprices">
									<?php
									if (is_array($prices)) {
										foreach ($prices as $pr) {
											?>
											<div class="vrc-ratestable-newprice">
												<span class="vrc-ratestable-newprice-name"><?php echo $pr['name']; ?></span>
												<span class="vrc-ratestable-newprice-cost">
													<span class="vrc-ratestable-newprice-cost-currency"><?php echo $currencysymb; ?></span>
													<span class="vrc-ratestable-newprice-cost-amount">
														<input type="number" min="0" step="any" name="hprice<?php echo $pr['id']; ?>" value=""/>
													</span>
												</span>
											<?php
											if (!empty($pr['attr'])) {
												?>
												<div class="vrc-ratestable-newprice-attribute">
													<span class="vrc-ratestable-newprice-name"><?php echo $pr['attr']; ?></span>
													<span class="vrc-ratestable-newprice-cost">
														<input type="text" name="hattr<?php echo $pr['id']; ?>" value="" size="10"/>
													</span>
												</div>
												<?php
											}
											?>
											</div>
											<?php
										}
									}
									?>
										</div>
									</div>
								</div>
								<div class="vrc-insertrates-save">
									<input type="submit" class="btn vrc-config-btn" name="newdispcost" value="<?php echo JHtml::_('esc_attr', JText::_('VRINSERT')); ?>"/>
									<input type="hidden" name="cid[]" value="<?php echo JHtml::_('esc_attr', $idcar); ?>"/>
									<input type="hidden" name="task" value="tariffshours"/>
								</div>
							</form>

						</div>
					</div>
				</div>
			</div>
		</fieldset>
	</div>

	<div class="vrc-config-maintab-right">
		<fieldset class="adminform">
			<div class="vrc-params-wrap">
				<div class="vrc-params-container vrc-list-table-container">
				<?php
				if (empty($rows)) {
					?>
					<p class="warn"><?php echo JText::_('VRNOTARFOUND'); ?></p>
					<form name="adminForm" id="adminForm" action="index.php" method="post">
						<input type="hidden" name="task" value="">
						<input type="hidden" name="option" value="com_vikrentcar">
					</form>
					<?php
				} else {
					$mainframe = JFactory::getApplication();
					$lim = $mainframe->getUserStateFromRequest("com_vikrentcar.limit", 'limit', 15, 'int');
					$lim0 = VikRequest::getVar('limitstart', 0, '', 'int');
					$allpr = array();
					$tottar = array();
					foreach ($rows as $r) {
						if (!array_key_exists($r['idprice'], $allpr)) {
							$allpr[$r['idprice']] = VikRentCar::getPriceAttr($r['idprice']);
						}
						$tottar[$r['hours']][] = $r;
					}
					$prord = array();
					$prvar = '';
					foreach ($allpr as $kap => $ap) {
						$prord[] = $kap;
						$prvar .= "<th class=\"title center\" width=\"150\">".VikRentCar::getPriceName($kap).(!empty($ap) ? " - ".$ap : "")."</th>\n";
					}
					$totrows = count($tottar);
					$tottar = array_slice($tottar, $lim0, $lim, true);
					?>
					<form action="index.php?option=com_vikrentcar" method="post" name="adminForm" id="adminForm" class="vrc-list-form">
						<div class="vrc-tariffs-updaterates-cont">
							<input type="submit" name="modtarhourscharges" value="<?php echo JHtml::_('esc_attr', JText::_('VRPVIEWTARTWO')); ?>" onclick="vrRateSetTask(event);" class="btn vrc-config-btn" />
						</div>
						<div class="table-responsive">
							<table cellpadding="4" cellspacing="0" border="0" width="100%" class="table table-striped vrc-list-table">
								<thead>
								<tr>
									<th width="20" class="title left">
										<input type="checkbox" onclick="Joomla.checkAll(this)" value="" name="checkall-toggle">
									</th>
									<th class="title left" width="100" style="text-align: left;"><?php echo JText::_('VRPVIEWTARONE'); ?></th>
									<?php echo $prvar; ?>
								</tr>
								</thead>
							<?php
							$k = 0;
							$i = 0;
							foreach ($tottar as $kt => $vt) {
								$multiid = "";
								foreach ($prord as $ord) {
									foreach ($vt as $kkkt => $vvv) {
										if ($vvv['idprice'] == $ord) {
											$multiid .= $vvv['id'].";";
											break;
										}
									}
								}
								?>
								<tr class="row<?php echo $k; ?>">
									<td class="left">
										<input type="checkbox" id="cb<?php echo $i;?>" name="cid[]" value="<?php echo JHtml::_('esc_attr', $multiid); ?>" onclick="Joomla.isChecked(this.checked);">
									</td>
									<td class="left"><?php echo $kt; ?></td>
								<?php
								foreach ($prord as $ord) {
									$thereis = false;
									foreach ($vt as $kkkt => $vvv) {
										if ($vvv['idprice'] == $ord) {
											echo "<td class=\"center\"><input type=\"number\" min=\"0\" step=\"any\" name=\"cost".$vvv['id']."\" value=\"".$vvv['cost']."\" />".(!empty($vvv['attrdata'])? " - <input type=\"text\" name=\"attr".$vvv['id']."\" value=\"".$vvv['attrdata']."\" size=\"10\"/>" : "")."</td>\n";
											$thereis = true;
											break;
										}
									}
									if (!$thereis) {
										echo "<td></td>\n";
									}
									unset($thereis);
								}
								?>
								</tr>
								<?php
								unset($multiid);
								$k = 1 - $k;
								$i++;
							}
							?>
							</table>
						</div>
						<input type="hidden" name="carid" value="<?php echo (int)$carrows['id']; ?>" />
						<input type="hidden" name="cid[]" value="<?php echo (int)$carrows['id']; ?>" />
						<input type="hidden" name="option" value="com_vikrentcar" />
						<input type="hidden" name="task" id="vrtask" value="tariffshours" />
						<input type="hidden" name="tarmodhours" id="vrtarmod" value="" />
						<input type="hidden" name="boxchecked" value="0" />
						<?php echo JHTML::_( 'form.token' ); ?>
						<?php
						jimport('joomla.html.pagination');
						$pageNav = new JPagination( $totrows, $lim0, $lim );
						$navbut = "<table align=\"center\"><tr><td>".$pageNav->getListFooter()."</td></tr></table>";
						echo $navbut;
						?>
					</form>
					<?php
					}
					?>
				</div>
			</div>
		</fieldset>
	</div>
</div>

<script type="text/javascript">
jQuery(document).ready(function() {
	jQuery('#hhoursfrom').change(function() {
		var fnights = parseInt(jQuery(this).val());
		if (!isNaN(fnights)) {
			jQuery('#hhoursto').attr('min', fnights);
			var tnights = jQuery('#hhoursto').val();
			if (!(tnights.length > 0)) {
				jQuery('#hhoursto').val(fnights);
			} else {
				if (parseInt(tnights) < fnights) {
					jQuery('#hhoursto').val(fnights);
				}
			}
		}
	});
	jQuery("#vrc-car-selection").select2();
});
function vrRateSetTask(event) {
	event.preventDefault();
	document.getElementById('vrtarmod').value = '1';
	document.getElementById('vrtask').value = 'cars';
	document.adminForm.submit();
}
</script>
