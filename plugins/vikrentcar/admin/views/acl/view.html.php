<?php

// No direct access
defined('ABSPATH') or die('No script kiddies please!');

jimport('joomla.application.component.view');
jimport('adapter.acl.access');

/**
 * VikRentCar ACL view.
 * @wponly
 *
 * @since 1.0
 */
class VikRentCarViewAcl extends JView
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
		$active = $input->getString('activerole', '');

		// get roles
		$roles = array();

		foreach (wp_roles()->roles as $slug => $role)
		{
			$roles[$slug] = $role['name'];
		}

		// reverse the roles (from the lowest to the highest)
		$roles = array_reverse($roles);

		// get actions
		$actions = JAccess::getActions('vikrentcar');

		if (empty($active))
		{
			if (count($user->roles))
			{
				$active = $user->roles[0];
			}
			else
			{
				$keys = array_keys($roles);
				$active = array_shift($keys);
			}
		}

		$this->roles 		= &$roles;
		$this->actions 		= &$actions;
		$this->user 		= &$user;
		$this->returnLink 	= &$return;
		$this->activeRole 	= &$active;

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
		JToolbarHelper::title(JText::_('VRCACLMENUTITLE'));

		JToolbarHelper::apply('acl.save');
		JToolbarHelper::save('acl.saveclose');
		JToolbarHelper::cancel('acl.cancel');
	}
}
