<?php

// No direct access
defined('ABSPATH') or die('No script kiddies please!');

jimport('joomla.application.component.view');
jimport('adapter.acl.access');

/**
 * VikRentCar Shortcode view.
 * @wponly
 *
 * @since 1.0
 */
class VikRentCarViewShortcode extends JView
{
	/**
	 * @override
	 * View display method.
	 *
	 * @return 	void
	 */
	public function display($tpl = null)
	{
		$app 	= JFactory::getApplication();
		$input 	= $app->input;
		$dbo 	= JFactory::getDbo();

		$model = $this->getModel();

		$type 	= $input->getString('type');
		$return = $input->getBase64('return', '');

		$shortcode = (array) $model->loadFormData();

		JLoader::import('adapter.filesystem.folder');

		// views

		$views = array();

		// get all the views that contain a default.xml file
		// [0] : base path
		// [1] : query
		// [2] : true for recursive search
		// [3] : true to return full paths
		$files = JFolder::files(VRC_SITE_PATH . DIRECTORY_SEPARATOR . 'views', 'default.xml', true, true);

		foreach ($files as $f)
		{
			// retrieve the view ID from the path: /views/[ID]/tmpl/default.xml
			if (preg_match("/[\/\\\\]views[\/\\\\](.*?)[\/\\\\]tmpl[\/\\\\]default\.xml$/i", $f, $matches))
			{
				$id = $matches[1];
				// load the XML form
				$form = JForm::getInstance($id, $f);
				// get the view title
				$views[$id] = array(
					'name' => (string) $form->getXml()->layout->attributes()->title,
					'desc' => (string) $form->getXml()->layout->message,
				);
			}
		}
		
		$this->shortcode 	= &$shortcode;
		$this->views 		= &$views;
		$this->returnLink 	= &$return;
		$this->form 		= &$form;

		$this->addToolbar($type);
		
		// display parent
		parent::display($tpl);
	}

	/**
	 * Helper method to setup the toolbar.
	 *
	 * @return 	void
	 */
	public function addToolbar($type)
	{
		if ($type == 'edit')
		{
			JToolbarHelper::title(JText::_('VRCEDITSHORTCDMENUTITLE'));
		}
		else
		{
			JToolbarHelper::title(JText::_('VRCNEWSHORTCDMENUTITLE'));
		}

		JToolbarHelper::apply('shortcode.save');
		JToolbarHelper::save('shortcode.saveclose');
		JToolbarHelper::save2new('shortcode.savenew');
		JToolbarHelper::cancel('shortcodes.cancel');
	}
}
