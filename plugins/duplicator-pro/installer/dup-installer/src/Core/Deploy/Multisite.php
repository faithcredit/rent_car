<?php

namespace Duplicator\Installer\Core\Deploy;

use VendorDuplicator\Amk\JsonSerialize\JsonSerialize;
use Duplicator\Installer\Core\Params\Descriptors\ParamDescMultisite;
use Duplicator\Installer\Core\Params\Models\SiteOwrMap;
use Duplicator\Installer\Core\Params\PrmMng;
use Duplicator\Installer\REST\RESTPoints;
use Duplicator\Installer\Utils\Log\Log;
use Duplicator\Libs\Snap\SnapURL;
use DUPX_ArchiveConfig;
use DUPX_Ctrl_Params;
use DUPX_InstallerState;
use DUPX_U;
use Exception;

class Multisite
{
    /**
     * Init new subistes info
     *
     * @return void
     */
    public static function overwriteSubsitesInit()
    {
        $paramsManager = PrmMng::getInstance();
        /** @var SiteOwrMap[] $overwriteMapping */
        $overwriteMapping = PrmMng::getInstance()->getValue(PrmMng::PARAM_SUBSITE_OVERWRITE_MAPPING);
        $sendData         = JsonSerialize::serialize($overwriteMapping, JsonSerialize::JSON_SKIP_CLASS_NAME);

        $errorMessage = '';
        $numSubsites  = count($overwriteMapping);
        if (($subsitesInfo = RESTPoints::getInstance()->subsiteActions($sendData, $numSubsites, $errorMessage)) == false) {
            Log::info('Creation subisites error, message: ' . $errorMessage);
            throw new Exception('Can\'t create a new sub site message :' . $errorMessage);
        }

        $overwriteData = $paramsManager->getValue(PrmMng::PARAM_OVERWRITE_SITE_DATA);

        foreach ($subsitesInfo as $subsiteInfo) {
            switch ($subsiteInfo['targetId']) {
                case SiteOwrMap::NEW_SUBSITE_WITH_SLUG:
                case SiteOwrMap::NEW_SUBSITE_WITH_FULL_DOMAIN:
                    $overwriteData['subsites'][] = $subsiteInfo['info'];
                    Log::info('NEW SUBSITE CREATED ON ID: ' . $subsiteInfo['info']['id'] . ' URL ' . $subsiteInfo['info']['fullSiteUrl']);

                    if (($owrMap = ParamDescMultisite::getOwrMapBySourceId($subsiteInfo['sourceId'])) == false) {
                        throw new Exception('OwrMap object not boud by id :' . $subsiteInfo['sourceId']);
                    }
                    $owrMap->setTargetId($subsiteInfo['info']['id']);
                    break;
                default:
                    // none
                    break;
            }
        }

        $paramsManager->setValue(PrmMng::PARAM_OVERWRITE_SITE_DATA, $overwriteData);
        $paramsManager->setValue(PrmMng::PARAM_SUBSITE_OVERWRITE_MAPPING, $overwriteMapping);

        DUPX_Ctrl_Params::setParamsOnAddSiteOnMultisite();
        $paramsManager->save();
    }

    /**
     * Return new subsite URLs
     *
     * @return array<int, string> Return mapped URLs
     */
    public static function getMappedSubisteURLs()
    {
        static $mappedURLs = null;

        if (is_null($mappedURLs)) {
            $mappedURLs = [];

            $config = DUPX_ArchiveConfig::getInstance();
            /** @var SiteOwrMap[] */
            $customMap      = PrmMng::getInstance()->getValue(PrmMng::PARAM_MU_REPLACE);
            $mainSiteDomain = SnapURL::parseUrl(PrmMng::getInstance()->getValue(PrmMng::PARAM_URL_NEW), PHP_URL_HOST);

            $sourceMainSiteIndex = $config->getMainSiteIndex();
            $sourceMainUrl       = $config->getUrlFromSubsiteObj($config->subsites[$sourceMainSiteIndex]);

            if (DUPX_InstallerState::isAddSiteOnMultisite()) {
                $overwriteData = PrmMng::getInstance()->getValue(PrmMng::PARAM_OVERWRITE_SITE_DATA);
                $subdomain     = (isset($overwriteData['subdomain']) && $overwriteData['subdomain']);
            } else {
                $subdomain = $config->isSubdomain();
            }

            for ($i = 0; $i < count($config->subsites); $i++) {
                $subsite = $config->subsites[$i];
                for ($j = 0; $j < count($customMap); $j++) {
                    if ($subsite->id != $customMap[$j]->getSourceId()) {
                        continue;
                    }

                    $mappedURLs[$subsite->id] = $customMap[$j]->getNewSlugFullUrl($mainSiteDomain, $subdomain, true);
                    break;
                }

                if ($j == count($customMap)) {
                    $mappedURLs[$subsite->id] = DUPX_U::getDefaultURL($config->getUrlFromSubsiteObj($subsite), $sourceMainUrl);
                }
            }
        }

        return $mappedURLs;
    }
}
