<?php

/**
 * @package   Duplicator/Installer
 * @copyright (c) 2022, Snap Creek LLC
 */

namespace Duplicator\Installer\Core\Deploy\Database;

use Duplicator\Installer\Core\Deploy\Multisite;
use Duplicator\Installer\Core\Params\Models\SiteOwrMap;
use Duplicator\Installer\Core\Params\PrmMng;
use Duplicator\Installer\Utils\Log\Log;
use Duplicator\Libs\Snap\SnapIO;
use DUPX_ArchiveConfig;
use DUPX_DB_Tables;
use DUPX_InstallerState;
use DUPX_S_R_ITEM;
use DUPX_S_R_MANAGER;
use DUPX_U;
use DUPX_UpdateEngine;
use Exception;

class DbReplace
{
    /** @var string */
    protected $mainUrlOld = '';
    /** @var string */
    protected $mainUrlNew = '';
    /** @var bool */
    protected $forceReplaceSiteSubfolders = false;

    /**
     * Class constructor
     */
    public function __construct()
    {
        $prmMng           = PrmMng::getInstance();
        $this->mainUrlOld = $prmMng->getValue(PrmMng::PARAM_URL_OLD);
        $this->mainUrlNew = $prmMng->getValue(PrmMng::PARAM_URL_NEW);
    }

    /**
     * Set search and replace strings
     *
     * @return bool
     */
    public function setSearchReplace()
    {
        $this->setCustomReplaceList();

        switch (DUPX_InstallerState::getInstType()) {
            case DUPX_InstallerState::INSTALL_MULTISITE_SUBDOMAIN:
            case DUPX_InstallerState::INSTALL_MULTISITE_SUBFOLDER:
                $this->setMultisiteSearchReplace();
                $this->setGlobalSearchAndReplaceList();
                break;
            case DUPX_InstallerState::INSTALL_STANDALONE:
                $this->setStandalonSearchReplace();
                $this->setGlobalSearchAndReplaceList();
                break;
            case DUPX_InstallerState::INSTALL_SINGLE_SITE:
                $this->setGlobalSearchAndReplaceList();
                break;
            case DUPX_InstallerState::INSTALL_SINGLE_SITE_ON_SUBDOMAIN:
            case DUPX_InstallerState::INSTALL_SINGLE_SITE_ON_SUBFOLDER:
            case DUPX_InstallerState::INSTALL_SUBSITE_ON_SUBDOMAIN:
            case DUPX_InstallerState::INSTALL_SUBSITE_ON_SUBFOLDER:
                $this->setAddOnMultisiteSearchReplace();
                $this->setGlobalSearchAndReplaceList();
                break;
            case DUPX_InstallerState::INSTALL_RBACKUP_MULTISITE_SUBDOMAIN:
            case DUPX_InstallerState::INSTALL_RBACKUP_MULTISITE_SUBFOLDER:
            case DUPX_InstallerState::INSTALL_RBACKUP_SINGLE_SITE:
            case DUPX_InstallerState::INSTALL_RECOVERY_MULTISITE_SUBDOMAIN:
            case DUPX_InstallerState::INSTALL_RECOVERY_MULTISITE_SUBFOLDER:
            case DUPX_InstallerState::INSTALL_RECOVERY_SINGLE_SITE:
                throw new Exception('Replace engine isn\'t available for restore backup mode');
            case DUPX_InstallerState::INSTALL_NOT_SET:
            default:
                throw new Exception('Invalid installer mode');
        }

        return true;
    }

    /**
     * Set global search replace
     *
     * @return void
     */
    private function setGlobalSearchAndReplaceList()
    {
        $srManager     = DUPX_S_R_MANAGER::getInstance();
        $paramsManager = PrmMng::getInstance();

        // DIRS PATHS
        $this->addReplaceEnginePaths();

        Log::info('GLOBAL SEARCH REPLACE ', Log::LV_DETAILED);

        if (
            !DUPX_InstallerState::isInstallerCreatedInThisLocation() &&
            !DUPX_InstallerState::isAddSiteOnMultisite()
        ) {
            $uploadUrlOld = $paramsManager->getValue(PrmMng::PARAM_URL_UPLOADS_OLD);
            $uploadUrlNew = $paramsManager->getValue(PrmMng::PARAM_URL_UPLOADS_NEW);

            if (self::checkRelativeAndAbsoluteDiff($this->mainUrlOld, $this->mainUrlNew, $uploadUrlOld, $uploadUrlNew)) {
                $srManager->addItem($uploadUrlOld, $uploadUrlNew, DUPX_S_R_ITEM::TYPE_URL_NORMALIZE_DOMAIN, DUPX_UpdateEngine::SR_PRORITY_GENERIC_SUBST_P1);
            }

            $siteUrlOld = $paramsManager->getValue(PrmMng::PARAM_SITE_URL_OLD);
            $siteUrlNew = $paramsManager->getValue(PrmMng::PARAM_SITE_URL);
            if (self::checkRelativeAndAbsoluteDiff($this->mainUrlOld, $this->mainUrlNew, $siteUrlOld, $siteUrlNew)) {
                $srManager->addItem($siteUrlOld, $siteUrlNew, DUPX_S_R_ITEM::TYPE_URL_NORMALIZE_DOMAIN, DUPX_UpdateEngine::SR_PRORITY_GENERIC_SUBST_P3);
            }

            $srManager->addItem($this->mainUrlOld, $this->mainUrlNew, DUPX_S_R_ITEM::TYPE_URL_NORMALIZE_DOMAIN, DUPX_UpdateEngine::SR_PRORITY_GENERIC_SUBST_P3);
        }

        $pluginsUrlOld = $paramsManager->getValue(PrmMng::PARAM_URL_PLUGINS_OLD);
        $pluginsUrlNew = $paramsManager->getValue(PrmMng::PARAM_URL_PLUGINS_NEW);
        if (
            $this->forceReplaceSiteSubfolders ||
            self::checkRelativeAndAbsoluteDiff($this->mainUrlOld, $this->mainUrlNew, $pluginsUrlOld, $pluginsUrlNew)
        ) {
            $srManager->addItem($pluginsUrlOld, $pluginsUrlNew, DUPX_S_R_ITEM::TYPE_URL_NORMALIZE_DOMAIN, DUPX_UpdateEngine::SR_PRORITY_GENERIC_SUBST_P1);
        }

        $mupluginsUrlOld = $paramsManager->getValue(PrmMng::PARAM_URL_MUPLUGINS_OLD);
        $mupluginsUrlNew = $paramsManager->getValue(PrmMng::PARAM_URL_MUPLUGINS_NEW);
        if (
            $this->forceReplaceSiteSubfolders ||
            self::checkRelativeAndAbsoluteDiff($this->mainUrlOld, $this->mainUrlNew, $mupluginsUrlOld, $mupluginsUrlNew)
        ) {
            $srManager->addItem($mupluginsUrlOld, $mupluginsUrlNew, DUPX_S_R_ITEM::TYPE_URL_NORMALIZE_DOMAIN, DUPX_UpdateEngine::SR_PRORITY_GENERIC_SUBST_P1);
        }

        $contentUrlOld = $paramsManager->getValue(PrmMng::PARAM_URL_CONTENT_OLD);
        $contentUrlNew = $paramsManager->getValue(PrmMng::PARAM_URL_CONTENT_NEW);
        if (
            $this->forceReplaceSiteSubfolders ||
            self::checkRelativeAndAbsoluteDiff($this->mainUrlOld, $this->mainUrlNew, $contentUrlOld, $contentUrlNew)
        ) {
            $srManager->addItem($contentUrlOld, $contentUrlNew, DUPX_S_R_ITEM::TYPE_URL_NORMALIZE_DOMAIN, DUPX_UpdateEngine::SR_PRORITY_GENERIC_SUBST_P2);
        }

        // Replace email address (xyz@oldomain.com to xyz@newdomain.com).
        if ($paramsManager->getValue(PrmMng::PARAM_EMAIL_REPLACE)) {
            $at_old_domain = '@' . DUPX_U::getDomain($this->mainUrlOld);
            $at_new_domain = '@' . DUPX_U::getDomain($this->mainUrlNew);
            $srManager->addItem($at_old_domain, $at_new_domain, DUPX_S_R_ITEM::TYPE_STRING, DUPX_UpdateEngine::SR_PRORITY_LOW);
        }
    }

    /**
     * add paths to replace on sear/replace engine
     *
     * @return void
     */
    private function addReplaceEnginePaths()
    {
        $srManager     = DUPX_S_R_MANAGER::getInstance();
        $paramsManager = PrmMng::getInstance();
        if ($paramsManager->getValue(PrmMng::PARAM_SKIP_PATH_REPLACE)) {
            return;
        }

        $archiveConfig = DUPX_ArchiveConfig::getInstance();
        $originalPaths = $archiveConfig->getRealValue('originalPaths');
        $mainPathOld   = $paramsManager->getValue(PrmMng::PARAM_PATH_OLD);
        $mainPathNew   = $paramsManager->getValue(PrmMng::PARAM_PATH_NEW);

        if (
            !DUPX_InstallerState::isInstallerCreatedInThisLocation() ||
            !DUPX_InstallerState::isAddSiteOnMultisite()
        ) {
            $uploadPathOld = $paramsManager->getValue(PrmMng::PARAM_PATH_UPLOADS_OLD);
            $uploadPathNew = $paramsManager->getValue(PrmMng::PARAM_PATH_UPLOADS_NEW);
            if (self::checkRelativeAndAbsoluteDiff($mainPathOld, $mainPathNew, $uploadPathOld, $uploadPathNew)) {
                $srManager->addItem($uploadPathOld, $uploadPathNew, DUPX_S_R_ITEM::TYPE_PATH, DUPX_UpdateEngine::SR_PRORITY_GENERIC_SUBST_P1);
            }
            if (
                $originalPaths->uploads != $uploadPathOld &&
                self::checkRelativeAndAbsoluteDiff($originalPaths->home, $mainPathNew, $originalPaths->uploads, $uploadPathNew)
            ) {
                $srManager->addItem($originalPaths->uploads, $uploadPathNew, DUPX_S_R_ITEM::TYPE_PATH, DUPX_UpdateEngine::SR_PRORITY_GENERIC_SUBST_P1);
            }

            $corePathOld = $paramsManager->getValue(PrmMng::PARAM_PATH_WP_CORE_OLD);
            $corePathNew = $paramsManager->getValue(PrmMng::PARAM_PATH_WP_CORE_NEW);
            if (self::checkRelativeAndAbsoluteDiff($mainPathOld, $mainPathNew, $corePathOld, $corePathNew)) {
                $srManager->addItem($corePathOld, $corePathNew, DUPX_S_R_ITEM::TYPE_PATH, DUPX_UpdateEngine::SR_PRORITY_GENERIC_SUBST_P3);
            }
            if (
                $originalPaths->abs != $corePathOld &&
                self::checkRelativeAndAbsoluteDiff($originalPaths->home, $mainPathNew, $originalPaths->abs, $corePathNew)
            ) {
                $srManager->addItem($originalPaths->abs, $corePathNew, DUPX_S_R_ITEM::TYPE_PATH, DUPX_UpdateEngine::SR_PRORITY_GENERIC_SUBST_P3);
            }

            $srManager->addItem($mainPathOld, $mainPathNew, DUPX_S_R_ITEM::TYPE_PATH, DUPX_UpdateEngine::SR_PRORITY_GENERIC_SUBST_P3);
            if ($originalPaths->home != $mainPathOld) {
                $srManager->addItem($originalPaths->home, $mainPathNew, DUPX_S_R_ITEM::TYPE_PATH, DUPX_UpdateEngine::SR_PRORITY_GENERIC_SUBST_P3);
            }
        }

        $pluginsPathOld = $paramsManager->getValue(PrmMng::PARAM_PATH_PLUGINS_OLD);
        $pluginsPathNew = $paramsManager->getValue(PrmMng::PARAM_PATH_PLUGINS_NEW);
        if (self::checkRelativeAndAbsoluteDiff($mainPathOld, $mainPathNew, $pluginsPathOld, $pluginsPathNew)) {
            $srManager->addItem($pluginsPathOld, $pluginsPathNew, DUPX_S_R_ITEM::TYPE_PATH, DUPX_UpdateEngine::SR_PRORITY_GENERIC_SUBST_P1);
        }
        if (
            $originalPaths->plugins != $pluginsPathOld &&
            self::checkRelativeAndAbsoluteDiff($originalPaths->home, $mainPathNew, $originalPaths->plugins, $pluginsPathNew)
        ) {
            $srManager->addItem($originalPaths->plugins, $pluginsPathNew, DUPX_S_R_ITEM::TYPE_PATH, DUPX_UpdateEngine::SR_PRORITY_GENERIC_SUBST_P1);
        }

        $mupluginsPathOld = $paramsManager->getValue(PrmMng::PARAM_PATH_MUPLUGINS_OLD);
        $mupluginsPathNew = $paramsManager->getValue(PrmMng::PARAM_PATH_MUPLUGINS_NEW);
        if (self::checkRelativeAndAbsoluteDiff($mainPathOld, $mainPathNew, $mupluginsPathOld, $mupluginsPathNew)) {
            $srManager->addItem($mupluginsPathOld, $mupluginsPathNew, DUPX_S_R_ITEM::TYPE_PATH, DUPX_UpdateEngine::SR_PRORITY_GENERIC_SUBST_P1);
        }
        if (
            $originalPaths->muplugins != $mupluginsPathOld &&
            self::checkRelativeAndAbsoluteDiff($originalPaths->home, $mainPathNew, $originalPaths->muplugins, $mupluginsPathNew)
        ) {
            $srManager->addItem($originalPaths->muplugins, $mupluginsPathNew, DUPX_S_R_ITEM::TYPE_PATH, DUPX_UpdateEngine::SR_PRORITY_GENERIC_SUBST_P1);
        }

        $contentPathOld = $paramsManager->getValue(PrmMng::PARAM_PATH_CONTENT_OLD);
        $contentPathNew = $paramsManager->getValue(PrmMng::PARAM_PATH_CONTENT_NEW);
        if (self::checkRelativeAndAbsoluteDiff($mainPathOld, $mainPathNew, $contentPathOld, $contentPathNew)) {
            $srManager->addItem($contentPathOld, $contentPathNew, DUPX_S_R_ITEM::TYPE_PATH, DUPX_UpdateEngine::SR_PRORITY_GENERIC_SUBST_P2);
        }
        if (
            $originalPaths->wpcontent != $contentPathOld &&
            self::checkRelativeAndAbsoluteDiff($originalPaths->home, $mainPathNew, $originalPaths->wpcontent, $contentPathNew)
        ) {
            $srManager->addItem($originalPaths->wpcontent, $contentPathNew, DUPX_S_R_ITEM::TYPE_PATH, DUPX_UpdateEngine::SR_PRORITY_GENERIC_SUBST_P2);
        }
    }

    /**
     * Custom search and replace
     *
     * @return void
     */
    private function setCustomReplaceList()
    {
        $srManager   = DUPX_S_R_MANAGER::getInstance();
        $searchList  = PrmMng::getInstance()->getValue(PrmMng::PARAM_CUSTOM_SEARCH);
        $replaceList = PrmMng::getInstance()->getValue(PrmMng::PARAM_CUSTOM_REPLACE);

        foreach ($searchList as $search_index => $search_for) {
            if (strlen($search_for) > 0) {
                $replace_with = $replaceList[$search_index];
                $srManager->addItem($search_for, $replace_with, DUPX_S_R_ITEM::TYPE_STRING, DUPX_UpdateEngine::SR_PRORITY_CUSTOM);
            }
        }
    }

    /**
     * Multisite search and replace
     *
     * @return void
     */
    private function setMultisiteSearchReplace()
    {
        $srManager = DUPX_S_R_MANAGER::getInstance();
        $prmMng    = PrmMng::getInstance();
        $arcConfig = DUPX_ArchiveConfig::getInstance();

        $oldMuUrls = $arcConfig->getOldUrlsArrayIdVal();
        $newMuUrls = Multisite::getMappedSubisteURLs();

        $mainSite = $arcConfig->getMainSiteInfo();

        // put the main sub site at the end
        $subsitesIds = array_keys($oldMuUrls);
        if (($delKey      = array_search($mainSite->id, $subsitesIds)) !== false) {
            unset($subsitesIds[$delKey]);
        }
        $subsitesIds[] = $mainSite->id;

        Log::info("MAIN URL :" . Log::v2str($arcConfig->getUrlFromSubsiteObj($mainSite)), Log::LV_DETAILED);
        Log::info('-- SUBSITES --' . "\n" . print_r($arcConfig->subsites, true), Log::LV_DEBUG);

        foreach ($subsitesIds as $currentSubid) {
            if (($subSiteObj = $arcConfig->getSubsiteObjById($currentSubid)) === false) {
                Log::info('INVALID SUBSITE ID: ' . $currentSubid);
                throw new Exception('Invalid subsite id');
            }

            Log::info('SUBSITE ID:' . $currentSubid . 'OLD URL: ' . $oldMuUrls[$currentSubid] . ' NEW URL ' . $newMuUrls[$currentSubid], Log::LV_DEBUG);

            $isMainSite = $currentSubid == $mainSite->id;

            $search  = $oldMuUrls[$currentSubid];
            $replace = $newMuUrls[$currentSubid];

            // get table for search and replace scope for subsites
            if ($prmMng->getValue(PrmMng::PARAM_MULTISITE_CROSS_SEARCH) == false && !$isMainSite) {
                $tables = DUPX_DB_Tables::getInstance()->getSubsiteTablesNewNames($currentSubid);
            } else {
                // global scope
                $tables = true;
            }

            $priority = ($isMainSite) ? DUPX_UpdateEngine::SR_PRORITY_GENERIC_SUBST_P4 : DUPX_UpdateEngine::SR_PRORITY_NETWORK_SUBSITE;
            $srManager->addItem($search, $replace, DUPX_S_R_ITEM::TYPE_URL_NORMALIZE_DOMAIN, $priority, $tables);

            // Replace email address (xyz@oldomain.com to xyz@newdomain.com).
            if ($prmMng->getValue(PrmMng::PARAM_EMAIL_REPLACE)) {
                $at_old_domain = '@' . DUPX_U::getDomain($search);
                $at_new_domain = '@' . DUPX_U::getDomain($replace);
                $srManager->addItem($at_old_domain, $at_new_domain, DUPX_S_R_ITEM::TYPE_STRING, DUPX_UpdateEngine::SR_PRORITY_LOW, $tables);
            }

            // for domain host and path priority is on main site
            $sUrlInfo = parse_url($search);
            $sHost    = isset($sUrlInfo['host']) ? $sUrlInfo['host'] : '';
            $sPath    = isset($sUrlInfo['path']) ? $sUrlInfo['path'] : '';
            $rUrlInfo = parse_url($replace);
            $rHost    = isset($rUrlInfo['host']) ? $rUrlInfo['host'] : '';
            $rPath    = isset($rUrlInfo['path']) ? $rUrlInfo['path'] : '';

            // add path and host scope for custom columns in database
            $srManager->addItem($sHost, $rHost, DUPX_S_R_ITEM::TYPE_URL, $priority, 'domain_host');
            $srManager->addItem($sPath, $rPath, DUPX_S_R_ITEM::TYPE_STRING, $priority, 'domain_path');
        }
    }

    /**
     * Set standalone search replace
     *
     * @return void
     */
    private function setStandalonSearchReplace()
    {
        $srManager = DUPX_S_R_MANAGER::getInstance();
        $prmMng    = PrmMng::getInstance();
        $arcConfig = DUPX_ArchiveConfig::getInstance();

        $subsiteId = $prmMng->getValue(PrmMng::PARAM_SUBSITE_ID);

        $originalPaths  = $arcConfig->getRealValue('originalPaths');
        $contentPathOld = $prmMng->getValue(PrmMng::PARAM_PATH_CONTENT_OLD);
        $uploadPathOld  = $prmMng->getValue(PrmMng::PARAM_PATH_UPLOADS_OLD);

        if (($subsiteObj = $arcConfig->getSubsiteObjById($subsiteId)) == false) {
            throw new Exception('Subite id ' . $subsiteId . ' not valid');
        }

        if ($subsiteId == 1) {
            return;
        }

        $oldSubsiteUrl = $arcConfig->getUrlFromSubsiteObj($subsiteObj);
        $newUrl        = $prmMng->getValue(PrmMng::PARAM_URL_NEW);

        // Need to swap the subsite prefix for the main table prefix
        $uploadsDirSubOld = $uploadPathOld . '/sites/' . $subsiteId;
        $uploadsNew       = $prmMng->getValue(PrmMng::PARAM_PATH_UPLOADS_NEW);

        if (!$prmMng->getValue(PrmMng::PARAM_SKIP_PATH_REPLACE)) {
            $srManager->addItem($uploadsDirSubOld, $uploadsNew, DUPX_S_R_ITEM::TYPE_PATH, DUPX_UpdateEngine::SR_PRORITY_NETWORK_SUBSITE_HIGH);
            if ($originalPaths->uploads != $uploadPathOld) {
                $uploadsDirSubOld = $originalPaths->uploads . '/sites/' . $subsiteId;
                $srManager->addItem($uploadsDirSubOld, $uploadsNew, DUPX_S_R_ITEM::TYPE_PATH, DUPX_UpdateEngine::SR_PRORITY_NETWORK_SUBSITE_HIGH);
            }
        }

        $uploadsUrlNew = $prmMng->getValue(PrmMng::PARAM_URL_UPLOADS_NEW);

        $uploadsUrlSubOld = $arcConfig->getUploadsUrlFromSubsiteObj($subsiteObj) . '/sites/' . $subsiteId;
        $srManager->addItem($uploadsUrlSubOld, $uploadsUrlNew, DUPX_S_R_ITEM::TYPE_URL_NORMALIZE_DOMAIN, DUPX_UpdateEngine::SR_PRORITY_NETWORK_SUBSITE_HIGH);

        $uploadsUrlSubOld = $prmMng->getValue(PrmMng::PARAM_URL_UPLOADS_OLD) . '/sites/' . $subsiteId;
        $srManager->addItem($uploadsUrlSubOld, $uploadsUrlNew, DUPX_S_R_ITEM::TYPE_URL_NORMALIZE_DOMAIN, DUPX_UpdateEngine::SR_PRORITY_NETWORK_SUBSITE_HIGH);

        //Replace WP 3.4.5 subsite uploads path in DB
        if ($arcConfig->mu_generation === 1) {
            $blogsDirOld = $contentPathOld . '/blogs.dir/' . $subsiteId . '/files';

            if (!$prmMng->getValue(PrmMng::PARAM_SKIP_PATH_REPLACE)) {
                $srManager->addItem($blogsDirOld, $uploadsNew, DUPX_S_R_ITEM::TYPE_PATH, DUPX_UpdateEngine::SR_PRORITY_NETWORK_SUBSITE_HIGH);
                if ($originalPaths->wpcontent != $contentPathOld) {
                    $blogsDirOld = $originalPaths->wpcontent . '/blogs.dir/' . $subsiteId . '/files';
                    $srManager->addItem($blogsDirOld, $uploadsNew, DUPX_S_R_ITEM::TYPE_PATH, DUPX_UpdateEngine::SR_PRORITY_NETWORK_SUBSITE_HIGH);
                }
            }

            $subSiteFilesUrl = $prmMng->getValue(PrmMng::PARAM_URL_NEW) . '/files';
            $uploadUrlNew    = $prmMng->getValue(PrmMng::PARAM_URL_UPLOADS_NEW);
            $srManager->addItem($subSiteFilesUrl, $uploadUrlNew, DUPX_S_R_ITEM::TYPE_URL, DUPX_UpdateEngine::SR_PRORITY_NETWORK_SUBSITE_HIGH);
        }

        $srManager->addItem($oldSubsiteUrl, $newUrl, DUPX_S_R_ITEM::TYPE_URL_NORMALIZE_DOMAIN, DUPX_UpdateEngine::SR_PRORITY_NETWORK_SUBSITE);
    }

    /**
     * Set addon multiste search replace
     *
     * @return void
     */
    private function setAddOnMultisiteSearchReplace()
    {
        $srManager = DUPX_S_R_MANAGER::getInstance();
        $prmMng    = PrmMng::getInstance();
        $arcConfig = DUPX_ArchiveConfig::getInstance();
        $tablesMng = DUPX_DB_Tables::getInstance();

        /** @var SiteOwrMap[] $overwriteMapping */
        $overwriteMapping = PrmMng::getInstance()->getValue(PrmMng::PARAM_SUBSITE_OVERWRITE_MAPPING);

        $mainSite                         = (array) $arcConfig->getMainSiteInfo();
        $this->forceReplaceSiteSubfolders = true;

        foreach ($overwriteMapping as $map) {
            $sourceInfo = $map->getSourceSiteInfo();
            $targetInfo = $map->getTargetSiteInfo();

            $isMainSite = ($sourceInfo['id'] == $mainSite['id']);

            // get table for search and replace scope for subsites/
            $scope = $tablesMng->getSubsiteTablesNewNames($sourceInfo['id']);

            if (!$prmMng->getValue(PrmMng::PARAM_SKIP_PATH_REPLACE)) {
                $srManager->addItem(
                    $sourceInfo['fullUploadPath'],
                    $targetInfo['fullUploadPath'],
                    DUPX_S_R_ITEM::TYPE_PATH,
                    ($sourceInfo['id'] == 1 ? DUPX_UpdateEngine::SR_PRORITY_GENERIC_SUBST_P1 : DUPX_UpdateEngine::SR_PRORITY_NETWORK_SUBSITE_HIGH),
                    $scope
                );
                if ($sourceInfo['fullUploadPath'] != $sourceInfo['fullUploadSafePath']) {
                    $srManager->addItem(
                        $sourceInfo['fullUploadSafePath'],
                        $targetInfo['fullUploadPath'],
                        DUPX_S_R_ITEM::TYPE_PATH,
                        ($sourceInfo['id'] == 1 ? DUPX_UpdateEngine::SR_PRORITY_GENERIC_SUBST_P1 : DUPX_UpdateEngine::SR_PRORITY_NETWORK_SUBSITE_HIGH),
                        $scope
                    );
                }
            }

            $srManager->addItem(
                $sourceInfo['fullUploadUrl'],
                $targetInfo['fullUploadUrl'],
                DUPX_S_R_ITEM::TYPE_URL_NORMALIZE_DOMAIN,
                ($isMainSite ? DUPX_UpdateEngine::SR_PRORITY_GENERIC_SUBST_P1 : DUPX_UpdateEngine::SR_PRORITY_NETWORK_SUBSITE),
                $scope
            );

            if (!($isMainSite && DUPX_InstallerState::isInstallerCreatedInThisLocation())) {
                $srManager->addItem(
                    $sourceInfo['fullHomeUrl'],
                    $targetInfo['fullHomeUrl'],
                    DUPX_S_R_ITEM::TYPE_URL_NORMALIZE_DOMAIN,
                    ($isMainSite ? DUPX_UpdateEngine::SR_PRORITY_GENERIC_SUBST_P3 : DUPX_UpdateEngine::SR_PRORITY_NETWORK_SUBSITE),
                    $scope
                );
            }

            // Replace email address (xyz@oldomain.com to xyz@newdomain.com).
            if ($prmMng->getValue(PrmMng::PARAM_EMAIL_REPLACE)) {
                $at_old_domain = '@' . DUPX_U::getDomain($sourceInfo['fullUploadUrl']);
                $at_new_domain = '@' . DUPX_U::getDomain($targetInfo['fullUploadUrl']);
                $srManager->addItem(
                    $at_old_domain,
                    $at_new_domain,
                    DUPX_S_R_ITEM::TYPE_STRING,
                    DUPX_UpdateEngine::SR_PRORITY_LOW,
                    $scope
                );
            }
        }
    }

    /**
     * Check if sub path if different
     *
     * @param string $mainOld main old path
     * @param string $mainNew main new path
     * @param string $old     old sub path
     * @param string $new     new sub path
     *
     * @return bool
     */
    private static function checkRelativeAndAbsoluteDiff($mainOld, $mainNew, $old, $new)
    {
        $mainOld = SnapIO::safePath($mainOld);
        $mainNew = SnapIO::safePath($mainNew);
        $old     = SnapIO::safePath($old);
        $new     = SnapIO::safePath($new);

        $log = "CHECK REL AND ABS DIF\n" .
            "\tMAIN OLD: " . Log::v2str($mainOld) . "\n" .
            "\tMAIN NEW: " . Log::v2str($mainNew) . "\n" .
            "\tOLD: " . Log::v2str($old) . "\n" .
            "\tNEW: " . Log::v2str($new);
        Log::info($log, Log::LV_DEBUG);

        $isRelativePathDifferent = substr($old, strlen($mainOld)) !== substr($new, strlen($mainNew));

        if ($old === $mainOld) {
            // If the main path coincides with the current path it is not possible to distinguish
            // the two paths and make different substitutions so I skip the substitution
            Log::info("\t*** RESULT: FALSE", Log::LV_DEBUG);
            return false;
        } elseif (strpos($old, $mainOld) !== 0 || strpos($new, $mainNew) !== 0 || $isRelativePathDifferent) {
            Log::info("\t*** RESULT: TRUE", Log::LV_DEBUG);
            return true;
        } else {
            Log::info("\t*** RESULT: FALSE", Log::LV_DEBUG);
            return false;
        }
    }
}
