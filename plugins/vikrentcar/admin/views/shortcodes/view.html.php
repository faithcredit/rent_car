<?php

// No direct access
defined('ABSPATH') or die('No script kiddies please!');

jimport('joomla.application.component.view');
jimport('adapter.acl.access');

/**
 * VikRentCar Shortcodes view.
 * @wponly
 *
 * @since 1.0
 */
class VikRentCarViewShortcodes extends JView
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
		$user 	= JFactory::getUser();

		if (!$user->authorise('core.admin', 'com_vikrentcar'))
		{
			wp_die(
				'<h1>' . JText::_('FATAL_ERROR') . '</h1>' .
				'<p>' . JText::_('RESOURCE_AUTH_ERROR') . '</p>',
				403
			);
		}

		$return = $input->getBase64('return', '');

		// get filters
		$filters = array();
		$filters['search'] = $app->getUserStateFromRequest('shortcode.filters.search', 'filter_search', '', 'string');
		$filters['lang']   = $app->getUserStateFromRequest('shortcode.filters.lang', 'filter_lang', '*', 'string');
		$filters['type']   = $app->getUserStateFromRequest('shortcode.filters.type', 'filter_type', '', 'string');

		// get shortcodes

		$shortcodes = array();

		$lim 	= $app->getUserStateFromRequest('shortcodes.limit', 'limit', $app->get('list_limit'), 'uint');
		$lim0 	= $app->getUserStateFromRequest('vrcshortcodes.limitstart', 'limitstart', 0, 'uint');
		$navbut	= "";

		/**
		 * Filters the shortcodes by using the requested values.
		 *
		 * @since 1.1.5
		 */
		
		$q = $dbo->getQuery(true)
			->select('SQL_CALC_FOUND_ROWS *')
			->from($dbo->qn('#__vikrentcar_wpshortcodes'));

		if ($filters['search'])
		{
			$q->where($dbo->qn('name') . ' LIKE ' . $dbo->q("%{$filters['search']}%"));
		}

		if ($filters['lang'] != '*')
		{
			$q->where($dbo->qn('lang') . ' = ' . $dbo->q($filters['lang']));
		}

		if ($filters['type'])
		{
			$q->where($dbo->qn('type') . ' = ' . $dbo->q($filters['type']));
		}

		$dbo->setQuery($q, $lim0, $lim);
		$dbo->execute();

		if ($dbo->getNumRows())
		{
			$shortcodes = $dbo->loadObjectList();

			$dbo->setQuery('SELECT FOUND_ROWS()');
			jimport('joomla.html.pagination');
			$pageNav = new JPagination($dbo->loadResult(), $lim0, $lim);
			$navbut = '<table align="center"><tr><td>' . $pageNav->getListFooter() . '</td></tr></table>';
		}

		JLoader::import('adapter.filesystem.folder');

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
				$views[$id] = (string) $form->getXml()->layout->attributes()->title;
			}
		}
		
		$this->shortcodes 	= &$shortcodes;
		$this->navbut 		= &$navbut;
		$this->returnLink 	= &$return;
		$this->filters 		= &$filters;
		$this->views 		= &$views;

		$this->addToolbar();
		
		// display parent
		parent::display($tpl);
	}

	/**
	 * Helper method to setup the toolbar.
	 *
	 * @return 	void
	 */
	public function addToolbar()
	{
		JToolbarHelper::title(JText::_('VRCSHORTCDSMENUTITLE'));

		JToolbarHelper::addNew('shortcodes.create');
		JToolbarHelper::editList('shortcodes.edit');
		JToolbarHelper::deleteList(JText::_('VRCDELCONFIRM'), 'shortcodes.delete');
		JToolbarHelper::cancel('shortcodes.back');
	}
}
