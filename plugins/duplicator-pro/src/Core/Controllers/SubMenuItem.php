<?php

namespace Duplicator\Core\Controllers;

use Duplicator\Core\CapMng;

/**
 * Sub menu item class
 */
class SubMenuItem
{
    /** @var string */
    public $slug = '';
    /** @var string */
    public $label = '';
    /** @var string */
    public $parent = '';
    /** @var bool|string */
    public $capatibility = true;
    /** @var int */
    public $position = 10;
    /** @var string */
    public $link = '';
    /** @var bool */
    public $active = false;

    /**
     * Class constructor
     *
     * @param string      $slug         item slug
     * @param string      $label        menu label
     * @param string      $parent       parent slug
     * @param bool|string $capatibility item capability, true if have parent permission
     * @param int         $position     position
     */
    public function __construct(
        $slug,
        $label = '',
        $parent = '',
        $capatibility = true,
        $position = 10
    ) {
        $this->slug         = (string) $slug;
        $this->label        = (string) $label;
        $this->parent       = (string) $parent;
        $this->capatibility = $capatibility;
        $this->position     = $position;
    }

    /**
     * Check if user can see this item
     *
     * @return bool
     */
    public function userCan()
    {
        if ($this->capatibility === true) {
            return true;
        }

        return CapMng::can($this->capatibility, false);
    }
}
