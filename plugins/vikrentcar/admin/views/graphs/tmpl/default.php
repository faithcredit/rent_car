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

$bookings = $this->bookings;
$arr_cars = $this->arr_cars;
$fromts = $this->fromts;
$tots = $this->tots;
$pstatsmode = $this->pstatsmode;
$arr_months = $this->arr_months;
$arr_channels = $this->arr_channels;
$arr_countries = $this->arr_countries;
$arr_totals = $this->arr_totals;
$tot_cars_units = $this->tot_cars_units;

$vrc_app = new VrcApplication();
$pid_car = VikRequest::getInt('id_car', '', 'request');
$df = VikRentCar::getDateFormat(true);
if ($df == "%d/%m/%Y") {
	$usedf = 'd/m/Y';
} elseif ($df == "%m/%d/%Y") {
	$usedf = 'm/d/Y';
} else {
	$usedf = 'Y/m/d';
}
$currencysymb = VikRentCar::getCurrencySymb(true);
$days_diff = (int)floor(($tots - $fromts) / 86400);
?>
<form action="index.php?option=com_vikrentcar&amp;task=graphs" id="vrc-statsform" method="post" style="margin: 0;" class="vrc-stats-topform">
	<div id="filter-bar" class="btn-toolbar vrc-btn-toolbar" style="width: 100%; display: inline-block;">
		<div class="btn-group pull-left">
			<select name="statsmode" onchange="document.getElementById('vrc-statsform').submit();">
				<option value="ts"<?php echo $pstatsmode == 'ts' ? ' selected="selected"' : ''; ?>><?php echo JText::_('VRCSTATSMODETS'); ?></option>
				<option value="nights"<?php echo $pstatsmode == 'nights' ? ' selected="selected"' : ''; ?>><?php echo JText::_('VRCSTATSMODENIGHTS'); ?></option>
			</select>
		</div>
		<div class="btn-group pull-right">
			&nbsp;<button type="submit" class="btn"><?php echo JText::_('VRCORDERSLOCFILTERBTN'); ?></button>
		</div>
		<div class="btn-group pull-right">
			<select name="id_car">
				<option value=""><?php echo JText::_('VRCSTATSALLCARS'); ?></option>
			<?php
			foreach ($arr_cars as $car) {
				?>
				<option value="<?php echo (int)$car['id']; ?>"<?php echo $car['id'] == $pid_car ? ' selected="selected"' : ''; ?>><?php echo JHtml::_('esc_html', $car['name']); ?></option>
				<?php
			}
			?>
			</select>
		</div>
		<div class="btn-group pull-right">
			<?php echo $vrc_app->getCalendar(date($usedf, $tots), 'dto', 'dto', $df, array('class'=>'', 'size'=>'10', 'maxlength'=>'19', 'todayBtn' => 'true')); ?>
		</div>
		<div class="btn-group pull-right">
			<?php echo $vrc_app->getCalendar(date($usedf, $fromts), 'dfrom', 'dfrom', $df, array('class'=>'', 'size'=>'10', 'maxlength'=>'19', 'todayBtn' => 'true')); ?>
		</div>
	</div>
</form>

<script type="text/javascript">
jQuery(document).ready(function() {
	jQuery('#dfrom').val('<?php echo date($usedf, $fromts); ?>').attr('data-alt-value', '<?php echo date($usedf, $fromts); ?>');
	jQuery('#dto').val('<?php echo date($usedf, $tots); ?>').attr('data-alt-value', '<?php echo date($usedf, $tots); ?>');
});
</script>

<?php
$months_map = array(
	'1' => JText::_('VRSHORTMONTHONE'),
	'2' => JText::_('VRSHORTMONTHTWO'),
	'3' => JText::_('VRSHORTMONTHTHREE'),
	'4' => JText::_('VRSHORTMONTHFOUR'),
	'5' => JText::_('VRSHORTMONTHFIVE'),
	'6' => JText::_('VRSHORTMONTHSIX'),
	'7' => JText::_('VRSHORTMONTHSEVEN'),
	'8' => JText::_('VRSHORTMONTHEIGHT'),
	'9' => JText::_('VRSHORTMONTHNINE'),
	'10' => JText::_('VRSHORTMONTHTEN'),
	'11' => JText::_('VRSHORTMONTHELEVEN'),
	'12' => JText::_('VRSHORTMONTHTWELVE')
);
if (!(count($bookings) > 0) || !(count($arr_months) > 0)) {
	?>
<p class="warn"><?php echo JText::_('VRNOBOOKINGSTATS'); ?></p>
<form name="adminForm" id="adminForm" action="index.php" method="post">
	<input type="hidden" name="task" value="">
	<input type="hidden" name="option" value="com_vikrentcar" />
</form>
	<?php
} else {
	$datasets = array();
	$donut_datasets = array();
	$nights_datasets = array();
	$nights_donut_datasets = array();
	$months_labels = array_keys($arr_months);
	foreach ($months_labels as $mlbk => $mlbv) {
		$mlb_parts = explode('-', $mlbv);
		$months_labels[$mlbk] = $months_map[$mlb_parts[0]].' '.$mlb_parts[1];
	}
	$tot_months = count($months_labels);
	$tot_channels = count($arr_channels);
	$cars_pool = array();
	foreach ($bookings as $bk => $bv) {
		if (array_key_exists('car_names', $bv) && count($bv['car_names']) > 0) {
			foreach ($bv['car_names'] as $r) {
				if (!in_array($r, $cars_pool)) {
					$cars_pool[] = $r;
				}
			}
		}
	}
	$tot_cars = count($cars_pool);
	$rand_max = $tot_channels + $tot_cars;
	$rgb_rand = array();
	for ($z = 0; $z < $rand_max; $z++) { 
		$rgb_rand[$z] = mt_rand(0, 255).','.mt_rand(0, 255).','.mt_rand(0, 255);
	}
	$known_ch_rgb = array(
		JText::_('VRCWEBSITECHANNEL') => '34,72,93',
	);
	$ch_dataset = array();
	$ch_donut_dataset = array();
	$ch_map = array();
	foreach ($arr_channels as $chname) {
		$ch_color = $rgb_rand[rand(0, ($tot_channels - 1))];
		if (array_key_exists(strtolower($chname), $known_ch_rgb)) {
			$ch_color = $known_ch_rgb[strtolower($chname)];
		} else {
			foreach ($known_ch_rgb as $kch => $krgb) {
				if (stripos($chname, $kch) !== false) {
					$ch_color = $krgb;
					break;
				}
			}
		}
		$ch_dataset[$chname] = array(
			'label' => $chname,
			'backgroundColor' => "rgba(".$ch_color.",0.2)",
			'borderColor' => "rgba(".$ch_color.",1)",
			'pointBackgroundColor' => "rgba(".$ch_color.",1)",
			'pointBorderColor' => "#fff",
			'pointHoverBackgroundColor' => "#fff",
			'pointHoverBorderColor' => "rgba(".$ch_color.",1)",
			'pointRadius' => 4,
			'pointHoverRadius' => 5,
			'tot_bookings' => 0,
			'data' => array()
		);
		$ch_donut_dataset[$chname] = array(
			'label' => $chname,
			'backgroundColor' => "rgba(".$ch_color.",1)",
			'hoverBorderColor' => "rgba(".$ch_color.",0.9)",
			'value' => 0
		);
		$ch_map[$chname] = $chname;
	}
	$ch_nights_dataset = array(
		'label' => JText::_('VRCGRAPHTOTNIGHTSLBL'),
		'backgroundColor' => "rgba(34,72,93,0.2)",
		'borderColor' => "rgba(34,72,93,1)",
		'pointBackgroundColor' => "rgba(34,72,93,1)",
		'pointBorderColor' => "#fff",
		'pointHoverBackgroundColor' => "#fff",
		'pointHoverBorderColor' => "rgba(34,72,93,1)",
		'pointRadius' => 4,
		'pointHoverRadius' => 5,
		'tot_nights' => 0,
		'data' => array()
	);
	$ch_nights_donut_dataset = array();
	foreach ($cars_pool as $rpk => $r) {
		$ch_color = $rgb_rand[($tot_channels + $rpk)];
		$ch_nights_donut_dataset[$r] = array(
			'label' => $r,
			'backgroundColor' => "rgba(".$ch_color.",1)",
			'hoverBorderColor' => "rgba(".$ch_color.",0.9)",
			'value' => 0
		);
	}
	foreach ($arr_months as $monyear => $chbookings) {
		$tot_monchannels = count($chbookings);
		$monchannels = array();
		$totnb = 0;
		foreach ($chbookings as $chname => $ords) {
			$monchannels[] = $chname;
			$totchb = 0;
			foreach ($ords as $ord) {
				$totchb += (float)$ord['order_total'];
				$totnb += $ord['days'];
				if (array_key_exists('car_names', $ord)) {
					foreach ($ord['car_names'] as $r) {
						if (array_key_exists($r, $ch_nights_donut_dataset)) {
							$ch_nights_donut_dataset[$r]['value'] += $ord['days'];
						}
					}
				}
			}
			$ch_dataset[$chname]['tot_bookings'] += count($ords);
			$ch_dataset[$chname]['data'][] = $totchb;
			$ch_donut_dataset[$chname]['value'] += $totchb;
		}
		$ch_nights_dataset['tot_nights'] += $totnb;
		$ch_nights_dataset['data'][] = $totnb;
		if ($tot_monchannels < $tot_channels) {
			$ch_missing = array_diff($ch_map, $monchannels);
			foreach ($ch_missing as $chnk => $chnv) {
				if (array_key_exists($chnv, $ch_dataset)) {
					$ch_dataset[$chnv]['data'][] = 0;
				}
			}
		}
	}
	foreach ($ch_dataset as $chname => $chgraph) {
		$chgraph['label'] = $chgraph['label'].' ('.$chgraph['tot_bookings'].')';
		unset($chgraph['tot_bookings']);
		$datasets[] = $chgraph;
	}
	foreach ($ch_donut_dataset as $chname => $chgraph) {
		$donut_datasets[] = $chgraph;
	}
	$nights_datasets[] = $ch_nights_dataset;
	//Sort the array depending on the number of days sold per car
	$nights_donut_sortmap = array();
	foreach ($ch_nights_donut_dataset as $rname => $rgraph) {
		// round values
		$rounded_val = round($rgraph['value'], 2);
		$ch_nights_donut_dataset[$rname]['value'] = $rounded_val;
		$rgraph['value'] = $rounded_val;
		//
		$nights_donut_sortmap[$rname] = $rgraph['value'];
	}
	arsort($nights_donut_sortmap);
	$copy_nights_donut = $ch_nights_donut_dataset;
	$ch_nights_donut_dataset = array();
	foreach ($nights_donut_sortmap as $rname => $soldnights) {
		$ch_nights_donut_dataset[$rname] = $copy_nights_donut[$rname];
	}
	unset($copy_nights_donut);
	//end Sort
	foreach ($ch_nights_donut_dataset as $rname => $rgraph) {
		$nights_donut_datasets[] = $rgraph;
	}
	?>
<div class="vrc-stats-wrapper">
	<form name="adminForm" id="adminForm" action="index.php" method="post">
		<fieldset class="adminform">
			<legend class="adminlegend"><?php echo JText::sprintf('VRCSTATSFOR', count($bookings), $days_diff); ?></legend>
			<div class="vrc-graph-top-wrapper">
				<div class="vrc-graph-introtitle">
					<span><?php echo JText::_('VRCGRAPHTOTSALES'); ?></span>
				</div>
				<div class="vrc-graph-top-inner">
					<div class="vrc-graph-top-left">
						<div class="vrc-graphstats-left">
							<canvas id="vrc-graphstats-left-canv"></canvas>
						</div>
					</div>
					<div class="vrc-graph-top-right">
						<div class="vrc-graphstats-secondright">
							<h4><?php echo JText::_('VRCSTATSTOPCOUNTRIES'); ?></h4>
							<div class="vrc-graphstats-countries">
							<?php
							$clisted = 0;
							foreach ($arr_countries as $ccode => $cdata) {
								if ($clisted > 4) {
									break;
								}
								?>
								<div class="vrc-graphstats-country-wrap">
									<span class="vrc-graphstats-country-img"><?php echo $cdata['img']; ?></span>
									<span class="vrc-graphstats-country-name"><?php echo $cdata['country_name']; ?></span>
									<span class="vrc-graphstats-country-totb badge"><?php echo $cdata['tot_bookings']; ?></span>
								</div>
								<?php
								$clisted++;
							}
							?>
							</div>
						</div>
						<div class="vrc-graphstats-thirdright">
							<p class="vrc-graphstats-income">
								<span><?php echo $pstatsmode == 'nights' ? $vrc_app->createPopover(array('title' => JText::_('VRCSTATSTOTINCOME'), 'content' => JText::_('VRCGRAPHAVGVALUES'), 'icon_class' => VikRentCarIcons::i('info-circle'))).'&nbsp;' : ''; ?><?php echo JText::_('VRCSTATSTOTINCOME'); ?></span> <?php echo $currencysymb.' '.VikRentCar::numberFormat($arr_totals['total_income']); ?>
							</p>
						</div>
					</div>
				</div>
			</div>
		<?php
		if ($pstatsmode == 'nights') {
			$tot_occ_pcent = round((100 * $arr_totals['nights_sold'] / ($tot_cars_units * $days_diff)), 3);
			?>
			<div class="vrc-graph-bottom-wrapper">
				<div class="vrc-graph-introtitle">
					<span><?php echo JText::sprintf('VRCGRAPHTOTNIGHTS', $arr_totals['nights_sold']); ?> - <?php echo JText::sprintf('VRCGRAPHTOTOCCUPANCY', $tot_occ_pcent); ?></span>
				</div>
				<div class="vrc-graph-bottom-inner">
					<div class="vrc-graph-bottom-left">
						<div class="vrc-graphstats-left vrc-graphstats-left-nights">
							<canvas id="vrc-graphstats-left-canv-nights"></canvas>
						</div>
					</div>
					<div class="vrc-graph-bottom-right">
						<?php
						if (count($nights_donut_datasets) > 0) {
						?>
						<div class="vrc-graphstats-right vrc-graphstats-right-nights">
							<canvas id="vrc-graphstats-right-canv-nights"></canvas>
						</div>
						<?php
						}
						?>
						<div class="vrc-graphstats-thirdright vrc-graphstats-thirdright-nights">
							<p class="vrc-graphstats-totocc"><span><?php echo JText::_('VRCGRAPHTOTOCCUPANCYLBL'); ?></span> <?php echo $tot_occ_pcent; ?>%</p>
							<p class="vrc-graphstats-totunits"><span><?php echo JText::_('VRCGRAPHTOTUNITSLBL'); ?></span> <?php echo $tot_cars_units; ?></p>
						<?php
						if ($tot_months > 1 && count($nights_datasets[0]['data']) > 1) {
							$remonths_labels = array_keys($arr_months);
							$max_nights = max($nights_datasets[0]['data']);
							$min_nights = min($nights_datasets[0]['data']);
							$max_month_key = array_search($max_nights, $nights_datasets[0]['data']);
							$min_month_key = array_search($min_nights, $nights_datasets[0]['data']);
							$max_monyear = explode('-', $remonths_labels[$max_month_key]);
							$max_month_days = date('t', mktime(0, 0, 0, $max_monyear[0], 1, $max_monyear[1]));
							$min_monyear = explode('-', $remonths_labels[$min_month_key]);
							$min_month_days = date('t', mktime(0, 0, 0, $min_monyear[0], 1, $min_monyear[1]));
							if ($max_month_key !== false && $min_month_key !== false) {
								?>
							<div class="vrc-graphstats-thirdright-nights-bestworst">
								<span class="vrc-graphstats-nights-best"><i class="vrcicn-stats-bars2" style="color: green;"></i> <?php echo $months_labels[$max_month_key]; ?>: <?php echo $max_nights; ?> <?php echo JText::_('VRCGRAPHTOTNIGHTSLBL'); ?> (<?php echo round((100 * $max_nights / ($tot_cars_units * $max_month_days)), 3); ?>%)</span>
								<span class="vrc-graphstats-nights-worst"><?php echo $months_labels[$min_month_key]; ?>: <?php echo $min_nights; ?> <?php echo JText::_('VRCGRAPHTOTNIGHTSLBL'); ?> (<?php echo round((100 * $min_nights / ($tot_cars_units * $min_month_days)), 3); ?>%) <i class="vrcicn-stats-bars2" style="color: red; margin: 0 0 0 0.25em;"></i></span>
							</div>
								<?php
							}
						}
						?>
						</div>
					</div>
				</div>
			</div>
			<?php
		}
		?>
		</fieldset>
		<input type="hidden" name="task" value="">
		<input type="hidden" name="option" value="com_vikrentcar" />
	</form>
</div>

<?php
/**
 * Adjust datasets compatibility with Chart.js v2.x.
 * 
 * @since 	1.14
 */
$donut_labels = $nights_donut_labels = array();
$new_donut_datasets = $new_nights_donut_datasets = array(
	'label' 			=> 'Dataset',
	'data' 				=> array(),
	'backgroundColor'  	=> array(),
	'hoverBorderColor' 	=> array(),
);
foreach ($donut_datasets as $k => $v) {
	array_push($donut_labels, $v['label']);
	array_push($new_donut_datasets['data'], $v['value']);
	array_push($new_donut_datasets['backgroundColor'], $v['backgroundColor']);
	array_push($new_donut_datasets['hoverBorderColor'], $v['hoverBorderColor']);
}
foreach ($nights_donut_datasets as $k => $v) {
	array_push($nights_donut_labels, $v['label']);
	array_push($new_nights_donut_datasets['data'], $v['value']);
	array_push($new_nights_donut_datasets['backgroundColor'], $v['backgroundColor']);
	array_push($new_nights_donut_datasets['hoverBorderColor'], $v['hoverBorderColor']);
}
//
?>

<script type="text/javascript">
var data = {
	labels: <?php echo json_encode($months_labels); ?>,
	datasets: <?php echo json_encode($datasets); ?>,
};
// this object is not used as we have 3 graphs at the moment
var donut_data = {
	labels: <?php echo json_encode($donut_labels); ?>,
	datasets: <?php echo json_encode(array($new_donut_datasets)); ?>,
};
var nights_data = {
	labels: <?php echo json_encode($months_labels); ?>,
	datasets: <?php echo json_encode($nights_datasets); ?>
};
var nights_donut_data = {
	labels: <?php echo json_encode($nights_donut_labels); ?>,
	datasets: <?php echo json_encode(array($new_nights_donut_datasets)); ?>,
};

var options = {
	responsive: true,
	plugins: {
		legend: {
			display: true,
			position: 'bottom',
		},
		legendCallback: function (chart) {
			// Return the HTML string here.
			var text = [];
			text.push("<ul class=\"chart-line-legend\">");
			for (var i = 0; i < chart.data.datasets.length; i++) {
				text.push("<li>");
				text.push("<span class=\"legend-entry\" style=\"background-color: " + chart.data.datasets[i].backgroundColor + "\"></span>");
				text.push("<span class=\"legend-label\">" + chart.data.datasets[i].label + "</span>");
				text.push("</li>");
			}
			text.push("</ul>");
			return text.join("");
		},
		// tooltip handling
		tooltip: {
			// tooltip callbacks are used to customize default texts
			callbacks: {
				// format the tooltip text displayed when hovering a point
				label: function(context) {
					// format value as currency with channel name
					var chname = context.dataset.label || '';
					var label = chname + ': <?php echo $currencysymb; ?> ' + context.parsed.y;
					return ' ' + label;
				},
				// change label colors because, by default, the legend background is blank
				labelColor: function(context) {
					// get tooltip item meta data
					var meta = context.dataset;
					return {
						// use white border
						borderColor: 'rgb(0,0,0)',
						// use same item background color
						backgroundColor: meta.borderColor,
					};
				},
			},
		},
	},
};

var pie_options = {
	responsive: true,
	plugins: {
		legend: {
			display: true,
			position: 'bottom',
		},
		legendCallback: function (chart) {
			// Return the HTML string here.
			var text = [];
			text.push("<ul class=\"chart-line-legend chart-pie-legend\">");
			for (var i = 0; i < chart.data.labels.length; i++) {
				text.push("<li>");
				text.push("<span class=\"legend-entry\" style=\"background-color: " + chart.data.datasets[0].backgroundColor[i] + "\"></span>");
				text.push("<span class=\"legend-label\">" + chart.data.labels[i] + "</span>");
				text.push("</li>");
			}
			text.push("</ul>");
			return text.join("");
		},
		// tooltip handling
		tooltip: {
			// tooltip callbacks are used to customize default texts
			callbacks: {
				// format the tooltip text displayed when hovering a point
				label: function(context) {
					var chname = context.dataset.label || '';
					var label = chname + ': <?php echo $currencysymb; ?> ' + context.parsed.y;
					return ' ' + label;
				},
				// change label colors because, by default, the legend background is blank
				labelColor: function(context) {
					// get tooltip item meta data
					return {
						// use white border
						borderColor: 'rgb(0,0,0)',
						// use same item background color
						backgroundColor: context.dataset.backgroundColor[context.dataIndex],
					};
				},
			},
		},
	},
	animation: {
		duration: 1000,
	},
};

jQuery(function() {
	var ctx = document.getElementById("vrc-graphstats-left-canv").getContext("2d");
	var vrcLineChart = new Chart(ctx, {
		type: 'line',
		data: data,
		options: options,
	});
	// jQuery('#vrc-graphstats-left-canv').parent().append(vrcLineChart.generateLegend());

	<?php
	if ($pstatsmode == 'nights') {
		?>
	var nights_options = JSON.parse(JSON.stringify(options));
	nights_options.plugins.tooltip.callbacks.label = function(context) {
		// format value as days booked
		var label = context.parsed.y || '';
		return ' ' + label + ' <?php echo addslashes(JText::_('VRCGRAPHTOTNIGHTSLBL')); ?>';
	};
	var nights_ctx = document.getElementById("vrc-graphstats-left-canv-nights").getContext("2d");
	var vrcNightsLineChart = new Chart(nights_ctx, {
		type: 'line',
		data: nights_data,
		options: nights_options,
	});
	// jQuery('#vrc-graphstats-left-canv-nights').parent().append(vrcNightsLineChart.generateLegend());
		<?php
	}
	if (count($nights_donut_datasets) > 0) {
		?>
	var nights_pie_options = JSON.parse(JSON.stringify(pie_options));
	nights_pie_options.plugins.tooltip.callbacks.label = function(context) {
		var label = context.label || '';
		if (label) {
			label += ': ';
		}
		var parsed = context.parsed || '';
		label += parsed + ' <?php echo addslashes(JText::_('VRCGRAPHTOTNIGHTSLBL')); ?>';
		return ' ' + label;
	};
	var nights_donut_ctx = document.getElementById("vrc-graphstats-right-canv-nights").getContext("2d");
	var vrcNightsDonutChart = new Chart(nights_donut_ctx, {
		type: 'pie',
		data: nights_donut_data,
		options: nights_pie_options,
	});
	// jQuery('#vrc-graphstats-right-canv-nights').parent().append(vrcNightsDonutChart.generateLegend());
		<?php
	}
	?>
});
</script>
	<?php
}
