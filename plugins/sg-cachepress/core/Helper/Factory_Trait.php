<?php
namespace SiteGround_Optimizer\Helper;

use SiteGround_i18n\i18n_Service;
/**
 * Trait used for factory pattern in the plugin.
 */
trait Factory_Trait {

	/**
	 * Create a new dependency.
	 *
	 * @since 5.9.0
	 *
	 * @param string $namespace  The namespace of the dependency.
	 * @param string $class The type of the dependency.
	 *
	 * @throws \Exception Exception If the type is not supported.
	 */
	public function factory( $namespace, $class ) {
		// Build the type and path for the dependency.
		$type = str_replace( ' ', '_', ucwords( str_replace( '_', ' ', $class ) ) );
		$path = str_replace( ' ', '_', ucwords( str_replace( '_', ' ', $namespace ) ) );

		$class_path = 'SiteGround_Optimizer\\' . $path . '\\' . $type;

		if ( ! class_exists( $class_path ) ) {
			throw new \Exception( 'Unknown dependency type "' . $type . '" in "' . $path . '".' );
		}

		// Define the class.
		$this->$class = new $class_path();
	}
}