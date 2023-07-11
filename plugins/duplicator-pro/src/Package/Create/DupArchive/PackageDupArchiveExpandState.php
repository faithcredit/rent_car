<?php

/**
 *
 * @package   Duplicator
 * @copyright (c) 2022, Snap Creek LLC
 */

namespace Duplicator\Package\Create\DupArchive;

use DUP_PRO_Global_Entity;
use DUP_PRO_Package;
use Duplicator\Libs\DupArchive\Headers\DupArchiveHeader;
use Duplicator\Libs\DupArchive\States\DupArchiveExpandState;
use Exception;

/**
 * Dup archive expand state
 */
class PackageDupArchiveExpandState extends DupArchiveExpandState
{
    /** @var DUP_PRO_Package */
    private $package = null;

    /**
     * Class constructor
     *
     * @param DupArchiveHeader $archiveHeader archive header
     * @param DUP_PRO_Package  $package       package
     */
    public function __construct(DupArchiveHeader $archiveHeader, DUP_PRO_Package $package = null)
    {
        if ($package == null) {
            throw new Exception('Package required');
        }
        $this->package = $package;
        parent::__construct($archiveHeader);
        $global                  = DUP_PRO_Global_Entity::getInstance();
        $this->throttleDelayInUs = $global->getMicrosecLoadReduction();
    }

    /**
     * Filter props on json encode
     *
     * @return string[]
     */
    public function __sleep()
    {
        $props = array_keys(get_object_vars($this));
        return array_diff($props, array('package'));
    }

    /**
     * Set package
     *
     * @param DUP_PRO_Package $package packge archive
     *
     * @return void
     */
    public function setPackage(DUP_PRO_Package $package)
    {
        $this->package = $package;
    }

    /**
     * Save state functon
     *
     * @return void
     */
    public function save()
    {
        $this->package->build_progress->dupExpand = $this;
        $this->package->save();
    }
}
