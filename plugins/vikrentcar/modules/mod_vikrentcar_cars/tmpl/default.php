<?php
/**
 * @package     VikRentCar
 * @subpackage  mod_vikrentcar_cars
 * @author      Alessio Gaggii - e4j - Extensionsforjoomla.com
 * @copyright   Copyright (C) 2018 e4j - Extensionsforjoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 * @link        https://e4j.com
 */

// no direct access
defined('ABSPATH') or die('No script kiddies please!');

$currencysymb = $params->get('currency');
$get_cars_layout = $params->get('layoutlist');
$widthroom = $params->get('widthroom');

$numb_total = $params->get('numb');
$numb_xrow = (int)$params->get('numb_carrow');
$numb_xrow = $numb_xrow < 1 ? 1 : $numb_xrow;
$autoplayparam = $params->get('autoplay');

$calc_item_width = 100 / $numb_xrow;

if ($autoplayparam == 1) {
	$autoplayparam_status = "true";
	
} else {
	$autoplayparam_status = "false";
}
$pagination = $params->get('pagination');

if ($pagination == 1) {
	$pagination_status = "true";
} else {
	$pagination_status = "false";
}

$navigation = $params->get('navigation');

if ($navigation == 1) {
	$navigation_status = "true";
} else {
	$navigation_status = "false";
}

$itemid = $params->get('itemid');

if ($get_cars_layout == 1) {
	$document = JFactory::getDocument();
	$document->addStyleSheet($baseurl.'modules/mod_vikrentcar_cars/src/owl.carousel.min.css');
	//$document->addStyleSheet($baseurl.'modules/mod_vikrentcar_cars/src/owl.theme.css');
	JHtml::_('script', $baseurl.'modules/mod_vikrentcar_cars/src/owl.carousel.min.js');
}
$document->addStyleSheet($baseurl.'modules/mod_vikrentcar_cars/mod_vikrentcar_cars.css');

?>
<div class="vrcmodcarsgridcontainer column-container <?php echo ($get_cars_layout) ? 'wrap ' : 'container-fluid'; ?>">
	<div>
		<div id="vrc-modcars-<?php echo $randid; ?>" class="<?php echo ($get_cars_layout) ? 'owl-carousel owl-theme ' : ''; ?>vrcmodcarsgridcont-items vrcmodcarsgridhorizontal row-fluid">
		<?php
		foreach ($cars as $c) {
			$car_link = JRoute::_('index.php?option=com_vikrentcar&view=cardetails&carid='.$c['id'].(!empty($itemid) ? '&Itemid='.$itemid : ''));

			$carats = Modvikrentcar_carsHelper::getCarCaratOriz($c['idcarat'], array(), Modvikrentcar_carsHelper::getTranslator());
			?>
			<div class="vrc-modcars-item <?php echo ($get_cars_layout) ? '' : 'vrc-modcars-grid-item'; ?>" style="<?php echo ($get_cars_layout) ? '' : 'width: '.$calc_item_width.'%;' ; ?>" data-groups='["<?php echo $c['catname']; ?>"]'>

				<figure class="vrcmodcarsgridcont-item">
					<div class="vrcmodcarsgridboxdiv">	
						<?php
						if (!empty($c['img'])) {
						?>
						<a href="<?php echo $car_link; ?>" title="<?php echo $c['name']; ?>"><img src="<?php echo VRC_ADMIN_URI; ?>resources/<?php echo $c['img']; ?>" alt="<?php echo $c['name']; ?>" class="vrcmodcarsgridimg"/></a>
						<?php
						}
						?>
						<div class="vrcmodcarsgrid-item_details">
						<figcaption class="vrcmodcarsgrid-item_title"><?php echo $c['name']; ?></figcaption>
				        <?php if ($params->get('show_desc')) { ?>
				       		<div class="vrcmodcarsgrid-item-desc"><?php echo $c['short_info']; ?></div>
				        <?php
						}
						?>
						<?php
						if ($c['cost'] > 0) {
						?>
						<div class="vrcmodcarsgrid-box-cost">
							<span class="vrcmodcarsgridstartfrom"><?php echo JText::_('VRCMODCARSTARTFROM'); ?></span>
							<span class="vrcmodcarsgridcarcost"><span class="vrc_currency"><?php echo $currencysymb; ?></span> <span class="vrc_price"><?php echo Modvikrentcar_carsHelper::numberFormat($c['cost']); ?></span></span>
						</div>
						<?php
						}
						?>
				        </div>
						<div class="vrcmodcarsgridview">
							<a class="btn btn-vrcmodcarsgrid-btn vrc-pref-color-btn" href="<?php echo $car_link; ?>" title="<?php htmlspecialchars($c['name']); ?>"><?php echo JText::_('VRCMODCARCONTINUE'); ?></a>
						</div>
						<div class="vrcmodcarsgrid-item-btm">
					        <?php
							if ($showcatname) {
							?>
							<div class="vrcmodcarsgrid-item_cat"><?php echo $c['catname']; ?></div>
							<?php
							}
							?>
							<div class="vrcmodcarsgrid-item_carat"><?php echo $carats; ?></div>
						</div>
					</div>	
				</figure>
			</div>
			<?php
		}
		?>
		</div>
	</div>
</div>

<?php if ($get_cars_layout == 1) { ?>
	<script type="text/javascript">
	jQuery(document).ready(function(){ 
		jQuery("#vrc-modcars-<?php echo $randid; ?>").owlCarousel({
			items : <?php echo $numb_xrow; ?>,
			autoplay : <?php echo $autoplayparam_status; ?>,
			nav : <?php echo $navigation_status; ?>,
			dots : <?php echo $pagination_status; ?>,
			lazyLoad : true,
			responsiveClass: true,
			responsive: {
			0: {
				items: 1,
				nav: true
			},
			<?php if($numb_xrow == 1) { ?>
				600: {
					items:1,
					nav: true
				},
			<?php } else { ?>
				600: {
					items:2,
					nav: true
				},
			<?php } ?>
			<?php if($numb_xrow == 1) { ?>
				820: {
					items: 1,
					nav: true
				},
			<?php } else if($numb_xrow == 2) { ?>
				820: {
					items: 2,
					nav: true
				},
			<?php } else { ?>
				820: {
					items: 3,
					nav: true
				},
			<?php } ?>
			1024: {
				items: <?php echo $numb_xrow; ?>,
				nav: true
			}
		}
		});
		<?php if($navigation_status == "false") { ?>
			jQuery("#vrc-modcars-<?php echo $randid; ?> .owl-nav").addClass('owl-disabled');
		<?php } ?>
	});
	</script>
<?php } ?>
