/**
 * Pledgeball Pledge Submit Javascript.
 *
 * Implements functionality for the "Submit a Standalone Pledge" form.
 *
 * @since 1.0
 *
 * @package Pledgeball_Client
 */

/**
 * Create Pledgeball Pledge Submit object.
 *
 * This works as a "namespace" of sorts, allowing us to hang properties, methods
 * and "sub-namespaces" from it.
 *
 * @since 1.0
 */
var Pledgeball_Pledge_Submit = Pledgeball_Pledge_Submit || {};

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
	function Pledge_Submit_Settings() {

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
			if ( 'undefined' !== typeof Pledgeball_Form_Pledge_Submit_Settings ) {
				me.localisation = Pledgeball_Form_Pledge_Submit_Settings.localisation;
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
			if ( 'undefined' !== typeof Pledgeball_Form_Pledge_Submit_Settings ) {
				me.settings = Pledgeball_Form_Pledge_Submit_Settings.settings;
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
	function Pledge_Submit_Form() {

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

			me.first_name = $('#pledgeball_first_name');
			me.last_name = $('#pledgeball_last_name');
			me.email = $('#pledgeball_email');
			me.pledges = $('.pledge_checkbox');
			me.other = $('#pledgeball_other');
			me.okemails = $('#pledgeball_updates');
			me.consent = $('#pledgeball_consent');

			me.submit_button = $('#pledge_submit_button');
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
			 * Add a click event listener to Pledge checkboxes.
			 *
			 * @param {Object} event The event object.
			 */
			me.pledges.on( 'click', function( event ) {
				me.pledges.css( 'border-color', '#8c8f94' );
			});

			/**
			 * Add a click event listener to Consent checkbox.
			 *
			 * @param {Object} event The event object.
			 */
			me.consent.on( 'click', function( event ) {
				if ( ! me.consent.prop( 'checked' ) ) {
					me.submit_button.prop( 'disabled', true );
				} else {
					me.submit_button.prop( 'disabled', false );
				}
			});

			/**
			 * Add a click event listener to the Submit button.
			 *
			 * @param {Object} event The event object.
			 */
			me.submit_button.on( 'click', function( event ) {

				// Define vars.
				var ajax_nonce = me.submit_button.data( 'security' ),
					consent = me.consent.prop( 'checked' ),
					okemails = me.okemails.prop( 'checked' ),
					first_name = me.first_name.val(),
					last_name = me.last_name.val(),
					email = me.email.val(),
					other = me.other.val(),
					pledge_ids = [],
					data = {},
					submitting = Pledgeball_Pledge_Submit_Settings.get_localisation( 'submitting' ),
					field_required = Pledgeball_Pledge_Submit_Settings.get_localisation( 'field_required' ),
					pledge_required = Pledgeball_Pledge_Submit_Settings.get_localisation( 'pledge_required' );

				// Prevent form submission.
				if ( event.preventDefault ) {
					event.preventDefault();
				}

				// Probably redundant, but check Consent.
				me.consent.css( 'border-color', '#8c8f94' );
				if ( ! consent ) {
					me.consent.css( 'border-color', 'red' );
				}

				// Reset fields.
				me.pledges.css( 'border-color', '#8c8f94' );
				me.first_name.css( 'border-color', '#8c8f94' );
				me.last_name.css( 'border-color', '#8c8f94' );
				me.email.css( 'border-color', '#8c8f94' );

				// Check fields.
				if ( ! first_name ) {
					me.first_name.css( 'border-color', 'red' );
				}
				if ( ! last_name ) {
					me.last_name.css( 'border-color', 'red' );
				}
				if ( ! email ) {
					me.email.css( 'border-color', 'red' );
				}

				// Bail if fields fail basic validation.
				if ( ! consent || ! first_name || ! last_name || ! email ) {
					$('.pledgeball_error').html( '<p>' + field_required + '</p>' );
					$('.pledgeball_error').show();
					return false;
				}

				// Check Pledges.
				me.pledges.each( function( index ) {
					if ( $(this).prop( 'checked' ) ) {
						pledge_ids.push( $(this).val() );
					}
				});

				// Bail if there are no Pledges.
				if ( ! pledge_ids.length ) {
					me.pledges.css( 'border-color', 'red' );
					$('.pledgeball_error').html( '<p>' + pledge_required + '</p>' );
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
					action: 'pledgeball_pledge_submit',
					first_name: first_name,
					last_name: last_name,
					email: email,
					pledge_ids: pledge_ids,
					other: other,
					consent: consent,
					okemails: okemails,
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
			var url = Pledgeball_Pledge_Submit_Settings.get_setting( 'ajax_url' );

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

			var submit = Pledgeball_Pledge_Submit_Settings.get_localisation( 'submit' );

			if ( data.saved ) {

				// Convert to jQuery object.
				if ( $.parseHTML ) {
					markup = $( $.parseHTML( data.message ) );
				} else {
					markup = $(data.message);
				}

				// Replace Form with Message.
				$('.pledge_submit_inner').html( '<div class="pledgeball_notice pledgeball_message"><p>' + data.message + '</p></div>' );

				// Bring top of Form into view.
				var form_offset = $('#pledge_submit').offset();
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
	var Pledgeball_Pledge_Submit_Settings = new Pledge_Submit_Settings();
	var Pledgeball_Pledge_Submit_Form = new Pledge_Submit_Form();
	Pledgeball_Pledge_Submit_Settings.init();
	Pledgeball_Pledge_Submit_Form.init();

	/**
	 * Trigger dom_ready methods where necessary.
	 *
	 * @since 1.0
	 */
	$(document).ready(function($) {

		// The DOM is loaded now.
		Pledgeball_Pledge_Submit_Settings.dom_ready();
		Pledgeball_Pledge_Submit_Form.dom_ready();

	});

} )( jQuery );
