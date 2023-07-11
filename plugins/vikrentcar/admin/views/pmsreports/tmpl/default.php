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

$report_objs = $this->report_objs;
$country_objs = $this->country_objs;
$countries = $this->countries;

$vrc_app = new VrcApplication();
$vrc_app->loadSelect2();
$preport = VikRequest::getString('report', '', 'request');
$pkrsort = VikRequest::getString('krsort', '', 'request');
$pkrorder = VikRequest::getString('krorder', '', 'request');
$execreport = VikRequest::getString('execreport', '', 'request');
$execreport = !empty($execreport);
$pexportreport = VikRequest::getInt('exportreport', 0, 'request');
$report_obj = null;
if ($pexportreport > 0 && $execreport) {
	//if report requested, call the exportCSV() method before outputting any HMTL code
	foreach ($report_objs as $obj) {
		if ($obj->getFileName() == $preport) {
			if (method_exists($obj, 'customExport')) {
				$obj->customExport($pexportreport);
				break;
			}
			if (method_exists($obj, 'exportCSV')) {
				$obj->exportCSV();
				break;
			}
		}
	}
	foreach ($country_objs as $cobj) {
		foreach ($cobj as $obj) {
			if ($obj->getFileName() == $preport) {
				if (method_exists($obj, 'customExport')) {
					$obj->customExport($pexportreport);
					break;
				}
				if (method_exists($obj, 'exportCSV')) {
					$obj->exportCSV();
					break;
				}
			}
		}
	}
}
?>

<div class="vrc-info-overlay-block">
	<a class="vrc-info-overlay-close" href="javascript: void(0);"></a>
	<div class="vrc-info-overlay-content vrc-info-overlay-report"></div>
</div>

<div class="vrc-reports-container">
	<form name="adminForm" action="index.php?option=com_vikrentcar&task=pmsreports" method="post" enctype="multipart/form-data" id="adminForm">
		<div class="vrc-reports-filters-outer vrc-btn-toolbar">
			<div class="vrc-reports-filters-main">
				<select id="choose-report" name="report" onchange="document.adminForm.submit();">
					<option value=""></option>
				<?php
				foreach ($report_objs as $obj) {
					$opt_active = false;
					if ($obj->getFileName() == $preport) {
						//get current report object
						$report_obj = $obj;
						//
						$opt_active = true;
					}
					?>
					<option value="<?php echo JHtml::_('esc_attr', $obj->getFileName()); ?>"<?php echo $opt_active ? ' selected="selected"' : ''; ?>><?php echo JHtml::_('esc_html', $obj->getName()); ?></option>
					<?php
				}
				foreach ($country_objs as $ccode => $cobj) {
					?>
					<optgroup label="<?php echo $countries[$ccode]; ?>">
					<?php
					foreach ($cobj as $obj) {
						$opt_active = false;
						if ($obj->getFileName() == $preport) {
							//get current report object
							$report_obj = $obj;
							//
							$opt_active = true;
						}
						?>
						<option value="<?php echo JHtml::_('esc_attr', $obj->getFileName()); ?>"<?php echo $opt_active ? ' selected="selected"' : ''; ?>><?php echo JHtml::_('esc_html', $obj->getName()); ?></option>
						<?php
					}
					?>
					</optgroup>
					<?php
				}
				?>
				</select>
			</div>
		<?php
		$report_filters = $report_obj !== null ? $report_obj->getFilters() : array();
		if (count($report_filters)) {
			?>
			<div class="vrc-reports-filters-report">
			<?php
			foreach ($report_filters as $filt) {
				?>
				<div class="vrc-report-filter-wrap">
				<?php
				if (isset($filt['label']) && !empty($filt['label'])) {
					?>
					<div class="vrc-report-filter-lbl">
						<span><?php echo $filt['label']; ?></span>
					</div>
					<?php
				}
				if (isset($filt['html']) && !empty($filt['html'])) {
					?>
					<div class="vrc-report-filter-val">
						<?php echo $filt['html']; ?>
					</div>
					<?php
				}
				?>
				</div>
				<?php
			}
			?>
			</div>
			<?php
		}
		if ($report_obj !== null) {
			?>
			<div class="vrc-reports-filters-launch">
				<input type="submit" class="btn" name="execreport" value="<?php echo JHtml::_('esc_attr', JText::_('VRCREPORTLOAD')); ?>" />
			</div>
			<?php
			if ($execreport && property_exists($report_obj, 'exportAllowed') && $report_obj->exportAllowed) {
			?>
			<div class="vrc-reports-filters-export">
				<a href="JavaScript: void(0);" onclick="vrcDoExport();" class="vrccsvexport"><?php VikRentCarIcons::e('table'); ?> <span><?php echo JText::_('VRCREPORTCSVEXPORT'); ?></span></a>
			</div>
			<?php
			} elseif ($execreport && property_exists($report_obj, 'customExport')) {
			?>
			<div class="vrc-reports-filters-export">
				<?php echo $report_obj->customExport; ?>
			</div>
			<?php
			}
		}
		?>
		</div>
		<div id="vrc_hidden_fields"></div>
		<input type="hidden" name="krsort" value="<?php echo JHtml::_('esc_attr', $pkrsort); ?>" />
		<input type="hidden" name="krorder" value="<?php echo JHtml::_('esc_attr', $pkrorder); ?>" />
		<input type="hidden" name="e4j_debug" value="<?php echo VikRequest::getInt('e4j_debug', 0, 'request'); ?>" />
		<input type="hidden" name="task" value="pmsreports" />
		<input type="hidden" name="option" value="com_vikrentcar" />
	</form>
<?php
if ($report_obj !== null && $execreport) {
	// execute the report
	$res = $report_obj->getReportData();
	// get the report Chart (if any)
	$report_chart = $report_obj->getChart();

	if ($res && !empty($report_chart)) {
		// display the layout type choice
		?>
	<div class="vrc-report-layout-type">
		<div class="vrc-report-layout-type-inner">
			<label for="vrc-report-layout"><?php VikRentCarIcons::e('chart-line'); ?></label>
			<select id="vrc-report-layout" onchange="vrcSetReportLayout(this.value);">
				<option value="sheetnchart"><?php echo JText::_('VRCSHEETNCHART'); ?></option>
				<option value="sheet"><?php echo JText::_('VRCSHEETONLY'); ?></option>
				<option value="chart"><?php echo JText::_('VRCCHARTONLY'); ?></option>
			</select>
		</div>
	</div>
		<?php
	}
	?>
	<div class="vrc-reports-output <?php echo empty($report_chart) ? 'vrc-report-sheetonly' : 'vrc-report-sheetnchart'; ?> vrc-report-output-<?php echo $report_obj->getFileName(); ?>">
	<?php
	if (!$res) {
		// error generating the report
		?>
		<p class="err"><?php echo $report_obj->getError(); ?></p>
		<?php
	} else {
		// display the report and set default ordering and sorting
		if (empty($pkrsort) && property_exists($report_obj, 'defaultKeySort')) {
			$pkrsort = $report_obj->defaultKeySort;
		}
		if (empty($pkrorder) && property_exists($report_obj, 'defaultKeyOrder')) {
			$pkrorder = $report_obj->defaultKeyOrder;
		}
		if (strlen($report_obj->getWarning())) {
			// warning message should not stop the report from rendering
			?>
		<p class="warn"><?php echo $report_obj->getWarning(); ?></p>
			<?php
		}

		// parse the classic sheet of the report
		?>
		<div class="vrc-report-sheet">
			<div class="table-responsive">
				<table class="table">
					<thead>
						<tr>
						<?php
						foreach ($report_obj->getReportCols() as $col) {
							$col_cont = (isset($col['tip']) ? $vrc_app->createPopover(array('title' => $col['label'], 'content' => $col['tip'])) : '').$col['label'];
							?>
							<th<?php echo isset($col['attr']) && count($col['attr']) ? ' '.implode(' ', $col['attr']) : ''; ?>>
							<?php
							if (isset($col['sortable'])) {
								$krorder = $pkrsort == $col['key'] && $pkrorder == 'DESC' ? 'ASC' : 'DESC';
								?>
								<a href="JavaScript: void(0);" onclick="vrcSetFilters({krsort: '<?php echo $col['key']; ?>', krorder: '<?php echo $krorder; ?>'}, true);" class="<?php echo $pkrsort == $col['key'] ? 'vrc-list-activesort' : ''; ?>">
									<span><?php echo $col_cont; ?></span>
									<i class="fa <?php echo $pkrsort == $col['key'] && $krorder == 'DESC' ? 'fa-sort-asc' : ($pkrsort == $col['key'] ? 'fa-sort-desc' : 'fa-sort'); ?>"></i>
								</a>
								<?php
							} else {
								?>
								<span><?php echo $col_cont; ?></span>
								<?php
							}
							?>
							</th>
							<?php
						}
						?>
						</tr>
					</thead>
					<tbody>
					<?php
					foreach ($report_obj->getReportRows() as $row) {
						?>
						<tr>
						<?php
						foreach ($row as $cell) {
							?>
							<td<?php echo isset($cell['attr']) && count($cell['attr']) ? ' '.implode(' ', $cell['attr']) : ''; ?>>
								<span><?php echo isset($cell['callback']) && is_callable($cell['callback']) ? $cell['callback']($cell['value']) : $cell['value']; ?></span>
							</td>
							<?php
						}
						?>
						</tr>
						<?php
					}
					?>
					</tbody>
				<?php
				if (count($report_obj->getReportFooterRow())) {
					?>
					<tfoot>
					<?php
					foreach ($report_obj->getReportFooterRow() as $row) {
						?>
						<tr>
						<?php
						foreach ($row as $cell) {
							?>
							<td<?php echo isset($cell['attr']) && count($cell['attr']) ? ' '.implode(' ', $cell['attr']) : ''; ?>>
								<span><?php echo isset($cell['callback']) && is_callable($cell['callback']) ? $cell['callback']($cell['value']) : $cell['value']; ?></span>
							</td>
							<?php
						}
						?>
						</tr>
						<?php
					}
					?>
					</tfoot>
					<?php
				}
				?>
				</table>
			</div>
		</div>
		<?php

		// parse the Chart, if defined
		if (!empty($report_chart)) {
			$report_chart_title = $report_obj->getChartTitle();
			?>
		<div class="vrc-report-chart-wrap">
			<div class="vrc-report-chart-inner">
				<div class="vrc-report-chart-main">
				<?php
				$top_chart_metas = $report_obj->getChartMetaData('top');
				if (is_array($top_chart_metas) && count($top_chart_metas)) {
					?>
					<div class="vrc-report-chart-metas vrc-report-chart-metas-top">
					<?php
					foreach ($top_chart_metas as $chart_meta) {
						?>
						<div class="vrc-report-chart-meta<?php echo isset($chart_meta['class']) ? ' ' . $chart_meta['class'] : ''; ?>">
							<div class="vrc-report-chart-meta-inner">
								<div class="vrc-report-chart-meta-lbl"><?php echo isset($chart_meta['label']) ? $chart_meta['label'] : ''; ?></div>
								<div class="vrc-report-chart-meta-val">
									<span class="vrc-report-chart-meta-val-main"><?php echo isset($chart_meta['value']) ? $chart_meta['value'] : ''; ?></span>
								<?php
								if (isset($chart_meta['descr'])) {
									?>
									<span class="vrc-report-chart-meta-val-descr"><?php echo $chart_meta['descr']; ?></span>
									<?php
								}
								?>
									<?php echo isset($chart_meta['extra']) ? $chart_meta['extra'] : ''; ?>
								</div>
							</div>
						</div>
						<?php
					}
					?>
					</div>
					<?php
				}
				?>
					<div class="vrc-report-chart-content">
					<?php
					if (!empty($report_chart_title)) {
						?>
						<h4><?php echo $report_chart_title; ?></h4>
						<?php
					}
					?>
						<?php echo $report_chart; ?>
					</div>
				<?php
				$bottom_chart_metas = $report_obj->getChartMetaData('bottom');
				if (is_array($bottom_chart_metas) && count($bottom_chart_metas)) {
					?>
					<div class="vrc-report-chart-metas vrc-report-chart-metas-bottom">
					<?php
					foreach ($bottom_chart_metas as $chart_meta) {
						?>
						<div class="vrc-report-chart-meta<?php echo isset($chart_meta['class']) ? ' ' . $chart_meta['class'] : ''; ?>">
							<div class="vrc-report-chart-meta-inner">
								<div class="vrc-report-chart-meta-lbl"><?php echo isset($chart_meta['label']) ? $chart_meta['label'] : ''; ?></div>
								<div class="vrc-report-chart-meta-val">
									<span class="vrc-report-chart-meta-val-main"><?php echo isset($chart_meta['value']) ? $chart_meta['value'] : ''; ?></span>
								<?php
								if (isset($chart_meta['descr'])) {
									?>
									<span class="vrc-report-chart-meta-val-descr"><?php echo $chart_meta['descr']; ?></span>
									<?php
								}
								?>
									<?php echo isset($chart_meta['extra']) ? $chart_meta['extra'] : ''; ?>
								</div>
							</div>
						</div>
						<?php
					}
					?>
					</div>
					<?php
				}
				?>
				</div>
			<?php
			$right_chart_metas = $report_obj->getChartMetaData('right');
			if (is_array($right_chart_metas) && count($right_chart_metas)) {
				?>
				<div class="vrc-report-chart-right">
					<div class="vrc-report-chart-metas vrc-report-chart-metas-right">
					<?php
					foreach ($right_chart_metas as $chart_meta) {
						?>
						<div class="vrc-report-chart-meta<?php echo isset($chart_meta['class']) ? ' ' . $chart_meta['class'] : ''; ?>">
							<div class="vrc-report-chart-meta-inner">
								<div class="vrc-report-chart-meta-lbl"><?php echo isset($chart_meta['label']) ? $chart_meta['label'] : ''; ?></div>
								<div class="vrc-report-chart-meta-val">
									<span class="vrc-report-chart-meta-val-main"><?php echo isset($chart_meta['value']) ? $chart_meta['value'] : ''; ?></span>
								<?php
								if (isset($chart_meta['descr'])) {
									?>
									<span class="vrc-report-chart-meta-val-descr"><?php echo $chart_meta['descr']; ?></span>
									<?php
								}
								?>
									<?php echo isset($chart_meta['extra']) ? $chart_meta['extra'] : ''; ?>
								</div>
							</div>
						</div>
						<?php
					}
					?>
					</div>
				</div>
				<?php
			}
			?>
			</div>
		</div>
			<?php
		}
	}
	?>
	</div>
	<?php
}
?>
</div>

<script type="text/javascript">
function vrcSetFilters(obj, dosubmit) {
	if (typeof obj != "object") {
		console.log("arg is not an object");
		return;
	}
	for (var p in obj) {
		if (!obj.hasOwnProperty(p)) {
			continue;
		}
		var elem = document.adminForm[p];
		if (elem) {
			document.adminForm[p].value = obj[p];
		} else {
			document.getElementById("vrc_hidden_fields").innerHTML += "<input type='hidden' name='"+p+"' value='"+obj[p]+"' />";
		}
	}
	if (!obj.hasOwnProperty('execreport')) {
		document.getElementById("vrc_hidden_fields").innerHTML += "<input type='hidden' name='execreport' value='1' />";
	}
	if (dosubmit) {
		document.adminForm.submit();
	}
}
function vrcDoExport() {
	document.adminForm.target = '_blank';
	document.adminForm.action += '&tmpl=component';
	vrcSetFilters({exportreport: '1'}, true);
	setTimeout(function() {
		document.adminForm.target = '';
		document.adminForm.action = document.adminForm.action.replace('&tmpl=component', '');
		vrcSetFilters({exportreport: '0'}, false);
	}, 1000);
}
var vrc_overlay_on = false;
function vrcShowOverlay() {
	jQuery(".vrc-info-overlay-block").fadeIn(400, function() {
		if (jQuery(".vrc-info-overlay-block").is(":visible")) {
			vrc_overlay_on = true;
		} else {
			vrc_overlay_on = false;
		}
	});
}
function vrcHideOverlay() {
	jQuery(".vrc-info-overlay-block").fadeOut();
	vrc_overlay_on = false;
}
function vrcSetReportLayout(layout) {
	if (layout == 'sheet') {
		jQuery('.vrc-reports-output').removeClass('vrc-report-sheetnchart').removeClass('vrc-report-chartonly').addClass('vrc-report-sheetonly');
		jQuery('.vrc-report-chart-wrap').hide();
		jQuery('.vrc-report-sheet').show();
	} else if (layout == 'chart') {
		jQuery('.vrc-reports-output').removeClass('vrc-report-sheetnchart').removeClass('vrc-report-sheetonly').addClass('vrc-report-chartonly');
		jQuery('.vrc-report-sheet').hide();
		jQuery('.vrc-report-chart-wrap').show();
	} else if (layout == 'sheetnchart') {
		jQuery('.vrc-reports-output').removeClass('vrc-report-sheetonly').removeClass('vrc-report-chartonly').addClass('vrc-report-sheetnchart');
		jQuery('.vrc-report-sheet').show();
		jQuery('.vrc-report-chart-wrap').show();
	}
}
jQuery(document).ready(function() {
	jQuery("#choose-report").select2({placeholder: '<?php echo addslashes(JText::_('VRCREPORTSELECT')); ?>', width: "200px"});
	jQuery(document).mouseup(function(e) {
		if (!vrc_overlay_on) {
			return false;
		}
		var vrc_overlay_cont = jQuery(".vrc-info-overlay-content");
		if (!vrc_overlay_cont.is(e.target) && vrc_overlay_cont.has(e.target).length === 0) {
			vrcHideOverlay();
		}
	});
	jQuery(document).keyup(function(e) {
		if (e.keyCode == 27 && vrc_overlay_on) {
			vrcHideOverlay();
		}
	});
});
<?php echo $report_obj !== null && strlen($report_obj->getScript()) ? $report_obj->getScript() : ''; ?>
</script>
