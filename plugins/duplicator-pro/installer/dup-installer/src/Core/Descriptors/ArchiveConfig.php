<?php

/**
 * @package   Duplicator
 * @copyright (c) 2022, Snap Creek LLC
 */

namespace Duplicator\Installer\Core\Descriptors;

/**
 * Archive config descriptior
 *
 * @todo The DUPX_ArchiveConfig class and all its methods will have to be transferred to this class
 */
class ArchiveConfig
{
    const SECURE_MODE_NONE        = 0;
    const SECURE_MODE_INST_PWD    = 1;
    const SECURE_MODE_ARC_ENCRYPT = 2;
}
