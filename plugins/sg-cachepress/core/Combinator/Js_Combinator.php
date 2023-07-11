<?php
namespace SiteGround_Optimizer\Combinator;

use SiteGround_Optimizer\Options\Options;
use SiteGround_Optimizer\Front_End_Optimization\Front_End_Optimization;
use SiteGround_Helper\Helper_Service;
use SiteGround_Optimizer\Helper\Helper;

/**
 * SG JS_Combinator main plugin class
 */
class Js_Combinator extends Abstract_Combinator {
	/**
	 * Array containing all excluded inline content.
	 *
	 * @since 5.5.0
	 *
	 * @var array Array containing all excluded inline content.
	 */
	private $excluded_inline_content = array(
		'CDATA',
		'window.bookly',
		'var _iub',
		'#fld_',
		'.shop-filter',
		'var markersData',
		'setREVStartSize',
		'countUp',
		'a2a_config',
		'spg_current_url',
		'ctSetCookie',
		'stm_ajax_add_review',
		'stm_lms',
		'ywapo_textarea_',
		'var disqus_config',
		'map.write("map_id',
		'wpfh-print-action',
		'tdbMenuItem',
		'avadaFusionSliderVars',
		'tgpli',
		'tgpQueue',
		'gem_fix_fullwidth_position',
		'window.opener.location.href="',
		'var __CONFIG__',
		'var DTGS_NONCE_FRONTEND',
		'revslider_ajax_call_front',
		'#wpb_wcma_menu_',
		'UNCODE.initRow',
		'$(\'head\').append(\'<style',
		'$("head").append("<style',
		'#dfd-isotope-container-',
		'#dfd-horizontal-scroll-',
		'var form_container =',
		'var container = document.querySelectorAll( \'[data-view-breakpoint-pointer="',
		'var et_animation_data =',
		'_ASP.initialize',
		'theChampSiteUrl',
		'var _beeketing =',
		'glami(\'track',
		'pysWooSelectContentData',
		'init_scroll_shortcode',
		'_leartsInlineStyle',
		'div[rel=tipsy]\')',
		'g5core-social-networks',
		'woocs_array_of_get',
		'woocs_current_currency',
		'DokanWholesale',
		'woocommerce_price_slider_params',
		'amzn_assoc_ad_type',
		'var logview',
		'wc-livechat-script',
		'window.SIDX',
		'$(\'.room-check-form',
		'var css = \'#lordcros',
		'currentQuestion = {',
		'et_core_api_spam_recaptcha',
		'var __eae_open',
		'var WIL_SINGLE_LISTING',
		'tcb_post_lists',
		'DSListTracData',
		'tribe_js_config',
		'var tracking_id =',
		'var side_feed',
		'tdbSearchItem.blockUid = \'t',
		'_initLayerSlider',
		'var quickViewNonce',
		'getElementById("eeb-',
		'function reenableButton',
		'bs_ajax_paginate_',
		'subscribe-field',
		'_paq',
		'NSLPopupCenter',
		'fwduvpMainPlaylist',
		'fts_security',
		'post_id',
		'theChampLJAuthUrl',
		'ulp_content_id',
		'#iphorm-',
		'clicky_site_ids',
		'hc_rand_id',
		'mdf_current_page_url',
		'syntaxhighlighter',
		'Bibblio.initRelatedContent',
		'idcomments_acct',
		'ch_client',
		'"+nRemaining+"',
		'_mmunch',
		'woopack_config',
		'currency_data=',
		'contextly',
		'adthrive',
		'_atrk_opts',
		'wcct_info',
		'oneall_social_login_providers_',
		'var categories_',
		'gt_request_uri',
		'showUFC()',
		'wphc_data',
		'nonce',
		'galleries.gallery_',
		'Springbot.product_id',
		'w2dc_js_objects',
		'document.write',
		'_gaLt',
		'wcj_evt.prodID',
		'loadCSS',
		'edToolbar',
		'WP_Statistics_http',
		'cherry_ajax',
		'alsp_map_markers_attrs',
		'owl=$("#',
		'hbspt.forms.create',
		'lazyLoadOptions',
		'theChampRegRedirectionUrl',
		'elementid',
		'GoogleAnalyticsObject',
		'sc_online_t',
		'ult-carousel-',
		'jetpack_remote_comment',
		'betterads_screen_width',
		'WPCOM_sharing_counts',
		'RecaptchaLoad',
		'theChampTwitterRedirect',
		'omapi_localized',
		'rankMath = {',
		'wpp_params',
		'bs_deferred_loading_',
		'clicky_custom',
		'after_share_easyoptin',
		'RBL_ADD',
		'wpRestNonce',
		'#svc_carousel2_container_',
		'advads.move',
		'#fancy-',
		'pysWooProductData',
		'bannersnack_embed',
		'yithautocomplete',
		'function svc_center_',
		'zeen_',
		'_stq',
		'nfForms',
		'woof_really_curr_tax',
		'atatags-',
		'dfd-heading',
		'google_tag_params',
		'setAttribute( "id"',
		'Insticator',
		'penci_block_',
		'theChampFBCommentUrl',
		'arf_conditional_logic',
		'iworks_upprev',
		'ci_cap_',
		'"url":',
		'yith_wcevti_tickets',
		'omapi_data',
		'ANS_customer_id',
		'data-parallax-speed',
		'advadsGATracking.postContext',
		'_thriveCurrentPost',
		'adsbygoogle',
		'window.metrilo.ensure_cbuid',
		'theChampRedirectionUrl',
		'tabs.easyResponsiveTabs',
		'woocommerce_wishlist_add_to_wishlist_url',
		'gtag',
		'tdLocalCache',
		'esc_login_url',
		'styles: \' #custom-menu-',
		'thirstyFunctions.isThirstyLink',
		'penci_megamenu',
		'PHP.wp_p_id',
		'currentAjaxUrl',
		'avia_framework_globals',
		'function(c,h,i,m,p)',
		'geodir_event_call_calendar_',
		'searchwp_live_search_params',
		'quicklinkOptions',
		'heateorSsHorSharingShortUrl',
		'orig_request_uri',
		'uLogin.customInit',
		'algoliaAutocomplete',
		'ven_video_key',
		'doGTranslate',
		'LogHuman',
		'advads_has_ads',
		'dataLayer',
		'selection+pagelink',
		'bimber_front_microshare',
		'e.Newsletter2GoTrackingObject',
		'ESSB_CACHE_URL',
		'top.location,thispage',
		'dataTable({',
		'setAttribute("id"',
		'ct_checkjs_',
		'ic_window_resolution',
		'wpseo_map_init',
		'AfsAnalyticsObject',
		'wordpress_page_root',
		'ctSetCookie(\'ct_checkjs\'',
		'_stq',
		'wp-cumulus/tagcloud.swf?r=',
		'TribeEventsPro',
		'#wpnbio-show',
		'vtn_player_type',
		'tminusnow',
		'docTitle',
		'f._fbq',
		'TL_Const',
		'searchlocationHeader',
		'google_ad',
		'elementorFrontendConfig',
		'cedexisData',
		'i18n_no_matching_variations_text',
		'ShopifyBuy.UI.onReady(client)',
		'advads_tracking_ads',
		'metrilo.event',
		'location_data.push',
		'disqusIdentifier',
		'tdBlock',
		'_gaq.push',
		'gtm',
		'cartsguru_cart_token',
		'var inc_opt =',
		'ad_block_',
		'peepsotimedata',
		'e.setAttribute(\'unselectable',
		'function auxinNS(n)',
		'script_memory_usage',
		'var jetReviewsWidget',
		'var wpa_hidden_field',
		'snaptr',
		'function awbMapInit',
	);

	/**
	 * Excluded paths.
	 *
	 * @since 5.5.0
	 *
	 * @var array Array containing all paths that should be excluded.
	 */
	private $excluded_paths = array(
		'scripts.sirv.com',
		'cdn.ampproject.org',
		'app.getresponse.com',
		'googleadservices.com',
		'a.optmnstr.com',
		'adthrive.com',
		'www.uplaunch.com',
		'widget-prime.rafflecopter.com',
		'gist.github.com',
		'html5.js',
		'video.unrulymedia.com',
		'forms.aweber.com',
		'scripts.chitika.net/',
		'apps.shareaholic.com',
		'mailmunch.co',
		'stats.wp.com',
		'c.ad6media.fr',
		'code.tidio.co',
		's0.wp.com',
		'a.optmstr.com',
		'histats.com/js',
		'recaptcha/api.js',
		'mediavine.com',
		'nutrifox.com',
		'show_ads.js',
		'stats.wordpress.com',
		'contextual.media.net',
		'googlesyndication.com',
		'imagesrv.adition.com',
		'releases.flowplayer.org',
		'ws.amazon.com/widgets',
		'www.smava.de',
		's.gravatar.com',
		'verify.authorize.net',
		'/ads/',
		'files.bannersnack.com',
		'cdn.stickyadstv.com',
		'dsms0mj1bbhn4.cloudfront.net',
		'js.juicyads.com',
		'app.ecwid.com',
		'smarticon.geotrust.com',
		'jotform.com/',
		'embed.finanzcheck.de',
		'www.industriejobs.de',
		'js.hsforms.net',
		'form.jotformeu.com',
		'speakerdeck.com',
		'widget.rafflecopter.com',
		'amazon-adsystem.com',
		'ads.themoneytizer.com',
		'ads.investingchannel.com',
		'web.ventunotech.com',
		'intensedebate.com',
		'widget.reviewability.com',
		'js.gleam.io',
		'wprp.zemanta.com',
		'content.jwplatform.com',
		'adserver.reklamstore.com',
		'f.convertkit.com',
	);

	/**
	 * Move after the combined script.
	 *
	 * @since 5.5.0
	 *
	 * @var array $move_after Inline JS patterns to move after the combined JS file
	 */
	private $move_after_excludes = array(
		'#product-search-field-',
		'wpseo-address-wrapper',
		'ec:addProduct',
		'gform_ajax_frame_',
		'#owl-carousel-instagram-',
		'et_animation_data=',
		'window.FlowFlowOpts',
		'wlt_pop_distance_',
		'data.token',
		'it_logo_field_owl-box_',
		'#views-extra-css").text',
		'.flo-block-slideshow-',
		'jQuery(\'.td_uid_',
		'wmp_update',
		'wlt_star_',
		'cb_nombre',
		'test_run_nf_conditional_logic',
		'a3revWCDynamicGallery_',
		'smart_list_tip',
		'woof_is_mobile',
		'_wca',
		'gd-wgt-pagi-',
		'sinceID_',
		'dfads_ajax_load_ads',
		'data-rf-id=',
		'ip_common_function()',
		'h5ab-print-article',
		'.woocommerce-tabs-',
		'vc_prepareHoverBox',
		'callback:window.renderBadge',
		'vc-row-destroy-equal-heights-',
		'wpt_view_count',
		'electro-wc-product-gallery',
		'startclock',
		'dfd-icon-list-',
		'user_rating.prototype.eraseCookie',
		'var dateNow',
		'platform.stumbleupon.com',
		'berocket_aapf_time_to_fix_products_style',
		'$("#myCarousel',
		'fbq(\'trackCustom\'',
		'fusetag.setTargeting',
		'dfd-button-hover-in',
		'_wswebinarsystem_already_',
		'pa_woo_product_info',
		'ec:addImpression',
		'dpsp-networks-btns-wrapper',
		'gform_post_render',
		'mec_skin_',
		'WLTChangeState',
		'hit.uptrendsdata.com',
		'window.SLB',
		'#ut-background-video-ut-section',
		'gallery_product_',
		'GOTMLS_login_offset',
		'+window.comment_tab_width+',
		'td_live_css_uid',
		'#dfd-vcard-widget-',
		'jQuery(\'.videonextup',
		'CustomEvent.prototype=window.Event.prototype',
		'wpvl_paramReplace',
		'clear_better_facebook_comments',
		'act_css_tooltip',
		'sharrre',
		'window.vc_googleMapsPointer',
		'sharing_enabled_on_post_via_metabox',
		'mts_view_count',
		'tdAjaxCount',
		'data=\'api-key=ct-',
		'fbq(\'track\'',
		'penci_megamenu__',
		'jQuery(".slider-',
		'#sf-instagram-widget-',
		'us.templateDirectoryUri=',
		'function($){google_maps_',
		'("style#gsf-custom-css").append',
		'tvc_po=',
		'tie_postviews',
		'SFM_template',
		'test_run_nf_conditional',
		'scrapeazon',
		'current_url="',
		'.fat-gallery-item',
		'$(\'.fl-node-',
		'wp-temp-form-div',
		'map_fusion_map_',
		'wpp_params',
		'_taboola',
		'.ratingbox',
		'wp.apiFetch.nonceMiddleware',
		'initMap',
	);

	/**
	 * Regex parts.
	 *
	 * @since 5.5.0
	 *
	 * @var array Javascript tags regular expression
	 */
	public $regex_parts = array(
		'~', // The php quotes.
		'<script\b', // Opening script tag.
			'([^>]*)', // Tag attributes.
		'>', // Closing script tag.
			'(?:\/\*\s*<!\[CDATA\[\s*\*\/)?\s*', // Match CDATA.
			'([\s\S]*?)', // The script content, if any.
			'\s*(?:\/\*\s*\]\]>\s*\*\/)?', // Anything else until closing tag.
		'<\/script>', // Closing script tag.
		'~', // The php quotes.
		'ims', // The flags.
	);

	/**
	 * Src attribute regex parts.
	 *
	 * @since 5.5.0
	 *
	 * @var array Javascript src attributes regular expression.
	 */
	public $src_regex_parts = array(
		'~',
		'<script\s+',
		'([^>]+[\s\'"])?',
		'src\s*=\s*[\'"]\s*?',
		'([^\'"]+\.js(?:[^\'"]*)?)\s*?',
		'[\'"]',
		'([^>]+)?',
		'\/?>',
		'~',
		'Umsi',
	);
	/**
	 * Inline javascript regex parts.
	 *
	 * @since 5.5.0
	 *
	 * @var array Inline javascript regular expression.
	 */
	public $inline_regex_parts = array(
		'~',
		'<script\b',
		'(?<attrs>[^>]*)>',
		'(?:\/\*\s*<!\[CDATA\[\s*\*\/)?',
		'\s*(?<content>[\s\S]*?)\s*',
		'(?:\/\*\s*\]\]>\s*\*\/)?',
		'<\/script>',
		'~',
		'msi',
	);

	/**
	 * Array containing all scripts handles that should be excluded.
	 *
	 * @since 5.5.0
	 *
	 * @var array Array containing all scripts handles that should be excluded.
	 */
	private $combined_scripts_exclude_handles = array(
		'jquery',
		'jquery-core',
		'wc-authorize-net-cim',
		'sv-wc-payment-gateway-payment-form',
		'elementor-menus-frontend',
		'uncode-app',
		'uncode-plugins',
		'uncode-init',
		'lodash',
		'wp-api-fetch',
		'wp-i18n',
		'wp-polyfill',
		'wp-url',
		'wp-hooks',
		'wc-square',
	);

	/**
	 * Array containing all script handle regex' that should be excluded.
	 *
	 * @since 7.1.0
	 *
	 * @var   array Array containing all script handle regex' that should be excluded.
	 */
	private $combined_scripts_exclude_regex = array(
		'sv-wc-payment-gateway-payment-form-', // Authorize.NET payment gateway payment form script.
	);

	/**
	 * The singleton instance.
	 *
	 * @since 5.5.2
	 *
	 * @var The singleton instance.
	 */
	private static $instance;

	/**
	 * The constructor.
	 *
	 * @since 5.5.2
	 */
	public function __construct() {
		parent::__construct();
		self::$instance = $this;
	}

	/**
	 * Get the singleton instance.
	 *
	 * @since 5.5.2
	 *
	 * @return  The singleton instance.
	 */
	public static function get_instance() {
		if ( null == self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Combine scripts included in header and footer
	 *
	 * @since  5.5.0
	 *
	 * @param  string $html The page html.
	 *
	 * @return string       Modified html with combined scripts tag.
	 */
	public function run( $html ) {
		// Prepaare the localized scripts.
		$this->prepare_localized_scripts();
		// Prepaare the localized scripts.
		$this->prepare_excluded_scripts();
		// Hide comments from html.
		$html_without_comments = $this->hide_comments( $html );
		// Get scripts from the html.
		$scripts = $this->get_items( $html_without_comments );

		// Bail if there are no scripts to combine.
		if ( empty( $scripts ) ) {
			return $html;
		}
		// Get scripts content.
		$content = $this->parse( $scripts );

		// Bail if the scripts content is empty.
		if ( empty( $content ) ) {
			return $html;
		}

		return $this->get_new_html( $html, $content );
	}

	/**
	 * Prepare localized scripts.
	 *
	 * @since  5.5.0
	 */
	public function prepare_localized_scripts() {
		// Get all scripts.
		global $wp_scripts;

		$scripts = array();

		// Loop through all scripts in the queue and get all extra scripts.
		foreach ( array_unique( $wp_scripts->queue ) as $item ) {
			$scripts[] = wp_scripts()->print_extra_script( $item, false );
		}

		// Remove the empty items and set the localized scripts.
		$this->localized_scripts = array_filter( $scripts );
	}

	/**
	 * Prepare the excluded scripts
	 *
	 * @since  5.5.0
	 */
	public function prepare_excluded_scripts() {
		global $wp_scripts;

		// Get the excluded scripts list.
		$excluded_handles = apply_filters(
			'sgo_javascript_combine_exclude',
			array_merge(
				$this->combined_scripts_exclude_handles,
				get_option( 'siteground_optimizer_combine_javascript_exclude', array() )
			)
		);

		// Get handles of all registered scripts.
		$registered = array_keys( $wp_scripts->registered );
		$excluded   = array();

		// Remove excluded script handles using regex.
		foreach ( $this->combined_scripts_exclude_regex as $regex ) {
			$excluded_handles = array_merge( $excluded_handles, Helper::get_script_handle_regex( $regex, $registered ) );
		}

		// Loop through all excluded handles and get their src.
		foreach ( $excluded_handles as $handle ) {
			// Bail if handle is now found.
			if ( ! in_array( $handle, $registered ) ) {
				continue;
			}

			// Replace the site url and get the src.
			$excluded[] = trim( str_replace( Helper_Service::get_site_url(), '', strtok( wp_scripts()->registered[ $handle ]->src, '?' ) ), '/\\' );
		}

		// Set the excluded urls.
		$this->excluded_urls = $excluded;
	}

	/**
	 * Get combined js tag.
	 *
	 * @since  5.5.0
	 *
	 * @param  string $html         The original page content.
	 * @param  string $scripts_data Script data.
	 *
	 * @return string               Modified html.
	 */
	public function get_new_html( $html, $scripts_data ) {
		$move_after = '';
		// Build move after content and remove the original scripts.
		if ( ! empty( $this->move_after ) ) {
			foreach ( $this->move_after as $script ) {
				$move_after .= $script;
				$html        = str_replace( $script, '', $html );
			}
		}

		// Remove script tags.
		foreach ( $scripts_data as $script => $content ) {
			$html = str_replace( $script, '', $html );
			$new_content[] = $content;
		}

		$tag_data = $this->create_temp_file_and_get_url( $new_content, 'combined-js', 'js' );

		// Add defer attribute to combined script if the javascript async loaded is enabled.
		$atts = Options::is_enabled( 'siteground_optimizer_optimize_javascript_async' ) ? 'defer' : '';

		// Add combined script tag.
		// phpcs:ignore
		return str_replace( '</body>', '<script ' . $atts . ' src="' . $tag_data['url'] . '"></script>' . $move_after . '</body>', $html );
	}

	/**
	 * Parse ans prepare scripts for combination.
	 *
	 * @since  5.5.0
	 *
	 * @param  array $scripts Array of scripts data.
	 *
	 * @return array          Array of scripts content.
	 */
	public function parse( $scripts ) {
		foreach ( $scripts as $script ) {
			// Try to get the source of the script.
			preg_match(
				/**
				Build the regular expression.
				~<script\s+([^>]+[\s\'"])?src\s*=\s*[\'"]\s*?([^\'"]+\.js(?:\?[^\'"]*)?)\s*?[\'"]([^>]+)?\/?>~Umsi
				*/
				implode( '', $this->src_regex_parts ),
				$script[0], // The script tag.
				$matches // The matches.
			);

			if ( isset( $matches[2] ) ) {
				$content[ $script[0] ] = $this->process_script( $matches[2] );
			} else {
				$content[ $script[0] ] = $this->try_to_process_inline_script( $script[0] );
			}
		}

		return array_filter( $content );
	}

	/**
	 * Process script.
	 *
	 * @since  5.5.0
	 *
	 * @param  string $src Script source attribute.
	 *
	 * @return string      Script content.
	 */
	public function process_script( $src ) {
		$is_external = false;

		if (
			@strpos( Helper_Service::get_home_url(), parse_url( $src, PHP_URL_HOST ) ) === false &&
			! @strpos( $src, 'wp-includes' )
		) {
			$is_external = true;
		}

		if ( $this->is_excluded( $src, $is_external ) ) {
			return;
		}

		return true === $is_external ? $this->get_external_file_content( $src, 'js', 'externals' ) : $this->get_content( $src );
	}

	/**
	 * Check if the script is inline and process it.
	 *
	 * @since  5.5.0
	 *
	 * @param  string $script The script tag.
	 *
	 * @return string         Script content.
	 */
	public function try_to_process_inline_script( $script ) {
		// Check if all inline scripts are excluded from combination via filter.
		if ( true === apply_filters( 'sgo_javascript_combine_exclude_all_inline', false ) ) {
			return;
		}

		// Check if all inline module scripts are excluded from combination via filter.
		if (
			preg_match( '~script type=["\']module["\']~', $script ) &&
			true === apply_filters( 'sgo_javascript_combine_exclude_all_inline_modules', false )
		) {
			return;
		}

		preg_match(
			/**
			Build the regular expression.
			~<script\b(?<attrs>[^>]*)>(?:\/\*\s*<!\[CDATA\[\s*\*\/)?\s*(?<content>[\s\S]*?)\s*(?:\/\*\s*\]\]>\s*\*\/)?<\/script>~msi
			*/
			implode( '', $this->inline_regex_parts ),
			$script, // The script tag.
			$matches // The matches.
		);

		if ( $this->is_excluded_inline_content( $matches ) ) {
			return;
		}

		return $matches['content'];
	}

	/**
	 * Check if the inline content is excluded.
	 *
	 * @since  5.5.0
	 *
	 * @param  array $data Script data.
	 *
	 * @return boolean     True of the script is excluded, false otherwise.
	 */
	public function is_excluded_inline_content( $data ) {
		// Check for catastrophic backtracking.
		// More info: https://www.regular-expressions.info/catastrophic.html
		if ( PREG_BACKTRACK_LIMIT_ERROR == preg_last_error() ) {
			return true;
		}

		// Bail if the script doesn't a src attribute.
		if ( false !== @strpos( $data['attrs'], 'src=' ) ) {
			return true;
		}

		// Bail if the type is not js/es.
		if (
			@strpos( $data['attrs'], 'type' ) !== false &&
			! preg_match( '/type\s*=\s*["\']?(?:text|application)\/(?:(?:x\-)?javascript|ecmascript)["\']?/i', $data['attrs'] )
		) {
			return true;
		}

		// Bail if it's localized script.
		if ( in_array( $data['content'], $this->localized_scripts, true ) ) {
			return true;
		}

		// Get excluded inline content.
		$excluded_inline_content = apply_filters( 'sgo_javascript_combine_excluded_inline_content', $this->excluded_inline_content );

		// Do not combine excluded content.
		foreach ( $excluded_inline_content as $excluded_content ) {
			if ( false !== @strpos( $data['content'], $excluded_content ) ) {
				return true;
			}
		}

		// Get excluded inline content.
		$move_after_scripts = apply_filters( 'sgo_javascript_combine_exclude_move_after', $this->move_after_excludes );

		foreach ( $move_after_scripts as $move_after_script ) {
			if ( false !== @strpos( $data['content'], $move_after_script ) ) {
				$this->move_after[] = $data[0];
				return true;
			}
		}

		return false;
	}

	/**
	 * Check if the script is excluded
	 *
	 * @since  5.5.0
	 *
	 * @param  string  $src      Script source.
	 * @param  boolean $external Whether the script is external.
	 *
	 * @return boolean     True if the script is excluded, false otherwise.
	 */
	public function is_excluded( $src, $external = false ) {
		// Check if the script is external.
		if ( true === $external ) {
			$excluded_paths = apply_filters( 'sgo_javascript_combine_excluded_external_paths', $this->excluded_paths );
			foreach ( $excluded_paths as $path ) {
				if ( false !== @strpos( $src, $path ) ) {
					return true;
				}
			}
		} else {
			$src = Front_End_Optimization::remove_query_strings( $src );

			if ( in_array( str_replace( trailingslashit( Helper_Service::get_site_url() ), '', $src ), $this->excluded_urls ) ) {
				return true;
			}

			return false;
		}
	}

	/**
	 * Replace all url to full urls.
	 *
	 * @since  5.5.0
	 *
	 * @param  string $contents Array with link to scripts and script content.
	 *
	 * @return string           Imploded content.
	 */
	public function get_content_with_replacements( $contents ) {
		$new_content = array();

		foreach ( $contents as $url => $content ) {
			$new_content[] = preg_replace(
				'~^(\/\/|\/\*)(#|@)\s(sourceURL|sourceMappingURL)=(.*)(\*\/)?$~m',
				'',
				$content
			);
		}

		return implode( ";\n", $new_content );
	}
}
