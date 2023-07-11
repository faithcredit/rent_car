<?php

/**
 * Class used to control values about the package meta data
 *
 * Standard: PSR-2
 *
 * @link http://www.php-fig.org/psr/psr-2 Full Documentation
 *
 * @package SC\DUPX\ArchiveConfig
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

use Duplicator\Installer\Addons\ProBase\License;
use Duplicator\Installer\Core\Params\Models\SiteOwrMap;
use Duplicator\Installer\Utils\Log\Log;
use Duplicator\Installer\Core\Params\PrmMng;
use Duplicator\Libs\Snap\SnapIO;
use Duplicator\Libs\Snap\SnapURL;
use Duplicator\Libs\Snap\SnapUtil;
use Duplicator\Libs\Snap\SnapWP;
use Duplicator\Libs\WpConfig\WPConfigTransformer;

/**
 * singleton class
 */
class DUPX_ArchiveConfig
{
    const NOTICE_ID_PARAM_EMPTY = 'param_empty_to_validate';

    // READ-ONLY: COMPARE VALUES
    public $dup_type;
    public $created;
    public $version_dup;
    public $version_wp;
    public $version_db;
    public $version_php;
    public $version_os;
    public $packInfo;
    public $fileInfo;
    public $dbInfo;
    public $wpInfo;
    // GENERAL
    public $secure_on;
    public $secure_pass;
    public $installer_base_name   = '';
    public $installer_backup_name = '';
    public $package_name;
    public $package_hash;
    public $package_notes;
    public $wp_tableprefix;
    public $blogname;
    public $blogNameSafe;
    public $exportOnlyDB;
    //ADV OPTS
    public $opts_delete;
    //MULTISITE
    public $mu_mode;
    public $mu_generation;
    /** @var mixed[] */
    public $subsites     = [];
    public $main_site_id = 1;
    public $mu_is_filtered;
    public $mu_siteadmins = array();
    //LICENSING
    /** @var int<0, max> */
    public $license_limit = 0;
    /** @var int ENUM LICENSE TYPE */
    public $license_type = License::TYPE_UNLICENSED;
    //PARAMS
    public $overwriteInstallerParams = array();
    /** @var ?string */
    public $dbhost = null;
    /** @var ?string */
    public $dbname = null;
    /** @var ?string */
    public $dbuser = null;
    /** @var object */
    public $brand = null;
    /** @var ?string */
    public $cpnl_host;
    /** @var ?string */
    public $cpnl_user;
    /** @var ?string */
    public $cpnl_pass;
    /** @var ?string */
    public $cpnl_enable;

    /** @var ?self */
    private static $instance = null;

    /**
     * Get instance
     *
     * @return self
     */
    public static function getInstance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Singleton class constructor
     */
    protected function __construct()
    {
        $config_filepath = DUPX_Package::getPackageArchivePath();
        if (!file_exists($config_filepath)) {
            throw new Exception("Archive file $config_filepath doesn't exist");
        }

        if (($file_contents = file_get_contents($config_filepath)) === false) {
            throw new Exception("Can\'t read Archive file $config_filepath");
        }

        if (($data = json_decode($file_contents)) === null) {
            throw new Exception("Can\'t decode archive json");
        }

        foreach ($data as $key => $value) {
            $this->{$key} = $value;
        }

        //Instance Updates:
        $this->blogNameSafe = preg_replace("/[^A-Za-z0-9?!]/", '', $this->blogname);
    }

    /**
     *
     * @return bool
     */
    public function isZipArchive()
    {
        $extension = strtolower(pathinfo($this->package_name, PATHINFO_EXTENSION));
        return ($extension == 'zip');
    }

    /**
     *
     * @param string $define
     *
     * @return bool return true if define value exists
     */
    public function defineValueExists($define)
    {
        return isset($this->wpInfo->configs->defines->{$define});
    }

    public function getUsersLists()
    {
        $result = array();
        foreach ($this->wpInfo->adminUsers as $user) {
            $result[$user->ID] = $user->user_login;
        }
        return $result;
    }

    /**
     *
     * @param string $define
     * @param array  $default
     *
     * @return array
     */
    public function getDefineArrayValue($define, $default = array(
            'value'      => false,
            'inWpConfig' => false
        ))
    {
        $defines = $this->wpInfo->configs->defines;
        if (isset($defines->{$define})) {
            return (array) $defines->{$define};
        } else {
            return $default;
        }
    }

    /**
     * return define value from archive or default value if don't exists
     *
     * @param string $define
     * @param mixed  $default
     *
     * @return mixed
     */
    public function getDefineValue($define, $default = false)
    {
        $defines = $this->wpInfo->configs->defines;
        if (isset($defines->{$define})) {
            return $defines->{$define}->value;
        } else {
            return $default;
        }
    }

    /**
     * return define value from archive or default value if don't exists in wp-config
     *
     * @param string $define
     * @param mixed  $default
     *
     * @return mixed
     */
    public function getWpConfigDefineValue($define, $default = false)
    {
        $defines = $this->wpInfo->configs->defines;
        if (isset($defines->{$define}) && $defines->{$define}->inWpConfig) {
            return $defines->{$define}->value;
        } else {
            return $default;
        }
    }

    public function inWpConfigDefine($define)
    {
        $defines = $this->wpInfo->configs->defines;
        if (isset($defines->{$define})) {
            return $defines->{$define}->inWpConfig;
        } else {
            return false;
        }
    }

    /**
     *
     * @param string $key
     *
     * @return boolean
     */
    public function realValueExists($key)
    {
        return isset($this->wpInfo->configs->realValues->{$key});
    }

    /**
     * return read value from archive if exists of default if don't exists
     *
     * @param string $key
     * @param mixed  $default
     *
     * @return mixed
     */
    public function getRealValue($key, $default = false)
    {
        $values = $this->wpInfo->configs->realValues;
        if (isset($values->{$key})) {
            return $values->{$key};
        } else {
            return $default;
        }
    }

    /**
     *
     * @return string
     */
    public function getBlognameFromSelectedSubsiteId()
    {
        $subsiteId = PrmMng::getInstance()->getValue(PrmMng::PARAM_SUBSITE_ID);
        $blogname  = $this->blogname;
        if ($subsiteId > 0) {
            foreach ($this->subsites as $subsite) {
                if ($subsiteId == $subsite->id) {
                    $blogname = $subsite->blogname;
                    break;
                }
            }
        }
        return $blogname;
    }

    /**
     * Return package life
     *
     * @param string $type can be hours,human,timestamp
     *
     * @return int|string package life in hours, timestamp
     */
    public function getPackageLife($type = 'timestamp')
    {
        $created = strtotime($this->created);
        $current = strtotime(gmdate("Y-m-d H:i:s"));
        $delta   = $current - $created;

        switch ($type) {
            case 'hours':
                return max(0, floor($delta / 60 / 60));
            case 'timestamp':
            default:
                return $delta;
        }
    }

    /**
     *
     * @return int
     */
    public function totalArchiveItemsCount()
    {
        return $this->fileInfo->dirCount + $this->fileInfo->fileCount;
    }

    /**
     * Return true if source site is multisite
     *
     * @return bool
     */
    public function isNetwork()
    {
        return $this->mu_mode > 0;
    }

    /**
     * Return true if source site is subdomain multisite
     *
     * @return bool
     */
    public function isSubdomain()
    {
        return $this->mu_mode == 1;
    }

    /**
     * Return true if source site is partial multisite
     *
     * @return bool
     */
    public function isPartialNetwork()
    {
        $hasNotImportableSubsite = SnapUtil::inArrayExtended($this->subsites, function ($subsite) {
                return count($subsite->filteredTables) > 0;
        });
        return ($this->mu_mode != 0 && count($this->subsites) > 0 && $this->mu_is_filtered) || ($hasNotImportableSubsite && DUPX_InstallerState::isImportFromBackendMode());
    }

    public function setNewPathsAndUrlParamsByMainNew()
    {
        self::manageEmptyPathAndUrl(PrmMng::PARAM_PATH_WP_CORE_NEW, PrmMng::PARAM_SITE_URL);
        self::manageEmptyPathAndUrl(PrmMng::PARAM_PATH_CONTENT_NEW, PrmMng::PARAM_URL_CONTENT_NEW);
        self::manageEmptyPathAndUrl(PrmMng::PARAM_PATH_UPLOADS_NEW, PrmMng::PARAM_URL_UPLOADS_NEW);
        self::manageEmptyPathAndUrl(PrmMng::PARAM_PATH_PLUGINS_NEW, PrmMng::PARAM_URL_PLUGINS_NEW);
        self::manageEmptyPathAndUrl(PrmMng::PARAM_PATH_MUPLUGINS_NEW, PrmMng::PARAM_URL_MUPLUGINS_NEW);

        $paramsManager = PrmMng::getInstance();
        $noticeManager = DUPX_NOTICE_MANAGER::getInstance();
        $noticeManager->addNextStepNotice(array(
            'shortMsg'    => '',
            'level'       => DUPX_NOTICE_ITEM::NOTICE,
            'longMsg'     => '<span class="green">If desired, you can change the default values in "Advanced install" &gt; "Other options"</span>.',
            'longMsgMode' => DUPX_NOTICE_ITEM::MSG_MODE_HTML
            ), DUPX_NOTICE_MANAGER::ADD_UNIQUE_APPEND_IF_EXISTS, self::NOTICE_ID_PARAM_EMPTY);

        $paramsManager->save();
        $noticeManager->saveNotices();
    }

    protected static function manageEmptyPathAndUrl($pathKey, $urlKey)
    {
        $paramsManager = PrmMng::getInstance();
        $validPath     = (strlen($paramsManager->getValue($pathKey)) > 0);
        $validUrl      = (strlen($paramsManager->getValue($urlKey)) > 0);

        if ($validPath && $validUrl) {
            return true;
        }

        $paramsManager->setValue($pathKey, self::getDefaultPathUrlValueFromParamKey($pathKey));
        $paramsManager->setValue($urlKey, self::getDefaultPathUrlValueFromParamKey($urlKey));

        $noticeManager = DUPX_NOTICE_MANAGER::getInstance();
        $msg           = '<b>' . $paramsManager->getLabel($pathKey) . ' and/or ' . $paramsManager->getLabel($urlKey) . '</b> can\'t be generated automatically so they are set to their default value.' . "<br>\n";
        $msg          .= $paramsManager->getLabel($pathKey) . ': ' . $paramsManager->getValue($pathKey) . "<br>\n";
        $msg          .= $paramsManager->getLabel($urlKey) . ': ' . $paramsManager->getValue($urlKey) . "<br>\n";

        $noticeManager->addNextStepNotice(array(
            'shortMsg'    => 'URLs and/or PATHs set automatically to their default value.',
            'level'       => DUPX_NOTICE_ITEM::NOTICE,
            'longMsg'     => $msg . "<br>\n",
            'longMsgMode' => DUPX_NOTICE_ITEM::MSG_MODE_HTML
            ), DUPX_NOTICE_MANAGER::ADD_UNIQUE_APPEND, self::NOTICE_ID_PARAM_EMPTY);
    }

    public static function getDefaultPathUrlValueFromParamKey($paramKey)
    {
        $paramsManager = PrmMng::getInstance();
        switch ($paramKey) {
            case PrmMng::PARAM_SITE_URL:
                return $paramsManager->getValue(PrmMng::PARAM_URL_NEW);
            case PrmMng::PARAM_URL_CONTENT_NEW:
                return $paramsManager->getValue(PrmMng::PARAM_URL_NEW) . '/wp-content';
            case PrmMng::PARAM_URL_UPLOADS_NEW:
                return $paramsManager->getValue(PrmMng::PARAM_URL_CONTENT_NEW) . '/uploads';
            case PrmMng::PARAM_URL_PLUGINS_NEW:
                return $paramsManager->getValue(PrmMng::PARAM_URL_CONTENT_NEW) . '/plugins';
            case PrmMng::PARAM_URL_MUPLUGINS_NEW:
                return $paramsManager->getValue(PrmMng::PARAM_URL_CONTENT_NEW) . '/mu-plugins';
            case PrmMng::PARAM_PATH_WP_CORE_NEW:
                return $paramsManager->getValue(PrmMng::PARAM_PATH_NEW);
            case PrmMng::PARAM_PATH_CONTENT_NEW:
                return $paramsManager->getValue(PrmMng::PARAM_PATH_NEW) . '/wp-content';
            case PrmMng::PARAM_PATH_UPLOADS_NEW:
                return $paramsManager->getValue(PrmMng::PARAM_PATH_CONTENT_NEW) . '/uploads';
            case PrmMng::PARAM_PATH_PLUGINS_NEW:
                return $paramsManager->getValue(PrmMng::PARAM_PATH_CONTENT_NEW) . '/plugins';
            case PrmMng::PARAM_PATH_MUPLUGINS_NEW:
                return $paramsManager->getValue(PrmMng::PARAM_PATH_CONTENT_NEW) . '/mu-plugins';
            default:
                throw new Exception('Invalid URL or PATH param');
        }
    }

    /**
     *
     * @param string $oldMain
     * @param string $newMain
     * @param string $subOld
     *
     * @return boolean|string  return false if cant generate new sub string
     */
    public static function getNewSubString($oldMain, $newMain, $subOld)
    {
        if (($relativePath = SnapIO::getRelativePath($subOld, $oldMain)) === false) {
            return false;
        }
        return $newMain . '/' . $relativePath;
    }

    /**
     *
     * @param string $oldMain
     * @param string $newMain
     * @param string $subOld
     *
     * @return boolean|string  return false if cant generate new sub string
     */
    public static function getNewSubUrl($oldMain, $newMain, $subOld)
    {

        $parsedOldMain = SnapURL::parseUrl($oldMain);
        $parsedNewMain = SnapURL::parseUrl($newMain);
        $parsedSubOld  = SnapURL::parseUrl($subOld);

        $parsedSubNew           = $parsedSubOld;
        $parsedSubNew['scheme'] = $parsedNewMain['scheme'];
        $parsedSubNew['port']   = $parsedNewMain['port'];

        if ($parsedOldMain['host'] !== $parsedSubOld['host']) {
            return false;
        }
        $parsedSubNew['host'] = $parsedNewMain['host'];

        if (($newPath = self::getNewSubString($parsedOldMain['path'], $parsedNewMain['path'], $parsedSubOld['path'])) === false) {
            return false;
        }
        $parsedSubNew['path'] = $newPath;
        return SnapURL::buildUrl($parsedSubNew);
    }

    /**
     *
     * @return bool
     */
    public function isTablesCaseSensitive()
    {
        return $this->dbInfo->isTablesUpperCase;
    }

    public function isTablePrefixChanged()
    {
        return $this->wp_tableprefix != PrmMng::getInstance()->getValue(PrmMng::PARAM_DB_TABLE_PREFIX);
    }

    public function getTableWithNewPrefix($table)
    {
        $search  = '/^' . preg_quote($this->wp_tableprefix, '/') . '(.*)/';
        $replace = PrmMng::getInstance()->getValue(PrmMng::PARAM_DB_TABLE_PREFIX) . '$1';
        return preg_replace($search, $replace, $table, 1);
    }

    /**
     *
     * @param int $subsiteId
     *
     * @return boolean|string return false if don't exists subsiteid
     */
    public function getSubsitePrefixByParam($subsiteId)
    {
        if (($subSiteObj = $this->getSubsiteObjById($subsiteId)) === false) {
            return false;
        }

        if (!$this->isTablePrefixChanged()) {
            return $subSiteObj->blog_prefix;
        } else {
            $search  = '/^' . preg_quote($this->wp_tableprefix, '/') . '(.*)/';
            $replace = PrmMng::getInstance()->getValue(PrmMng::PARAM_DB_TABLE_PREFIX) . '$1';
            return preg_replace($search, $replace, $subSiteObj->blog_prefix, 1);
        }
    }

    public function getMainSiteIndex()
    {
        static $mainSubsiteIndex = null;
        if (is_null($mainSubsiteIndex)) {
            $mainSubsiteIndex = -1;
            if (!empty($this->subsites)) {
                foreach ($this->subsites as $index => $subsite) {
                    if ($subsite->id === $this->main_site_id) {
                        $mainSubsiteIndex = $index;
                        break;
                    }
                }
                if ($mainSubsiteIndex == -1) {
                    $mainSubsiteIndex = 0;
                }
            }
        }
        return $mainSubsiteIndex;
    }

    /**
     * Return main site info object
     *
     * @return stdClass
     */
    public function getMainSiteInfo()
    {
        return $this->subsites[$this->getMainSiteIndex()];
    }

    /**
     * @return array
     */
    public function getSubsitesIds()
    {
        static $subsitesIds = null;
        if (is_null($subsitesIds)) {
            $subsitesIds = array();
            foreach ($this->subsites as $subsite) {
                $subsitesIds[] = $subsite->id;
            }
        }

        return $subsitesIds;
    }

    /**
     * Get subsite object info by id
     *
     * @param int $id
     *
     * @return boolean|stdClass refurn false if id dont exists
     */
    public function getSubsiteObjById($id)
    {
        static $indexCache = array();

        if (!isset($indexCache[$id])) {
            foreach ($this->subsites as $subsite) {
                if ($subsite->id == $id) {
                    $indexCache[$id] = $subsite;
                    break;
                }
            }
            if (!isset($indexCache[$id])) {
                $indexCache[$id] = false;
            }
        }

        return $indexCache[$id];
    }

    public function getOldUrlScheme()
    {
        static $oldScheme = null;
        if (is_null($oldScheme)) {
            $siteUrl   = $this->getRealValue('siteUrl');
            $oldScheme = parse_url($siteUrl, PHP_URL_SCHEME);
            if ($oldScheme === false) {
                $oldScheme = 'http';
            }
        }
        return $oldScheme;
    }

    /**
     * subsite object from archive
     *
     * @param stdClass $subsite
     *
     * @return string
     */
    public function getUrlFromSubsiteObj($subsite)
    {
        return $this->getOldUrlScheme() . '://' . $subsite->domain . $subsite->path;
    }

    /**
     * @param stdClass $subsite
     *
     * @return string the uploads url with the subsite specific url (e.g. example.com/subsite/wp-content/uploads)
     */
    public function getUploadsUrlFromSubsiteObj($subsite)
    {
        if ($subsite->id == $this->getMainSiteIndex()) {
            return PrmMng::getInstance()->getValue(PrmMng::PARAM_URL_UPLOADS_OLD);
        }

        $subsiteOldUrl  = rtrim($this->getUrlFromSubsiteObj($subsite), '/');
        $mainOldUrlHost = parse_url(PrmMng::getInstance()->getValue(PrmMng::PARAM_URL_OLD), PHP_URL_HOST);

        return preg_replace(
            "/(https?:\/\/(?:www\.)?" . preg_quote($mainOldUrlHost) . ")(.*)/",
            $subsiteOldUrl . "$2",
            PrmMng::getInstance()->getValue(PrmMng::PARAM_URL_UPLOADS_OLD)
        );
    }

    /**
     *
     * @return array
     */
    public function getOldUrlsArrayIdVal()
    {
        if (empty($this->subsites)) {
            return array();
        }

        $result = array();

        foreach ($this->subsites as $subsite) {
            $result[$subsite->id] = rtrim($this->getUrlFromSubsiteObj($subsite), '/');
        }
        return $result;
    }

    /**
     * get relative path in archive of wordpress main paths
     *
     * @param string $pathKey (abs,home,plugins ...)
     *
     * @return string
     */
    public function getRelativePathsInArchive($pathKey = null)
    {
        static $realtviePaths = null;

        if (is_null($realtviePaths)) {
            $realtviePaths = (array) $this->getRealValue('archivePaths');
            foreach ($realtviePaths as $key => $path) {
                $realtviePaths[$key] = SnapIO::getRelativePath($path, $this->wpInfo->targetRoot);
            }
        }

        if (!empty($pathKey)) {
            if (array_key_exists($pathKey, $realtviePaths)) {
                return $realtviePaths[$pathKey];
            } else {
                throw new Exception('Invalid path key ' . $pathKey);
            }
        } else {
            return $realtviePaths;
        }
    }

    /**
     *
     * @param string          $path
     * @param string|string[] $pathKeys
     *
     * @return boolean
     */
    public function isChildOfArchivePath($path, $pathKeys = array())
    {
        if (is_scalar($pathKeys)) {
            $pathKeys = array($pathKeys);
        }

        $mainPaths = $this->getRelativePathsInArchive();
        foreach ($pathKeys as $key) {
            if (!isset($mainPaths[$key])) {
                continue;
            }

            if (strlen($mainPaths[$key]) == 0) {
                return true;
            }

            if (strpos($path, $mainPaths[$key]) === 0) {
                return true;
            }
        }
        return false;
    }

    /**
     *
     * @return string
     */
    public function getRelativeMuPlugins()
    {
        static $relativePath = null;
        if (is_null($relativePath)) {
            $relativePath = SnapIO::getRelativePath(
                PrmMng::getInstance()->getValue(PrmMng::PARAM_PATH_MUPLUGINS_NEW),
                PrmMng::getInstance()->getValue(PrmMng::PARAM_PATH_NEW)
            );
        }
        return $relativePath;
    }

    /**
     * return the mapping paths from relative path of archive zip and target folder
     * if exist only one entry return the target folter string
     *
     * @param bool $reset if true recalculater path mappintg
     *
     * @return string|array
     */
    public function getPathsMapping($reset = false)
    {
        static $pathsMapping = null;

        if (is_null($pathsMapping) || $reset) {
            $prmMng       = PrmMng::getInstance();
            $pathsMapping = array();

            $targeRootPath = $this->wpInfo->targetRoot;
            $paths         = (array) $this->getRealValue('archivePaths');

            foreach ($paths as $key => $path) {
                if (($relativePath = SnapIO::getRelativePath($path, $targeRootPath)) !== false) {
                    $paths[$key] = $relativePath;
                }
            }
            $pathsMapping[$paths['home']] = $prmMng->getValue(PrmMng::PARAM_PATH_NEW);
            if ($paths['home'] !== $paths['abs']) {
                $pathsMapping[$paths['abs']] = $prmMng->getValue(PrmMng::PARAM_PATH_WP_CORE_NEW);
            } elseif ($prmMng->getValue(PrmMng::PARAM_PATH_NEW) != $prmMng->getValue(PrmMng::PARAM_PATH_WP_CORE_NEW)) {
                // In case ABSPATH and HOME PATH are the same in the source site and different in the destination site is necessary
                // to map only the core files in a different way. site is necessary to change the path of the core files of wordpress
                $rootCoreList = SnapWP::getWpCoreFilesListInFolder();
                $absNew       = $prmMng->getValue(PrmMng::PARAM_PATH_WP_CORE_NEW);
                foreach ($rootCoreList['dirs'] as $dir) {
                    $pathsMapping[$paths['abs'] . $dir] = $absNew . '/' . $dir;
                }
                foreach ($rootCoreList['files'] as $file) {
                    if ($file == 'index.php') {
                        continue;
                    }
                    $pathsMapping[$paths['abs'] . $file] = $absNew . '/' . $file;
                }
            }
            $pathsMapping[$paths['wpcontent']] = $prmMng->getValue(PrmMng::PARAM_PATH_CONTENT_NEW);
            $pathsMapping[$paths['plugins']]   = $prmMng->getValue(PrmMng::PARAM_PATH_PLUGINS_NEW);
            $pathsMapping[$paths['muplugins']] = $prmMng->getValue(PrmMng::PARAM_PATH_MUPLUGINS_NEW);

            switch (DUPX_InstallerState::getInstType()) {
                case DUPX_InstallerState::INSTALL_SINGLE_SITE:
                case DUPX_InstallerState::INSTALL_MULTISITE_SUBDOMAIN:
                case DUPX_InstallerState::INSTALL_MULTISITE_SUBFOLDER:
                case DUPX_InstallerState::INSTALL_RBACKUP_SINGLE_SITE:
                case DUPX_InstallerState::INSTALL_RBACKUP_MULTISITE_SUBDOMAIN:
                case DUPX_InstallerState::INSTALL_RBACKUP_MULTISITE_SUBFOLDER:
                case DUPX_InstallerState::INSTALL_RECOVERY_SINGLE_SITE:
                case DUPX_InstallerState::INSTALL_RECOVERY_MULTISITE_SUBDOMAIN:
                case DUPX_InstallerState::INSTALL_RECOVERY_MULTISITE_SUBFOLDER:
                    $pathsMapping[$paths['uploads']] = $prmMng->getValue(PrmMng::PARAM_PATH_UPLOADS_NEW);
                    break;
                case DUPX_InstallerState::INSTALL_STANDALONE:
                    $pathsMapping[$paths['uploads']] = $prmMng->getValue(PrmMng::PARAM_PATH_UPLOADS_NEW);
                    if (($subsiteId = $prmMng->getValue(PrmMng::PARAM_SUBSITE_ID)) > 1) {
                        $subSiteObj                            = $this->getSubsiteObjById($subsiteId);
                        $pathsMapping[$subSiteObj->uploadPath] = $prmMng->getValue(PrmMng::PARAM_PATH_UPLOADS_NEW);
                    }
                    break;
                case DUPX_InstallerState::INSTALL_SINGLE_SITE_ON_SUBDOMAIN:
                case DUPX_InstallerState::INSTALL_SINGLE_SITE_ON_SUBFOLDER:
                case DUPX_InstallerState::INSTALL_SUBSITE_ON_SUBDOMAIN:
                case DUPX_InstallerState::INSTALL_SUBSITE_ON_SUBFOLDER:
                    /** @var SiteOwrMap[] $overwriteMapping */
                    $overwriteMapping = PrmMng::getInstance()->getValue(PrmMng::PARAM_SUBSITE_OVERWRITE_MAPPING);

                    foreach ($overwriteMapping as $map) {
                        if ($map->getTargetId() < 1) {
                            // if it is 0 the site has not been created yet therefore it is skipped
                            continue;
                        }
                        $sourceInfo                              = $map->getSourceSiteInfo();
                        $targetInfo                              = $map->getTargetSiteInfo();
                        $pathsMapping[$sourceInfo['uploadPath']] = $targetInfo['fullUploadPath'];
                    }
                    break;
                case DUPX_InstallerState::INSTALL_NOT_SET:
                    throw new Exception('Cannot change setup with current installation type [' . DUPX_InstallerState::getInstType() . ']');
                default:
                    throw new Exception('Unknown mode');
            }

            // remove all empty values for safe,
            // This should never happen, but if it does, there is a risk that the installer will remove all the files in the server root.
            $pathsMapping = array_filter($pathsMapping, function ($value) {
                return strlen($value) > 0;
            });

            $pathsMapping = SnapIO::sortBySubfoldersCount($pathsMapping, true, false, true);

            $unsetKeys = array();
            foreach (array_reverse($pathsMapping) as $oldPathA => $newPathA) {
                foreach ($pathsMapping as $oldPathB => $newPathB) {
                    if ($oldPathA == $oldPathB) {
                        continue;
                    }

                    if (
                        ($relativePathOld = SnapIO::getRelativePath($oldPathA, $oldPathB)) === false ||
                        ($relativePathNew = SnapIO::getRelativePath($newPathA, $newPathB)) === false
                    ) {
                        continue;
                    }

                    if ($relativePathOld == $relativePathNew) {
                        $unsetKeys[] = $oldPathA;
                        break;
                    }
                }
            }
            foreach (array_unique($unsetKeys) as $unsetKey) {
                unset($pathsMapping[$unsetKey]);
            }

            $tempArray    = $pathsMapping;
            $pathsMapping = array();
            foreach ($tempArray as $key => $val) {
                $pathsMapping['/' . $key] = $val;
            }

            switch (count($pathsMapping)) {
                case 0:
                    throw new Exception('Paths archive mapping is inconsistent');
                case 1:
                    $pathsMapping = reset($pathsMapping);
                    break;
                default:
            }

            Log::info("--------------------------------------");
            Log::info('PATHS MAPPING : ' . Log::v2str($pathsMapping));
            Log::info("--------------------------------------");
        }
        return $pathsMapping;
    }

    /**
     * get absolute target path from archive relative path
     *
     * @param string $pathInArchive
     *
     * @return string
     */
    public function destFileFromArchiveName($pathInArchive)
    {
        $pathsMapping = $this->getPathsMapping();

        if (is_string($pathsMapping)) {
            return $pathsMapping . '/' . ltrim($pathInArchive, '\\/');
        }

        if (strlen($pathInArchive) === 0) {
            $pathInArchive = '/';
        } elseif ($pathInArchive[0] != '/') {
            $pathInArchive = '/' . $pathInArchive;
        }

        foreach ($pathsMapping as $archiveMainPath => $newMainPath) {
            if (($relative = SnapIO::getRelativePath($pathInArchive, $archiveMainPath)) !== false) {
                return $newMainPath . (strlen($relative) ? ('/' . $relative) : '');
            }
        }

        // if don't find corrispondance in mapping get the path new as default (this should never happen)
        return PrmMng::getInstance()->getValue(PrmMng::PARAM_PATH_NEW) . '/' . ltrim($pathInArchive, '\\/');
    }

    protected function destCoreFilesExtraCheck()
    {
        static $extraCheck = null;

        if ($extraCheck === null) {
            $prmMng     = PrmMng::getInstance();
            $extraCheck = (
                $prmMng->getValue(PrmMng::PARAM_PATH_OLD) == $prmMng->getValue(PrmMng::PARAM_PATH_WP_CORE_OLD) &&
                $prmMng->getValue(PrmMng::PARAM_PATH_NEW) != $prmMng->getValue(PrmMng::PARAM_PATH_WP_CORE_NEW)
            );
        }

        return $extraCheck;
    }

    /**
     *
     * @return string[]
     */
    public function invalidCharsets()
    {
        return array_diff($this->dbInfo->charSetList, DUPX_DB_Functions::getInstance()->getCharsetsList());
    }

    /**
     *
     * @return string[]
     */
    public function invalidCollations()
    {
        return array_diff($this->dbInfo->collationList, DUPX_DB_Functions::getInstance()->getCollationsList());
    }

    /**
     *
     * @return string[] list of MySQL engines in source site not supported by the current database
     */
    public function invalidEngines()
    {
        return array_diff($this->dbInfo->engineList, DUPX_DB_Functions::getInstance()->getSupportedEngineList());
    }

    /**
     * Update Wp config by param
     *
     * @param WPConfigTransformer $confTrans
     * @param string              $defineKey
     * @param string              $paramKey
     *
     * @return void
     */
    public static function updateWpConfigByParam(WPConfigTransformer $confTrans, $defineKey, $paramKey)
    {
        $paramsManager = PrmMng::getInstance();
        $wpConfVal     = $paramsManager->getValue($paramKey);
        self::updateWpConfigByValue($confTrans, $defineKey, $wpConfVal);
    }

    /**
     * Update wp conf
     *
     * @param WPConfigTransformer $confTrans
     * @param string              $defineKey
     * @param array               $wpConfVal
     * @param mixed               $customValue if is not null custom value overwrite value
     *
     * @return void
     */
    public static function updateWpConfigByValue(WPConfigTransformer $confTrans, $defineKey, $wpConfVal, $customValue = null)
    {
        if ($wpConfVal['inWpConfig']) {
            $stringVal = '';
            if ($customValue !== null) {
                $stringVal = $customValue;
                $updParam  = array('raw' => true, 'normalize' => true);
            } else {
                switch (gettype($wpConfVal['value'])) {
                    case "boolean":
                        $stringVal = $wpConfVal['value'] ? 'true' : 'false';
                        $updParam  = array('raw' => true, 'normalize' => true);
                        break;
                    case "integer":
                    case "double":
                        $stringVal = (string) $wpConfVal['value'];
                        $updParam  = array('raw' => true, 'normalize' => true);
                        break;
                    case "string":
                        $stringVal = $wpConfVal['value'];
                        $updParam  = array('raw' => false, 'normalize' => true);
                        break;
                    case "NULL":
                        $stringVal = 'null';
                        $updParam  = array('raw' => true, 'normalize' => true);
                        break;
                    case "array":
                    case "object":
                    case "resource":
                    case "resource (closed)":
                    case "unknown type":
                    default:
                        $stringVal = '';
                        $updParam  = array('raw' => true, 'normalize' => true);
                        break;
                }
            }

            Log::info('WP CONFIG UPDATE ' . $defineKey . ' ' . Log::v2str($stringVal));
            $confTrans->update('constant', $defineKey, $stringVal, $updParam);
        } else {
            if ($confTrans->exists('constant', $defineKey)) {
                Log::info('WP CONFIG REMOVE ' . $defineKey);
                $confTrans->remove('constant', $defineKey);
            }
        }
    }
}
