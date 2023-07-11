<?php
namespace SiteGround_Optimizer\Database_Optimizer;

/**
 * Extends the background process class for the database optimization background process.
 */
class Database_Optimizer_Background extends \WP_Background_Process {
	/**
	 * Action
	 *
	 * (default value: 'background_process')
	 *
	 * @var string
	 * @access protected
	 */
	protected $action = 'database_optimization';

	/**
	 * Task
	 *
	 * @param array $item Array containing the class and the
	 *                    method to call in background process.
	 *
	 * @return mixed      False on process success.
	 *                    The current item on failure, which will restart the process.
	 */
	protected function task( $item ) {
		$database_optimizer = new Database_Optimizer();
		// Call the class method.
		$result = call_user_func( array( $database_optimizer, $item ) );

		// Remove the process from queue.
		return false;
	}
}
