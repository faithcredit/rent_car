<?php
namespace SiteGround_Optimizer\Site_Tools_Client;

/**
 * Site Tools Client class.
 */
class Site_Tools_Client {
    /**
     * SiteTools Client Unix Socket.
     *
     * @since 7.2.7
     *
     * @var string Path to the SiteTools UNIX socket file.
     */
    const SITE_TOOLS_SOCK_FILE = '/chroot/tmp/site-tools.sock';

    /**
     * Open socket and run a specific command.
     *
     * @since  7.2.7
     *
     * @param  array         $args        The command arguments.
     * @param  bool          $json_object Wheather to force json object upon json encode.
     * @return boolean|array $result      Array with results or false.
     */
    public static function call_site_tools_client( $args, $json_object = false ) {
        // Bail if the socket does not exists.
        if ( ! file_exists( self::SITE_TOOLS_SOCK_FILE ) ) {
            return false;
        }

        // Bail if no arguments present.
        if ( empty( $args ) ) {
            return false;
        }

        // Open unix socket connection.
        $fp = stream_socket_client( 'unix://' . self::SITE_TOOLS_SOCK_FILE, $errno, $errstr, 5 );

        // Bail if the connection fails.
        if ( false === $fp ) {
            return false;
        }

        // Build the request params.
        $request = array(
            'api'      => $args['api'],
            'cmd'      => $args['cmd'],
            'params'   => $args['params'],
            'settings' => $args['settings'],
        );

        // Generate the json_encode flags based on passed variable.
        $flags = ( false === $json_object ) ? 0 : JSON_FORCE_OBJECT;

        // Sent the params to the Unix socket.
        fwrite( $fp, json_encode( $request, $flags ) . "\n" );

        // Fetch the response.
        $response = fgets( $fp, 32 * 1024 );

        // Close the connection.
        fclose( $fp );

        // Decode the response.
        $result = @json_decode( $response, true );

        if ( false === $result || isset( $result['err_code'] ) ) {
            return false;
        }

        return $result;
    }

    /**
     * Gets the site tools matching domain.
     *
     * @since 7.2.7
     *
     * @return array $matches Matches from the search.
     */
    public static function get_site_tools_matching_domain() {
        preg_match( '/^(?:https?:\/\/)?(?:www\.)?([^\/]+)/im', get_home_url(), $matches );

        return $matches;
    }
}