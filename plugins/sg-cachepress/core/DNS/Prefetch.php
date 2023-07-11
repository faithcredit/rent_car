<?php
namespace SiteGround_Optimizer\DNS;

/**
 * DNS Prefetching class.
 */
class Prefetch {

	/**
	 * The singleton instance.
	 *
	 * @since 5.6.0
	 *
	 * @var The singleton instance.
	 */
	private static $instance;

	/**
	 * The constructor.
	 */
	public function __construct() {
		self::$instance = $this;
	}

	/**
	 * Get the singleton instance.
	 *
	 * @since 5.6.0
	 *
	 * @return \DNS prefetch The singleton instance.
	 */
	public static function get_instance() {
		if ( null == self::$instance ) {
			static::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Run the dns-prefetch link insertion.
	 *
	 * @since  5.6.0
	 *
	 * @param  string $html The HTML Content.
	 */
	public function run( $html ) {
		// Check if there are any urls inserted by the user.
		$urls = get_option( 'siteground_optimizer_dns_prefetch_urls', false );

		// Return, if no url's are set by the user.
		if ( empty( $urls ) ) {
			return $html;
		}

		$new_html = '';

		// Loop trough urls and prepare them for insertion.
		foreach ( $urls as $url ) {
			// Replace the protocol with //.
			$url_without_protocol = preg_replace( '~(?:(?:https?:)?(?:\/\/)(?:www\.|(?!www)))?((?:.*?)\.(?:.*))~', '//$1', $url );

			// Remove the protocol if for some reason url has passed with it.
			$new_html .= '<link rel="dns-prefetch" href="' . $url_without_protocol . '" data-set-by="SiteGround Optimizer"/>';
		}

		// Insert the link in the head section.
		return str_replace( '</head>', $new_html . '</head>', $html );
	}
}
