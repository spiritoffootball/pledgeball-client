<?php
/**
 * Plugin Name: Pledgeball Client
 * Plugin URI: https://github.com/spiritoffootball/pledgeball-client
 * GitHub Plugin URI: https://github.com/spiritoffootball/pledgeball-client
 * Description: Enables access to the Pledgeball API.
 * Author: Christian Wach
 * Version: 1.0a
 * Author URI: https://theball.tv
 * Text Domain: pledgeball-client
 * Domain Path: /languages
 *
 * @package Pledgeball_Client
 * @since 1.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

// Set plugin version here.
define( 'PLEDGEBALL_CLIENT_VERSION', '1.0a' );

// Store reference to this file.
if ( ! defined( 'PLEDGEBALL_CLIENT_FILE' ) ) {
	define( 'PLEDGEBALL_CLIENT_FILE', __FILE__ );
}

// Store URL to this plugin's directory.
if ( ! defined( 'PLEDGEBALL_CLIENT_URL' ) ) {
	define( 'PLEDGEBALL_CLIENT_URL', plugin_dir_url( PLEDGEBALL_CLIENT_FILE ) );
}

// Store path to this plugin's directory.
if ( ! defined( 'PLEDGEBALL_CLIENT_PATH' ) ) {
	define( 'PLEDGEBALL_CLIENT_PATH', plugin_dir_path( PLEDGEBALL_CLIENT_FILE ) );
}

// Set plugin debugging state.
if ( ! defined( 'PLEDGEBALL_CLIENT_DEBUG' ) ) {
	define( 'PLEDGEBALL_CLIENT_DEBUG', false );
}

/**
 * Pledgeball Client Class.
 *
 * A class that encapsulates this plugin's functionality.
 *
 * @since 1.0
 */
class Pledgeball_Client {

	/**
	 * Pledgeball Remote API object.
	 *
	 * @since 1.0
	 * @access public
	 * @var Pledgeball_Client_Remote
	 */
	public $remote;

	/**
	 * Pledgeball Form object.
	 *
	 * @since 1.0
	 * @access public
	 * @var Pledgeball_Client_Form
	 */
	public $form;

	/**
	 * Initialises this object.
	 *
	 * @since 1.0
	 */
	public function __construct() {

		// Initialise this plugin.
		$this->initialise();

	}

	/**
	 * Initialises this plugin.
	 *
	 * @since 1.0
	 */
	public function initialise() {

		// Only do this once.
		static $done;
		if ( isset( $done ) && $done === true ) {
			return;
		}

		// Bootstrap plugin.
		$this->translation();
		$this->include_files();
		$this->setup_objects();

		/**
		 * Broadcast that this plugin is active.
		 *
		 * @since 1.0
		 */
		do_action( 'pledgeball_client/init' );

		// We're done.
		$done = true;

	}

	/**
	 * Enables translation.
	 *
	 * @since 1.0
	 */
	public function translation() {

		// Load translations.
		// phpcs:ignore WordPress.WP.DeprecatedParameters.Load_plugin_textdomainParam2Found
		load_plugin_textdomain(
			'pledgeball-client', // Unique name.
			false, // Deprecated argument.
			dirname( plugin_basename( PLEDGEBALL_CLIENT_FILE ) ) . '/languages/' // Relative path to files.
		);

	}

	/**
	 * Includes files.
	 *
	 * @since 1.0
	 */
	public function include_files() {

		// Load our class files.
		include PLEDGEBALL_CLIENT_PATH . 'includes/class-remote.php';
		include PLEDGEBALL_CLIENT_PATH . 'includes/class-form.php';

	}

	/**
	 * Sets up this plugin's objects.
	 *
	 * @since 1.0
	 */
	public function setup_objects() {

		// Initialise objects.
		$this->remote = new Pledgeball_Client_Remote( $this );
		$this->form = new Pledgeball_Client_Form( $this );

	}

	// -------------------------------------------------------------------------

	/**
	 * Checks if this plugin is network activated.
	 *
	 * @since 1.0
	 *
	 * @return bool $is_network_active True if network activated, false otherwise.
	 */
	public function is_network_activated() {

		// Only need to test once.
		static $is_network_active;

		// Have we done this already?
		if ( isset( $is_network_active ) ) {
			return $is_network_active;
		}

		// If not multisite, it cannot be.
		if ( ! is_multisite() ) {
			$is_network_active = false;
			return $is_network_active;
		}

		// Make sure plugin file is included when outside admin.
		if ( ! function_exists( 'is_plugin_active_for_network' ) ) {
			require_once ABSPATH . '/wp-admin/includes/plugin.php';
		}

		// Get path from 'plugins' directory to this plugin.
		$this_plugin = plugin_basename( PLEDGEBALL_CLIENT_FILE );

		// Test if network active.
		$is_network_active = is_plugin_active_for_network( $this_plugin );

		// --<
		return $is_network_active;

	}

}



/**
 * Loads plugin if not yet loaded and return reference.
 *
 * @since 1.0
 *
 * @return Pledgeball_Client $plugin The plugin reference.
 */
function pledgeball_client() {

	// Instantiate plugin if not yet instantiated.
	static $plugin;
	if ( ! isset( $plugin ) ) {
		$plugin = new Pledgeball_Client();
	}

	// --<
	return $plugin;

}

// Load only when all plugins have loaded.
add_action( 'plugins_loaded', 'pledgeball_client' );

/**
 * Performs plugin activation tasks.
 *
 * @since 1.0
 */
function pledgeball_client_activate() {

	/**
	 * Broadcast that this plugin has been activated.
	 *
	 * @since 1.0
	 */
	do_action( 'pledgeball_client/activated' );

}

// Activation.
register_activation_hook( __FILE__, 'pledgeball_client_activate' );

/**
 * Performs plugin deactivation tasks.
 *
 * @since 1.0
 */
function pledgeball_client_deactivated() {

	/**
	 * Broadcast that this plugin has been deactivated.
	 *
	 * @since 1.0
	 */
	do_action( 'pledgeball_client/deactivated' );

}

// Deactivation.
register_deactivation_hook( __FILE__, 'pledgeball_client_deactivated' );

/*
 * Uninstall uses the 'uninstall.php' method.
 *
 * @see https://developer.wordpress.org/reference/functions/register_uninstall_hook/
 */
