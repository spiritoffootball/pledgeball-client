<?php
/**
 * Remote API Class.
 *
 * Handles Remote API connections.
 *
 * @package Pledgeball_Client
 * @since 1.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Remote API Class.
 *
 * A class that encapsulates Remote API connections.
 *
 * @since 1.0
 */
class Pledgeball_Client_Remote_API {

	/**
	 * The API Base URL.
	 *
	 * @since 1.0
	 * @access protected
	 * @var string $url The API Base URL.
	 */
	protected $url;

	/**
	 * The API Username.
	 *
	 * @since 1.0
	 * @access protected
	 * @var string $username The API Username.
	 */
	protected $username;

	/**
	 * The API Application Password.
	 *
	 * @since 1.0
	 * @access protected
	 * @var string $app_pwd The API Application Password.
	 */
	protected $app_pwd;

	/**
	 * Localhost flag.
	 *
	 * @since 1.0
	 * @access protected
	 * @var string $localhost True if calling a localhost site.
	 */
	protected $localhost = false;

	/**
	 * Constructor.
	 *
	 * @since 1.0
	 *
	 * @param string $url The API Base URL.
	 * @param string $username The API Username.
	 * @param string $app_pwd The API Application Password.
	 * @param string $local Leave empty for hosts.
	 */
	public function __construct( $url = '', $username = '', $app_pwd = '', $local = '' ) {

		// Initialise this instance when the constants exist.
		if ( defined( 'PLEDGEBALL_URL' ) && defined( 'PLEDGEBALL_USER' ) && defined( 'PLEDGEBALL_PWD' ) ) {
			$this->initialise( PLEDGEBALL_URL, PLEDGEBALL_USER, PLEDGEBALL_PWD );
		}

	}

	/**
	 * Initialises this Remote API instance.
	 *
	 * This method can be used to override the Remote API instance if needed.
	 *
	 * @since 1.0
	 *
	 * @param string $url The API Base URL.
	 * @param string $username The API Username.
	 * @param string $app_pwd The API Application Password.
	 * @param string $local Leave empty for truly remote hosts.
	 * @return bool $success True if successful, false otherwise.
	 */
	public function initialise( $url, $username, $app_pwd, $local = '' ) {

		// Sanity check params.
		if ( empty( $url ) || empty( $username ) || empty( $app_pwd ) ) {
			return false;
		}

		// Store params.
		$this->url = trailingslashit( $url );
		$this->username = $username;
		$this->app_pwd = $app_pwd;
		if ( ! empty( $local ) || defined( 'PLEDGEBALL_HOST' ) ) {
			$this->localhost = true;
		}

		// --<
		return true;

	}

	/**
	 * Sends a GET request to the Remote API and returns the response.
	 *
	 * @since 1.0
	 *
	 * @param string $endpoint The Remote API endpoint.
	 * @param array $body The params to send.
	 * @param bool $auth True if authentication is required. Default false.
	 * @param array $headers Any extra headers to send.
	 * @return array|bool $result The response array, or false on failure.
	 */
	public function get( $endpoint, $body = [], $auth = false, $headers = [] ) {

		// Init return.
		$result = false;

		// Some GET requests require authentication.
		if ( $auth === true ) {

			// Construct authentication string.
			// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
			$auth = 'Basic ' . base64_encode( $this->username . ':' . $this->app_pwd );

			// Build headers array.
			$http_headers = [
				'Authorization' => $auth,
			] + $headers;

		} else {

			// Use supplied headers array.
			$http_headers = $headers;

		}

		// Build GET arguments.
		$args = [
			'headers' => $http_headers,
			'body' => $body,
		];

		// Pre-request check.
		$this->pre_request();

		// Fire the GET request.
		$response = wp_remote_get( $this->url . $endpoint, $args );

		// Post-request checks.
		$result = $this->post_request( $response );

		// --<
		return $result;

	}

	/**
	 * Sends a POST request to the Remote API and returns the response.
	 *
	 * @since 1.0
	 *
	 * @param string $endpoint The Remote API endpoint.
	 * @param array $body The params to send.
	 * @param array $headers The headers to send.
	 * @return array|bool $result The response array, or false on failure.
	 */
	public function post( $endpoint, $body = [], $headers = [] ) {

		// Init return.
		$result = false;

		// POST always requires authentication.
		// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
		$auth = 'Basic ' . base64_encode( $this->username . ':' . $this->app_pwd );

		// Build headers array.
		$http_headers = [
			'Authorization' => $auth,
		] + $headers;

		// Build POST arguments.
		$args = [
			'headers' => $http_headers,
			'body' => $body,
		];

		// Pre-request check.
		$this->pre_request();

		// Fire the POST request.
		$response = wp_remote_post( $this->url . $endpoint, $args );

		/*
		 * Post-request checks.
		 *
		 * * Create requests are successful with code 201.
		 * * Update requests are successful with code 200.
		 *
		 * We're happy with either.
		 */
		$result = $this->post_request( $response, [ 200, 201 ] );

		// --<
		return $result;

	}

	/**
	 * Sends a DELETE request to the Remote API and returns the response.
	 *
	 * @since 1.0
	 *
	 * @param string $endpoint The Remote API endpoint.
	 * @param array $body The params to send.
	 * @param array $headers The headers to send.
	 * @return array|bool $result The response array, or false on failure.
	 */
	public function delete( $endpoint, $body = [], $headers = [] ) {

		// Init return.
		$result = false;

		// DELETE always requires authentication.
		// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
		$auth = 'Basic ' . base64_encode( $this->username . ':' . $this->app_pwd );

		// Build headers array.
		$http_headers = [
			'Authorization' => $auth,
		] + $headers;

		// Build DELETE arguments.
		$args = [
			'method' => 'DELETE',
			'headers' => $http_headers,
			'body' => $body,
		];

		// Pre-request check.
		$this->pre_request();

		// Fire the DELETE request.
		$response = wp_remote_request( $this->url . $endpoint, $args );

		// Post-request checks.
		$result = $this->post_request( $response );

		// --<
		return $result;

	}

	/**
	 * Sends a configurable request to the Remote API and returns the response.
	 *
	 * @since 1.0
	 *
	 * @param string $endpoint The Remote API endpoint.
	 * @param array $body The params to send.
	 * @param string $method The request method. Default GET.
	 * @param array $headers The headers to send.
	 * @return array|bool $result The response array, or false on failure.
	 */
	public function request( $endpoint, $body = [], $method = 'GET', $headers = [] ) {

		// Init return.
		$result = false;

		// Always add authentication.
		// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
		$auth = 'Basic ' . base64_encode( $this->username . ':' . $this->app_pwd );

		// Build headers array.
		$http_headers = [
			'Authorization' => $auth,
		] + $headers;

		// Build arguments.
		$args = [
			'method' => $method,
			'headers' => $http_headers,
			'body' => $body,
		];

		// Pre-request check.
		$this->pre_request();

		// Fire the request.
		$response = wp_remote_request( $this->url . $endpoint, $args );

		// Post-request checks.
		$result = $this->post_request( $response, [ 200, 201 ] );

		// --<
		return $result;

	}

	/**
	 * Pre-request checks.
	 *
	 * @since 1.0
	 */
	public function pre_request() {

		// Disregard SSL on localhost.
		if ( $this->localhost === true ) {
			add_filter( 'https_ssl_verify', [ $this, 'ssl_verify_disable' ] );
		}

	}

	/**
	 * Post-request checks and response parsing.
	 *
	 * @since 1.0
	 *
	 * @param mixed $response The request response.
	 * @param array $success_codes The anticipated success codes. Default 200.
	 * @return array|bool $result The response array, or false on failure.
	 */
	public function post_request( $response, $success_codes = [ 200 ] ) {

		// Reimplement SSL checks.
		if ( $this->localhost === true ) {
			remove_filter( 'https_ssl_verify', [ $this, 'ssl_verify_disable' ] );
		}

		// Init return.
		$result = false;

		// Log what we can if there's an error.
		if ( is_wp_error( $response ) ) {
			$e = new \Exception();
			$trace = $e->getTraceAsString();
			$this->log_error( [
				'method' => __METHOD__,
				'message' => $response->get_error_message(),
				'response' => $response,
				'backtrace' => $trace,
			] );
			return $result;
		}

		// Log something if the response isn't what we expect.
		if ( ! is_array( $response ) ) {
			$e = new \Exception();
			$trace = $e->getTraceAsString();
			$this->log_error( [
				'method' => __METHOD__,
				'error' => __( 'Response is not an array.', 'pledgeball-client-side' ),
				'response' => $response,
				'backtrace' => $trace,
			] );
			return $result;
		}

		// Log something if the response isn't an expected success code.
		if ( empty( $response['response']['code'] ) || ! in_array( (int) $response['response']['code'], $success_codes ) ) {
			$e = new \Exception();
			$trace = $e->getTraceAsString();
			$this->log_error( [
				'method' => __METHOD__,
				'error' => __( 'Request was not successful.', 'pledgeball-client-side' ),
				'response' => $response,
				'backtrace' => $trace,
			] );
			return $result;
		}

		// Try and format the result.
		try {
			$result = json_decode( $response['body'] );
		} catch ( Exception $ex ) {
			$this->log_error( [
				'method' => __METHOD__,
				'error' => __( 'Failed to decode JSON.', 'pledgeball-client-side' ),
				'response' => $response,
				'backtrace' => $ex->getTraceAsString(),
			] );
			$result = false;
		}

		// --<
		return $result;

	}

	/**
	 * Disable SSL checks.
	 *
	 * @since 1.0
	 */
	public function ssl_verify_disable() {
		return false;
	}

	/**
	 * Write to the error log.
	 *
	 * @since 1.0
	 *
	 * @param array $data The data to write to the log file.
	 */
	public function log_error( $data = [] ) {

		// Skip if not debugging.
		if ( PLEDGEBALL_CLIENT_DEBUG === false ) {
			return;
		}

		// Skip if empty.
		if ( empty( $data ) ) {
			return;
		}

		// Format data.
		// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_print_r
		$error = print_r( $data, true );

		// Write to log file.
		// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
		error_log( $error );

	}

}
