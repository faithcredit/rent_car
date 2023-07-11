<?php

/**
 * @package   Duplicator
 * @copyright (c) 2022, Snap Creek LLC
 */

namespace Duplicator\Core\Models;

interface UpdateFromInputInterface
{
    /**
     * Set data from query input
     *
     * @param int $type One of INPUT_GET, INPUT_POST, INPUT_COOKIE, INPUT_SERVER, or INPUT_ENV, SnapUtil::INPUT_REQUEST
     *
     * @return bool true on success or false on failure
     */
    public function setFromInput($type);
}
