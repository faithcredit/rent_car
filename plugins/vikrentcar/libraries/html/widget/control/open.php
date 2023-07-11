<?php
/** 
 * @package   	VikRentCar - Libraries
 * @subpackage 	html.widget
 * @author    	E4J s.r.l.
 * @copyright 	Copyright (C) 2018 E4J s.r.l. All Rights Reserved.
 * @license  	http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link 		https://vikwp.com
 */

// No direct access
defined('ABSPATH') or die('No script kiddies please!');

$label  = isset($displayData['label']) 			? $displayData['label'] 		: '';
$desc  	= isset($displayData['description']) 	? $displayData['description'] 	: '';
$id 	= isset($displayData['id'])				? $displayData['id'] 			: '';
$req 	= isset($displayData['required']) 		? $displayData['required']		: 0;

$label = JText::_($label);

if ($desc)
{
	$desc = JText::_($desc);

	// make sure the description and the label don't contain the same text
	if ($desc != $label)
	{
		$label = VikApplication::getInstance()->textPopover(array(
			'title' 	=> $label,
			'content' 	=> $desc,
		));
	}
}

?>
<p>
	<label
		for="<?php echo esc_attr($id); ?>"
	><?php echo $label; ?><?php echo ($req ? '*' : ''); ?>:</label>
