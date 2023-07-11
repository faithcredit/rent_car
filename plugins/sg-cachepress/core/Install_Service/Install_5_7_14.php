<?php
namespace SiteGround_Optimizer\Install_Service;

use SiteGround_Optimizer\Htaccess\Htaccess;
use SiteGround_Optimizer\Options\Options;

class Install_5_7_14 extends Install {

	/**
	 * The default install version. Overridden by the installation packages.
	 *
	 * @since 5.7.14
	 *
	 * @access protected
	 *
	 * @var string $version The install version.
	 */
	protected static $version = '5.7.14';

	/**
	 * Run the install procedure.
	 *
	 * @since 5.7.14
	 */
	public function install() {
		if (
			! Options::is_enabled( 'siteground_optimizer_user_agent_header' )
		) {
			$htaccess = new Htaccess();
			$htaccess->enable( 'user-agent-vary' );
		}
	}
}
