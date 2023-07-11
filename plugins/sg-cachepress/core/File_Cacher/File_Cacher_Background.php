<?php
namespace SiteGround_Optimizer\File_Cacher;

/**
 * Extends the background process class for the Filebased cache preload background process.
 */
class File_Cacher_Background extends \WP_Background_Process {
	/**
	 * Action
	 *
	 * (default value: 'background_process')
	 *
	 * @var string
	 * @access protected
	 */
	protected $action = 'file_cacher_preload';

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
		$file_cacher = new File_Cacher();
		$file_cacher->hit_url_cache( $item );

		sleep( 1 );

		// Remove the process from queue.
		return false;
	}
}
