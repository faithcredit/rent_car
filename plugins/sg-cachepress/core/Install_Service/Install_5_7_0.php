<?php
namespace SiteGround_Optimizer\Install_Service;
use SiteGround_Optimizer\Options\Options;

class Install_5_7_0 extends Install {

	/**
	 * The default install version. Overridden by the installation packages.
	 *
	 * @since 5.5.0
	 *
	 * @access protected
	 *
	 * @var string $version The install version.
	 */
	protected static $version = '5.7.0';

	/**
	 * Run the install procedure.
	 *
	 * @since 5.5.0
	 */
	public function install() {

		update_option( 'siteground_optimizer_whats_new', array(
			array(
				'type'         => 'default',
				'title'        => __( 'Web Fonts Optimization', 'sg-cachepress' ),
				'text'         => __( 'With this optimization we are changing the default way to load Google fonts in order to save HTTP requests. In addition to that, all other fonts that your website uses will be properly preloaded so browsers take the least possible amount of time to cache and render them.', 'sg-cachepress' ),
				'icon'         => 'presentational-fonts-optimization',
				'icon_color'   => 'salmon',
				'optimization' => 'optimize_web_fonts',
				'button' => array(
					'text'  => __( 'Enable Now', 'sg-cachepress' ),
					'color' => 'primary',
					'link'  => 'frontend',
				),
			),
		) );

		if ( Options::is_enabled( 'siteground_optimizer_combine_google_fonts' ) ) {
			update_option( 'siteground_optimizer_optimize_web_fonts', 1 );
		}

		$this->populate_webfonts();
	}

	/**
	 * Add the font that shoudl be preloaded.
	 *
	 * @since  5.7.0
	 */
	public function populate_webfonts() {
		// Get the insigths from google api.
		$response = wp_remote_get(
			'https://www.googleapis.com/pagespeedonline/v5/runPagespeed?url=' . site_url( '/' ) . '&category=performance',
			array( 'timeout' => 15 )
		);

		// Bail if the response code is not 200.
		if ( 200 !== wp_remote_retrieve_response_code( $response ) ) {
			return;
		}

		// Get the body.
		$body = wp_remote_retrieve_body( $response );

		// Convert the json to array.
		$data = json_decode( $body, true );

		// Bail if the fonts are ok.
		if ( empty( $data['lighthouseResult']['audits']['font-display']['details']['items'] ) ) {
			return;
		}

		$fonts = get_option( 'siteground_optimizer_fonts_preload_urls', array() );

		foreach ( $data['lighthouseResult']['audits']['font-display']['details']['items'] as $item ) {
			$fonts[] = $item['url'];
		}

		update_option( 'siteground_optimizer_fonts_preload_urls', $fonts );
	}
}
