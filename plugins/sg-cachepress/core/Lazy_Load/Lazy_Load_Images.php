<?php
namespace SiteGround_Optimizer\Lazy_Load;

/**
 * SG Lazy_Load_Images main plugin class
 */
class Lazy_Load_Images extends Abstract_Lazy_Load {

	/**
	 * Regex parts for checking content
	 *
	 * @var string
	 */
	public $regexp = '/<img[\s\r\n]+.*?>/is';

	/**
	 * Regex for already replaced items
	 *
	 * @var string
	 */
	public $regex_replaced = "/src=['\"]data:image/is";

	/**
	 * Replace patterns.
	 *
	 * @var array
	 */
	public $patterns = array(
		'/(?<!noscript\>)((<img.*?src=["|\'].*?["|\']).*?(\/?>))/i',
		'/(?<!noscript\>)(<img.*?)(src)=["|\']((?!data).*?)["|\']/i',
		'/(?<!noscript\>)(<img.*?)((srcset)=["|\'](.*?)["|\'])/i',
	);

	/**
	 * Replacements.
	 *
	 * @var array
	 */
	public $replacements = array(
		'$1<noscript>$1</noscript>',
		'$1src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" data-$2="$3"',
		'$1data-$3="$4"',
	);

	/**
	 * Filter for excluding specific image.
	 *
	 * @var string
	 */
	public $exclude_assets_filter = 'sgo_lazy_load_exclude_images';

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
		return str_replace( '<img', '<img class="lazyload"', $element );
	}
}
