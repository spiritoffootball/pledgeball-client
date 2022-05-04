<?php
/**
 * Remote Class.
 *
 * Handles functionality for remote operations.
 *
 * @package Pledgeball_Client
 * @since 1.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Remote Class.
 *
 * A class that encapsulates functionality for remote operations.
 *
 * @since 1.0
 */
class Pledgeball_Client_Remote {

	/**
	 * Plugin object.
	 *
	 * @since 1.0
	 * @access public
	 * @var object $plugin The Plugin object.
	 */
	public $plugin;

	/**
	 * API object.
	 *
	 * @since 1.0
	 * @access public
	 * @var object $api The API object.
	 */
	public $api;

	/**
	 * Cache object.
	 *
	 * @since 1.0
	 * @access public
	 * @var object $cache The API Cache object.
	 */
	public $cache;

	/**
	 * Transient key.
	 *
	 * @since 1.0
	 * @access public
	 * @var str $meta_key The Transient key.
	 */
	public $transient_key = 'pledgeball_remote_data_lists';

	/**
	 * Constructor.
	 *
	 * @since 1.0
	 *
	 * @param object $plugin The plugin object.
	 */
	public function __construct( $plugin ) {

		// Store reference to Plugin object.
		$this->plugin = $plugin;

		// Init when this plugin is loaded.
		add_action( 'pledgeball_client/init', [ $this, 'initialise' ] );

	}

	/**
	 * Initialises this object.
	 *
	 * @since 1.0
	 */
	public function initialise() {

		// Constants for URL, Username and Application Password are required.
		if ( ! defined( 'PLEDGEBALL_URL' ) ) {
			return;
		}
		if ( ! defined( 'PLEDGEBALL_USER' ) ) {
			return;
		}
		if ( ! defined( 'PLEDGEBALL_PWD' ) ) {
			return;
		}

		// Bootstrap class.
		$this->include_files();
		$this->setup_objects();
		$this->register_hooks();

		/**
		 * Broadcast that this class is active.
		 *
		 * @since 1.0
		 */
		do_action( 'pledgeball_client/remote/init' );

	}

	/**
	 * Includes files.
	 *
	 * @since 1.0
	 */
	public function include_files() {

		// Include class files.
		include PLEDGEBALL_CLIENT_PATH . 'includes/class-remote-api.php';
		include PLEDGEBALL_CLIENT_PATH . 'includes/class-remote-cache.php';

	}

	/**
	 * Instantiates objects.
	 *
	 * @since 1.0
	 */
	public function setup_objects() {

		// Init objects.
		$this->cache = new Pledgeball_Client_Remote_Cache();

	}

	/**
	 * Registers hooks.
	 *
	 * @since 1.0
	 */
	public function register_hooks() {

		// Add callbacks for cache queue results.
		add_action( 'pledgeball_client/queue/item', [ $this, 'pledge_created' ], 10, 2 );

	}

	// -------------------------------------------------------------------------

	/**
	 * Creates a Pledge record.
	 *
	 * This is an example of how "high level" methods for accessing the Pledgeball API
	 * can be created here.
	 *
	 * @since 1.0
	 *
	 * @param array $data The array of Pledge data.
	 * @return array|bool $response The returned data if successfully added, false otherwise.
	 */
	public function pledge_create( $data ) {

		// Get connection instance.
		$connection = new Pledgeball_Client_Remote_API();

		// Send the data.
		$response = $connection->post( 'wp-json/wp/v2/pledge', $data );

		// Bail on failure.
		if ( $response === false ) {
			return;
		}

		/*
		 * Return should be something like:
		 *
		 * stdClass Object (
		 *   "id": 118,
		 *   "pledge": 12,
		 *   "description": "Go vegan",
		 *   ... more data ...
		 * )
		 *
		 * When there is an error, we could add this query to the query cache.
		 * However we may not need caching for this particular method. We can
		 * return an error to the form and ask for it to be re-submitted instead.
		 */
		if ( $response === false ) {

			// Build query.
			$query = [
				'action' => 'pledge_create',
				'endpoint' => 'wp-json/wp/v2/pledge',
				'body' => $data,
				'method' => 'POST',
			];

			// Add to cache.
			$this->cache->queue_add( $query );

			// --<
			return false;

		}

		// --<
		return $response;

	}

	/**
	 * Called when a queue item has been moved off the stack.
	 *
	 * This is an example callback.
	 *
	 * @since 1.0
	 *
	 * @param array $item The queue item.
	 * @param object $result The result of a successful Pledgeball API call.
	 */
	public function pledge_created( $item, $result ) {

		// Bail if not the action we're after.
		if ( $item['action'] !== 'pledge_create' ) {
			return;
		}

		// Maybe do something.

	}

}
