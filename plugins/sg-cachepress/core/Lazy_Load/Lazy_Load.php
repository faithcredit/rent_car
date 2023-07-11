<?php
namespace SiteGround_Optimizer\Lazy_Load;

/**
 * SG Lazy_Load_Images main plugin class
 */
class Lazy_Load {
	/**
	 * Local variables
	 *
	 * @var mixed
	 */
	public $lazyload_iframes;
	public $lazyload_videos;
	public $lazyload_images;

	/**
	 * Children classes.
	 *
	 * @var array
	 */
	public $children = array(
		'lazyload_videos'  => array(
			array(
				'option' => 'videos',
				'hook'   => 'the_content',
			),
		),
		'lazyload_iframes' => array(
			array(
				'option' => 'iframes',
				'hook'   => 'the_content',

			),
		),
		'lazyload_images'  => array(
			array(
				'option' => 'images',
				'hook'   => 'the_content',
			),
			array(
				'option' => 'textwidgets',
				'hook'   => 'widget_text',
			),
			array(
				'option' => 'textwidgets',
				'hook'   => 'widget_block_content',
			),
			array(
				'option' => 'thumbnails',
				'hook'   => 'post_thumbnail_html',
			),
			array(
				'option' => 'gravatars',
				'hook'   => 'get_avatar',
			),
			array(
				'option' => 'woocommerce',
				'hook'   => 'woocommerce_product_get_image',
			),
			array(
				'option' => 'woocommerce',
				'hook'   => 'woocommerce_single_product_image_thumbnail_html',
			),
		),
	);

	/**
	 * The constructor.
	 *
	 * @since 5.0.0
	 */
	public function __construct() {
		$this->lazyload_iframes = new Lazy_Load_Iframes();
		$this->lazyload_videos  = new Lazy_Load_Videos();
		$this->lazyload_images  = new Lazy_Load_Images();
	}

	/**
	 * Load the scripts.
	 *
	 * @since  5.0.0
	 */
	public function load_scripts() {
		// Load the main script.
		wp_enqueue_script(
			'siteground-optimizer-lazy-sizes-js',
			\SiteGround_Optimizer\URL . '/assets/js/lazysizes.min.js',
			array(), // Dependencies.
			\SiteGround_Optimizer\VERSION,
			true
		);
	}
}
