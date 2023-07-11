<?php

/**
 * @package   Duplicator
 * @copyright (c) 2022, Snapcreek LLC
 */

namespace Duplicator\Utils;

/**
 * This class is for accumulation of messages. It implements __toString method, so objects
 * of this class can be passed to strval or be used in other contexts where string is expected.
 */
class IncrementalStatusMessage
{
    /** @var string Prepend to each line in __toString method */
    private $prepend = "-> ";
    /** @var string Append to each line in __toString method */
    private $append = "\n";
    /** @var string[] */
    protected $messages = [];

    /**
     * Constructor of the class
     */
    public function __construct()
    {
    }

    /**
     * @param mixed $message Message to add to container. It will be converted to string.
     *
     * @return void
     */
    public function addMessage($message)
    {
        $this->messages[] = (string) $message;
    }

    /**
     * Resets/empties messages container
     *
     * @return void
     */
    public function reset()
    {
        $this->messages = array();
    }

    /**
     * Converts/combines logged messages into one string.
     * Takes into account attributes $append and $prepend for each line.
     *
     * @return string
     */
    public function __toString()
    {
        $finalString = "";
        foreach ($this->messages as $message) {
            $finalString .= $this->prepend . $message . $this->append;
        }
        return $finalString;
    }

    /**
     * Setter method
     *
     * @param string $prepend Prepend to each line
     *
     * @return void
     */
    public function setPrepend($prepend = "->")
    {
        $this->prepend = (string) $prepend;
    }

    /**
     * Setter method
     *
     * @param string $append Append to each line
     *
     * @return void
     */
    public function setAppend($append = "\n")
    {
        $this->append = (string) $append;
    }

    /**
     * Getter method
     *
     * @return string prepend
     */
    public function getPrepend()
    {
        return $this->prepend;
    }

    /**
     * Getter method
     *
     * @return string append
     */
    public function getAppend()
    {
        return $this->append;
    }
}
