<?php
/**
 * Form Class.
 *
 * This class loads the default Pledgeball form classes.
 *
 * @package Pledgeball_Client
 * @since 1.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Form Class.
 *
 * A class that encapsulates form-related functionality.
 *
 * @since 1.0
 */
class Pledgeball_Client_Form {

	/**
	 * Plugin object.
	 *
	 * @since 1.0
	 * @access public
	 * @var object $plugin The Plugin object.
	 */
	public $plugin;

	/**
	 * "Submit Pledge" Form object.
	 *
	 * @since 1.0
	 * @access public
	 * @var object $pledge_submit The "Submit Pledge" Form object.
	 */
	public $pledge_submit;

	/**
	 * "Create Event" Form object.
	 *
	 * @since 1.0
	 * @access public
	 * @var object $event_create The "Create Event" Form object.
	 */
	public $event_create;

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

		// Bootstrap class.
		$this->include_files();
		$this->setup_objects();
		$this->register_hooks();

		/**
		 * Broadcast that this object is now initialised.
		 *
		 * @since 1.0
		 */
		do_action( 'pledgeball_client/form/init' );

	}

	/**
	 * Includes files.
	 *
	 * @since 1.0
	 */
	public function include_files() {

		// Include class files.
		include PLEDGEBALL_CLIENT_PATH . 'includes/class-form-pledge.php';
		include PLEDGEBALL_CLIENT_PATH . 'includes/class-form-event.php';

	}

	/**
	 * Instantiates objects.
	 *
	 * @since 1.0
	 */
	public function setup_objects() {

		// Init objects.
		$this->pledge_submit = new Pledgeball_Client_Form_Pledge_Submit( $this );
		$this->event_create = new Pledgeball_Client_Form_Event_Create( $this );

	}

	/**
	 * Registers hooks.
	 *
	 * @since 1.0
	 */
	public function register_hooks() {

	}

}
