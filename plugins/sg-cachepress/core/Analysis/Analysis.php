<?php
namespace SiteGround_Optimizer\Analysis;

use SiteGround_Optimizer\Options\Options;
use SiteGround_Helper\Helper_Service;

/**
 * SG Analysis main plugin class
 */
class Analysis {
	/**
	 * The number of speed tests we store in the database.
	 *
	 * @var integer
	 */
	private $speed_test_count = 10;

	/**
	 * The group keys, added to the audit array for purposes of the react app.
	 *
	 * @var array
	 */
	private $group_keys = array(
		'coding_optimizations' => array(
			'first-contentful-paint',
			'resource-summary',
			'largest-contentful-paint',
			'total-byte-weight',
			'uses-rel-preconnect',
			'dom-size',
			'long-tasks',
			'mainthread-work-breakdown',
			'uses-passive-event-listeners',
			'preload-lcp-image',
			'third-party-summary',
			'time-to-first-byte',
		),
		'css_optimizations' => array(
			'render-blocking-resources',
			'largest-contentful-paint-element',
			'total-blocking-time',
			'max-potential-fid',
			'interactive',
			'first-contentful-paint',
			'critical-request-chains',
			'unminified-css',
			'unused-css-rules',
			'uses-rel-preload',
			'font-display',
		),
		'javascript_optimizations' => array(
			'render-blocking-resources',
			'unminified-javascript',
			'bootup-time',
			'unused-javascript',
			'no-document-write',
			'user-timings',
			'legacy-javascript',
			'duplicated-javascript',
		),
		'media_optimizations' => array(
			'uses-responsive-images',
			'offscreen-images',
			'uses-optimized-images',
			'uses-webp-images',
			'efficient-animated-content',
			'non-composited-animations',
			'third-party-facades',
		),
		'general_optimizations' => array(
			'server-response-time',
			'uses-text-compression',
			'redirects',
			'uses-long-cache-ttl',
		),
	);

	/**
	 * Array containing audits that we need to check for additional actions.
	 *
	 * @var array
	 */
	private $additional_check = array(
		'render-blocking-resources',
	);

	/**
	 * Disable specific optimizations for a blog.
	 *
	 * @since  5.4.0
	 *
	 * @param  array $result Speed test results.
	 */
	public function process_analysis( $result ) {
		// Bail if the are no results.
		if ( empty( $result ) ) {
			wp_send_json_error();
		}

		$optimizations = $this->get_available_optimizations();
		$items         = array(
			'data'                     => array(),
			'timeStamp'                => time(),
			'optimizations'            => array(),
		);
		$options = array();

		foreach ( $result['lighthouseResult']['categories'] as $group ) {
			foreach ( $group['auditRefs'] as $ref ) {

				if ( 'server-response-time' === $ref['id'] ) {
					$items['scores']['ttfb'] = round( $result['lighthouseResult']['audits'][ $ref['id'] ]['numericValue'] );
				}

				if ( 'first-contentful-paint' === $ref['id'] ) {
					$items['scores']['fcp'] = $result['lighthouseResult']['audits'][ $ref['id'] ]['numericValue'];
				}

				if ( empty( $ref['group'] ) ) {
					continue;
				}

				// Do not show render blocking message if we have top score.
				if (
					'render-blocking-resources' === $ref['id'] &&
					1.00 === $result['lighthouseResult']['categories']['performance']['score']
				) {
					continue;
				}

				$audit = $result['lighthouseResult']['audits'][ $ref['id'] ];
				$optimization_group = 'other';
				if ( in_array( $ref['group'], array( 'load-opportunities', 'diagnostics' ) ) ) {

					if ( array_key_exists( $audit['id'], $optimizations ) ) {
						if ( ! empty( $optimizations[ $audit['id'] ] ) && ! Options::is_enabled( $audit['id'] ) ) {
							$items['optimizations'] = array_merge( $items['optimizations'], $optimizations[ $audit['id'] ] );
						}
					}

					$optimization_group = 'other';

					foreach ( $this->group_keys as $key => $value ) {
						if ( in_array( $audit['id'], $value ) ) {
							$optimization_group = $key;
						}
					}

					switch ( $audit['scoreDisplayMode'] ) {
						case 'manual':
						case 'notApplicable':
							$items['data']['passed']['data'][ $optimization_group ][] = $audit;
							break;
						case 'numeric':
						case 'binary':
						default:
							if ( $audit['score'] >= 0.9 ) {
								$items['data']['passed']['data'][ $optimization_group ][] = $audit;
							} else {
								$items['data'][ $ref['group'] ]['info'] = $result['lighthouseResult']['categoryGroups'][ $ref['group'] ];
								$items['data'][ $ref['group'] ]['data'][ $optimization_group ][] = $audit;
							}
							break;
					}
				} else {
					$items['data'][ $ref['group'] ]['info'] = $result['lighthouseResult']['categoryGroups'][ $ref['group'] ];
					// The optimization group may have to be left empty.
					$items['data'][ $ref['group'] ]['data'][ $optimization_group ][] = $audit;
				}
			}
		}


		unset( $items['data']['budgets'] );
		unset( $items['data']['diagnostics'] );
		unset( $items['data']['metrics'] );
		$items['scores']['score'] = round( $result['lighthouseResult']['categories']['performance']['score'] * 100 );
		// Check if we need to group render blocking resources.
		if ( ! empty( $items['data']['load-opportunities']['data'] ) &&
			array_key_exists( 'javascript_optimizations', $items['data']['load-opportunities']['data'] ) ) {
			$items = $this->group_render_blocking_assets( $items );
		}

		if ( ! empty( $items['passed'] ) ) {
			$items['passed']['info'] = array(
				'title'       => __( 'The Following Areas of Your Site Are Well Optimized:', 'sg-cachepress' ),
				'id'          => 'passed',
			);
		}

		if ( ! empty( $items['load-opportunities'] ) ) {
			$items['load-opportunities']['info'] = array(
				'title'       => __( 'Opportunities to Optimize', 'sg-cachepress' ),
				'id'          => 'opportunities',
			);
		}

		$items['scores'] = $this->get_messages( $items['scores'] );

		// Return the response and add additional info if necessary.
		return $items;
	}

	/**
	 * Loop items in order to move the necesary render-blocking information group.
	 *
	 * @since  5.8.0
	 *
	 * @param  array $items The array containing the speed test items.
	 *
	 * @return array $items The array contaning the speed test items, but with proper cattegory set.
	 */
	public function group_render_blocking_assets( $items ) {
		// Loop the oportunities results. A check for passed should also be added and change the load-opportunities to a variable.
		foreach ( $items['data']['load-opportunities']['data']['javascript_optimizations'] as $item => $prop ) {
			// Check if the item is in the additional check list.
			if ( ! in_array( $prop['id'], $this->additional_check ) && empty( $prop['details']['items'] ) ) {
				continue;
			}

			$resources = array(
				'css' => array(),
				'js' => array(),
			);

			foreach ( $prop['details']['items'] as $key => $item ) {

				preg_match( '~(?:\.|\/\/)(css|js|fonts)~', $item['url'], $matches );

				if ( empty( $matches[1] ) ) {
					continue;
				}

				// Check from which array we must remove.
				switch ( $matches[1] ) {
					case 'css':
					case 'fonts':
						$resources['css'][] = $item;
						break;
					case 'js':
						$resources['js'][] = $item;
						break;
				}
			}


			if ( ! empty( $resources['css'] ) ) {
				if ( ! isset( $items['data']['load-opportunities']['data']['css_optimizations'] ) ) {
					$items['data']['load-opportunities']['data']['css_optimizations'] = array();
				}
				$css_props = $prop;

				$css_props['details']['items'] = $resources['css'];

				$items['data']['load-opportunities']['data']['css_optimizations'][] = $css_props;
			}

			if ( ! empty( $resources['js'] ) ) {
				$items['data']['load-opportunities']['data']['javascript_optimizations'][0]['details']['items'] = $resources['js'];
			} else {
				unset( $items['data']['load-opportunities']['data']['javascript_optimizations'][0] );
			}

			if ( empty( $items['data']['load-opportunities']['data']['javascript_optimizations'] ) ) {
				unset( $items['data']['load-opportunities']['data']['javascript_optimizations'] );
			} else {
				$items['data']['load-opportunities']['data']['javascript_optimizations'] = array_values( $items['data']['load-opportunities']['data']['javascript_optimizations'] );
			}
		}

		return $items;
	}

	/**
	 * Save the test results in the database.
	 *
	 * @since  5.8.0
	 *
	 * @param  array $items The array containing all speed results.
	 */
	public function save_test_result( $items ) {
		// Get the results from previous stored tests.
		$previous_tests = $this->get_test_results();

		// Check if we need to delete a test in order to match the test count limit.
		if ( ! empty( $previous_tests ) && $this->speed_test_count <= count( $previous_tests ) ) {
			// Get the oldest test and delete it.
			$oldest_test = array_pop( $previous_tests );
			// Delete the oldest speed test stored in the database.
			delete_option( $oldest_test['option_name'] );
		}

		// Add the speed test to the database. Set Autoload to 'no', since we only need these test when comparing.
		add_option( 'sgo_speed_test_' . time(), $items, '', false );
	}

	/**
	 * Get the available speed results from the database.
	 *
	 * @since  5.8.0
	 *
	 * @return array An Array containing all of the test's data.
	 */
	public function get_test_results() {
		global $wpdb;

		// Get the tests result stored in the db.
		$result = $wpdb->get_results(
			'SELECT * FROM ' . $wpdb->options . " WHERE option_name LIKE 'sgo_speed_test_%' ORDER BY option_name DESC",
			ARRAY_A
		);

		return $result;
	}

	/**
	 * Get the previous test results.
	 *
	 * @since  5.8.0
	 *
	 * @return array $data The results array.
	 */
	public function rest_get_test_results() {
		// Get the results from the database.
		$results = $this->get_test_results();
		$data = array();

		// Bail if no results are present.
		if ( empty( $results ) ) {
			return $data;
		}

		// Loop the results and make the arrays consistent.
		foreach ( $results as $result ) {
			$test_data = get_option( $result['option_name'] );

			if ( $test_data['scores']['score']['score'] < 2 ) {
				$test_data['scores']['score']['score'] = $test_data['scores']['score']['score'] * 100;
			}

			// Show human readable timestamp with local timezone.
			$date = new \DateTime( '@'.$test_data['timeStamp'] );
			$date->setTimezone( new \DateTimeZone( \wp_timezone_string() ));
			$test_data['human_readable_timestamp'] = $date->format( 'd M Y, G:i e' );

			$data[] = array(
				'option_name' => $result['option_name'],
				'result'      => $test_data,
			);
		}

		$previous_test = get_option( 'sgo_pre_migration_speed_test', false );

		// Return the tests if a test from the previous host doesn't exists.
		if ( false === $previous_test ) {
			return $data;
		}

		$data[] = array(
			'option_name'   => 'previous_test',
			'result'        => $previous_test,
			'previous_test' => 1,
		);

		return $data;
	}

	/**
	 * Get optimization messages.
	 *
	 * @since  5.4.0
	 *
	 * @return array Custom analysis messages.
	 */
	public function get_available_optimizations() {
		$optimizations = array(
			'render-blocking-resources' => array(
				'optimize_javascript_async' => array(
					'title' => __( 'Defer Render-blocking JS', 'sg-cachepress' ),
					'message' => __( 'Defer loading of render-blocking JavaScript files for faster initial site load.', 'sg-cachepress' ),
				),
			),
			'modern-image-formats' => array(
				'webp_support' => array(
					'title' => __( 'Use WebP Images', 'sg-cachepress' ),
					'message' => __( 'WebP is a next generation image format supported by modern browsers which greatly reduces the size of standard image formats while keeping the same quality. Almost all current browsers work with WebP.', 'sg-cachepress' ),
				),
			),
			'offscreen-images' => array(
				'lazyload_images' => array(
					'title' => __( 'Lazy Load Media', 'sg-cachepress' ),
					'message' => __( 'Load images only when they are visible in the browser.', 'sg-cachepress' ),
				),
			),
			'unused-css-rules' => array(
				'optimize_css' => array(
					'title' => __( 'Combine CSS Files', 'sg-cachepress' ),
					'message' => __( 'Combine multiple CSS files into one to lower the number of requests your site generates.', 'sg-cachepress' ),
				),
			),
			'time-to-first-byte' => array(
				'enable_cache' => array(
					'title' => __( 'Dynamic Caching', 'sg-cachepress' ),
					'message' => __( 'Store your content in the server’s memory for a faster access with this full-page caching solution powered by NGINX.', 'sg-cachepress' ),
				),
			),
			'uses-rel-preload' => array(
				'optimize_javascript_async' => array(
					'title' => __( 'Defer Render-blocking JS', 'sg-cachepress' ),
					'message' => __( 'Defer loading of render-blocking JavaScript files for faster initial site load.', 'sg-cachepress' ),
				),
				'optimize_web_fonts' => array(
					'title' => __( 'Web Fonts Optimization', 'sg-cachepress' ),
					'message' => __( 'With this optimization we\'re changing the default way to load Google fonts in order to save HTTP requests. In addition to that, all other fonts that your website uses will be properly preloaded so browsers take the least possible amount of time to cache and render them.', 'sg-cachepress' ),
				),
			),
			'total-byte-weight' => array(
				'optimize_html' => array(
					'title' => __( 'Minify the HTML Output', 'sg-cachepress' ),
					'message' => __( 'Removes unnecessary characters from your HTML output saving data and improving your site speed.', 'sg-cachepress' ),
				),
				'optimize_javascript' => array(
					'title' => __( 'Minify JavaScript Files', 'sg-cachepress' ),
					'message' => __( 'Minify your JavaScript files in order to reduce their size and reduce the number of requests to the server.', 'sg-cachepress' ),
				),
				'combine_css' => array(
					'title' => __( 'Combine CSS Files', 'sg-cachepress' ),
					'message' => __( 'Combine multiple CSS files into one to lower the number of requests your site generates.', 'sg-cachepress' ),
				),
			),
			'server-response-time' => array(
				'enable_cache' => array(
					'title' => __( 'Dynamic Caching', 'sg-cachepress' ),
					'message' => __( 'Store your content in the server’s memory for a faster access with this full-page caching solution powered by NGINX.', 'sg-cachepress' ),
				),
			),
		);

		return $optimizations;
	}

	/**
	 * Return predefined response messages.
	 *
	 * @since  5.4.0
	 *
	 * @param  int $scores The score returned from Google.
	 *
	 * @return array      Messages.
	 */
	public function get_messages( $scores ) {
		$data = array();
		$descriptions = array(
			'ttfb'  => __( 'Time to First Byte identifies the time for which your server sends a response.', 'sg-cachepress' ),
			'score' => __( 'Summarizes the page\'s performance.', 'sg-cachpress' ),
			'fcp'   => __( 'Speed Index shows how quickly the contents of a page are visibly populated.', 'sg-cachepress' ),
		);

		$conditions = array(
			'fcp'   => array(
				'low' => 2000,
				'medium' => 4000,
				'colors' => array(
					'low' => array(
						'class_name'       => 'placeholder-without-svg placeholder-top',
						'class_name_table' => 'success',
					),
					'medium' => array(
						'class_name'       => 'placeholder-without-svg placeholder-meduim',
						'class_name_table' => 'warning',
					),
					'high' => array(
						'class_name'       => 'placeholder-without-svg placeholder-low',
						'class_name_table' => 'error',
					),
				),
			),
			'ttfb'  => array(
				'low' => 100,
				'medium' => 600,
				'colors' => array(
					'low' => array(
						'class_name'       => 'placeholder-without-svg placeholder-top',
						'class_name_table' => 'success',
					),
					'medium' => array(
						'class_name'       => 'placeholder-without-svg placeholder-meduim',
						'class_name_table' => 'warning',
					),
					'high' => array(
						'class_name'       => 'placeholder-without-svg placeholder-low',
						'class_name_table' => 'error',
					),
				),
			),
			'score' => array(
				'low' => 49,
				'medium' => 90,
				'colors' => array(
					'low' => array(
						'class_name'       => 'placeholder-without-svg placeholder-low',
						'class_name_table' => 'error',
					),
					'medium' => array(
						'class_name'       => 'placeholder-without-svg placeholder-meduim',
						'class_name_table' => 'warning',
					),
					'high' => array(
						'class_name'       => 'placeholder-without-svg placeholder-top',
						'class_name_table' => 'success',
					),
				),
			),
		);

		foreach ( $scores as $key => $score ) {
			if ( $score < $conditions[ $key ]['medium'] && $score > $conditions[ $key ]['low'] ) {
				$data[ $key ] = array_merge( $conditions[ $key ]['colors']['medium'], array( 'score' => $score ) );
				continue;
			}

			if ( $score < $conditions[ $key ]['low'] ) {
				$data[ $key ] = array_merge( $conditions[ $key ]['colors']['low'], array( 'score' => $score ) );
				continue;
			}

			$data[ $key ] = array_merge( $conditions[ $key ]['colors']['high'], array( 'score' => $score ) );
		}

		$data['descriptions'] = $descriptions;

		return $data;
	}

	/**
	 * Get the page speed results from Google API.
	 *
	 * @since  5.4.0
	 *
	 * @param  string  $url     The URL to test.
	 * @param  string  $device  The device type.
	 * @param  integer $counter Added to retry 3 times if the request fails.
	 *
	 * @return array            The analisys result.
	 */
	public function run_analysis( $url, $device = 'desktop', $counter = 0 ) {
		// Try to get the analysis 3 times and then bail.
		if ( 3 === $counter ) {
			return false;
		}

		$full_url = home_url( '/' ) . trim( $url, '/' );
		// Hit the url, so it can be cached, when Google Api make the request.
		if ( 0 === $counter ) {
			wp_remote_get( $full_url );
		}

		// Make the request.
		$response = wp_remote_get(
			'https://www.googleapis.com/pagespeedonline/v5/runPagespeed?url=' . $full_url . '&locale=' . get_locale() . '&strategy=' . $device,
			array(
				'timeout' => 15,
			)
		);

		// Make another request if the previous fail.
		if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {

			$counter++;
			return $this->run_analysis( $url, $device, $counter );
		}

		// Decode the response.
		$response = json_decode( $response['body'], true );

		// Return the analysis.
		$items = $this->process_analysis( $response );
		$items['device'] = $device;

		// Save the test results in the database.
		$this->save_test_result( $items );

		return $items;
	}

	/**
	 * Get the pre-migration speed results.
	 *
	 * @since  5.7.13
	 *
	 * @return bool/string False if the file is non existing/The array containing the speed-test results.
	 */
	public function check_for_premigration_test() {
		global $wp_filesystem;
		// Bail if the file does not exist.
		if ( ! file_exists( Helper_Service::get_uploads_dir() . '/pagespeed_results.json' ) ) {
			return false;
		}
		// Return the string containing the pre-migration speed test.
		$pre_migration_result = json_decode( $wp_filesystem->get_contents( Helper_Service::get_uploads_dir() . '/pagespeed_results.json' ), true );

		// Bail if json cannot be decoded or if the encoded data is deeper than the nesting limit.
		if ( false === (bool) $pre_migration_result ) {
			// Delete the file so we don't try to decode it upon future activation.
			$wp_filesystem->delete( Helper_Service::get_uploads_dir() . '/pagespeed_results.json' );
			return false;
		}

		$data = array_merge( $this->process_analysis( $pre_migration_result ), array( 'device' => 'desktop' ) );

		// Save the processed analysis in the database.
		add_option( 'sgo_pre_migration_speed_test', $data, '', false );

		// Remove the file from the folder.
		$wp_filesystem->delete( Helper_Service::get_uploads_dir() . '/pagespeed_results.json' );
	}

}
