<?php

/**
 * Controller interface
 *
 * @package   Duplicator
 * @copyright (c) 2022, Snap Creek LLC
 */

namespace Duplicator\Core\Controllers;

interface ControllerInterface
{
    /**
     * Method called on wordpress hook init action
     *
     * @return void
     */
    public function hookWpInit();

    /**
     * Excecute controller
     *
     * @return void
     */
    public function run();

    /**
     * Render page
     *
     * @return void
     */
    public function render();
}
