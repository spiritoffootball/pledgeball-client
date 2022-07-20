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
	 * Unused as yet, but probably useful in the future.
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
		add_action( 'pledgeball_client/queue/item', [ $this, 'event_saved' ], 10, 2 );
		add_action( 'pledgeball_client/queue/item', [ $this, 'event_deleted' ], 10, 2 );
		add_action( 'pledgeball_client/queue/item', [ $this, 'pledges_saved' ], 10, 2 );

	}

	// -------------------------------------------------------------------------

	/**
	 * Gets all Pledge definitions.
	 *
	 * @since 1.0
	 *
	 * @param array $args The optional array of key/value pairs to send to the API.
	 * @return array|bool $pledges The Pledge definitions if retrieved, false otherwise.
	 */
	public function definitions_get_all( $args = [] ) {

		// Get connection instance.
		$connection = new Pledgeball_Client_Remote_API();

		// Send the request.
		$response = $connection->get( 'wp-json/pledgeapi/v1/pledgelist', $args, true );

		// We do not need caching for this particular method.
		if ( $response === false ) {
			return false;
		}

		// Sanity check.
		if ( empty( $response->data ) ) {
			return false;
		}

		// Extract data as array, since object properties are Pledge IDs.
		$pledges = (array) $response->data;
		if ( empty( $pledges ) ) {
			return false;
		}

		// --<
		return $pledges;

	}

	// -------------------------------------------------------------------------

	/**
	 * Gets the Event Group ID for a given Event Group name.
	 *
	 * @since 1.0
	 *
	 * @param string $name The name of the Event Group.
	 * @return int|bool $group_id The numeric ID of the Event Group, false otherwise.
	 */
	public function event_group_get_by_name( $name ) {

		// Get connection instance.
		$connection = new Pledgeball_Client_Remote_API();

		// Send the request.
		$response = $connection->get( 'wp-json/pledgeapi/v1/geteventid', [ 'name' => $name ], true );

		// We do not need caching for this particular method.
		if ( $response === false ) {
			return false;
		}

		// Sanity check.
		if ( empty( $response->data ) ) {
			return false;
		}

		// Extract data as array, since object property has no name.
		$data = (array) $response->data;
		if ( empty( $data ) ) {
			return false;
		}

		// There should only be one item.
		$data = array_pop( $data );
		if ( empty( $data->ID ) ) {
			return false;
		}

		// Assign Group ID.
		$group_id = (int) $data->ID;

		// --<
		return $group_id;

	}

	// -------------------------------------------------------------------------

	/**
	 * Gets all Event records.
	 *
	 * @since 1.0
	 *
	 * @return array|bool $events The array of all Event record objects, false otherwise.
	 */
	public function events_get_all() {

		// Get connection instance.
		$connection = new Pledgeball_Client_Remote_API();

		// Send the request.
		$response = $connection->get( 'wp-json/pledgeapi/v1/findevents', [], true );

		// We do not need caching for this particular method.
		if ( $response === false ) {
			return false;
		}

		// Sanity check.
		if ( empty( $response->data ) ) {
			return false;
		}

		// Extract Events as array, since object properties are Event IDs.
		$events = (array) $response->data;
		if ( empty( $events ) ) {
			return false;
		}

		// --<
		return $events;

	}

	/**
	 * Gets an Event record by its ID.
	 *
	 * @since 1.0
	 *
	 * @param integer $event_id The numeric ID of the Event.
	 * @return object|bool $event The retrieved Event data, false otherwise.
	 */
	public function event_get_by_id( $event_id ) {

		// Get connection instance.
		$connection = new Pledgeball_Client_Remote_API();

		// Send the request.
		$response = $connection->get( 'wp-json/pledgeapi/v1/eventdetails', [ 'id' => $event_id ], true );

		// We do not need caching for this particular method.
		if ( $response === false ) {
			return false;
		}

		// Sanity check.
		if ( empty( $response->data ) ) {
			return false;
		}

		// Extract the Event object.
		$event = $response->data;

		// --<
		return $event;

	}

	/**
	 * Gets an Event record by source data.
	 *
	 * @since 1.0
	 *
	 * @param string $source The array of Source data.
	 * @return object|bool $event The retrieved Event data, false otherwise.
	 */
	public function event_get_by_source( $source ) {

		// Get connection instance.
		$connection = new Pledgeball_Client_Remote_API();

		// Send the request.
		$response = $connection->get( 'wp-json/pledgeapi/v1/eventdetails', $source, true );

		// We do not need caching for this particular method.
		if ( $response === false ) {
			return false;
		}

		// Sanity check.
		if ( empty( $response->data ) ) {
			return false;
		}

		// Extract the Event object.
		$event = $response->data;

		// --<
		return $event;

	}

	/**
	 * Creates or updates an Event record.
	 *
	 * Passing an Event ID in the data array will cause the Event to be updated.
	 *
	 * @since 1.0
	 *
	 * @param array $data The data for the Event.
	 * @return int|bool $event_id The Event ID if successfully added, false otherwise.
	 */
	public function event_save( $data ) {

		// Get connection instance.
		$connection = new Pledgeball_Client_Remote_API();

		// Send the data.
		$response = $connection->post( 'wp-json/pledgeapi/v1/storeorupdateevent', $data );

		/*
		 * Return should be something like:
		 *
		 * stdClass Object (
		 *   "id": 1234,
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
				'action' => 'event_save',
				'endpoint' => 'wp-json/pledgeapi/v1/storeorupdateevent',
				'body' => $data,
				'method' => 'POST',
			];

			// Add to cache.
			// Disabled, but shows how this would be done.
			// phpcs:ignore Squiz.Commenting.InlineComment.InvalidEndChar
			//$this->cache->queue_add( $query );

			// --<
			return false;

		}

		// Sanity check.
		if ( empty( $response->data ) ) {
			return false;
		}

		// Extract the Event ID.
		$event_id = (int) $response->data;

		// --<
		return $event_id;

	}

	/**
	 * Called when a queue item has been moved off the stack.
	 *
	 * @since 1.0
	 *
	 * @param array $item The queue item.
	 * @param object $result The result of a successful Pledgeball API call.
	 */
	public function event_saved( $item, $result ) {

		// Bail if not the action we're after.
		if ( $item['action'] !== 'event_save' ) {
			return;
		}

		// Maybe do something.

	}

	/**
	 * Deletes an Event record.
	 *
	 * Passing an Event ID will cause the Event to be deleted.
	 *
	 * @since 1.0
	 *
	 * @param integer $event_id The numeric ID of the Event.
	 * @return array|bool $response The returned data if successfully deleted, false otherwise.
	 */
	public function event_delete( $event_id ) {

		// Get connection instance.
		$connection = new Pledgeball_Client_Remote_API();

		// Send the data.
		$response = $connection->request( 'wp-json/pledgeapi/v1/storeorupdateevent', [ 'id' => $event_id ], 'DELETE' );

		/*
		 * Return should be something like:
		 *
		 * stdClass Object (
		 *   "id": 1234,
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
				'action' => 'event_delete',
				'endpoint' => 'wp-json/pledgeapi/v1/storeorupdateevent',
				'body' => [ 'id' => $event_id ],
				'method' => 'POST',
			];

			// Add to cache.
			// Disabled, but shows how this would be done.
			// phpcs:ignore Squiz.Commenting.InlineComment.InvalidEndChar
			//$this->cache->queue_add( $query );

			// --<
			return false;

		}

		// --<
		return $response;

	}

	/**
	 * Called when a queue item has been moved off the stack.
	 *
	 * @since 1.0
	 *
	 * @param array $item The queue item.
	 * @param object $result The result of a successful Pledgeball API call.
	 */
	public function event_deleted( $item, $result ) {

		// Bail if not the action we're after.
		if ( $item['action'] !== 'event_delete' ) {
			return;
		}

		// Maybe do something.

	}

	// -------------------------------------------------------------------------

	/**
	 * Creates a Standalone Pledge record.
	 *
	 * @since 1.0
	 *
	 * @param array $data The array of Pledge data.
	 * @return array|bool $response The returned data if successfully added, false otherwise.
	 */
	public function pledge_create( $data ) {

		// Get connection instance.
		$connection = new Pledgeball_Client_Remote_API();

		// Send JSON-encoded data.
		$response = $connection->post( 'wp-json/pledgeapi/v1/storepledges', $data, [], true );

		// We do not need caching for this particular method.
		if ( $response === false ) {
			// Trigger failure and form notice.
			return false;
		}

		// --<
		return $response;

	}

	// -------------------------------------------------------------------------

	/**
	 * Creates multiple Pledge records.
	 *
	 * @since 1.0
	 *
	 * @param array $data The array of Pledge data.
	 * @return array|bool $response The returned data if successfully added, false otherwise.
	 */
	public function pledges_save( $data ) {

		// Get connection instance.
		$connection = new Pledgeball_Client_Remote_API();

		// Send JSON-encoded data.
		$response = $connection->post( 'wp-json/pledgeapi/v1/storepledges', $data, [], true );

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
				'action' => 'pledges_save',
				'endpoint' => 'wp-json/pledgeapi/v1/storepledges',
				'body' => $data,
				'method' => 'POST',
			];

			// Add to cache.
			// Disabled, but shows how this would be done.
			// phpcs:ignore Squiz.Commenting.InlineComment.InvalidEndChar
			//$this->cache->queue_add( $query );

			// --<
			return false;

		}

		// --<
		return $response;

	}

	/**
	 * Called when a queue item has been moved off the stack.
	 *
	 * @since 1.0
	 *
	 * @param array $item The queue item.
	 * @param object $result The result of a successful Pledgeball API call.
	 */
	public function pledges_saved( $item, $result ) {

		// Bail if not the action we're after.
		if ( $item['action'] !== 'pledges_save' ) {
			return;
		}

		// Maybe do something.

	}

}
