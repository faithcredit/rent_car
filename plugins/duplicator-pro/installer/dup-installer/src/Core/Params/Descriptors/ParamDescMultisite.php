<?php

/**
 * Multisite params descriptions
 *
 * @category  Duplicator
 * @package   Installer
 * @author    Snapcreek <admin@snapcreek.com>
 * @copyright 2011-2021  Snapcreek LLC
 * @license   https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 */

namespace Duplicator\Installer\Core\Params\Descriptors;

use Duplicator\Installer\Core\Params\PrmMng;
use Duplicator\Installer\Core\Params\Items\ParamItem;
use Duplicator\Installer\Core\Params\Items\ParamForm;
use Duplicator\Installer\Core\Params\Items\ParamFormSitesOwrMap;
use Duplicator\Installer\Core\Params\Items\ParamOption;
use Duplicator\Installer\Core\Params\Items\ParamFormURLMapping;
use Duplicator\Installer\Core\Params\Models\SiteOwrMap;
use Duplicator\Libs\Snap\SnapURL;
use DUPX_ArchiveConfig;
use DUPX_InstallerState;

/**
 * class where all parameters are initialized. Used by the param manager
 */
final class ParamDescMultisite implements DescriptorInterface
{
    /**
     * Init params
     *
     * @param ParamItem[]|ParamForm[] $params params list
     *
     * @return void
     */
    public static function init(&$params)
    {
        $archive_config = \DUPX_ArchiveConfig::getInstance();

        $params[PrmMng::PARAM_SUBSITE_ID] = new ParamForm(
            PrmMng::PARAM_SUBSITE_ID,
            ParamForm::TYPE_INT,
            ParamForm::FORM_TYPE_SELECT,
            array(
                'default'      => -1,
                'acceptValues' => array(__CLASS__, 'getSubSiteIdsAcceptValues')
            ),
            array(
                'status' => function (ParamItem $paramObj) {
                    if (
                        DUPX_InstallerState::isInstType(
                            array(
                                DUPX_InstallerState::INSTALL_STANDALONE
                            )
                        )
                    ) {
                        return ParamForm::STATUS_ENABLED;
                    } else {
                        return ParamForm::STATUS_DISABLED;
                    }
                },
                'label'          => 'Subsite:',
                'wrapperClasses' => array('revalidate-on-change'),
                'options'        => array(__CLASS__, 'getSubSiteIdsOptions'),
            )
        );

        $params[PrmMng::PARAM_SUBSITE_OVERWRITE_MAPPING] = new ParamFormSitesOwrMap(
            PrmMng::PARAM_SUBSITE_OVERWRITE_MAPPING,
            ParamFormSitesOwrMap::TYPE_ARRAY_SITES_OWR_MAP,
            ParamFormSitesOwrMap::FORM_TYPE_SITES_OWR_MAP,
            array(
                'default'          => [],
                'validateCallback' => function ($value, ParamItem $paramObj) {
                    /** @var SiteOwrMap[] $value */

                    if (!DUPX_InstallerState::isAddSiteOnMultisite()) {
                        return true;
                    }

                    $overwriteData  = PrmMng::getInstance()->getValue(PrmMng::PARAM_OVERWRITE_SITE_DATA);
                    $mainSiteURL    = $overwriteData['urls']['home'];
                    $subdomain      = (isset($overwriteData['subdomain']) && $overwriteData['subdomain']);
                    $newFullURLs = [];

                    foreach ($value as $map) {
                        switch ($map->getTargetId()) {
                            case SiteOwrMap::NEW_SUBSITE_WITH_SLUG:
                                if (($newFullUrl = $map->getNewSlugFullUrl($mainSiteURL, $subdomain)) == false) {
                                    $paramObj->setInvalidMessage('New sub site can\'t have new ' . ($subdomain ? 'subdomain' : 'subpath') . ' empty');
                                    return false;
                                }
                                break;
                            case SiteOwrMap::NEW_SUBSITE_WITH_FULL_DOMAIN:
                                if (($newFullUrl = $map->getNewSlugFullUrl($mainSiteURL, $subdomain)) == false) {
                                    $paramObj->setInvalidMessage('New sub site URL can\'t be empty');
                                    return false;
                                }
                                break;
                            default:
                                continue 2;
                        }

                        $newFullURLs[] = $newFullUrl;
                        foreach ($overwriteData['subsites'] as $subsite) {
                            $subsiteFullUrl = $subsite['domain'] . $subsite['path'];
                            if (strcmp($newFullUrl, $subsiteFullUrl) === 0) {
                                $paramObj->setInvalidMessage('New subsite URL already exists');
                                return false;
                            }
                        }
                    }

                    if (count($newFullURLs) !== count(array_unique($newFullURLs))) {
                        $paramObj->setInvalidMessage('Different new sub-sites cannot have the same URL ');
                        return false;
                    }

                    return true;
                }
            ),
            array(
                'label'          => 'Overwrite mapping',
                'renderLabel'    => false,
                'wrapperClasses' => array('revalidate-on-change')
            )
        );

        $params[PrmMng::PARAM_MU_REPLACE] = new ParamFormURLMapping(
            PrmMng::PARAM_MU_REPLACE,
            ParamFormURLMapping::TYPE_ARRAY_SITES_OWR_MAP,
            ParamFormURLMapping::FORM_TYPE_URL_MAPPING,
            array(
                'default' => [],
                'validateCallback' => function ($value, ParamItem $paramObj) {
                    /** @var SiteOwrMap[] $value */

                    if (!DUPX_InstallerState::isMultisiteInstall()) {
                        return true;
                    }

                    $config = DUPX_ArchiveConfig::getInstance();
                    $subdomain = $config->isSubdomain();

                    foreach ($value as $map) {
                        switch ($map->getTargetId()) {
                            case SiteOwrMap::NEW_SUBSITE_WITH_SLUG:
                                if (strlen($map->getNewSlug()) == 0) {
                                    $paramObj->setInvalidMessage('New sub site can\'t have new ' . ($subdomain ? 'subdomain' : 'subpath') . ' empty');
                                    return false;
                                }
                                break;
                            case SiteOwrMap::NEW_SUBSITE_WITH_FULL_DOMAIN:
                                if (strlen($map->getNewSlug()) == 0) {
                                    $paramObj->setInvalidMessage('New sub site URL can\'t be empty');
                                    return false;
                                }
                                break;
                            default:
                                $paramObj->setInvalidMessage('Invalid param');
                                return false;
                        }
                    }
                    return true;
                }
            ),
            array(
                'label'       => 'URLs mapping',
                'renderLabel' => false,
                'wrapperClasses' => array('revalidate-on-change')
            )
        );

        $params[PrmMng::PARAM_MULTISITE_CROSS_SEARCH] = new ParamForm(
            PrmMng::PARAM_MULTISITE_CROSS_SEARCH,
            ParamForm::TYPE_BOOL,
            ParamForm::FORM_TYPE_CHECKBOX,
            array(
                'default' => (count($archive_config->subsites) <= MAX_SITES_TO_DEFAULT_ENABLE_CORSS_SEARCH)
            ),
            array(
                'status' => function ($paramObj) {
                    if (DUPX_InstallerState::isNewSiteIsMultisite()) {
                        return ParamForm::STATUS_ENABLED;
                    } else {
                        return ParamForm::STATUS_SKIP;
                    }
                },
                'label'         => 'Database search:',
                'checkboxLabel' => 'Cross-search between the sites of the network.'
            )
        );
    }

    /**
     * Update params after overwrite logic
     *
     * @param ParamItem[]|ParamForm[] $params params list
     *
     * @return void
     */
    public static function updateParamsAfterOverwrite($params)
    {
    }

    /**
     * Get overwrite map by source id
     *
     * @param int $sourceId subsite source id
     *
     * @return SiteOwrMap|bool false if don't exists
     */
    public static function getOwrMapBySourceId($sourceId)
    {
        static $indexCache = array();

        if (!isset($indexCache[$sourceId])) {
            /** @var SiteOwrMap[] $overwriteMapping */
            $overwriteMapping = PrmMng::getInstance()->getValue(PrmMng::PARAM_SUBSITE_OVERWRITE_MAPPING);

            foreach ($overwriteMapping as $map) {
                if ($map->getSourceId() == $sourceId) {
                    $indexCache[$sourceId] = $map;
                    break;
                }
            }
            if (!isset($indexCache[$sourceId])) {
                $indexCache[$sourceId] = false;
            }
        }

        return $indexCache[$sourceId];
    }

    /**
     * Return option
     *
     * @return ParamOption[]
     */
    public static function getSubSiteIdsOptions()
    {
        $archive_config = \DUPX_ArchiveConfig::getInstance();
        $options        = array();
        foreach ($archive_config->subsites as $subsite) {
            $label     = $subsite->domain . $subsite->path;
            $options[] = new ParamOption($subsite->id, $label, ParamFormSitesOwrMap::getSourceIdOptionStatus($subsite));
        }
        return $options;
    }

    /**
     *
     * @return int[]
     */
    public static function getSubSiteIdsAcceptValues()
    {
        $archive_config = \DUPX_ArchiveConfig::getInstance();
        $acceptValues   = array(-1);
        foreach ($archive_config->subsites as $subsite) {
            if (ParamFormSitesOwrMap::isQualifiedSourceIdForImport($subsite)) {
                $acceptValues[] = $subsite->id;
            }
        }
        return $acceptValues;
    }
}
