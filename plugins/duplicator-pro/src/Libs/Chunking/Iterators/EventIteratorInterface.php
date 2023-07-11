<?php

/**
 * @package   Duplicator
 * @copyright (c) 2022, Snap Creek LLC
 */

namespace Duplicator\Libs\Chunking\Iterators;

use Iterator;

/**
 * Event iterator interface
 */
interface EventIteratorInterface extends Iterator
{
    /**
     * The callback accept 3 params
     * $event , $index and $current values
     *
     * @param null|callable $callback the callback to be exectured
     *
     * @return mixed
     */
    public function setEventCallback($callback = null);

    /**
     * Execute event
     *
     * @param mixed $event   the event
     * @param mixed $key     the key
     * @param mixed $current current position
     *
     * @return mixed
     */
    public function doEvent($event, $key, $current);
}
