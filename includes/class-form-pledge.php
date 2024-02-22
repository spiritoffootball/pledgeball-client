<?php
/**
 * "Submit Pledge" Form Class.
 *
 * Handles "Submit Pledge" Form functionality.
 *
 * @package Pledgeball_Client
 * @since 1.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * "Submit Pledge" Form Class.
 *
 * A class that encapsulates "Submit Pledge" Form functionality.
 *
 * @since 1.0
 */
class Pledgeball_Client_Form_Pledge_Submit {

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
	private $nonce_action = 'pledge_submit_action';

	/**
	 * POST Nonce name.
	 *
	 * @since 1.0
	 * @access private
	 * @var string
	 */
	private $nonce_name = 'pledge_submit_nonce';

	/**
	 * AJAX Nonce name.
	 *
	 * @since 1.0
	 * @access private
	 * @var string
	 */
	private $nonce_ajax = 'pledge_submit_ajax';

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
		$this->form   = $form;

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
		do_action( 'pledgeball_client/form/pledge_submit/init' );

	}

	/**
	 * Registers hooks.
	 *
	 * @since 1.0
	 */
	public function register_hooks() {

		// Register Shortcode.
		add_shortcode( 'pledgeball_pledge_form', [ $this, 'form_render' ] );

		// Register Form handlers.
		add_action( 'wp_ajax_pledgeball_pledge_submit', [ $this, 'form_submitted_ajax' ] );
		add_action( 'wp_ajax_nopriv_pledgeball_pledge_submit', [ $this, 'form_submitted_ajax' ] );
		add_action( 'init', [ $this, 'form_submitted_post' ], 1000 );

	}

	// -------------------------------------------------------------------------

	/**
	 * Adds the "Submit Pledge" Form to a Page via a Shortcode.
	 *
	 * @since 1.0
	 *
	 * @param array $attr The saved Shortcode attributes.
	 * @param str   $content The enclosed content of the Shortcode.
	 * @return str $markup The HTML markup for the Shortcode.
	 */
	public function form_render( $attr, $content = null ) {

		// Init markup.
		$markup = '';

		// Get all possible Pledge definitions.
		$pledges = $this->plugin->remote->definitions_get_all();

		// Bail if we didn't get any results.
		if ( empty( $pledges ) ) {
			$markup .= '<h3>' . __( 'Submit a Standalone Pledge', 'pledgeball-client' ) . '</h3>' . "\n";
			$markup .= '<p>' . __( 'Sorry, something went wrong. Please reload and try again.', 'pledgeball-client' ) . '</p>' . "\n";
			return $markup;
		}

		// Let's build an array keyed by Category.
		$build = [];
		foreach ( $pledges as $pledge ) {

			$input = '<input type="checkbox" class="pledge_checkbox" name="pledgeball_ids[]" id="pledgeball_id_' . esc_attr( $pledge->Number ) . '" value="' . esc_attr( $pledge->Number ) . '">';
			$label = '<label for="pledgeball_id_' . esc_attr( $pledge->Number ) . '">' . esc_html( $pledge->Description ) . '</label>';

			$saving = '';
			if ( ! empty( $pledge->KgCO2e ) && '-1' !== $pledge->KgCO2e ) {
				/* translators: %s The number of kilogrammes. */
				$saving = ' <span>' . sprintf( __( 'Saves %s kg of CO<sub>2</sub>e per year.', 'pledgeball-client' ), esc_html( $pledge->KgCO2e ) ) . '</span>';
			}
			if ( ! empty( $pledge->KgCO2e ) && '-1' === $pledge->KgCO2e ) {
				$saving = ' <span>' . __( 'Saves CO<sub>2</sub>e but hard to quantify.', 'pledgeball-client' ) . '</span>';
			}

			$context = '';
			if ( ! empty( $pledge->UsefulURL ) ) {
				$context = ' <span>(<a href="' . esc_url( $pledge->UsefulURL ) . '" target="_blank">' . __( 'More information', 'pledgeball-client' ) . ')</a></span>';
			}

			$divider = '';
			if ( ! empty( $saving ) || ! empty( $context ) ) {
				$divider = '<br>';
			}

			$build[ esc_html( $pledge->Category ) ][] = $input . $label . $divider . $saving . $context;

		}

		ksort( $build );

		// Define Consent text.
		$consent = __( 'I consent to my details being stored by PledgeBall (required)', 'pledgeball-client' );

		/**
		 * Allow "Consent" text to be filtered.
		 *
		 * @since 1.0
		 *
		 * @param string $consent The default "Consent" text.
		 */
		$consent = apply_filters( 'pledgeball_client/form/pledge_submit/consent_text', $consent );

		// Define Updates text.
		$updates = __( 'Tick to receive occasional updates about the impact of you and your fellow Pledgeballers (and if you like freebies). NB please tick even if you have already subscribed otherwise you will be unsubscribed.', 'pledgeball-client' );

		/**
		 * Allow "Updates" text to be filtered.
		 *
		 * @since 1.0
		 *
		 * @param string $updates The default "Updates" text.
		 */
		$updates = apply_filters( 'pledgeball_client/form/pledge_submit/updates_text', $updates );

		// Add styles.
		$markup .= $this->form_styles();

		// Start buffering.
		ob_start();

		// Now, instead of echoing, Shortcode output ends up in buffer.
		include PLEDGEBALL_CLIENT_PATH . 'assets/templates/forms/pledge-submit.php';

		// Save the output and flush the buffer.
		$markup .= ob_get_clean();

		// Enqueue Javascript.
		$this->form_scripts( $pledges );

		// --<
		return $markup;

	}

	/**
	 * Gets the basic styles for the Submit Pledge Form.
	 *
	 * @since 1.0
	 *
	 * @return str $styles The CSS for the Submit Pledge Form.
	 */
	public function form_styles() {

		// Define styles.
		$styles = '<style>

			#pledge_submit {
				border: 1px solid #999;
				padding: 0;
			}

			#pledge_submit h3 {
				padding: 1em;
				margin: 0;
				background: #eee;
			}

			#pledge_submit .pledgeball_notice {
				background: #fff;
				border: 1px solid #c3c4c7;
				border-left-width: 4px;
				box-shadow: 0 1px 1px rgb(0 0 0 / 4%);
				margin: 1em;
				padding: 1px 12px;
			}

			#pledge_submit .pledgeball_message {
				border-left-color: #00a32a;
			}

			#pledge_submit .pledgeball_notice p {
				margin: 0.5em 0;
				padding: 2px;
				line-height: 1.5;
			}

			#pledge_submit .pledgeball_error {
				display: none;
				border-left-color: #d63638;
			}

			#pledge_submit fieldset {
				border-top: 1px solid #999;
				padding: 0 1em 1em 1em;
			}

			#pledge_submit .pledgeball_main_label {
				display: inline-block;
				width: 20%;
				margin-right: 2em;
			}

			#pledge_submit .pledgeball_main_input {
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

			#pledge_submit .pledgeball_other_input {
				width: 90%;
			}

			#pledge_submit .pledgeball_pledges {
				height: 400px;
				overflow-x: scroll;
				padding: 1em;
				border: 1px solid #ddd;
			}

			#pledge_submit ul {
				list-style: none;
				padding-left: 0;
				margin-left: 1.6em;
			}

			#pledge_submit li {
				list-style: none;
				text-indent: -1.6em;
			}

			#pledge_submit h5 {
				border-top: 1px solid #ddd;
				padding-top: 1em;
			}

			#pledge_submit h5:first-child {
				border-top: none;
				margin-top: 0;
				padding-top: 0;
			}

			#pledge_submit input[type="checkbox"] {
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

			#pledge_submit input[type="checkbox"]:checked::before {
				float: left;
				display: inline-block;
				vertical-align: middle;
				width: 1rem;
				speak: never;
				-webkit-font-smoothing: antialiased;
				-moz-osx-font-smoothing: grayscale;
			}

			#pledge_submit input[type="checkbox"]:checked::before {
				/* Use the "Yes" SVG Dashicon */
				content: url("data:image/svg+xml;utf8,%3Csvg%20xmlns%3D%27http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%27%20viewBox%3D%270%200%2020%2020%27%3E%3Cpath%20d%3D%27M14.83%204.89l1.34.94-5.81%208.38H9.02L5.78%209.67l1.34-1.25%202.57%202.4z%27%20fill%3D%27%233582c4%27%2F%3E%3C%2Fsvg%3E");
				margin: -0.1875rem 0 0 -0.25rem;
				height: 1.3125rem;
				width: 1.3125rem;
			}

			#pledge_submit input[type="checkbox"]:focus {
				border-color: #2271b1;
				box-shadow: 0 0 0 1px #2271b1;
				/* Only visible in Windows High Contrast mode */
				outline: 2px solid transparent;
			}

			#pledge_submit input[type="checkbox"]:disabled,
			#pledge_submit input[type="checkbox"].disabled,
			#pledge_submit input[type="checkbox"]:disabled:checked:before,
			#pledge_submit input[type="checkbox"].disabled:checked:before {
				opacity: 0.7;
			}

			#pledge_submit ul label {
				font-weight: bold;
			}

			#pledge_submit .pledgeball_updates {
				margin-left: 1.8em;
				text-indent: -1.8em;
			}

			#pledge_submit .pledge_submit_button {
				padding: 1em;
			}

			#pledge_submit #pledge_submit_button {
				padding: 0.5em;
				font-size: 120%;
			}

			#pledge_submit .spinner {
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
				#pledge_submit .spinner {
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
		return apply_filters( 'pledgeball_client/form/pledge_submit/styles', $styles );

	}

	/**
	 * Enqueue the necessary scripts.
	 *
	 * @since 1.0
	 *
	 * @param array $pledges The array of all possible Pledges.
	 */
	public function form_scripts( $pledges ) {

		// Enqueue custom javascript.
		wp_enqueue_script(
			'pledge-submit-js',
			PLEDGEBALL_CLIENT_URL . 'assets/js/pledge-submit.js',
			[ 'jquery' ],
			PLEDGEBALL_CLIENT_VERSION,
			true // In footer.
		);

		// Init localisation.
		$localisation = [
			'field_required'  => __( 'Please complete the fields marked in red.', 'pledgeball-client' ),
			'pledge_required' => __( 'Please choose at least one Pledge.', 'pledgeball-client' ),
			'submit'          => __( 'Submit Pledge', 'pledgeball-client' ),
			'submitting'      => __( 'Submitting...', 'pledgeball-client' ),
		];

		// Init settings.
		$settings = [
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'pledges'  => $pledges,
		];

		// Localisation array.
		$vars = [
			'localisation' => $localisation,
			'settings'     => $settings,
		];

		// Localise the WordPress way.
		wp_localize_script(
			'pledge-submit-js',
			'Pledgeball_Form_Pledge_Submit_Settings',
			$vars
		);

	}

	// -------------------------------------------------------------------------

	/**
	 * Called when the "Submit Pledge" Form is submitted with Javascript.
	 *
	 * @since 1.0
	 */
	public function form_submitted_ajax() {

		// Default response.
		$data = [
			'notice' => __( 'Could not submit the Pledge. Please try again.', 'pledgeball-client' ),
			'saved'  => false,
		];

		// Skip if not AJAX submission.
		if ( ! wp_doing_ajax() ) {
			wp_send_json( $data );
		}

		// Since this is an AJAX request, check security.
		$result = check_ajax_referer( $this->nonce_ajax, false, false );
		if ( false === $result ) {
			$data['notice'] = __( 'Authentication failed. Could not submit the Pledge.', 'pledgeball-client' );
			wp_send_json( $data );
		}

		// Extract "First Name".
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$first_name_raw = isset( $_POST['first_name'] ) ? trim( wp_unslash( $_POST['first_name'] ) ) : '';
		$first_name     = sanitize_text_field( $first_name_raw );
		if ( empty( $first_name ) ) {
			$data['notice'] = __( 'Please enter a First Name.', 'pledgeball-client' );
			wp_send_json( $data );
		}

		// Extract "Last Name".
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$last_name_raw = isset( $_POST['last_name'] ) ? trim( wp_unslash( $_POST['last_name'] ) ) : '';
		$last_name     = sanitize_text_field( $last_name_raw );
		if ( empty( $last_name ) ) {
			$data['notice'] = __( 'Please enter a Last Name.', 'pledgeball-client' );
			wp_send_json( $data );
		}

		// Extract Email.
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$email_raw = isset( $_POST['email'] ) ? trim( wp_unslash( $_POST['email'] ) ) : '';
		$email     = sanitize_email( $email_raw );
		if ( empty( $email ) || ! is_email( $email ) ) {
			$data['notice'] = __( 'Please enter a valid Email Address.', 'pledgeball-client' );
			wp_send_json( $data );
		}

		// Extract Pledge IDs.
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$pledge_ids = isset( $_POST['pledge_ids'] ) ? stripslashes_deep( $_POST['pledge_ids'] ) : [];
		array_walk(
			$pledge_ids,
			function( &$item ) {
				$item = (int) trim( $item );
			}
		);
		if ( empty( $pledge_ids ) ) {
			$data['notice'] = __( 'Please choose at least one Pledge.', 'pledgeball-client' );
			wp_send_json( $data );
		}

		// Extract Consent.
		$consent = false;
		if ( isset( $_POST['consent'] ) ) {
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			$consent_raw = wp_unslash( $_POST['consent'] );
			if ( 'true' === $consent_raw ) {
				$consent = true;
			}
		}
		if ( false === $consent ) {
			$data['notice'] = __( 'Cannot submit your Pledge unless you consent to us storing your data.', 'pledgeball-client' );
			wp_send_json( $data );
		}

		// Extract Mailing List.
		$okemails = 0;
		if ( isset( $_POST['okemails'] ) ) {
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			$okemails_raw = wp_unslash( $_POST['okemails'] );
			if ( 'true' === $okemails_raw ) {
				$okemails = 1;
			}
		}

		// Extract "Other" value.
		$other = '';
		if ( isset( $_POST['other'] ) ) {
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			$other_raw = trim( wp_unslash( $_POST['other'] ) );
			$other     = sanitize_text_field( $other_raw );
		}

		// Let's format the Pledges properly.
		$pledges = [];
		foreach ( $pledge_ids as $pledge_id ) {
			// Maybe apply the "Other" value.
			$other_value = '';
			if ( 66 === $pledge_id ) {
				$other_value = $other;
			}
			// Apply formatting.
			$pledges[] = [
				'pledgenumber' => $pledge_id,
				'other'        => $other_value,
			];
		}

		// Let's make an array of submission data.
		$submission = [
			'firstname' => $first_name,
			'lastname'  => $last_name,
			'email'     => $email,
			'pledges'   => $pledges,
			'okemails'  => $okemails,
		];

		/*
		// Submit the Standalone Pledge.
		$response = $this->plugin->remote->pledge_create( $submission );
		if ( $response === false ) {
			// TODO: Alter this fake success response.
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
		do_action( 'pledgeball_client/form/pledge_submit/submission', $submission, $response );

		// Data response.
		$data = [
			'message' => __( 'Your Pledge has been submitted. Thanks for taking part!', 'pledgeball-client' ),
			'saved'   => true,
		];

		// Return the data.
		wp_send_json( $data );

	}

	/**
	 * Called when the "Submit Pledge" Form is submitted without Javascript.
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

		// Extract "First Name".
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$first_name_raw = isset( $_POST['pledgeball_first_name'] ) ? trim( wp_unslash( $_POST['pledgeball_first_name'] ) ) : '';
		$first_name     = sanitize_text_field( $first_name_raw );
		if ( empty( $first_name ) ) {
			$this->form_redirect( [ 'error' => 'no-first-name' ] );
		}

		// Extract "Last Name".
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$last_name_raw = isset( $_POST['pledgeball_last_name'] ) ? trim( wp_unslash( $_POST['pledgeball_last_name'] ) ) : '';
		$last_name     = sanitize_text_field( $last_name_raw );
		if ( empty( $last_name ) ) {
			$this->form_redirect( [ 'error' => 'no-last-name' ] );
		}

		// Extract Email.
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$email_raw = isset( $_POST['pledgeball_email'] ) ? trim( wp_unslash( $_POST['pledgeball_email'] ) ) : '';
		$email     = sanitize_email( $email_raw );
		if ( empty( $email ) ) {
			$this->form_redirect( [ 'error' => 'no-email' ] );
		}

		// Extract Pledge IDs.
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$pledge_ids = isset( $_POST['pledgeball_ids'] ) ? stripslashes_deep( $_POST['pledgeball_ids'] ) : [];
		if ( empty( $pledge_ids ) ) {
			$this->form_redirect( [ 'error' => 'no-pledges' ] );
		}
		array_walk(
			$pledge_ids,
			function( &$item ) {
				$item = (int) trim( $item );
			}
		);

		// Extract Consent.
		$consent = false;
		if ( isset( $_POST['pledgeball_consent'] ) ) {
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			$consent_raw = (int) wp_unslash( $_POST['pledgeball_consent'] );
			if ( 1 === $consent_raw ) {
				$consent = true;
			}
		}
		if ( false === $consent ) {
			$this->form_redirect( [ 'error' => 'no-consent' ] );
		}

		// Extract Mailing List.
		$okemails = 0;
		if ( isset( $_POST['pledgeball_updates'] ) ) {
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			$okemails_raw = wp_unslash( $_POST['pledgeball_updates'] );
			if ( 1 === (int) trim( $okemails_raw ) ) {
				$okemails = 1;
			}
		}

		// Extract "Other" value.
		$other = '';
		if ( isset( $_POST['pledgeball_other'] ) ) {
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			$other_raw = trim( wp_unslash( $_POST['pledgeball_other'] ) );
			$other     = sanitize_text_field( $other_raw );
		}

		// Let's format the Pledges properly.
		$pledges = [];
		foreach ( $pledge_ids as $pledge_id ) {
			// Maybe apply the "Other" value.
			$other_value = '';
			if ( 66 === $pledge_id ) {
				$other_value = $other;
			}
			// Apply formatting.
			$pledges[] = [
				'pledgenumber' => $pledge_id,
				'other'        => $other_value,
			];
		}

		// Let's make an array of submission data.
		$submission = [
			'firstname' => $first_name,
			'lastname'  => $last_name,
			'email'     => $email,
			'pledges'   => $pledges,
			'consent'   => $consent,
			'okemails'  => $okemails,
		];

		/*
		// Submit the Pledge.
		$response = $this->plugin->remote->pledge_create( $submission );
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
		do_action( 'pledgeball_client/form/pledge_submit/submission', $submission, $response );

		// Our array of arguments.
		$args = [
			'submitted' => 'true',
		];

		// Redirect.
		$this->form_redirect( $args );

	}

	/**
	 * Redirects after the "Submit Pledge" Form is submitted.
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
