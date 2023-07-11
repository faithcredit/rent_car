<?php
/**
 * @package     VikRentCar
 * @subpackage  mod_vikrentcar_currencyconverter
 * @author      Alessio Gaggii - E4J s.r.l
 * @copyright   Copyright (C) 2019 E4J s.r.l. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 * @link        https://vikwp.com
 */

// No direct access
defined('ABSPATH') or die('No script kiddies please!');

$session = JFactory::getSession();
$last_lang = $session->get('vrcLastCurrency', '');

$document = JFactory::getDocument();
$document->addStyleSheet($baseurl.'modules/mod_vikrentcar_currencyconverter/mod_vikrentcar_currencyconverter.css');

$active_suff = empty($last_lang) ? $def_currency : $last_lang;

?>
<script type="text/javascript">
jQuery.noConflict();
var sendprices = new Array();
var vrccurconvbasepath = '<?php echo $baseurl.'modules/mod_vikrentcar_currencyconverter/images/flags/'; ?>';
var vrccurconvbaseflag = '<?php echo $baseurl.'modules/mod_vikrentcar_currencyconverter/images/flags/'.$active_suff.'.png'; ?>';
var fromCurrency = '<?php echo $def_currency; ?>';
var fromSymbol;
var pricestaken = 0;
jQuery(document).ready(function() {
	if(jQuery(".vrc_price").length > 0) {
		jQuery(".vrc_price").each(function() {
			sendprices.push(jQuery(this).text());
		});
		pricetaken = 1;
	}
	if(jQuery(".vrc_currency").length > 0) {
		fromSymbol = jQuery(".vrc_currency").first().html();
	}
	<?php
	if(!empty($last_lang) && $last_lang != $def_currency) {
		?>
	if(jQuery(".vrc_price").length > 0) {
		vrcConvertCurrency('<?php echo $last_lang; ?>');
	}
		<?php
	}
	?>
});
function vrcConvertCurrency(toCurrency) {
	if(sendprices.length > 0) {
		jQuery(".vrc_currency").text(toCurrency);
		jQuery(".vrc_price").text("").addClass("vrc_converting");
		var modvrccurconvax = jQuery.ajax({
			type: "POST",
			url: "<?php echo JRoute::_('index.php?option=com_vikrentcar&task=currencyconverter', false); ?>",
			data: {prices: sendprices, fromsymbol: fromSymbol, fromcurrency: fromCurrency, tocurrency: toCurrency, tmpl: "component"}
		}).done(function(resp) {
			jQuery(".vrc_price").removeClass("vrc_converting");
			var convobj = JSON.parse(resp);
			if(convobj.hasOwnProperty("error")) {
				alert(convobj.error);
				vrcUndoConversion();
			}else {
				jQuery(".vrc_currency").html(convobj[0].symbol);
				jQuery(".vrc_price").each(function(i) {
					jQuery(this).text(convobj[i].price);
				});
				jQuery("#vrccurconv-flag-img").attr("src", vrccurconvbasepath+toCurrency+".png");
				jQuery("#vrccurconv-flag-img").attr("alt", toCurrency);
				jQuery("#vrccurconv-flag-img").attr("title", toCurrency);
				jQuery("#vrccurconv-flag-symb").html(convobj[0].symbol);
			}
		}).fail(function(){
			jQuery(".vrc_price").removeClass("vrc_converting");
			vrcUndoConversion();
		});
	}else {
		jQuery("#modcurconvsel").val("<?php echo $active_suff; ?>");
	}
}
function vrcUndoConversion() {
	jQuery(".vrc_currency").text(fromSymbol);
	jQuery(".vrc_price").each(function(i) {
		jQuery(this).text(sendprices[i]);
	});
	jQuery("#vrccurconv-flag-symb").text(fromSymbol);
	jQuery("#vrccurconv-flag-img").attr("src", vrccurconvbaseflag);
	jQuery("#vrccurconv-flag-img").attr("alt", fromCurrency);
	jQuery("#vrccurconv-flag-img").attr("title", fromCurrency);
	jQuery("#modcurconvsel").val(fromCurrency);
}
</script>
<div class="vrc-curconv-wrap">
	<div class="vrccurconvcontainer">
		<div class="vrccurconv-flag">
		<?php
		echo '<img id="vrccurconv-flag-img" alt="'.$active_suff.'" title="'.$active_suff.'" src="'.$baseurl.'modules/mod_vikrentcar_currencyconverter/images/flags/'.$active_suff.'.png'.'"/>';
		$active_symb = array_key_exists($active_suff, $currencymap) && isset($currencymap[$active_suff]['symbol']) ? '&#'.$currencymap[$active_suff]['symbol'].';' : '';
		?>
			<span id="vrccurconv-flag-symb"><?php echo $active_symb; ?></span>
		</div>
		<div class="vrccurconv-menu">
			<select id="modcurconvsel" name="mod_vikrentcar_currencyconverter" onchange="vrcConvertCurrency(this.value);">
		<?php
	    foreach($currencies as $cur) {
	    	$three_code = substr($cur, 0, 3);
			$curparts = explode(':', $cur);
			if($currencynameformat == 1) {
				$curname = $three_code;
			}elseif($currencynameformat == 2) {
				$curname = trim($curparts[1]);
			}else {
				$curname = trim($curparts[1]).' ('.$three_code.')';
			}
			?>
			<option value="<?php echo $three_code; ?>"<?php echo ((empty($last_lang) && $three_code == $def_currency) || (!empty($last_lang) && $three_code == $last_lang) ? ' selected="selected"' : ''); ?>><?php echo $curname; ?></option>
			<?php
	    }
	    ?>
	    	</select>
	    </div>
	</div>
</div>