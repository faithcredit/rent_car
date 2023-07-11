<?php

namespace SiteGround_Optimizer\Campaign_Service;

use SiteGround_Helper\Helper_Service;
use SiteGround_Emails\Email_Service;

/**
 * Email Campaign class.
 */
class Campaign_Service {

	/**
	 * The campaign steps count.
	 *
	 * @var int
	 */
	public $campaign_steps;

	/**
	 * The campaign service email.
	 *
	 * @var Email_Service
	 */
	public $campaign_service_email;

	/**
	 * The Constructor.
	 *
	 * @since 7.1.0
	 */
	public function __construct() {
		// Get the number of campaign steps we've made.
		$this->campaign_steps = (int) get_option( 'siteground_optimizer_campaign_steps', 0 );
		// Prepare the email service.
		$this->prepare_email_service();
	}

	/**
	 * Prepare the email service helper.
	 *
	 * @since  7.1.0
	 */
	public function prepare_email_service() {
		// Modify the next run based on campaign step.
		$next_run = 0 === $this->campaign_steps ? strtotime( '+1 day' ) : strtotime( '+1 week' );

		// Initiate the Email Service Class.
		$this->campaign_service_email = new Email_Service(
			'sgo_campaign_cron',
			'weekly',
			$next_run,
			array(
				'recipients_option' => 'admin_email',
				'subject'           => array( '\SiteGround_Optimizer\Campaign_Service\Campaign_Service', 'get_email_subject' ),
				'body_method'       => array( '\SiteGround_Optimizer\Campaign_Service\Campaign_Service', 'generate_message_body' ),
				'from_name'         => 'SiteGround Optimizer',
			)
		);
	}

	/**
	 * Make a request to the remote server in order to fetch the correct email subject.
	 *
	 * @since  7.1.0
	 *
	 * @return string The email subject.
	 */
	static function get_email_subject() {
		// The campaign steps.
		$campaign_steps = (int) get_option( 'siteground_optimizer_campaign_steps', 0 );

		// Bail if we reached the campaign limit.
		if ( 2 < $campaign_steps ) {
			return;
		}

		// Get the campaign content.
		$response = wp_remote_get( 'https://sgwpdemo.com/jsons/campaigns/sg-cachepress-subject.json' );

		// Bail if the request fails.
		if ( 200 !== wp_remote_retrieve_response_code( $response ) ) {
			return 'SiteGround Optimizer';
		}

		// Get the body of the response.
		$body = wp_remote_retrieve_body( $response );

		// Decode the json response.
		$json_args = json_decode( $body, true );

		// Get the locale.
		$locale = get_locale();

		// Set the proper args.
		return array_key_exists( $locale, $json_args[ $campaign_steps ] ) ? $json_args[ $campaign_steps ][ $locale ] : $json_args[ $campaign_steps ]['default'];
	}

	/**
	 * Bump the number of emails we've sent.
	 *
	 * @since  7.1.0
	 */
	public function bump_campaign_count() {
		update_option( 'siteground_optimizer_campaign_steps', $this->campaign_steps + 1 );
	}

	/**
	 * Checks for starting the email campaign.
	 *
	 * @since  7.1.0
	 *
	 * @return bool true/false if we should send emails.
	 */
	public function maybe_send_emails() {
		// Check if it is a non SiteGround user.
		if ( 1 === Helper_Service::is_siteground() ) {
			return false;
		}

		// Check if user has given email consent.
		if ( 0 === (int) get_option( 'siteground_email_consent', 0 ) ) {
			return false;
		}

		// Check if we are sending notifications emails via SiteGround Security.
		if ( wp_next_scheduled( 'sgs_email_cron' ) ) {
			return false;
		}

		// Check if we meet the required time period for sending emails.
		if ( $this->maybe_has_promo_emails() ) {
			return false;
		}

		// Check if we can get the users admin email.
		if ( empty( get_option( 'admin_email', array() ) ) ) {
			return false;
		}

		// Check if we completed the campaign.
		if ( 2 < (int) $this->campaign_steps ) {
			return false;
		}

		return true;
	}

	/**
	 * Calculate the period from last promo email from SG Security and check if we meet the required times.
	 *
	 * @since  7.1.0
	 *
	 * @return bool true/false.
	 */
	public function maybe_has_promo_emails() {
		// Get the timestamp of the last security email.
		$sg_security_last_mail = (int) get_option( 'sg_security_weekly_email_timestamp', 0 );

		// Bail if we do not have a timestamp.
		if ( 0 === $sg_security_last_mail ) {
			return false;
		}

		// Bail if the period of 14 days is less than the last email we've sent.
		if ( ( 2 * WEEK_IN_SECONDS ) < ( strtotime( 'now' ) - $sg_security_last_mail ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Update the timestamp when the cron event was last ran.
	 *
	 * @since 7.1.0
	 */
	public function update_last_cron_run_timestamp() {
		update_option( 'sgo_campaign_cron_timestamp', time() );
	}

	/**
	 * Generate the message body and return it to the constructor.
	 *
	 * @since  7.1.0
	 *
	 * @return mixed false on failure, HTML of the message body on success.
	 */
	static function generate_message_body() {
		// SG Settings page.
		$settings_page = admin_url( 'options-general.php?page=siteground_settings' );

		// The campaign steps.
		$campaign_steps = (int) get_option( 'siteground_optimizer_campaign_steps', 0 );

		// Get the campaign content.
		$response = wp_remote_get( 'https://sgwpdemo.com/jsons/campaigns/sg-cachepress-body.json' );

		// Bail if the request fails.
		if ( 200 !== wp_remote_retrieve_response_code( $response ) ) {
			return false;
		}

		// Get the body of the response.
		$body = wp_remote_retrieve_body( $response );

		// Decode the json response.
		$json_args = json_decode( $body, true );

		// Get the locale.
		$locale = get_locale();

		// Set the proper args.
		$args = array_key_exists( $locale, $json_args[ $campaign_steps ] ) ? $json_args[ $campaign_steps ][ $locale ] : $json_args[ $campaign_steps ]['default'];

		// Add any additional arguments.
		$args['unsubscribe_link'] = $settings_page;

		// Start the output biffering.
		ob_start();

		// Include the template file.
		include \SiteGround_Optimizer\DIR . '/templates/campaigns/campaign-template.php';

		// Pass the contents of the output buffer to a variable.
		$message_body = ob_get_contents();

		// Clean the output buffer and end the buffering.
		ob_end_clean();

		// Return the message body content as a string.
		return $message_body;
	}
}
