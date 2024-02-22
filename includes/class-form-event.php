<?php
/**
 * "Create Event" Form Class.
 *
 * Handles "Create Event" Form functionality.
 *
 * @package Pledgeball_Client
 * @since 1.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * "Create Event" Form Class.
 *
 * A class that encapsulates "Create Event" Form functionality.
 *
 * @since 1.0
 */
class Pledgeball_Client_Form_Event_Create {

	/**
	 * Plugin object.
	 *
	 * @since 1.0
	 * @access public
	 * @var Pledgeball_Client
	 */
	public $plugin;

	/**
	 * Form object.
	 *
	 * @since 1.0
	 * @access public
	 * @var Pledgeball_Client_Form
	 */
	public $form;

	/**
	 * POST Nonce action.
	 *
	 * @since 1.0
	 * @access private
	 * @var string
	 */
	private $nonce_action = 'event_create_action';

	/**
	 * POST Nonce name.
	 *
	 * @since 1.0
	 * @access private
	 * @var string
	 */
	private $nonce_name = 'event_create_nonce';

	/**
	 * AJAX Nonce name.
	 *
	 * @since 1.0
	 * @access private
	 * @var string
	 */
	private $nonce_ajax = 'event_create_ajax';

	/**
	 * Constructor.
	 *
	 * @since 1.0
	 *
	 * @param object $form The form object.
	 */
	public function __construct( $form ) {

		// Store reference to Plugin object.
		$this->plugin = $form->plugin;
		$this->form = $form;

		// Init when this form class is loaded.
		add_action( 'pledgeball_client/form/init', [ $this, 'initialise' ] );

	}

	/**
	 * Initialises this object.
	 *
	 * @since 1.0
	 */
	public function initialise() {

		// Bootstrap class.
		$this->register_hooks();

		/**
		 * Broadcast that this object is now initialised.
		 *
		 * @since 1.0
		 */
		do_action( 'pledgeball_client/form/event_create/init' );

	}

	/**
	 * Registers hooks.
	 *
	 * @since 1.0
	 */
	public function register_hooks() {

		// Register Shortcode.
		add_shortcode( 'pledgeball_event_form', [ $this, 'form_render' ] );

		// Register Form handlers.
		add_action( 'wp_ajax_pledgeball_event_create', [ $this, 'form_submitted_ajax' ] );
		add_action( 'wp_ajax_nopriv_pledgeball_event_create', [ $this, 'form_submitted_ajax' ] );
		add_action( 'init', [ $this, 'form_submitted_post' ], 1000 );

	}

	// -------------------------------------------------------------------------

	/**
	 * Adds the "Create Event" Form to a Page via a Shortcode.
	 *
	 * @since 1.0
	 *
	 * @param array $attr The saved Shortcode attributes.
	 * @param str $content The enclosed content of the Shortcode.
	 * @return str $markup The HTML markup for the Shortcode.
	 */
	public function form_render( $attr, $content = null ) {

		// Init markup.
		$markup = '';

		// Add styles.
		$markup .= $this->form_styles();

		// Start buffering.
		ob_start();

		// Now, instead of echoing, Shortcode output ends up in buffer.
		include PLEDGEBALL_CLIENT_PATH . 'assets/templates/forms/event-create.php';

		// Save the output and flush the buffer.
		$markup .= ob_get_clean();

		// Enqueue Javascript.
		$this->form_scripts();

		// --<
		return $markup;

	}

	/**
	 * Gets the basic styles for the Create Event Form.
	 *
	 * @since 1.0
	 *
	 * @return str $styles The CSS for the Create Event Form.
	 */
	public function form_styles() {

		// Define styles.
		$styles = '<style>

			#event_create {
				border: 1px solid #999;
				padding: 0;
			}

			#event_create h3 {
				padding: 1em;
				margin: 0;
				background: #eee;
			}

			#event_create .pledgeball_notice {
				background: #fff;
				border: 1px solid #c3c4c7;
				border-left-width: 4px;
				box-shadow: 0 1px 1px rgb(0 0 0 / 4%);
				margin: 1em;
				padding: 1px 12px;
			}

			#event_create .pledgeball_message {
				border-left-color: #00a32a;
			}

			#event_create .pledgeball_notice p {
				margin: 0.5em 0;
				padding: 2px;
				line-height: 1.5;
			}

			#event_create .pledgeball_error {
				display: none;
				border-left-color: #d63638;
			}

			#event_create fieldset {
				border-top: 1px solid #999;
				padding: 0 1em 1em 1em;
			}

			#event_create .pledgeball_main_label {
				display: inline-block;
				width: 20%;
				margin-right: 2em;
			}

			#event_create .pledgeball_main_input {
				width: 60%;
				box-shadow: 0 0 0 transparent;
				border-radius: 4px;
				border: 1px solid #8c8f94;
				background-color: #fff;
				color: #2c3338;
				padding: 0 8px;
				line-height: 2;
				min-height: 30px;
			}

			#event_create h5 {
				border-top: 1px solid #ddd;
				padding-top: 1em;
			}

			#event_create h5:first-child {
				border-top: none;
				margin-top: 0;
				padding-top: 0;
			}

			#event_create ul {
				list-style: none;
				padding-left: 0;
				margin-left: 1.6em;
			}

			#event_create li {
				list-style: none;
				text-indent: -1.6em;
			}

			#event_create textarea {
				vertical-align: top;
				width: 60%;
				box-shadow: 0 0 0 transparent;
				border-radius: 4px;
				border: 1px solid #8c8f94;
				background-color: #fff;
				color: #2c3338;
				padding: 0 8px;
				line-height: 2;
				min-height: 80px;
			}

			#event_create input[type="checkbox"] {
				border: 1px solid #8c8f94;
				border-radius: 4px;
				background: #fff;
				color: #50575e;
				clear: none;
				cursor: pointer;
				display: inline-block;
				line-height: 0;
				height: 1rem;
				margin: -0.25rem 0.25rem 0 0;
				margin-right: 0.5em;
				outline: 0;
				padding: 0 !important;
				text-align: center;
				vertical-align: middle;
				width: 1rem;
				min-width: 1rem;
				-webkit-appearance: none;
				box-shadow: inset 0 1px 2px rgb(0 0 0 / 10%);
				transition: .05s border-color ease-in-out;
    		}

			#event_create input[type="checkbox"]:checked::before {
				float: left;
				display: inline-block;
				vertical-align: middle;
				width: 1rem;
				speak: never;
				-webkit-font-smoothing: antialiased;
				-moz-osx-font-smoothing: grayscale;
			}

			#event_create input[type="checkbox"]:checked::before {
				/* Use the "Yes" SVG Dashicon */
				content: url("data:image/svg+xml;utf8,%3Csvg%20xmlns%3D%27http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%27%20viewBox%3D%270%200%2020%2020%27%3E%3Cpath%20d%3D%27M14.83%204.89l1.34.94-5.81%208.38H9.02L5.78%209.67l1.34-1.25%202.57%202.4z%27%20fill%3D%27%233582c4%27%2F%3E%3C%2Fsvg%3E");
				margin: -0.1875rem 0 0 -0.25rem;
				height: 1.3125rem;
				width: 1.3125rem;
			}

			#event_create input[type="checkbox"]:focus {
				border-color: #2271b1;
				box-shadow: 0 0 0 1px #2271b1;
				/* Only visible in Windows High Contrast mode */
				outline: 2px solid transparent;
			}

			#event_create input[type="checkbox"]:disabled,
			#event_create input[type="checkbox"].disabled,
			#event_create input[type="checkbox"]:disabled:checked:before,
			#event_create input[type="checkbox"].disabled:checked:before {
				opacity: 0.7;
			}

			#event_create ul label {
				font-weight: bold;
			}

			#event_create .event_create_button {
				padding: 1em;
			}

			#event_create #event_create_button {
				padding: 0.5em;
				font-size: 120%;
			}

			#event_create .spinner {
				background: url(' . admin_url( 'images/spinner.gif' ) . ') no-repeat;
				background-size: 20px 20px;
				display: inline-block;
				visibility: hidden;
				vertical-align: middle;
				opacity: 0.7;
				filter: alpha(opacity=70);
				width: 20px;
				height: 20px;
				margin: -2px 10px 0;
			}

			@media print,
			(-webkit-min-device-pixel-ratio: 1.25),
			(min-resolution: 120dpi) {
				#event_create .spinner {
					background-image: url(' . admin_url( 'images/spinner-2x.gif' ) . ' );
				}
			}

		</style>' . "\n";

		/**
		 * Allow styles to be filtered.
		 *
		 * @since 1.0
		 *
		 * @param string $styles The default styles.
		 */
		return apply_filters( 'pledgeball_client/form/event_create/styles', $styles );

	}

	/**
	 * Enqueue the necessary scripts.
	 *
	 * @since 1.0
	 */
	public function form_scripts() {

		// Enqueue custom javascript.
		wp_enqueue_script(
			'event-create-js',
			PLEDGEBALL_CLIENT_URL . 'assets/js/event-create.js',
			[ 'jquery' ],
			PLEDGEBALL_CLIENT_VERSION,
			true // In footer.
		);

		// Init localisation.
		$localisation = [
			'field_required' => __( 'Please complete the fields marked in red.', 'pledgeball-client' ),
			'submit' => __( 'Create Event', 'pledgeball-client' ),
			'submitting' => __( 'Submitting...', 'pledgeball-client' ),
		];

		// Init settings.
		$settings = [
			'ajax_url' => admin_url( 'admin-ajax.php' ),
		];

		// Localisation array.
		$vars = [
			'localisation' => $localisation,
			'settings' => $settings,
		];

		// Localise the WordPress way.
		wp_localize_script(
			'event-create-js',
			'Pledgeball_Form_Event_Create_Settings',
			$vars
		);

	}

	// -------------------------------------------------------------------------

	/**
	 * Called when the "Create Event" Form is submitted with Javascript.
	 *
	 * @since 1.0
	 */
	public function form_submitted_ajax() {

		// Default response.
		$data = [
			'notice' => __( 'Could not submit the Event. Please try again.', 'pledgeball-client' ),
			'saved' => false,
		];

		// Skip if not AJAX submission.
		if ( ! wp_doing_ajax() ) {
			wp_send_json( $data );
		}

		// Since this is an AJAX request, check security.
		$result = check_ajax_referer( $this->nonce_ajax, false, false );
		if ( $result === false ) {
			$data['notice'] = __( 'Authentication failed. Could not submit the Event.', 'pledgeball-client' );
			wp_send_json( $data );
		}

		// Extract mandatory Email.
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$email_raw = isset( $_POST['email'] ) ? trim( wp_unslash( $_POST['email'] ) ) : '';
		$email = sanitize_email( $email_raw );
		if ( empty( $email ) || ! is_email( $email ) ) {
			$data['notice'] = __( 'Please enter a valid Email Address.', 'pledgeball-client' );
			wp_send_json( $data );
		}

		// Extract mandatory Event Title.
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$title_raw = isset( $_POST['title'] ) ? trim( wp_unslash( $_POST['title'] ) ) : '';
		$title = sanitize_text_field( $title_raw );
		if ( empty( $title ) ) {
			$data['notice'] = __( 'Please enter a valid Event title.', 'pledgeball-client' );
			wp_send_json( $data );
		}

		// Extract "First Name".
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$first_name_raw = isset( $_POST['first_name'] ) ? trim( wp_unslash( $_POST['first_name'] ) ) : '';
		$first_name = sanitize_text_field( $first_name_raw );

		// Extract "Last Name".
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$last_name_raw = isset( $_POST['last_name'] ) ? trim( wp_unslash( $_POST['last_name'] ) ) : '';
		$last_name = sanitize_text_field( $last_name_raw );

		// Extract Phone.
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$phone_raw = isset( $_POST['phone'] ) ? trim( wp_unslash( $_POST['phone'] ) ) : '';
		$phone = sanitize_text_field( $phone_raw );

		// Let's make an array of submission data.
		$submission = [
			'firstname' => $first_name,
			'lastname' => $last_name,
			'email' => $email,
			'phone' => $phone,
			'title' => $title,
		];

		/*
		// Submit the Event.
		$response = $this->plugin->remote->event_save( $submission );
		if ( $response === false ) {
			// phpcs:ignore Squiz.Commenting.InlineComment.InvalidEndChar
			//wp_send_json( $data );
		}
		*/

		// Fake a response.
		$response = false;

		/**
		 * Broadcast that a submission has been completed.
		 *
		 * @since 1.0
		 *
		 * @param array $submission The submitted data.
		 * @param array $response The response from the server.
		 */
		do_action( 'pledgeball_client/form/event_create/submission', $submission, $response );

		// Data response.
		$data = [
			'message' => __( 'Your Event has been submitted.', 'pledgeball-client' ),
			'saved' => true,
		];

		// Return the data.
		wp_send_json( $data );

	}

	/**
	 * Called when the "Create Event" Form is submitted without Javascript.
	 *
	 * @since 1.0
	 */
	public function form_submitted_post() {

		// Skip if AJAX submission.
		if ( wp_doing_ajax() ) {
			return;
		}

		// Skip if no form nonce.
		if ( ! isset( $_POST[ $this->nonce_name ] ) ) {
			return;
		}

		// Skip if nonce verification fails.
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		if ( ! wp_verify_nonce( wp_unslash( $_POST[ $this->nonce_name ] ), $this->nonce_action ) ) {
			$this->form_redirect( [ 'error' => 'no-auth' ] );
		}

		// Extract mandatory Email.
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$email_raw = isset( $_POST['pledgeball_email'] ) ? trim( wp_unslash( $_POST['pledgeball_email'] ) ) : '';
		if ( empty( $email_raw ) ) {
			$this->form_redirect( [ 'error' => 'no-email' ] );
		}
		$email = sanitize_email( $email_raw );
		if ( empty( $email ) ) {
			$this->form_redirect( [ 'error' => 'no-email' ] );
		}

		// Extract mandatory Title.
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$title_raw = isset( $_POST['pledgeball_title'] ) ? trim( wp_unslash( $_POST['pledgeball_title'] ) ) : '';
		if ( empty( $title_raw ) ) {
			$this->form_redirect( [ 'error' => 'no-title' ] );
		}
		$title = sanitize_text_field( $title_raw );
		if ( empty( $title ) ) {
			$this->form_redirect( [ 'error' => 'no-title' ] );
		}

		// Extract "First Name".
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$first_name_raw = isset( $_POST['pledgeball_first_name'] ) ? trim( wp_unslash( $_POST['pledgeball_first_name'] ) ) : '';
		$first_name = sanitize_text_field( $first_name_raw );

		// Extract "Last Name".
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$last_name_raw = isset( $_POST['pledgeball_last_name'] ) ? trim( wp_unslash( $_POST['pledgeball_last_name'] ) ) : '';
		$last_name = sanitize_text_field( $last_name_raw );

		// Extract Phone.
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$phone_raw = isset( $_POST['pledgeball_phone'] ) ? trim( wp_unslash( $_POST['pledgeball_phone'] ) ) : '';
		$phone = sanitize_text_field( $phone_raw );

		// Let's make an array of submission data.
		$submission = [
			'firstname' => $first_name,
			'lastname' => $last_name,
			'email' => $email,
			'phone' => $phone,
			'title' => $title,
		];

		/*
		// Submit the Event.
		$response = $this->plugin->remote->event_save( $submission );
		if ( $response === false ) {
			$this->form_redirect( [ 'error' => 'no-response' ] );
		}
		*/

		// Fake a response.
		$response = false;

		/**
		 * Broadcast that a submission has been completed.
		 *
		 * @since 1.0
		 *
		 * @param array $submission The submitted data.
		 * @param array $response The response from the server.
		 */
		do_action( 'pledgeball_client/form/event_create/submission', $submission, $response );

		// Our array of arguments.
		$args = [
			'submitted' => 'true',
		];

		// Redirect.
		$this->form_redirect( $args );

	}

	/**
	 * Redirects after the "Create Event" Form is submitted.
	 *
	 * @since 1.0
	 *
	 * @param array $args The query args.
	 */
	public function form_redirect( $args = [] ) {

		// Get the submitted URL.
		$url = wp_get_raw_referer();

		// Redirect to prevent re-submission.
		if ( ! empty( $url ) ) {
			wp_safe_redirect( add_query_arg( $args, $url ) );
		} else {
			wp_safe_redirect( get_home_url() );
		}

		exit();

	}

}
