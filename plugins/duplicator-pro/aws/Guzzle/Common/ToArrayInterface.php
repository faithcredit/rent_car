<?php
namespace DuplicatorPro\Guzzle\Common;

defined("ABSPATH") or die("");

/**
 * An object that can be represented as an array
 */
interface ToArrayInterface
{
    /**
     * Get the array representation of an object
     *
     * @return array
     */
    public function toArray();
}
