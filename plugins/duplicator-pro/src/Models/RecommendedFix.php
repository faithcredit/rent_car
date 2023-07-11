<?php

/**
 *
 * @package   Duplicator
 * @copyright (c) 2022, Snap Creek LLC
 */

namespace Duplicator\Models;

use ReflectionObject;

class RecommendedFix
{
    const TYPE_TEXT = 0;
    const TYPE_FIX  = 1;

    /** @var string */
    public $id = '';
    /** @var int Enum type */
    public $recommended_fix_type = self::TYPE_TEXT;
    /** @var string */
    public $error_text = '';
    /** @var string */
    public $parameter1 = '';
    /** @var mixed[] */
    public $parameter2 = [];

    /**
     * Get object
     *
     * @param mixed[] $data input data
     *
     * @return self
     */
    public static function objectFromData($data)
    {
        $result  = new self();
        $reflect = new ReflectionObject($result);
        foreach ($reflect->getProperties() as $prop) {
            if (!isset($data[$prop->getName()])) {
                continue;
            }
            $prop->setAccessible(true);
            $prop->setValue($result, $data[$prop->getName()]);
        }
        return $result;
    }
}
