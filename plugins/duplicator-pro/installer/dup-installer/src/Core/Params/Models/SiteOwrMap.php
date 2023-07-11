<?php

/**
 * @package   Duplicator\Installer
 * @copyright (c) 2022, Snap Creek LLC
 */

namespace Duplicator\Installer\Core\Params\Models;

use Duplicator\Installer\Core\Params\PrmMng;
use Duplicator\Libs\Snap\SnapURL;
use VendorDuplicator\Amk\JsonSerialize\AbstractJsonSerializable;
use DUPX_ArchiveConfig;
use Exception;

class SiteOwrMap extends AbstractJsonSerializable
{
    const NEW_SUBSITE_WITH_SLUG        = 0;
    const NEW_SUBSITE_WITH_FULL_DOMAIN = -1;
    const NEW_SUBSITE_NOT_VALID        = -2;

    /** @var int */
    protected $sourceId = -1;
    /** @var int */
    protected $targetId = -1;
    /** @var string */
    protected $newSlug = '';
    /** @var string */
    protected $blogName = null;

    /**
     * Class constructor
     *
     * @param int    $sourceId source subsite id
     * @param int    $targetId target subsite id
     * @param string $newSlug  new slug on new site
     */
    public function __construct($sourceId, $targetId, $newSlug = '')
    {
        if ($sourceId < 1) {
            throw new Exception('Source id [' . $sourceId . '] invalid ');
        }

        if ($targetId <= self::NEW_SUBSITE_NOT_VALID) {
            throw new Exception('Target id [' . $targetId . '] invalid ');
        }

        if (($sourceObj = DUPX_ArchiveConfig::getInstance()->getSubsiteObjById($sourceId)) === false) {
            throw new Exception('Source site info don\'t exists');
        }

        $this->sourceId = $sourceId;
        $this->targetId = $targetId;
        $this->newSlug  = $newSlug;
        $this->blogName = $sourceObj->blogname;
    }

    /**
     * Get the value of targetId
     *
     * @return int
     */
    public function getTargetId()
    {
        return $this->targetId;
    }

    /**
     * Update target id
     *
     * @param int $targetId new target id
     *
     * @return void
     */
    public function setTargetId($targetId)
    {
        $this->targetId = (int) $targetId;
    }

    /**
     * Get the value of sourceId
     *
     * @return int
     */
    public function getSourceId()
    {
        return $this->sourceId;
    }

    /**
     * Get the value of newSlug
     *
     * @return string
     */
    public function getNewSlug()
    {
        return $this->newSlug;
    }

    /**
     * Return full URL from new slug or false if isn't new mode
     *
     * @param string $mainSiteURL Main site domain
     * @param bool   $subdomain   if true is subdomain else subfolder
     * @param bool   $scheme      if true return URL with scheme
     *
     * @return false|string
     */
    public function getNewSlugFullUrl($mainSiteURL, $subdomain, $scheme = false)
    {
        $mainSiteDomain = SnapURL::parseUrl($mainSiteURL, PHP_URL_HOST);
        if (($schemeURL = SnapURL::parseUrl($mainSiteURL, PHP_URL_SCHEME)) == false) {
            $schemeURL = 'http';
        }

        $result = '';

        switch ($this->targetId) {
            case SiteOwrMap::NEW_SUBSITE_WITH_SLUG:
                if (strlen($this->newSlug) == 0) {
                    return false;
                }

                if ($subdomain) {
                    $result = $this->newSlug . '.' . SnapURL::wwwRemove($mainSiteDomain);
                } else {
                    $result =  $mainSiteDomain . '/' . $this->newSlug;
                }
                break;
            case SiteOwrMap::NEW_SUBSITE_WITH_FULL_DOMAIN:
                if (strlen($this->newSlug) == 0) {
                    return false;
                }
                $result = $this->newSlug;
                break;
            default:
                return false;
        }

        if ($scheme) {
            $result = $schemeURL . '://' . $result;
        }

        return $result;
    }

    /**
     * Get source sibsite info
     *
     * @return false|array<string, mixed>
     */
    public function getSourceSiteInfo()
    {
        if (($info = \DUPX_ArchiveConfig::getInstance()->getSubsiteObjById($this->sourceId)) == false) {
            return false;
        }

        return (array) $info;
    }

    /**
     * Return target site info
     *
     * @return false|array<string, mixed>
     */
    public function getTargetSiteInfo()
    {
        $overwriteData = PrmMng::getInstance()->getValue(PrmMng::PARAM_OVERWRITE_SITE_DATA);
        foreach ($overwriteData['subsites'] as $subsite) {
            if ($subsite['id'] == $this->targetId) {
                return $subsite;
            }
        }

        return false;
    }
}
