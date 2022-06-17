/**
 * Pledgeball "Create Event" Javascript.
 *
 * Implements functionality for the "Create an Event" form.
 *
 * @since 1.0
 *
 * @package Pledgeball_Client
 */

/**
 * Create Pledgeball Create Event object.
 *
 * This works as a "namespace" of sorts, allowing us to hang properties, methods
 * and "sub-namespaces" from it.
 *
 * @since 1.0
 */
var Pledgeball_Event_Create = Pledgeball_Event_Create || {};

/**
 * Pass the jQuery shortcut in.
 *
 * @since 1.0
 *
 * @param {Object} $ The jQuery object.
 */
( function( $ ) {

	/**
	 * Create Settings Object.
	 *
	 * @since 1.0
	 */
	function Event_Create_Settings() {

		// Prevent reference collisions.
		var me = this;

		/**
		 * Initialise Settings.
		 *
		 * This method should only be called once.
		 *
		 * @since 1.0
		 */
		this.init = function() {

			// Init localisation.
			me.init_localisation();

			// Init settings.
			me.init_settings();

		};

		/**
		 * Do setup when jQuery reports that the DOM is ready.
		 *
		 * This method should only be called once.
		 *
		 * @since 1.0
		 */
		this.dom_ready = function() {

		};

		// Init localisation array.
		me.localisation = [];

		/**
		 * Init localisation from settings object.
		 *
		 * @since 1.0
		 */
		this.init_localisation = function() {
			if ( 'undefined' !== typeof Pledgeball_Form_Event_Create_Settings ) {
				me.localisation = Pledgeball_Form_Event_Create_Settings.localisation;
			}
		};

		/**
		 * Getter for localisation.
		 *
		 * @since 1.0
		 *
		 * @param {String} The identifier for the desired localisation string.
		 * @return {String} The localised string.
		 */
		this.get_localisation = function( identifier ) {
			return me.localisation[identifier];
		};

		// Init settings array.
		me.settings = [];

		/**
		 * Init settings from settings object.
		 *
		 * @since 1.0
		 */
		this.init_settings = function() {
			if ( 'undefined' !== typeof Pledgeball_Form_Event_Create_Settings ) {
				me.settings = Pledgeball_Form_Event_Create_Settings.settings;
			}
		};

		/**
		 * Getter for retrieving a setting.
		 *
		 * @since 1.0
		 *
		 * @param {String} The identifier for the desired setting.
		 * @return The value of the setting.
		 */
		this.get_setting = function( identifier ) {
			return me.settings[identifier];
		};

	};

	/**
	 * Create Form Object.
	 *
	 * @since 1.0
	 */
	function Event_Create_Form() {

		// Prevent reference collisions.
		var me = this;

		/**
		 * Initialise Form.
		 *
		 * This method should only be called once.
		 *
		 * @since 1.0
		 */
		this.init = function() {

		};

		/**
		 * Do setup when jQuery reports that the DOM is ready.
		 *
		 * This method should only be called once.
		 *
		 * @since 1.0
		 */
		this.dom_ready = function() {

			// Set up methods.
			me.setup();
			me.listeners();

		};

		/**
		 * Set up Form instance.
		 *
		 * @since 1.0
		 */
		this.setup = function() {

			me.email = $('#pledgeball_email');
			me.title = $('#pledgeball_title');

			me.submit_button = $('#event_create_button');
			me.spinner = me.submit_button.next( '.spinner' );

			// Make form submit button disabled by default.
			me.submit_button.prop( 'disabled', true );
			me.spinner.css( 'visibility', 'hidden' );

		};

		/**
		 * Initialise listeners.
		 *
		 * This method should only be called once.
		 *
		 * @since 1.0
		 */
		this.listeners = function() {

			/**
			 * Add a click event listener to the Submit button.
			 *
			 * @param {Object} event The event object.
			 */
			me.submit_button.on( 'click', function( event ) {

				// Define vars.
				var ajax_nonce = me.submit_button.data( 'security' ),
					email = me.email.val(),
					title = me.title.val(),
					data = {},
					submitting = Pledgeball_Event_Create_Settings.get_localisation( 'submitting' ),
					field_required = Pledgeball_Event_Create_Settings.get_localisation( 'field_required' );

				// Prevent form submission.
				if ( event.preventDefault ) {
					event.preventDefault();
				}

				// Reset fields.
				me.title.css( 'border-color', '#8c8f94' );
				me.email.css( 'border-color', '#8c8f94' );

				// Check fields.
				if ( ! email ) {
					me.email.css( 'border-color', 'red' );
				}
				if ( ! title ) {
					me.title.css( 'border-color', 'red' );
				}

				// Bail if fields fail basic validation.
				if ( ! title || ! email ) {
					$('.pledgeball_error').html( '<p>' + field_required + '</p>' );
					$('.pledgeball_error').show();
					return false;
				}

				// Assign text to form submit button.
				me.submit_button.val( submitting );

				// Make form submit button disabled and show spinner.
				me.submit_button.prop( 'disabled', true );
				me.spinner.css( 'visibility', 'visible' );

				// Data received by WordPress.
				data = {
					action: 'pledgeball_event_create',
					first_name: first_name,
					last_name: last_name,
					email: email,
					title: title,
					_ajax_nonce: ajax_nonce
				};

				// Send the data to the server.
				me.send( data );

				// --<
				return false;

			});

		};

		/**
		 * Send AJAX request.
		 *
		 * @since 1.0
		 *
		 * @param {Array} data The array of data to submit.
		 */
		this.send = function( data ) {

			// Define vars.
			var url = Pledgeball_Event_Create_Settings.get_setting( 'ajax_url' );

			// Use jQuery post.
			$.post( url, data,

				/**
				 * AJAX callback which receives response from the server.
				 *
				 * Calls feedback method on success or shows an error in the console.
				 *
				 * @since 1.0
				 *
				 * @param {Mixed} response The received JSON data array.
				 * @param {String} textStatus The status of the response.
				 */
				function( response, textStatus ) {

					// Update if success, otherwise show error.
					if ( textStatus == 'success' ) {
						me.update( response );
					} else {
						if ( console.log ) {
							console.log( textStatus );
						}
					}

				},

				// Expected format.
				'json'

			);

		};

		/**
		 * Receive data from an AJAX request.
		 *
		 * @since 1.0
		 *
		 * @param {Array} data The data received from the server.
		 */
		this.update = function( data ) {

			var submit = Pledgeball_Event_Create_Settings.get_localisation( 'submit' );

			if ( data.saved ) {

				// Convert to jQuery object.
				if ( $.parseHTML ) {
					markup = $( $.parseHTML( data.message ) );
				} else {
					markup = $(data.message);
				}

				// Replace Form with Message.
				$('.event_create_inner').html( '<div class="pledgeball_notice pledgeball_message"><p>' + data.message + '</p></div>' );

				// Bring top of Form into view.
				var form_offset = $('#event_create').offset();
				$('html, body').stop().animate( { scrollTop: form_offset.top }, 500 );

			} else {

				// Show notice.
				$('.pledgeball_error').html( '<p>' + data.notice + '</p>' );
				$('.pledgeball_error').show();

				// Assign text to form submit button.
				me.submit_button.val( submit );

				// Make form submit button enabled and hide spinner.
				me.submit_button.prop( 'disabled', false );
				me.spinner.css( 'visibility', 'hidden' );

			}

		};

	};

	// Init Settings and Form classes.
	var Pledgeball_Event_Create_Settings = new Event_Create_Settings();
	var Pledgeball_Event_Create_Form = new Event_Create_Form();
	Pledgeball_Event_Create_Settings.init();
	Pledgeball_Event_Create_Form.init();

	/**
	 * Trigger dom_ready methods where necessary.
	 *
	 * @since 1.0
	 */
	$(document).ready(function($) {

		// The DOM is loaded now.
		Pledgeball_Event_Create_Settings.dom_ready();
		Pledgeball_Event_Create_Form.dom_ready();

	});

} )( jQuery );
