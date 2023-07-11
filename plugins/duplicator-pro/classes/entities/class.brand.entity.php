<?php

/**
 * @package   Duplicator
 * @copyright (c) 2022, Snap Creek LLC
 */

use Duplicator\Addons\ProBase\License\License;
use Duplicator\Core\Models\AbstractEntityList;
use Duplicator\Libs\Snap\SnapIO;

class DUP_PRO_Brand_Entity extends AbstractEntityList
{
    const MODE_KEEP_PLUGIN   = 0;
    const MODE_REMOVE_PLUGIN = 1;

    /** @var string */
    public $name = '';
    /** @var string */
    public $notes = '';
    /** @var bool */
    public $editable = true;
    /** @var string */
    public $logo = '<i class="fa fa-bolt fa-sm"></i> Duplicator Pro';
    /** @var string[] */
    public $attachments = array();
    /** @var bool */
    protected $default = false;
    /** @var int */
    protected $brandMode = self::MODE_REMOVE_PLUGIN;

    /**
     * Return entity type identifier
     *
     * @return string
     */
    public static function getType()
    {
        return 'DUP_PRO_Brand_Entity';
    }

    /**
     * Get the value of default
     *
     * @return bool true if is default brand
     */
    public function isDefault()
    {
        return $this->default;
    }

    /**
     * Return all list of brand with default brand included
     *
     * @return self[]|false false on failure
     */
    public static function getAllWithDefault()
    {
        if (License::can(License::CAPABILITY_BRAND)) {
            $brands = self::getAll();
        } else {
            $brands = [];
        }

        if ($brands === false) {
            return false;
        }

        array_unshift($brands, self::get_default_brand());
        return $brands;
    }

    /**
     * Get entity by id
     * For legacy reason $id can be -2 and thar case the brand is default
     *
     * @param int $id entity id
     *
     * @return self Return entity brand istance or default if son't exists
     */
    public static function getByIdOrDefault($id)
    {
        if ($id < 0 || !License::can(License::CAPABILITY_BRAND)) {
            return self::get_default_brand();
        } elseif (($result = parent::getById($id)) == false) {
            return self::get_default_brand();
        } else {
            return $result;
        }
    }

    public function get_mode_text()
    {
        $txt = __('Unknown', 'duplicator-pro');
        switch ($this->brandMode) {
            case self::MODE_KEEP_PLUGIN:
                $txt = __('Keep Plugin', 'duplicator-pro');
                break;
            case self::MODE_REMOVE_PLUGIN:
                $txt = __('Remove Plugin', 'duplicator-pro');
                break;
        }

        return $txt;
    }

    /**
     * Save entity
     *
     * @return bool  True on success, or false on error.
     */
    public function save()
    {
        if (!License::can(License::CAPABILITY_BRAND)) {
            return false;
        }
        if ($this->default) {
            return false;
        }
        return parent::save();
    }

    /**
     * Collect all attachments into `$this->attachments`
     *
     * @param string|array $attachments image paths inside /wp-content/uploads folder, Accept array or comma delimited array
     *
     * @return void
     */
    public function setAttachments($attachments)
    {
        if (!is_array($attachments)) {
            $attachments = array_map("trim", preg_split('/(;|,)/', $attachments));
        }

        $upload_dir = wp_upload_dir();
        $dir        = $upload_dir['basedir'];
        // Uploads folder
        $dir = str_replace(array('\\','//'), array('/','/'), $dir);
        foreach ($attachments as $attachment) {
            if (file_exists("{$dir}{$attachment}")) {
                $this->attachments[] = $attachment;
            }
        }
    }

    /**
     * Return default brand object
     *
     * @return self
     */
    public static function get_default_brand()
    {
        $brand              = new DUP_PRO_Brand_Entity();
        $brand->name        = __('Default', 'duplicator-pro');
        $brand->notes       = __('The default content used when a brand is not defined', 'duplicator-pro');
        $brand->logo        = sprintf(__('%s Duplicator Pro', 'duplicator-pro'), '<i class="fa fa-bolt fa-sm"></i>');
        $brand->editable    = false;
        $brand->attachments = [];

        $refObject   = new ReflectionObject($brand);
        $refProperty = $refObject->getProperty('default');
        $refProperty->setAccessible(true);
        $refProperty->setValue($brand, true);

        return $brand;
    }

    /**
     * Prepare attahcment to installer
     *
     * @return bool true on success, fail on failure
     */
    public function prepare_attachments_to_installer()
    {
        $this->emptyAttachmentFolder();

        if (empty($this->attachments)) {
            return true;
        }

        $brandAttFolder = self::getAttachmentFolder();

        if (wp_mkdir_p($brandAttFolder) === false) {
            return false;
        }

        $uploadInfo = wp_upload_dir();
        $uploadDir  = SnapIO::safePathUntrailingslashit($uploadInfo['basedir']);

        $copied = false;
        foreach ($this->attachments as $attachment) {
            $sourceFile = $uploadDir . $attachment;
            $targetFile = $brandAttFolder . $attachment;
            if (!file_exists($sourceFile)) {
                continue;
            }

            if (wp_mkdir_p(dirname($targetFile)) === false) {
                return false;
            }

            if (copy($sourceFile, $targetFile) === false) {
                DUP_PRO_Log::error("Error copying {$sourceFile} to {$targetFile}", '', false);
            } else {
                $copied = true;
            }
        }

        return $copied;
    }

    /**
     * Empty brand attachment folder
     *
     * @return bool true on success, fail on failure
     */
    protected function emptyAttachmentFolder()
    {
        $dir = self::getAttachmentFolder();
        if (file_exists($dir)) {
            return SnapIO::rrmdir($dir);
        }
        return true;
    }


    /**
     * Return attahcment folder
     *
     * @todo move this folder outsite pligin installation. (add attachmetn directly ad packag.
     *
     * @return string
     */
    protected static function getAttachmentFolder()
    {
        return DUPLICATOR____PATH . "/installer/dup-installer/assets/images/brand";
    }
}
