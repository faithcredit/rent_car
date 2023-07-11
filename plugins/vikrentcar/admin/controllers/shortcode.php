<?php

// No direct access
defined('ABSPATH') or die('No script kiddies please!');

JLoader::import('adapter.mvc.controllers.admin');

/**
 * VikRentCar plugin Shortcode controller.
 *
 * @since 	1.0
 * @see 	JControllerAdmin
 */
class VikRentCarControllerShortcode extends JControllerAdmin
{
	public function savenew()
	{
		$this->save(2);
	}

	public function saveclose()
	{
		$this->save(1);
	}

	public function save($close = 0)
	{
		$app 	= JFactory::getApplication();
		$input 	= $app->input;
		$dbo 	= JFactory::getDbo();

		// get return URL
		$encoded = $input->getBase64('return', '');

		// make sure the user is authorised to change shortcodes
		if (!JFactory::getUser()->authorise('core.admin', 'com_vikrentcar'))
		{
			$app->redirect($return);
		}

		// get item from request
		$data = $this->model->getFormData();

		// dispatch model to save the item
		$id = $this->model->save($data);

		if ($close == 2)
		{
			// save and new
			$return = 'admin.php?option=com_vikrentcar&task=shortcodes.create&return=' . $encoded;
		}
		else if ($close == 1)
		{
			// save and close
			$return = 'admin.php?option=com_vikrentcar&view=shortcodes&return=' . $encoded;
		}
		else
		{
			// save and stay in edit page
			$return = 'admin.php?option=com_vikrentcar&task=shortcodes.edit&cid[]=' . $id . '&return=' . $encoded;
		}

		$app->redirect($return);
	}

	public function params()
	{
		$input = JFactory::getApplication()->input;

		$id 	= $input->getInt('id', 0);
		$type 	= $input->getString('type', '');

		// dispatch model to get the item (an empty ITEM if not exists)
		$item = $this->model->getItem($id);

		// inject the type to load the right form
		$item->type = $type;

		// obtain the type form
		$form = $this->model->getTypeForm($item);

		// if the form doesn't exist, the type is probably empty
		if (!$form)
		{
			// return an empty HTML
			echo "";
		}
		// render the form and encode the response
		else
		{
			$args = json_decode($item->json);
			echo json_encode($form->renderForm($args));
		}
		
		exit;
	}

	/**
	 * This task will create a page on WordPress with the requested Shortcode inside it.
	 * This is useful to automatically link Shortcodes in pages with no manual actions.
	 * 
	 * @since 	1.1.0
	 */
	public function add_to_page()
	{
		$app 	= JFactory::getApplication();
		$input 	= $app->input;
		$dbo 	= JFactory::getDbo();

		// get return URL
		$encoded = $input->getBase64('return', '');
		$return = 'admin.php?option=com_vikrentcar&view=shortcodes&return=' . $encoded;

		// make sure the user is authorised to change shortcodes
		if (!JFactory::getUser()->authorise('core.admin', 'com_vikrentcar')) {
			$app->redirect($return);
			exit;
		}

		// get shortcode ID
		$shortcode_id = $input->getInt('sc_id', 0);
		if (empty($shortcode_id)) {
			$app->enqueueMessage('Invalid Shortcode ID', 'error');
			$app->redirect($return);
			exit;
		}

		// get shortcode record
		$item = $this->model->getItem($shortcode_id);
		if (!is_object($item) || empty($item->id)) {
			$app->enqueueMessage('Shortcode not found', 'error');
			$app->redirect($return);
			exit;
		}

		// make sure this Shortcode is not already linked to a post_id
		if (!empty($item->post_id)) {
			$app->enqueueMessage('This Shortcode is already linked to the page/post ID ' . $item->post_id, 'error');
			$app->redirect($return);
			exit;
		}

		/**
		 * Add a new page (we allow a WP_ERROR to be thrown in case of failure).
		 * This should automatically trigger the hook that we use to link the Shortcode 
		 * to the new page/post ID, and so there's no need to update the item.
		 */
		$new_page_id = wp_insert_post(array(
			'post_title' => (!empty($item->name) ? $item->name : JText::_($item->title)),
			'post_content' => $item->shortcode,
			'post_status' => 'publish',
			'post_type' => 'page',
		), true);

		if (!$new_page_id) {
			$app->enqueueMessage('Error creating the new page on your website', 'error');
			$app->redirect($return);
			exit;
		}

		// add success message and redirect
		$app->enqueueMessage(JText::_('VRC_SC_ADDTOPAGE_OK'));
		$app->redirect($return);
	}
}
