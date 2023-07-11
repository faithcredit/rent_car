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

$fpath = $this->fpath;

$editor = JEditor::getInstance('codemirror');
$fcode = '';
$fp = !empty($fpath) ? fopen($fpath, "rb") : false;
if (empty($fpath) || $fp === false) {
	?>
	<p class="err"><?php echo JText::_('VRCTMPLFILENOTREAD'); ?></p>
	<?php
} else {
	while (!feof($fp)) {
		$fcode .= fread($fp, 8192);
	}
	fclose($fp);
?>
<form name="adminForm" id="adminForm" action="index.php" method="post">
	<fieldset class="adminform">
		<legend class="adminlegend"><?php echo JText::_('VRCEDITTMPLFILE'); ?></legend>
		<p class="vrc-path-tmpl-file"><?php echo $fpath; ?></p>
		<?php
		if (interface_exists('Throwable')) {
			/**
			 * With PHP >= 7 supporting throwable exceptions for Fatal Errors
			 * we try to avoid issues with third party plugins that make use
			 * of the WP native function get_current_screen().
			 * 
			 * @wponly
			 */
			try {
				echo $editor->display("cont", $fcode, '100%', 300, 70, 20);
			} catch (Throwable $t) {
				echo $t->getMessage() . ' in ' . $t->getFile() . ':' . $t->getLine() . '<br/>';
			}
		} else {
			// we cannot catch Fatal Errors in PHP 5.x
			echo $editor->display("cont", $fcode, '100%', 300, 70, 20);
		}
		?>
		<br clear="all" />
		<p style="text-align: center;">
			<button type="button" class="btn btn-success" onclick="vrcSubmitTmplFile(this);"><?php echo JText::_('VRCSAVETMPLFILE'); ?></button>
		</p>
	</fieldset>
	<input type="hidden" name="path" value="<?php echo JHtml::_('esc_attr', $fpath); ?>">
	<input type="hidden" name="option" value="com_vikrentcar" />
	<input type="hidden" name="task" value="savetmplfile" />
	<?php echo JHtml::_('form.token'); ?>
</form>

<script type="text/javascript">
	function vrcSubmitTmplFile(elem) {
		/**
		 * @wponly 	the real textarea may not update with the new codemirror content
		 * 			and so we force the update of the textarea before submitting the form.
		 */
		try {
			document.getElementById('cont').value = Joomla.editors.instances['cont'].getValue();
		} catch (err) {
			console.error(err);
		}
		jQuery(elem).closest('form').submit();
	}
</script>
<?php
}
