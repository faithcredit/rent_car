<?php

/**
 * @package   Duplicator
 * @copyright (c) 2022, Snapcreek LLC
 */

namespace Duplicator\Utils;

use ReflectionClass;

class GroupOptions
{
    /** @var string */
    protected $option = '';
    /** @var string */
    protected $inputGroupPrefix = '';
    /** @var string[] */
    protected $possibleArguments = [];
    /** @var bool */
    protected $enabled = false;
    /** @var string[] */
    protected $arguments = [];

    /**
     * @param string   $option            name of the option parameter
     * @param string   $inputGroupPrefix  Input that will be used for the html output
     * @param bool     $enabled           status of the option
     * @param string[] $possibleArguments possible sub options
     * @param string[] $arguments         active sub option
     */
    public function __construct(
        $option,
        $inputGroupPrefix,
        $enabled,
        $possibleArguments = [],
        $arguments = []
    ) {
        $this->option            = $option;
        $this->inputGroupPrefix  = $inputGroupPrefix;
        $this->possibleArguments = $possibleArguments;
        $this->enabled           = $enabled;
        $this->arguments         = $arguments;
    }

    /**
     *  Retrives all possible Arguments of a group
     *
     * @return string[]
     */
    public function getPossibleArguments()
    {
        return $this->possibleArguments;
    }

    /**
     * Returns back the input option status
     *
     * @return bool
     */
    public function getEnabled()
    {
        return $this->enabled;
    }

    /**
     * Sets the input option to disabled
     *
     * @return void
     */
    public function disable()
    {
        $this->enabled = false;
    }

    /**
     * Sets the input option to enabled
     *
     * @return void
     */
    public function enabled()
    {
        $this->enabled = true;
    }

    /**
     * Get's the option name
     *
     * @return string
     */
    public function getOptionName()
    {
        return $this->option;
    }

    /**
     * Checks if the input is enabled
     *
     * @return boolean
     */
    public function isEnabled()
    {
        return $this->enabled ? true : false;
    }

    /**
     * Gets the input field name
     *
     * @return string
     */
    public function getInputName()
    {
        return $this->inputGroupPrefix . $this->getOptionName();
    }

    /**
     * Updated the input option status based on the request
     *
     * @return void
     */
    public function update()
    {
        if (filter_input(INPUT_POST, $this->getInputName(), FILTER_VALIDATE_BOOLEAN)) {
            $this->enabled();
        } else {
            $this->disable();
        }
    }

    /**
     * Search an option if exists
     *
     * @param self[] $options options list
     * @param string $search  option name to search
     *
     * @return false|int|string return array index or false if option don't exists
     */
    public static function optionExists($options, $search)
    {
        foreach ($options as $index => $val) {
            if ($val->getOptionName() === $search) {
                return $index;
            }
        }

        return false;
    }

    /**
     * Apply options to mysqldump command
     *
     * @param self[] $options Group Options Array
     *
     * @return string
     */
    public static function getShellOptions($options)
    {
        $resultOptions = [];

        foreach ($options as $option) {
            if (!$option->isEnabled()) {
                continue;
            }

            $resultOptions[] = ' --' . $option->getOptionName();
        }

        return implode(' ', $resultOptions);
    }

    /**
     * Get init object from data
     *
     * @param mixed[] $data array data
     *
     * @return self
     */
    public static function getObjectFromArray($data)
    {
        $reflect = new ReflectionClass(__CLASS__);
        $obj     = $reflect->newInstanceWithoutConstructor();
        foreach ($reflect->getProperties() as $prop) {
            if (!isset($data[$prop->getName()])) {
                continue;
            }
            $prop->setAccessible(true);
            $prop->setValue($obj, $data[$prop->getName()]);
        }
        return $obj;
    }
}
