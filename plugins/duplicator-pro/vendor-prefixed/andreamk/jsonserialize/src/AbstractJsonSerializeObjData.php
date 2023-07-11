<?php

/**
 * AbstractJsonSerializeObjData class
 *
 * @package Amk\JsonSerialize
 */
namespace VendorDuplicator\Amk\JsonSerialize;

use Exception;
use ReflectionClass;
use ReflectionObject;
/**
 * This calsse contains the logic that converts objects into values ready to be encoded in json
 */
abstract class AbstractJsonSerializeObjData
{
    const CLASS_KEY_FOR_JSON_SERIALIZE = 'CL_-=_-=';
    const JSON_SKIP_MAGIC_METHODS = 0b100000000000000000000000000000;
    // 29 bit mask
    const JSON_SKIP_CLASS_NAME = 0b1000000000000000000000000000000;
    // 30 bit mask
    const JSON_SKIP_SANITIZE = 0b10000000000000000000000000000000;
    // 31 bit mask
    /**
     * Convert object to array with private and protected proprieties.
     * Private parent class proprieties aren't considered.
     *
     * @param object         $obj        obejct to serialize
     * @param int            $flags      flags bitmask
     * @param int[]|string[] $objParents objs parents unique objects hash list
     *
     * @return mixed[]
     */
    protected static final function objectToJsonData($obj, $flags = 0, $objParents = [])
    {
        $reflect = new ReflectionObject($obj);
        $result = [];
        if (!($flags & self::JSON_SKIP_CLASS_NAME)) {
            $result[self::CLASS_KEY_FOR_JSON_SERIALIZE] = $reflect->name;
        }
        if (!($flags & self::JSON_SKIP_MAGIC_METHODS)) {
            if (\method_exists($obj, '__serialize')) {
                $data = $obj->__serialize();
                if (!\is_array($data)) {
                    throw new Exception('__serialize method must return an array');
                }
                return \array_merge($data, $result);
            } elseif (\method_exists($obj, '__sleep')) {
                $includeProps = $obj->__sleep();
                if (!\is_array($includeProps)) {
                    throw new Exception('__sleep method must return an array');
                }
            } else {
                $includeProps = \true;
            }
        } else {
            $includeProps = \true;
        }
        // Get all props of current class but not props private of parent class and static props
        foreach ($reflect->getProperties() as $prop) {
            if ($prop->isStatic()) {
                continue;
            }
            $propName = $prop->getName();
            if ($includeProps !== \true && !\in_array($propName, $includeProps)) {
                continue;
            }
            $prop->setAccessible(\true);
            $propValue = $prop->getValue($obj);
            $result[$propName] = self::valueToJsonData($propValue, $flags, $objParents);
        }
        return $result;
    }
    /**
     * Recursive parse values, all objects are transformed to array
     *
     * @param mixed          $value      valute to parse
     * @param int            $flags      flags bitmask
     * @param int[]|string[] $objParents objs parents unique hash or ids after PHP 7.2
     *
     * @return mixed
     */
    protected static final function valueToJsonData($value, $flags = 0, $objParents = [])
    {
        switch (\gettype($value)) {
            case "boolean":
            case "integer":
            case "double":
            case "string":
            case "NULL":
                return $value;
            case "array":
                /** @var mixed[] $value */
                $result = [];
                foreach ($value as $key => $arrayVal) {
                    $result[$key] = self::valueToJsonData($arrayVal, $flags, $objParents);
                }
                return $result;
            case "object":
                /** @var object $value */
                $objHash = self::getObjIdentifier($value);
                if (\in_array($objHash, $objParents)) {
                    // prevent infinite recursion loop
                    return null;
                }
                $objParents[] = $objHash;
                return self::objectToJsonData($value, $flags, $objParents);
            case "resource":
            case "resource (closed)":
            case "unknown type":
            default:
                return null;
        }
    }
    /**
     * Return value from json decoded data
     *
     * @param mixed               $value json decoded data
     * @param int                 $flags flags bitmask
     * @param ?JsonUnserializeMap $map   unserialize map
     *
     * @return mixed
     */
    protected static final function jsonDataToValue($value, $flags = 0, $map = null)
    {
        if ($map !== null) {
            $current = $map->getCurrent();
            if ($map->isMapped()) {
                $mappedVal = $map->getMappedValue($value, $isReference);
                if ($isReference) {
                    return $mappedVal;
                }
                switch (\gettype($mappedVal)) {
                    case 'array':
                        /** @var mixed[] $mappedVal */
                        $result = [];
                        foreach ($mappedVal as $key => $arrayVal) {
                            $map->setCurrent($key, $current);
                            $result[$key] = self::jsonDataToValue($arrayVal, $flags, $map);
                        }
                        return $result;
                    case 'object':
                        /** @var object $mappedVal */
                        if (!\is_array($value)) {
                            $value = [];
                        }
                        return self::fillObjFromValue($value, $mappedVal, $flags, $map);
                    default:
                        return $mappedVal;
                }
            }
        }
        switch (\gettype($value)) {
            case 'array':
                /** @var mixed[] $value */
                if (($newClassName = self::getClassFromArray($value)) === \false) {
                    $result = [];
                    foreach ($value as $key => $arrayVal) {
                        if ($map !== null) {
                            $map->setCurrent($key, $current);
                        }
                        $result[$key] = self::jsonDataToValue($arrayVal, $flags, $map);
                    }
                } else {
                    $result = self::fillObjFromValue($value, self::getObjFromClass($newClassName), $flags, $map);
                }
                return $result;
            case 'boolean':
            case 'integer':
            case 'double':
            case 'string':
            case "NULL":
                return $value;
            default:
                return null;
        }
    }
    /**
     * Get object from class name, if class don't exists return StdClass.
     * the object is intialized without call the constructor.
     *
     * @param string $class class name
     *
     * @return object
     */
    public static final function getObjFromClass($class)
    {
        if (\class_exists($class)) {
            $classReflect = new ReflectionClass($class);
            return $classReflect->newInstanceWithoutConstructor();
        } else {
            return new \StdClass();
        }
    }
    /**
     * Fill passed object from array values
     *
     * @param mixed[]             $value value from json data
     * @param object              $obj   object to fill with json data
     * @param int                 $flags flags bitmask
     * @param ?JsonUnserializeMap $map   unserialize map
     *
     * @return object
     */
    protected static final function fillObjFromValue($value, $obj, $flags = 0, $map = null)
    {
        if ($map !== null) {
            $current = $map->getCurrent();
            $map->addReferenceObjOfCurrent($obj);
        }
        if ($obj instanceof \stdClass) {
            foreach ($value as $arrayProp => $arrayValue) {
                if ($arrayProp == self::CLASS_KEY_FOR_JSON_SERIALIZE) {
                    continue;
                }
                if ($map !== null) {
                    $map->setCurrent($arrayProp, $current);
                }
                $obj->{$arrayProp} = self::jsonDataToValue($arrayValue, $flags, $map);
            }
        } else {
            $skipMagicMethods = $flags & self::JSON_SKIP_MAGIC_METHODS;
            if (!$skipMagicMethods && \method_exists($obj, '__unserialize')) {
                $obj->__unserialize($value);
            } else {
                $reflect = new ReflectionObject($obj);
                foreach ($reflect->getProperties() as $prop) {
                    $prop->setAccessible(\true);
                    $propName = $prop->getName();
                    if ($map !== null) {
                        $map->setCurrent($propName, $current);
                        if (!\array_key_exists($propName, $value) && $map->isMapped()) {
                            $value[$propName] = null;
                        }
                    }
                    if (!\array_key_exists($propName, $value) || $prop->isStatic()) {
                        continue;
                    }
                    $prop->setValue($obj, self::jsonDataToValue($value[$propName], $flags, $map));
                }
                if (!$skipMagicMethods && \method_exists($obj, '__wakeup')) {
                    $obj->__wakeup();
                }
            }
        }
        return $obj;
    }
    /**
     * Return class name from array values
     *
     * @param mixed[] $array array data
     *
     * @return false|string  false if prop not found
     */
    protected static final function getClassFromArray($array)
    {
        /** @var false|string $result */
        $result = isset($array[self::CLASS_KEY_FOR_JSON_SERIALIZE]) ? $array[self::CLASS_KEY_FOR_JSON_SERIALIZE] : \false;
        return $result;
    }
    /**
     * Get object unique identifier
     *
     * @param object $obj input object
     *
     * @return int|string
     */
    protected static final function getObjIdentifier($obj)
    {
        static $useObjId = null;
        if (\is_null($useObjId)) {
            $useObjId = \function_exists('spl_object_id');
        }
        return $useObjId ? \spl_object_id($obj) : \spl_object_hash($obj);
    }
}
