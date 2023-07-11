<?php

/**
 * Abstract class to extend in order to use the maximum potentialities of JsonSerialize
 *
 * @package Amk\JsonSerialize
 */
namespace VendorDuplicator\Amk\JsonSerialize;

/**
 * Abstract class to extend in order to use the maximum potentialities of JsonSerialize
 */
abstract class AbstractJsonSerializable extends AbstractJsonSerializeObjData implements \JsonSerializable
{
    /**
     * Prepared json serialized object
     *
     * @return mixed
     */
    #[\ReturnTypeWillChange]
    public final function jsonSerialize()
    {
        return self::objectToJsonData($this, 0, []);
    }
}
