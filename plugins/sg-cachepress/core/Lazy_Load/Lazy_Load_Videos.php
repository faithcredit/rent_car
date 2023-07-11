<?php
namespace SiteGround_Optimizer\Lazy_Load;

/**
 * SG Lazy_Load_Images main plugin class
 */
class Lazy_Load_Videos extends Abstract_Lazy_Load {

	/**
	 * Regex parts for checking content.
	 *
	 * @var string
	 */
	public $regexp = '/(?:<video[^>]*)(?:(?:\/>)|(?:>.*?<\/video>))/is';

	/**
	 * Regex for already replaced items.
	 *
	 * @var string
	 */
	public $regex_replaced = "/class=['\"][\w\s]*(lazyload)+[\w\s]*['\"]/is";

	/**
	 * Search patterns.
	 *
	 * @var array
	 */
	public $patterns = array(
		'/(<video[^>]+)(src)=["|\']((?!data).*?)["|\']/i',
	);

	/**
	 * Replace patterns.
	 *
	 * @var array
	 */
	public $replacements = array(
		'$1data-$2="$3"',
	);

	/**
	 * Filter for excluding specific video.
	 *
	 * @var string
	 */
	public $exclude_assets_filter = 'sgo_lazy_load_exclude_videos';

	/**
	 * Add classname to the html element.
	 *
	 * @since  5.6.0
	 *
	 * @param  string $element HTML element.
	 *
	 * @return string          HTML element with lazyload class.
	 */
	public function add_lazyload_class( $element ) {
		return str_replace( '<video', '<video class="lazyload"', $element );
	}
}
