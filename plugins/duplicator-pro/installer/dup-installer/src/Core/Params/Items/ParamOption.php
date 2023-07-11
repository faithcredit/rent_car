<?php

/**
 * @package   Duplicator\Installer
 * @copyright (c) 2022, Snap Creek LLC
 */

namespace Duplicator\Installer\Core\Params\Items;

/**
 * this class describes the options for select, radio and multiple checboxes
 */
class ParamOption
{
    const OPT_ENABLED  = 'opt_enabled';
    const OPT_DISABLED = 'opt_disabled';
    const OPT_HIDDEN   = 'opt_hidden';

    /** @var string */
    public $value = '';
    /** @var string */
    public $label = '';
    /** @var array<string, mixed> */
    public $attrs = [];
    /** @var string */
    protected $optStatus = self::OPT_ENABLED;
    /** @var string */
    protected $note = '';
    /** @var string */
    protected $groupLabel = '';

    /**
     * Class constructor
     *
     * @param mixed                $value     option value
     * @param string               $label     label
     * @param string|callable      $optStatus option status. can be a fixed status or a callback
     * @param array<string, mixed> $attrs     option attributes
     */
    public function __construct($value, $label, $optStatus = self::OPT_ENABLED, $attrs = array())
    {
        $this->value     = $value;
        $this->label     = $label;
        $this->optStatus = $optStatus;
        $this->attrs     = (array) $attrs;
    }

    /**
     * get current statis.
     *
     * @return string
     */
    public function getStatus()
    {
        if (is_callable($this->optStatus)) {
            return call_user_func($this->optStatus, $this);
        } else {
            return $this->optStatus;
        }
    }

    /**
     * Set options status
     *
     * @param string|callable $optStatus option status. can be a fixed status or a callback
     *
     * @return void
     */
    public function setStatus($optStatus)
    {
        $this->optStatus = $optStatus;
    }

    /**
     * Set option note
     *
     * @param string|callable $note option note
     *
     * @return void
     */
    public function setNote($note)
    {
        $this->note = is_callable($note) ? $note : ((string) $note);
    }

    /**
     *
     * @return string
     */
    public function getNote()
    {
        $note = '';
        if (is_callable($this->note)) {
            $note = call_user_func($this->note, $this);
        } else {
            $note = $this->note;
        }

        return (empty($note) ? '' : '<div class="sub-note" >' . $note . '</div>');
    }

    /**
     * Set option group, used on select
     *
     * @param string $label optiongroup label is empty reset option
     *
     * @return void
     */
    public function setOptGroup($label)
    {
        $this->groupLabel = (string) $label;
    }

    /**
     * Return option group label, empty if not set
     *
     * @return string
     */
    public function getOptGroup()
    {
        return $this->groupLabel;
    }

    /**
     *
     * @return bool
     */
    public function isEnable()
    {
        return $this->getStatus() == self::OPT_ENABLED;
    }

    /**
     *
     * @return bool
     */
    public function isDisabled()
    {
        return $this->getStatus() == self::OPT_DISABLED;
    }

    /**
     *
     * @return bool
     */
    public function isHidden()
    {
        return $this->getStatus() == self::OPT_HIDDEN;
    }
}
