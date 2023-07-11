<?php
namespace SiteGround_Optimizer\Lazy_Load;

/**
 * SG Lazy_Load_Images main plugin class
 */
class Lazy_Load_Iframes extends Abstract_Lazy_Load {

	/**
	 * Regex parts for checking content
	 *
	 * @var string
	 */
	public $regexp = '/(?:<iframe[^>]*)(?:(?:\/>)|(?:>.*?<\/iframe>))/i';

	/**
	 * Regex for already replaced items
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
		'/(<iframe.*?)(src)=["|\']((?!data).*?)["|\']/i',
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
	 * Filter for excluding specific iframe by source.
	 *
	 * @var string
	 */
	public $exclude_assets_filter = 'sgo_lazy_load_exclude_iframes';

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
		return str_replace( '<iframe', '<iframe class="lazyload"', $element );
	}
}
