<?php

/**
 * Proxy connection interface
 *
 * @package Requests\Proxy
 * @since   1.6
 */
namespace VendorDuplicator\WpOrg\Requests;

use VendorDuplicator\WpOrg\Requests\Hooks;
/**
 * Proxy connection interface
 *
 * Implement this interface to handle proxy settings and authentication
 *
 * Parameters should be passed via the constructor where possible, as this
 * makes it much easier for users to use your provider.
 *
 * @see \VendorDuplicator\WpOrg\Requests\Hooks
 *
 * @package Requests\Proxy
 * @since   1.6
 */
interface Proxy
{
    /**
     * Register hooks as needed
     *
     * This method is called in {@see \WpOrg\Requests\Requests::request()} when the user
     * has set an instance as the 'auth' option. Use this callback to register all the
     * hooks you'll need.
     *
     * @see \VendorDuplicator\WpOrg\Requests\Hooks::register()
     * @param \VendorDuplicator\WpOrg\Requests\Hooks $hooks Hook system
     */
    public function register(Hooks $hooks);
}
