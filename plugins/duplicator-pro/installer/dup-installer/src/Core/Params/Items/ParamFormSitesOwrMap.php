<?php

/**
 * @package   Duplicator\Installer
 * @copyright (c) 2022, Snap Creek LLC
 */

namespace Duplicator\Installer\Core\Params\Items;

use Duplicator\Installer\Core\Params\Models\SiteOwrMap;
use Duplicator\Installer\Core\Params\PrmMng;
use Duplicator\Installer\Utils\Log\Log;
use Duplicator\Libs\Snap\SnapIO;
use Duplicator\Libs\Snap\SnapURL;
use Duplicator\Libs\Snap\SnapUtil;
use DUPX_ArchiveConfig;
use DUPX_InstallerState;
use DUPX_U;
use DUPX_U_Html;
use Exception;

/**
 * Item for overwrite mapping
 */
class ParamFormSitesOwrMap extends ParamForm
{
    const SOFT_LIMIT_NUM = 10;
    const HARD_LIMIT_NUM = 20;

    const TYPE_ARRAY_SITES_OWR_MAP = 'arrayowrmap';
    const FORM_TYPE_SITES_OWR_MAP  = 'sitesowrmap';

    const NAME_POSTFIX_SOURCE_ID = '_source_id';
    const NAME_POSTFIX_TARGET_ID = '_target_id';
    const NAME_POSTFIX_NEW_SLUG  = '_new_slug';

    const STRING_ADD_NEW_SUBSITE = "Add as New Subsite in Network";

    /** @var ?mixed[] */
    protected $extraData = null;
    /** @var int<0,max> minimum item in list */
    protected $minListItems = 1;

    /**
     * Class constructor
     *
     * @param string               $name     param identifier
     * @param string               $type     Enum: TYPE_STRING | TYPE_ARRAY_STRING | ...
     * @param string               $formType FORM_TYPE_HIDDEN | FORM_TYPE_TEXT | ...
     * @param array<string, mixed> $attr     list of attributes
     * @param array<string, mixed> $formAttr list of form attributes
     */
    public function __construct($name, $type, $formType, array $attr = [], array $formAttr = [])
    {
        if ($type != static::TYPE_ARRAY_SITES_OWR_MAP) {
            throw new Exception('the type must be ' . static::TYPE_ARRAY_SITES_OWR_MAP);
        }

        if ($formType != static::FORM_TYPE_SITES_OWR_MAP) {
            throw new Exception('the form type must be ' . static::FORM_TYPE_SITES_OWR_MAP);
        }

        parent::__construct($name, $type, $formType, $attr, $formAttr);
    }

    /**
     * Render HTML
     *
     * @return void
     */
    protected function htmlItem()
    {
        if ($this->formType == static::FORM_TYPE_SITES_OWR_MAP) {
            $this->sitesOrwHtml();
        } else {
            parent::htmlItem();
        }
    }

    /**
     * Return soft limit message
     *
     * @return string
     */
    protected static function getEmptyListMessage()
    {
        return 'Add site to import';
    }

    /**
     * Return full list limit message
     *
     * @return string
     */
    protected static function getFullListMessage()
    {
        return 'All available sites have been added to the list.';
    }

    /**
     * Return soft limit message
     *
     * @return string
     */
    protected static function getSoftLimitMessage()
    {
        return 'It is possible to import a larger number of sites simultaneously,' .
            'but multiple installations are recommended to prevent stability errors.';
    }

    /**
     * Return hard limit message
     *
     * @return string
     */
    protected static function getHardLimitMesssage()
    {
        return 'Maximum number of sites that can be imported in a single installation reached. (' .
            static::HARD_LIMIT_NUM .
            ') If you wish to import several sites, carry out separate installations.';
    }

    /**
     * Render subsite owr mapping
     *
     * @return void
     */
    protected function sitesOrwHtml()
    {
        $extraData         = $this->getListExtraData();
        $numSites          = $extraData['sourceInfo']['numSites'];
        $haveMultipleItems = $numSites > 1;

        $numListItem = 0;
        $hardLimit   = false;
        $softLimit   = false;

        if (count($this->value) >= static::HARD_LIMIT_NUM) {
            $hardLimit = true;
        } elseif (count($this->value) >= static::SOFT_LIMIT_NUM) {
            $softLimit = true;
        }

        $addDisabled = (count($this->value) >= $extraData['sourceInfo']['numSites'] || $hardLimit);

        ?>
        <div 
            class="overwrite_sites_list <?php echo ($haveMultipleItems ? '' : 'no-multiple'); ?>"
            data-list-info="<?php echo DUPX_U::esc_attr(json_encode($this->getListExtraData())); ?>"
        >
            <div class="overwrite_site_item title">
                <div class="col">
                    <?php echo $this->getItemsLabels('sourceSite'); ?>
                </div>
                <div class="col">
                    <?php echo $this->getItemsLabels('targetSite'); ?>
                </div>
                <div class="col del">
                    <span class="del_item hidden" >
                        <i class="fa fa-minus-square"></i>
                    </span>
                </div>
            </div>
            <?php
            if (empty($this->value)) {
                for ($i = 0; $i < $this->minListItems; $i++) {
                    if (($defaultId = static::getDefaultSourceId($i)) == false) {
                        break;
                    }
                    $defaultItem = new SiteOwrMap($defaultId, SiteOwrMap::NEW_SUBSITE_WITH_SLUG, '');
                    $this->itemOwrHtml($defaultItem, 0, false);
                    $numListItem++;
                }
            } else {
                $canDelete = (count($this->value) > $this->minListItems);
                foreach ($this->value as $index => $siteMap) {
                    $this->itemOwrHtml($siteMap, $index, $canDelete);
                    $numListItem++;
                }
            }
            ?>
            <div class="overwrite_site_item add_item">
                <div class="full">
                    <button 
                        type="button" 
                        class="secondary-btn float-right add_button" 
                        data-new-item="<?php echo DUPX_U::esc_attr($this->itemOwrHtml(null, 0, false, false)); ?>"
                        <?php echo ($addDisabled ? 'disabled' : ''); ?>
                    >
                        <?php echo $this->getItemsLabels('addItem'); ?>
                    </button>
                    <?php if (strlen(static::getEmptyListMessage())) { ?>
                    <div class="overwrite_msg overwrite_site_empty_list_msg <?php echo ($numListItem == 0 ? '' : 'no-display'); ?>" >
                        <i class="fas fa-info-circle"></i> <?php echo static::getEmptyListMessage(); ?>
                    </div>
                    <?php } ?>
                    <?php if (strlen(static::getFullListMessage())) { ?>
                    <div 
                        class="overwrite_msg overwrite_site_full_list_msg <?php echo ($numListItem > $numSites ? '' : 'no-display'); ?>" >
                        <i class="fas fa-info-circle"></i> <?php echo static::getFullListMessage(); ?>
                    </div>
                    <?php } ?>
                    <?php if (strlen(static::getSoftLimitMessage())) { ?>
                    <div class="overwrite_msg overwrite_site_soft_limit_msg maroon <?php echo ($softLimit ? '' : 'no-display');?>" >
                        <i class="fas fa-exclamation-triangle"></i> <?php echo static::getSoftLimitMessage(); ?>
                    </div>
                    <?php } ?>
                    <?php if (strlen(static::getHardLimitMesssage())) { ?>
                    <div class="overwrite_msg overwrite_site_hard_limit_msg maroon <?php echo ($hardLimit ? '' : 'no-display');?>" >
                        <i class="fas fa-exclamation-triangle"></i> <?php echo static::getHardLimitMesssage(); ?>
                    </div>
                    <?php } ?>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Get add itm button label
     *
     * @param string $key label key
     *
     * @return string
     */
    protected function getItemsLabels($key)
    {
        $paramLabels = [
            'addItem' => 'Add Site to Import',
            'sourceSite' => 'Source Site',
            'targetSite' => 'Target Site'
        ];

        return (isset($paramLabels[$key]) ? $paramLabels[$key] : 'unknown label key');
    }

    /**
     * Render item html
     *
     * @param SiteOwrMap|null $map       map item
     * @param int             $index     current item inder
     * @param bool            $canDelete if false disable delete button
     * @param bool            $echo      if false return HTML
     *
     * @return string
     */
    protected function itemOwrHtml(SiteOwrMap $map = null, $index = 0, $canDelete = false, $echo = true)
    {
        ob_start();
        $selectSourceAttrs = array(
            'name'  => $this->getName() . static::NAME_POSTFIX_SOURCE_ID . '[]',
            'class' => 'source_id js-select ' . $this->getFormItemId() . static::NAME_POSTFIX_SOURCE_ID
        );

        $selectTargetAttrs = array(
            'name'  => $this->getName() . static::NAME_POSTFIX_TARGET_ID . '[]',
            'class' => 'target_id js-select ' . $this->getFormItemId() . static::NAME_POSTFIX_TARGET_ID
        );

        $newSlugAttrs = array(
            'name'  => $this->getName() . static::NAME_POSTFIX_NEW_SLUG . '[]',
            'class' => 'new_slug ' . $this->getFormItemId() . static::NAME_POSTFIX_NEW_SLUG
        );

        $extraData = $this->getListExtraData();

        if (is_null($map)) {
            $selectSourceAttrs['disabled'] = true;
            $selectedSource                = false;
            $noteSourceSlug                = '';
            $selectTargetAttrs['disabled'] = true;
            $selectedTarget                = SiteOwrMap::NEW_SUBSITE_WITH_SLUG;
            $noteTargetSlug                = '_____';
            $newSlugAttrs['disabled']      = true;
            $newSlugAttrs['value']         = '';
        } else {
            $selectedSource     = $map->getSourceId();
            $selectedSourceInfo = $extraData['sourceInfo']['sites']['id_' . $selectedSource];
            $noteSourceSlug     = $selectedSourceInfo['domain'] . $selectedSourceInfo['path'];
            $selectedTarget     = $map->getTargetId();
            $selectedTargetInfo = $extraData['targetInfo']['sites']['id_' . $selectedTarget];

            switch ($selectedTarget) {
                case SiteOwrMap::NEW_SUBSITE_WITH_SLUG:
                    $noteTargetSlug = (
                        strlen($selectedTargetInfo['slug']) == 0 ?
                            '_____' :
                            $extraData['sourceInfo']['urlPrefix'] . $selectedTargetInfo['slug'] . $extraData['sourceInfo']['urlPostfix']
                        );
                    break;
                case SiteOwrMap::NEW_SUBSITE_WITH_FULL_DOMAIN:
                    $noteTargetSlug = (strlen($selectedTargetInfo['slug']) == 0 ? '_____/_____' : $selectedTargetInfo['slug']);
                    break;
                default:
                    $noteTargetSlug = $selectedTargetInfo['domain'] . $selectedTargetInfo['path'];
                    break;
            }
            $newSlugAttrs['value'] = $map->getNewSlug();
        }

        $sourceIdsOptions = static::getSourceIdsOptions();
        ?>
        <div class="overwrite_site_item">
            <div class="col">
                <select <?php echo DUPX_U_Html::arrayAttrToHtml($selectSourceAttrs); ?> >
                    <?php static::renderSelectOptions($sourceIdsOptions, $selectedSource); ?>
                </select>
                <div class="sub-note source-site-note" >
                    <span class="site-prefix-slug"><?php echo DUpx_u::esc_html($extraData['sourceInfo']['urlScheme']); ?></span
                    ><span class="site-slug"><?php echo DUpx_u::esc_html($noteSourceSlug); ?></span
                    ><span class="site-postfix-slug"></span>
                </div>
            </div>
            <div class="col">
                <div class="target_select_wrapper" >
                    <select <?php echo DUPX_U_Html::arrayAttrToHtml($selectTargetAttrs); ?> >
                        <?php static::renderSelectOptions(static::getTargetIdsOptions(), $selectedTarget); ?>
                    </select>
                    <div class="new-slug-wrapper">
                        <input 
                            type="text" <?php echo DUPX_U_Html::arrayAttrToHtml($newSlugAttrs); ?> 
                            placeholder="Insert the new site slug"
                        >
                    </div>
                </div>
                <div class="sub-note target-site-note" >
                    <span class="site-prefix-slug"><?php echo DUpx_u::esc_html($extraData['targetInfo']['urlScheme']); ?></span
                    ><span class="site-slug"><?php echo DUpx_u::esc_html($noteTargetSlug); ?></span
                    ><span class="site-postfix-slug"></span>
                </div>
            </div>
            <div class="col del">
                <span class="del_item <?php echo $canDelete ? '' : 'disabled'; ?>" title="Remove this site">
                    <i class="fa fa-minus-square"></i>
                </span>
            </div>
        </div>
        <?php
        if ($echo) {
            ob_end_flush();
            return '';
        } else {
            return ob_get_clean();
        }
    }

    /**
     * Get default type attributes
     *
     * @param string $type param value type
     *
     * @return array<string, mixed>
     */
    protected static function getDefaultAttrForType($type)
    {
        $attrs = parent::getDefaultAttrForType($type);
        if ($type == static::TYPE_ARRAY_SITES_OWR_MAP) {
            $attrs['default'] = array();
        }
        return $attrs;
    }

    /**
     * Apply filter to value input
     *
     * @param mixed[] $superObject query string super object
     *
     * @return mixed
     */
    public function getValueFilter($superObject)
    {
        if (($items = json_decode($superObject[$this->getName()], true)) == false) {
            throw new Exception('Invalid json string');
        }
        return $items;
    }

    /**
     * Return sanitized value
     *
     * @param mixed $value value input
     *
     * @return SiteOwrMap[]
     */
    public function getSanitizeValue($value)
    {
        if (!is_array($value)) {
            return array();
        }

        for ($i = 0; $i < count($value); $i++) {
            $sourceId = (isset($value[$i]['sourceId']) ? (int) $value[$i]['sourceId'] : SiteOwrMap::NEW_SUBSITE_NOT_VALID);
            $targetId = (isset($value[$i]['targetId']) ? (int) $value[$i]['targetId'] : SiteOwrMap::NEW_SUBSITE_NOT_VALID);
            $newSlug  = (isset($value[$i]['newSlug']) ? SnapUtil::sanitizeNSCharsNewlineTrim($value[$i]['newSlug']) : '');
            switch ($targetId) {
                case SiteOwrMap::NEW_SUBSITE_WITH_SLUG:
                    $newSlug = preg_replace('/[\s"\'\\\\\/&?#,\.:;]+/m', '', $newSlug);
                    break;
                case SiteOwrMap::NEW_SUBSITE_WITH_FULL_DOMAIN:
                    $newSlug = preg_replace('/[\s"\'\\\\&?#,:;]+/m', '', $newSlug);
                    if (strlen($newSlug) > 0) {
                        $newSlug = SnapIO::trailingslashit($newSlug);
                    }
                    break;
                default:
                    $newSlug = '';
                    break;
            }
            $value[$i] = array(
                'sourceId' => $sourceId,
                'targetId' => $targetId,
                'newSlug'  => $newSlug
            );
        }

        return $value;
    }

    /**
     * Check if value is valid
     *
     * @param mixed $value         value
     * @param mixed $validateValue variable passed by reference. Updated to validated value in the case, the value is a valid value.
     *
     * @return bool true if is a valid value for this object
     */
    public function isValid($value, &$validateValue = null)
    {
        $validateValue = array();

        try {
            foreach ($value as $item) {
                if ($item instanceof SiteOwrMap) {
                    $validateValue[] = $item;
                    continue;
                }

                $validateValue[] = new SiteOwrMap(
                    $item['sourceId'],
                    $item['targetId'],
                    $item['newSlug']
                );
            }

            if (($result = $this->callValidateCallback($validateValue)) === false) {
                $validateValue = null;
            }
        } catch (Exception $e) {
            Log::info('Validation error message: ' . $e->getMessage());
            return false;
        }

        return $result;
    }

    /**
     * Set value from array. This function is used to set data from json array
     *
     * @param array<string, mixed> $data form data
     *
     * @return boolean
     */
    public function fromArrayData($data)
    {
        $result = parent::fromArrayData($data);
        return $result;
    }

    /**
     * return array dato to store in json array data
     *
     * @return array{value: mixed, status: string}
     */
    public function toArrayData()
    {
        $result          = parent::toArrayData();
        $result['value'] = array();
        foreach ($this->value as $obj) {
            $result['value'][] = $obj->jsonSerialize();
        }
        return $result;
    }

    /**
     * Get subsite slug by subsitedata
     *
     * @param object|array<string, mixed> $subsite     subsite info
     * @param string                      $mainUrl     main site url
     * @param bool                        $isSubdomain if true is subdomain
     *
     * @return string
     */
    public static function getSubsiteSlug($subsite, $mainUrl, $isSubdomain)
    {
        $subsite = (object) $subsite;
        if ($isSubdomain) {
            $mainDomain = SnapURL::wwwRemove(SnapURL::parseUrl($mainUrl, PHP_URL_HOST));
            $subDomain  = SnapURL::wwwRemove($subsite->domain);

            if ($subDomain == $mainDomain) {
                return '/';
            } elseif (strpos($subDomain, '.' . $mainDomain) !== false) {
                return substr($subDomain, 0, strpos($subDomain, '.' . $mainDomain));
            } else {
                return $subDomain;
            }
        } else {
            $maiPath     = SnapIO::trailingslashit((string) SnapURL::parseUrl($mainUrl, PHP_URL_PATH));
            $subsitePath = SnapIO::trailingslashit($subsite->path);

            if ($maiPath == $subsitePath) {
                return '/';
            } else {
                return trim(SnapIO::getRelativePath($subsitePath, $maiPath));
            }
        }
    }

    /**
     * Get subsites list in packages
     *
     * @return ParamOption[]
     */
    public static function getSourceIdsOptions()
    {
        static $sourceOpt = null;

        if (is_null($sourceOpt)) {
            $archiveConfig = DUPX_ArchiveConfig::getInstance();
            $sourceOpt     = array();

            foreach ($archiveConfig->subsites as $subsite) {
                $option = new ParamOption(
                    $subsite->id,
                    $subsite->path,
                    ParamFormSitesOwrMap::getSourceIdOptionStatus($subsite)
                );
                $option->setOptGroup($subsite->domain);

                $sourceOpt[] = $option;
            }
        }
        return $sourceOpt;
    }

    /**
     * @param object $subsite Sub Site Object
     *
     * @return string String that indicated if the option should be enabled or disabled
     */
    public static function getSourceIdOptionStatus($subsite)
    {
        return (self::isQualifiedSourceIdForImport($subsite))
            ? ParamOption::OPT_ENABLED
            : ParamOption::OPT_DISABLED;
    }

    /**
     * @param object $subsite Sub Site Object
     *
     * @return bool true or false if the site object if the source can be imported
     */
    public static function isQualifiedSourceIdForImport($subsite)
    {
        return (!DUPX_InstallerState::isImportFromBackendMode() || count($subsite->filteredTables) === 0);
    }

    /**
     * Get default source id
     *
     * @param int $index default index
     *
     * @return bool|int
     */
    protected static function getDefaultSourceId($index = 0)
    {
        if (!isset(DUPX_ArchiveConfig::getInstance()->subsites[$index])) {
            return false;
        }

        return DUPX_ArchiveConfig::getInstance()->subsites[$index]->id;
    }

    /**
     *
     * @return int[]
     */
    protected static function getSubSiteIdsAcceptValues()
    {
        $archiveConfig = DUPX_ArchiveConfig::getInstance();
        $acceptValues  = array(-1);
        foreach ($archiveConfig->subsites as $subsite) {
            if (ParamFormSitesOwrMap::isQualifiedSourceIdForImport($subsite)) {
                $acceptValues[] = $subsite->id;
            }
        }
        return $acceptValues;
    }

    /**
     * Get existing subsites list on import site
     *
     * @return ParamOption[]
     */
    public static function getTargetIdsOptions()
    {
        static $targetOpt = null;

        if (is_null($targetOpt)) {
            $overwriteData = PrmMng::getInstance()->getValue(PrmMng::PARAM_OVERWRITE_SITE_DATA);
            $targetOpt     = array();

            if (!is_array($overwriteData) || !isset($overwriteData['subsites'])) {
                return $targetOpt;
            }

            $targetOpt[] = new ParamOption(
                SiteOwrMap::NEW_SUBSITE_WITH_SLUG,
                'New ' . ($overwriteData['subdomain'] ? 'Domain' : 'Path'),
                ParamOption::OPT_ENABLED
            );

            $targetOpt[] = new ParamOption(
                SiteOwrMap::NEW_SUBSITE_WITH_FULL_DOMAIN,
                'New URL',
                ParamOption::OPT_ENABLED
            );

            foreach ($overwriteData['subsites'] as $subsite) {
                $subsite = (object) $subsite;
                $option  = new ParamOption(
                    $subsite->id,
                    $subsite->path,
                    ParamOption::OPT_ENABLED
                );
                $option->setOptGroup($subsite->domain);
                $targetOpt[] = $option;
            }
        }

        return $targetOpt;
    }

    /**
     * Get extra fata for data attribute list
     *
     * @return array<string, mixed>
     */
    protected function getListExtraData()
    {
        if (is_null($this->extraData)) {
            $archiveConfig   = DUPX_ArchiveConfig::getInstance();
            $this->extraData = array(
                'minListItems' => $this->minListItems,
                'softLimit' => static::SOFT_LIMIT_NUM,
                'hardLimit' => static::HARD_LIMIT_NUM
            );

            $isSubdomain                   = ($archiveConfig->mu_mode == 1);
            $mainSiteUrl                   = $archiveConfig->getRealValue('siteUrl');
            $this->extraData['sourceInfo'] = array(
                'numSites'   => count(static::getSourceIdsOptions()),
                'urlScheme'  => static::getUrlScheme($mainSiteUrl),
                'urlPrefix'  => static::prefixSlugByURL($mainSiteUrl, $isSubdomain),
                'urlPostfix' => static::postfixSlugByURL($mainSiteUrl, $isSubdomain),
                'sites'      => array()
            );
            foreach ($archiveConfig->subsites as $subsite) {
                $this->extraData['sourceInfo']['sites']['id_' . $subsite->id] = array(
                    'domain' => $subsite->domain,
                    'path' => $subsite->path,
                    'slug' => static::getSubsiteSlug($subsite, $mainSiteUrl, $isSubdomain) /** @todo remove */
                );
            }

            $targetData  = $this->getTargetData();
            $isSubdomain = $targetData['isSubdomain'];
            $mainSiteUrl = $targetData['mainSiteUrl'];

            $this->extraData['targetInfo'] = array(
                'numSites'   => count(static::getTargetIdsOptions()),
                'urlScheme'  => static::getUrlScheme($mainSiteUrl),
                'urlPrefix' => static::prefixSlugByURL($mainSiteUrl, $isSubdomain),
                'urlPostfix' => static::postfixSlugByURL($mainSiteUrl, $isSubdomain),
                'sites' => array(
                    'id_' . SiteOwrMap::NEW_SUBSITE_WITH_FULL_DOMAIN => array(
                        'id'   => SiteOwrMap::NEW_SUBSITE_WITH_FULL_DOMAIN,
                        'slug' => '_____/_____',
                        'domain' => '',
                        'path' => ''
                    ),
                    'id_' . SiteOwrMap::NEW_SUBSITE_WITH_SLUG => array(
                        'id'   => SiteOwrMap::NEW_SUBSITE_WITH_SLUG,
                        'slug' => '_____',
                        'domain' => '', /** @todo set set according to the type of multisite */
                        'path' => ''
                    )
                )
            );

            foreach ($targetData['subsites'] as $subsite) {
                $subsite = (object) $subsite;
                $this->extraData['targetInfo']['sites']['id_' . $subsite->id] = array(
                    'slug' => static::getSubsiteSlug($subsite, $mainSiteUrl, $isSubdomain),
                    'domain' => $subsite->domain,
                    'path' => $subsite->path
                );
            }
        }
        return  $this->extraData;
    }

    /**
     * Return target data
     *
     * @return mixed[]
     */
    protected function getTargetData()
    {
        $overwriteData         = PrmMng::getInstance()->getValue(PrmMng::PARAM_OVERWRITE_SITE_DATA);
        $result                = [];
        $result['isSubdomain'] = $overwriteData['subdomain'];
        $result['mainSiteUrl'] = $overwriteData['urls']['home'];
        $result['subsites']    = $overwriteData['subsites'];
        return $result;
    }

    /**
     * Return URL scheme
     *
     * @param string $url URL input
     *
     * @return string
     */
    protected static function getUrlScheme($url)
    {
        return SnapURL::parseUrl($url, PHP_URL_SCHEME) . '://';
    }

    /**
     * Get prefix URL slug
     *
     * @param string $url         URL string
     * @param bool   $isSubdomain if true is subdomain
     *
     * @return string
     */
    protected static function prefixSlugByURL($url, $isSubdomain = false)
    {
        if ($isSubdomain) {
            return '';
        } else {
            $parseUrl = SnapURL::parseUrl($url);
            return $parseUrl['host'] . SnapIO::trailingslashit($parseUrl['path']);
        }
    }

    /**
     * Get postifx URL slug
     *
     * @param string $url         URL string
     * @param bool   $isSubdomain if true is subdomain
     *
     * @return string
     */
    protected static function postfixSlugByURL($url, $isSubdomain = false)
    {
        if (!$isSubdomain) {
            return '/';
        }
        $parseUrl = SnapURL::parseUrl($url);
        return '.' . SnapURL::wwwRemove($parseUrl['host']) . SnapIO::trailingslashit($parseUrl['path']);
    }
}
