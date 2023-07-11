<?php
/**
 * @package     VikUpdater
 * @subpackage  views
 * @author      E4J s.r.l.
 * @copyright   Copyright (C) 2018 E4J s.r.l. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 * @link        https://vikwp.com
 */

defined('ABSPATH') or die('No script kiddies please!');




function vikupdater_render_update_page() {


	VikUpdaterHelper::CheckAllCurrentVersion();
	VikUpdaterHelper::UpdateChecksNumber();
	wp_enqueue_style('thickbox');
	wp_enqueue_script('thickbox');  
	$update = false;
	$lastCheck = get_option('vikupdater_update_lastcheck', null);
	$selectedHash = '';
	$settings = array();
	
	$settings = VikUpdaterLibrary::loadSettings();
	$settings['updatedays'] = empty($settings['updatedays']) ? 7 : $settings['updatedays'];
	echo VikUpdaterLibrary::loadMenu('update');

	if (!empty($lastCheck) && (time() - $lastCheck) > 24*60*60*$settings['updatedays']) {
		$result = VikUpdaterContact::CheckSelfVersion();
		if($result != 'latest') {
			$update = true;
		}
	}
	$url = '#TB_inline?width=500&height=400&inlineId=vikthickbox';





	/**
	 *
	 * @since 1.3
	 * 
	 * Added new check to trigger update. This is necessary if the update from 1.1 to 1.2 was installed via FTP since the new hook has not been registered.
	 *
	 */
	if (empty($lastCheck)) {
		add_option('vikupdater_update_lastcheck', time());
	}
	
	$method = "";
	$hash = ""; 
	

	

	if ($_POST && current_user_can('manage_options') && isset($_POST['method'])) {
		$method = $_POST['method'];
		$hash = isset($_POST['hash']) ? sanitize_text_field($_POST['hash']) : '';
		if (isset($_POST['hash']) && VikUpdaterHelper::ValidateHash($hash)) {
			switch ($method) {
				case 'validate':
					check_admin_referer('vikup.validate');
					if (VikUpdaterContact::RegisterProduct($hash)) {
						VikUpdaterHelper::ReportMessage(__('Your product was registered succesfully.', 'vikupdater'), 'success');
					}
					break;
				case 'remove':
					check_admin_referer('vikup.remove');
					if (VikUpdaterHelper::RemoveProduct($hash)) {
						VikUpdaterHelper::ReportMessage(__('Your product was removed succesfully.', 'vikupdater'), 'success');
					}
					break;
				case 'download':
					check_admin_referer('vikup.download');
					if (VikUpdaterContact::DownloadAndInstallProduct($hash)) {
						VikUpdaterHelper::ReportMessage(__('Your product was downloaded succesfully.', 'vikupdater'), 'success');
					}
					break;
			}
		}
		if ($method == 'self_update') {
			check_admin_referer('vikup.self.update');
			if (VikUpdaterContact::SelfUpdate()) { 
				VikUpdaterHelper::ReportMessage(__('VikUpdater was updated succesfully.', 'vikupdater'), 'success');
			} else { 
				VikUpdaterHelper::ReportMessage(__('VikUpdater was not able to update.', 'vikupdater'));
			}
		}
		if ($method == 'check_update') {
			check_admin_referer('vikup.update');
			if (VikUpdaterContact::CheckLatestVersion()) { 
				VikUpdaterHelper::ReportMessage(__('Updates were checked succesfully.', 'vikupdater'), 'success');
			} else { 
				VikUpdaterHelper::ReportMessage(__('Updates were checked with some errors.', 'vikupdater'));
			}
		}
	}
	$storedProducts = json_decode(get_option("vikupdater_products"), true);
	?>

<script type="text/javascript">
jQuery(document).ready(function() {   
    jQuery("#triggerUpdate").click(function() {                 
        tb_show("", "<?php echo $url; ?>");
        return false;
    });
});             
</script>

<div class="vikup-container vikup-title-container">
	<span class="vikup-title"><h1><?php _e('VikUpdater', 'vikupdater');?></h1></span>
	<div class="vikup-description">
		<span><?php _e('You can use this page to register and update the products you have purchased from <a href="vikwp.com">VikWp</a>. <a href="https://vikwp.com/support/knowledge-base/vikupdater">Here</a> is a guide on how to use the plugin.', 'vikupdater');?> </span>
		<?php if ($update) {
			?>
			<div class="update-message notice inline notice-warning notice-alt" style="margin-top: 15px;margin-left: 0;">
				<p style="display:inline-block;">There is a new version of VikUpdater available. </p>
				<form method="POST" style="display:inline-flex;" action="options-general.php?page=vikupdater">
					<?php wp_nonce_field('vikup.self.update'); ?>
					<input type="hidden" name="method" value="self_update"/>
					<button class="button button-primary vikup-confirm-button"><?php _e('Update VikUpdater!', 'vikupdater');?></button>
				</form>
			</div>
		<?php
		}
		?>
	</div>
</div>
<div class="vikup-container vikup-buttons-container">
	<div class="vikup-container vikup-hash-container">
		<form method="POST" action="options-general.php?page=vikupdater">
			<?php wp_nonce_field('vikup.validate'); ?>
			<input type="text" name="hash" autocomplete="off" class="vikup-input vikup-hash-input" />
			<input type="hidden" name="method" value="validate"/>
			<button class="button button-primary vikup-hash-confirm"><?php _e('Validate', 'vikupdater');?></button>
		</form>
	</div>
	<div class="vikup-container vikup-check-container">
		<form method="POST" action="options-general.php?page=vikupdater">
			<?php wp_nonce_field('vikup.update'); ?>
			<input type="hidden" name="method" value="check_update"/>
			<button class="button button-primary vikup-confirm-button"><?php _e('Check Updates', 'vikupdater');?></button>
		</form>
	</div>
</div>
<div class="vikup-container vikup-table-container">
<?php if (is_array($storedProducts) && !empty($storedProducts)) { ?>
	<table class="vikup-table-content">
		<tr>
			<th>
				<?php _e('Installed Product', 'vikupdater');?>
			</th>
			<th>
				<?php _e('Order Date', 'vikupdater');?>
			</th>
			<th>
				<?php _e('Last Check', 'vikupdater');?>
			</th>
			<th>
				<?php _e('Installed', 'vikupdater');?>
			</th>
			<th>
				<?php _e('Installed Version', 'vikupdater');?>
			</th>
			<th>
				<?php _e('Available Version', 'vikupdater');?>
			</th>
			<th>
				<?php _e('Download Update', 'vikupdater');?>
			</th>
			<th>
				<?php _e('Remove Product', 'vikupdater');?>
			</th>
		</tr>
			<?php foreach ($storedProducts as $hash => $jsonProduct) {
				$product = json_decode($jsonProduct, true);?>
		<tr>
			<td>
				<?php echo $product['name'];?>
			</td>
			<td>
			<?php echo $product['orderDate'];?>
			</td>
			<td>
			<?php echo date('Y-m-d h:i:s', $product['lastCheck']);?>
			</td>
			<td>
			<?php echo isset($product['installed']) && $product['installed'] ? '<span class="dashicons dashicons-yes"></span>' : '<span class="dashicons dashicons-no"></span>';?>
			</td>
			<td>
			<?php echo !empty($product['installedVersion']) ? $product['installedVersion'] : '---';?>
			</td>
			<td>
			<?php echo !empty($product['version']) ? $product['version'] : '---';?>
			</td>
			<td>
			<?php if(!$product['latest'] && isset($product['installed']) && $product['installed']) {
				if (!isset($product['changelog'])) {?>

					<form method="POST" action="options-general.php?page=vikupdater">
						<button class="button button-primary" ><?php _e('Download', 'vikupdater');?></button>	
						<?php wp_nonce_field('vikup.download'); ?>
						<input type="hidden" name="hash" value="<?php echo $hash;?>"/>
						<input type="hidden" name="method" value="download"/>
					</form>

				<?php
				} else {?>
					<button class="button button-primary"  id="triggerUpdate" onclick="<?php $selectedHash = $hash; ?>"><?php _e('Download', 'vikupdater');?></button>	

				<?php
				}
				?>

			
					
			<?php } else {
				echo "---";
			} ?>
			</td>
			<td>
				<form method="POST" action="options-general.php?page=vikupdater">
					<?php wp_nonce_field('vikup.remove'); ?>
					<input type="hidden" name="hash" value="<?php echo $hash;?>"/>
					<input type="hidden" name="method" value="remove"/>
					<button class="button button-error"><span class="dashicons dashicons-no" style="vertical-align: middle;"></span></button>
				</form>
			</td>
		</tr>
		
	<?php } ?>
	</table>
<?php
	} else { ?>
	<strong>
		<?php _e('No products registered!', 'vikupdater'); ?>
	</strong>
	<?php } ?>
</div>
<div class="vikthickbox" id="vikthickbox" >
	<div class="vikthick-dialog">
		<div class="vikthick-content">
			<div class="vikthick-header">
				<h2 class="vikthick-title" id="vikthickLabel"><?php echo $product['changelog'][0]['title']; ?></h2>
			</div>
			<div class="vikthick-body">
				<?php
				
					$product = json_decode($storedProducts[$selectedHash], true);
					if(isset($product['changelog'][0]['sections'])) {
						foreach ($product['changelog'][0]['sections'] as $section) {
							?>

							<h3 class="vik-section-title"><?php echo $section['title']; ?> </h3>

							<?php
							foreach ($section['children'] as $child) {
								//START CHILD
							?>

							<div class="vik-child-cnt">
								<h4 class="vik-child-title"><?php echo $child['title']; ?> </h4>
								<span class="vik-child-desc"><?php echo $child['description']; ?> </span>
							</div>

							<?php
								//END CHILD
							}
						}
					}
				?>
			</div>
			<div class="vikthick-footer">
				<form method="POST" action="options-general.php?page=vikupdater">
					<button class="button button-primary" ><?php _e('Download', 'vikupdater');?></button>	
					<?php wp_nonce_field('vikup.download'); ?>
					<input type="hidden" name="hash" value="<?php echo $selectedHash;?>"/>
					<input type="hidden" name="method" value="download"/>
				</form>
			</div>
		</div>
	</div>
</div>
<?php
}
?>
