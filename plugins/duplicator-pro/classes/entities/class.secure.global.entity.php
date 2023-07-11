<?php

/**
 * @package   Duplicator
 * @copyright (c) 2022, Snap Creek LLC
 */

use Duplicator\Core\Models\AbstractEntitySingleton;
use Duplicator\Core\Models\UpdateFromInputInterface;
use Duplicator\Libs\Snap\SnapUtil;
use Duplicator\Utils\Crypt\CryptBlowfish;
use VendorDuplicator\Amk\JsonSerialize\JsonSerialize;

/**
 * Secure Global Entity. Used to store settings requiring encryption.
 *
 * @todo remove this entity and put props on globals
 */
class DUP_PRO_Secure_Global_Entity extends AbstractEntitySingleton implements UpdateFromInputInterface
{
    /** @var string */
    public $basic_auth_password = '';
    /** @var string */
    public $lkp = '';

    /**
     * Class contructor
     */
    protected function __construct()
    {
    }

    /**
     * Entity type
     *
     * @return string
     */
    public static function getType()
    {
        return 'DUP_PRO_Secure_Global_Entity';
    }

    /**
     * Will be called, automatically, when Serialize
     *
     * @return array
     */
    public function __serialize()
    {
        $data = JsonSerialize::serializeToData($this, JsonSerialize::JSON_SKIP_MAGIC_METHODS |  JsonSerialize::JSON_SKIP_CLASS_NAME);
        if (strlen($this->basic_auth_password)) {
            $data['basic_auth_password'] = CryptBlowfish::encrypt($this->basic_auth_password);
        }
        if (strlen($this->lkp)) {
            $data['lkp'] = CryptBlowfish::encrypt($this->lkp);
        }
        return $data;
    }

    /**
     * Serialize
     *
     * Will be called, automatically, when unserialize() is called on a BigInteger object.
     *
     * @return void
     */
    public function __wakeup()
    {
        if (strlen($this->basic_auth_password)) {
            $this->basic_auth_password = CryptBlowfish::decrypt($this->basic_auth_password);
        }

        if (strlen($this->lkp)) {
            $this->lkp = CryptBlowfish::decrypt($this->lkp);
        }
    }

    /**
     * Set data from query input
     *
     * @param int $type One of INPUT_GET, INPUT_POST, INPUT_COOKIE, INPUT_SERVER, or INPUT_ENV, SnapUtil::INPUT_REQUEST
     *
     * @return bool true on success or false on failure
     */
    public function setFromInput($type)
    {
        $input = SnapUtil::getInputFromType($type);

        $this->basic_auth_password = isset($input['basic_auth_password']) ? SnapUtil::sanitizeNSCharsNewlineTrim($input['basic_auth_password']) : '';
        return true;
    }

    public function setFromImportData($global_data)
    {
        $this->basic_auth_password = $global_data->basic_auth_password;
        // skip in import settings
        //$this->lkp                 = $global_data->lkp;
    }
}
