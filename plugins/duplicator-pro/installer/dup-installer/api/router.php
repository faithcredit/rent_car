<?php

if (!defined('DUPXABSPATH')) {
    define('DUPXABSPATH', dirname(__FILE__));
}

use Duplicator\Installer\Core\Bootstrap;

define('DUPX_VERSION', '4.5.11');
define('DUPX_INIT', str_replace('\\', '/', dirname(__DIR__)));
define('DUPX_ROOT', preg_match('/^[\\\\\/]?$/', dirname(DUPX_INIT)) ? '/' : dirname(DUPX_INIT));

require_once(DUPX_INIT . '/src/Utils/Autoloader.php');
Duplicator\Installer\Utils\Autoloader::register();
/**
 * init constants and include
 */
Bootstrap::init(2);

require_once('class.api.php');
require_once('class.cpnl.base.php');
require_once('class.cpnl.ctrl.php');

//Register API Engine - If it processes the current route it spits out JSON and exits the process
$API_Server = new DUPX_API_Server();
$API_Server->add_controller(new DUPX_cPanel_Controller());
$API_Server->process_request(false);

dupxTplRender('api/front', array(
    'apiControllers' => $API_Server->controllers,
    'dupVersion'     => DUPX_ArchiveConfig::getInstance()->version_dup
));
