<?php
namespace SiteGround_Optimizer\Message_Service;

use SiteGround_Optimizer\Options\Options;

/**
 * Message service class.
 */
class Message_Service {
	/**
	 * React responce messages.
	 *
	 * @since 6.0.0
	 *
	 * @param bool   $result True for success, false for failure.
	 * @param string $option Option name.
	 * @param bool   $type   1 for enable, 0 for disable option.
	 */
	public static function get_response_message( $result, $option, $type = null ) {
		// Array containing message responses.
		$messages = array(
			'enable_cache'                                 => __( 'Dynamic Cache', 'sg-cachepress' ),
			'file_caching'                                 => __( 'File-Based Caching', 'sg-cachepress' ),
			'autoflush_cache'                              => __( 'Autoflush', 'sg-cachepress' ),
			'user_agent_header'                            => __( 'Browser-Specific Caching', 'sg-cachepress' ),
			'enable_memcached'                             => __( 'Memcached', 'sg-cachepress' ),
			'ssl'                                          => __( 'HTTPS', 'sg-cachepress' ),
			'fix_insecure_content'                         => __( 'Insecure Content Fix', 'sg-cachepress' ),
			'enable_gzip_compression'                      => __( 'GZIP Compression', 'sg-cachepress' ),
			'enable_browser_caching'                       => __( 'Browser Caching', 'sg-cachepress' ),
			'optimize_html'                                => __( 'HTML Minification', 'sg-cachepress' ),
			'optimize_javascript'                          => __( 'JavaScript Minification', 'sg-cachepress' ),
			'optimize_javascript_async'                    => __( 'Defer Render-blocking JS', 'sg-cachepress' ),
			'optimize_css'                                 => __( 'CSS Minification', 'sg-cachepress' ),
			'combine_css'                                  => __( 'CSS Combination', 'sg-cachepress' ),
			'combine_javascript'                           => __( 'JavaScript Files Combination', 'sg-cachepress' ),
			'optimize_web_fonts'                           => __( 'Web Fonts Optimization', 'sg-cachepress' ),
			'remove_query_strings'                         => __( 'Query Strings Removal', 'sg-cachepress' ),
			'disable_emojis'                               => __( 'Emoji Removal Filter', 'sg-cachepress' ),
			'backup_media'                                 => __( 'Backup Media', 'sg-cachepress' ),
			'lazyload_images'                              => __( 'Lazy Loading Images', 'sg-cachepress' ),
			'webp_support'                                 => __( 'WebP Generation for New Images', 'sg-cachepress' ),
			'resize_images'                                => __( 'Maximum Image Width is updated', 'sg-cachepress' ),
			'supercacher_permissions'                      => __( 'Can Config SuperCacher', 'sg-cachepress' ),
			'frontend_permissions'                         => __( 'Can Optimize Frontend', 'sg-cachepress' ),
			'images_permissions'                           => __( 'Can Optimize Images', 'sg-cachepress' ),
			'environment_permissions'                      => __( 'Can Optimize Environment', 'sg-cachepress' ),
			'heartbeat_control'                            => __( 'Heartbeat Optimization', 'sg-cachepress' ),
			'database_optimization'                        => __( 'Scheduled Database Maintenance', 'sg-cachepress' ),
			'database_optimization_updated'                => __( 'Scheduled Database Maintenance Updated', 'sg-cachepress' ),
			'dns_prefetch'                                 => __( 'DNS Prefetching', 'sg-cachepress' ),
			'preload_combined_css'                         => __( 'Preload Combined CSS', 'sg-cachepress' ),
			'enable_ssl_no_certificate'                    => __( 'Please, install an SSL certificate first!', 'sg-cachepress' ),
			'enable_memcache_empty_port'                   => __( 'SiteGround Optimizer was unable to connect to the Memcached server and it was disabled. Please, check your SiteGround control panel and turn it on if disabled.', 'sg-cachepress' ),
			'excluded_urls'                                => __( 'List of excluded urls is updated', 'sg-cachepress' ),
			'dns_prefetch_urls'                            => __( 'List of external URLs is updated', 'sg-cachepress' ),
			'minify_html_exclude'                          => __( 'List of excluded urls is updated', 'sg-cachepress' ),
			'fonts_preload_urls'                           => __( 'Preloaded fonts successfully modified', 'sg-cachepress' ),
			'post_types_exclude'                           => __( 'List of excluded post types is updated', 'sg-cachepress' ),
			'minify_css_exclude'                           => __( 'List of excluded styles is updated', 'sg-cachepress' ),
			'combine_css_exclude'                          => __( 'List of excluded styles is updated', 'sg-cachepress' ),
			'minify_javascript_exclude'                    => __( 'List of excluded scrpts is updated', 'sg-cachepress' ),
			'combine_javascript_exclude'                   => __( 'List of excluded scrpts is updated', 'sg-cachepress' ),
			'async_javascript_exclude'                     => __( 'List of excluded scrpts is updated', 'sg-cachepress' ),
			'excluded_lazy_load_classes'                   => __( 'List of excluded class names is updated', 'sg-cachepress' ),
			'excluded_lazy_load_media_types'               => __( 'List of excluded media types is updated', 'sg-cachepress' ),
			'image_compression_settings'                   => __( 'Compression settings updated', 'sg-cachepress' ),
			'siteground_optimizer_supercacher_permissions' => __( 'Can Config SuperCacher', 'sg-cachepress' ),
			'siteground_optimizer_frontend_permissions'    => __( 'Can Optimize Frontend', 'sg-cachepress' ),
			'siteground_optimizer_images_permissions'      => __( 'Can Optimize Images', 'sg-cachepress' ),
			'siteground_optimizer_environment_permissions' => __( 'Can Optimize Environment', 'sg-cachepress' ),
		);

		if ( is_null( $type ) ) {
			return sprintf( __( '%s', 'sg-cachepress' ), $messages[ $option ] );
		}

		if ( true === $result ) {
			if ( 1 === $type ) {
				return sprintf( __( '%s Enabled', 'sg-cachepress' ), $messages[ $option ] );
			}

			return sprintf( __( '%s Disabled', 'sg-cachepress' ), $messages[ $option ] );

		}

		if ( 1 === $type ) {
			return sprintf( __( 'Could not enable %s', 'sg-cachepress' ), $messages[ $option ] );
		}

		return sprintf( __( 'Could not disable %s', 'sg-cachepress' ), $messages[ $option ] );
	}
}
