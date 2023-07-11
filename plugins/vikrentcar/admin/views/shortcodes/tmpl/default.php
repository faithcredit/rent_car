<?php

// No direct access
defined('ABSPATH') or die('No script kiddies please!');

$app = JFactory::getApplication();
$vik = VikApplication::getInstance();

$dt_format = $app->get('date_format') . ' ' . $app->get('time_format');

?>

<form action="admin.php" method="post" name="adminForm" id="adminForm">

	<?php
	/**
	 * Added filters to search the shortcodes by name, type and language.
	 *
	 * @since 1.1.5
	 */
	?>
	<div class="tablenav top">

		<div class="alignleft actions">
			<input type="search" id="post-search-input" name="filter_search" value="<?php echo JHtml::_('esc_attr', $this->filters['search']); ?>" />
			<button type="submit" id="search-submit" class="button"><?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?></button>
		</div>

		<div class="alignright actions" style="padding-right:0;">
			<!-- TYPE filter -->
			<select name="filter_type" id="vik-type-filter" onchange="document.adminForm.submit();">
				<option value=""><?php echo JText::_('JOPTION_SELECT_TYPE'); ?></option>
				<?php
				foreach ($this->views as $type => $title)
				{
					?>
					<option value="<?php echo JHtml::_('esc_attr', $type); ?>" <?php echo ($type == $this->filters['type'] ? 'selected="selected"' : ''); ?>><?php echo JHtml::_('esc_html', JText::_($title)); ?></option>
					<?php
				}
				?>
			</select>

			<!-- LANGUAGE filter -->
			<select name="filter_lang" id="vik-lang-filter" onchange="document.adminForm.submit();" style="margin-right: 0;">
				<option value="*"><?php echo JText::_('JOPTION_SELECT_LANGUAGE'); ?></option>
				<?php
				foreach (JLanguage::getKnownLanguages() as $tag => $lang)
				{
					?>
					<option value="<?php echo JHtml::_('esc_attr', $tag); ?>" <?php echo ($tag == $this->filters['lang'] ? 'selected="selected"' : ''); ?>><?php echo JHtml::_('esc_html', $lang['nativeName']); ?></option>
					<?php
				}
				?>
			</select>
		</div>

	</div>

<?php if (count($this->shortcodes) == 0) { ?>

	<p class="warn"><?php echo JText::_('NO_ROWS_FOUND'); ?></p>

<?php } else { ?>

	<table cellpadding="4" cellspacing="0" border="0" width="100%" class="<?php echo $vik->getAdminTableClass(); ?>" style="margin-top:10px;">
		
		<?php echo $vik->openTableHead(); ?>
			<tr>
				<td width="1%" class="manage-column column-cb check-column">
					<?php echo $vik->getAdminToggle(count($this->shortcodes)); ?>
				</td>
				<th class="<?php echo $vik->getAdminThClass('left hidden-phone hidden-tablet'); ?>" width="3%" style="text-align: left;"><?php echo JText::_('JID'); ?></th>
				<th class="<?php echo $vik->getAdminThClass('left'); ?>" width="25%" style="text-align: left;"><?php echo JText::_('JNAME'); ?></th>
				<th class="<?php echo $vik->getAdminThClass('left hidden-phone'); ?>" width="15%" style="text-align: left;"><?php echo JText::_('JTYPE'); ?></th>
				<th class="<?php echo $vik->getAdminThClass(); ?>" width="10%" style="text-align: center;"><?php echo JText::_('JSHORTCODE'); ?></th>
				<th class="<?php echo $vik->getAdminThClass(); ?>" width="25%" style="text-align: center;"><?php echo JText::_('JPOST'); ?></th>
				<th class="<?php echo $vik->getAdminThClass('hidden-phone hidden-tablet'); ?>" width="10%" style="text-align: center;"><?php echo JText::_('JCREATEDBY'); ?></th>
				<th class="<?php echo $vik->getAdminThClass('hidden-phone'); ?>" width="11%" style="text-align: center;"><?php echo JText::_('JCREATEDON'); ?></th>
			</tr>
		<?php echo $vik->closeTableHead(); ?>
		
		<?php
		foreach ($this->shortcodes as $i => $row)
		{
			?>
			<tr class="row">
				<td><input type="checkbox" id="cb<?php echo $i;?>" name="cid[]" value="<?php echo (int)$row->id; ?>" onClick="<?php echo $vik->checkboxOnClick(); ?>"></td>
				
				<td class="hidden-phone hidden-tablet"><?php echo $row->id; ?></td>
				
				<td>
					<a href="javascript: void(0);" onclick="jQuery('#cb<?php echo $i; ?>').prop('checked', true);Joomla.submitbutton('shortcodes.edit');">
						<?php echo $row->name; ?>
					</a>
				</td>

				<td class="hidden-phone"><?php echo JText::_($row->title); ?></td>

				<td style="text-align: center;">
					<span class="vrc-shortcode-icn-wrap">
						<?php echo $vik->createPopover(array(
							'title' 	=> JText::_('JSHORTCODE'),
							'content' 	=> '<textarea style="width:250px;height:200px;" onclick="this.select();">' . $row->shortcode . '</textarea>',
							'icon' 		=> 'qrcode',
							'trigger'	=> 'click',
						)); ?>
					</span>
				</td>

				<td style="text-align: center;">
					<?php if ($row->post_id) { ?>
						
						<a href="<?php echo get_permalink($row->post_id); ?>" target="_blank" class="btn btn-primary vrc-link-btn-small">
							<?php echo JText::_('VRC_SC_VIEWFRONT'); ?> <i class="<?php echo VikRentCarIcons::i('external-link-square'); ?>"></i>
						</a>

					<?php } else if ($row->tmp_post_id) { 

						$post = get_post($row->tmp_post_id);

						?>

						<a href="edit.php?post_status=trash&post_type=<?php echo $post->post_type; ?>" target="_blank" class="btn vrc-link-btn-small">
							<?php echo JText::_('VRC_SC_VIEWTRASHPOSTS'); ?> <i class="<?php echo VikRentCarIcons::i('external-link-square'); ?>" style="color: #900;"></i>
						</a>

					<?php } else { ?>

						<a href="index.php?option=com_vikrentcar&task=shortcode.add_to_page&sc_id=<?php echo $row->id; ?>&return=<?php echo $this->returnLink; ?>" class="btn btn-danger vrc-link-btn-small" onclick="return confirm('<?php echo $this->escape(JText::_('VRC_SC_ADDTOPAGE_HELP')); ?>');">
							<?php echo JText::_('VRC_SC_ADDTOPAGE'); ?> <i class="<?php echo VikRentCarIcons::i('plus-square'); ?>"></i>
						</a>

					<?php } ?>
				</td>
				
				<td class="hidden-phone hidden-tablet" style="text-align: center;"><?php echo JUser::getInstance($row->createdby)->username; ?></td>

				<td class="hidden-phone" style="text-align: center;"><?php echo JHtml::_('date', $row->createdon, $dt_format); ?></td>
			</tr>
		<?php }	?>

	</table>

<?php } ?>

	<input type="hidden" name="option" value="com_vikrentcar" />
	<input type="hidden" name="view" value="shortcodes" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="return" value="<?php echo JHtml::_('esc_attr', $this->returnLink); ?>" />
	<?php echo $this->navbut; ?>

</form>
