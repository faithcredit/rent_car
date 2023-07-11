<?php
/**
 * @package     VikUpdater
 * @subpackage  constants
 * @author      E4J s.r.l.
 * @copyright   Copyright (C) 2018 E4J s.r.l. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 * @link        https://vikwp.com
 */

defined('ABSPATH') or die('No script kiddies please!');

// Plugin Version
define("VIKUPDATER_VERSION", '1.4.4');
// Language Folder
define("VIKUPDATER_LANGUAGEROOT", basename(dirname(__FILE__)).DIRECTORY_SEPARATOR."languages".DIRECTORY_SEPARATOR);
// Assets Folder
define("VIKUPDATER_ASSETSROOT", dirname(__FILE__).DIRECTORY_SEPARATOR."assets".DIRECTORY_SEPARATOR);
// Assets Directory Path
define("VIKUPDATER_ASSETS_URI", plugin_dir_url(__FILE__)."assets/");
// Install Folder
define("VIKUPDATER_DOWNLOADROOT", dirname(__FILE__).DIRECTORY_SEPARATOR."assets".DIRECTORY_SEPARATOR."downloads".DIRECTORY_SEPARATOR);
