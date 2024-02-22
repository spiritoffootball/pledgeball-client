<?php
/**
 * "Create Event" Form template.
 *
 * Handles markup for the "Create Event" Form.
 *
 * @package Pledgeball_Client
 * @since 1.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

?><form id="event_create" method="post" action="">

	<h3><?php esc_html_e( 'Create an Event', 'pledgeball-client' ); ?></h3>

	<div class="event_create_inner">

		<?php wp_nonce_field( $this->nonce_action, $this->nonce_name, true ); ?>
		<?php wp_original_referer_field(); ?>

		<fieldset>
			<h4><?php esc_html_e( 'Your Details', 'pledgeball-client' ); ?></h4>
			<p>
				<label class="pledgeball_main_label" for="pledgeball_first_name"><?php esc_html_e( 'First Name', 'pledgeball-client' ); ?></label>
				<input type="text" class="pledgeball_main_input" name="pledgeball_first_name" id="pledgeball_first_name" value="">
			</p>
			<p>
				<label class="pledgeball_main_label" for="pledgeball_last_name"><?php esc_html_e( 'Last Name', 'pledgeball-client' ); ?></label>
				<input type="text" class="pledgeball_main_input" name="pledgeball_last_name" id="pledgeball_last_name" value="">
			</p>
			<p>
				<label class="pledgeball_main_label" for="pledgeball_email"><?php esc_html_e( 'Email Address', 'pledgeball-client' ); ?></label>
				<input type="email" class="pledgeball_main_input pledgeball_input_email" name="pledgeball_email" id="pledgeball_email" value="">
			</p>
			<p>
				<label class="pledgeball_main_label" for="pledgeball_phone"><?php esc_html_e( 'Phone Number', 'pledgeball-client' ); ?></label>
				<input type="text" class="pledgeball_main_input pledgeball_input_phone" name="pledgeball_phone" id="pledgeball_phone" value="">
			</p>
		</fieldset>

		<fieldset>
			<h4><?php esc_html_e( 'Event Details', 'pledgeball-client' ); ?></h4>
			<p>
				<label class="pledgeball_main_label" for="pledgeball_title"><?php esc_html_e( 'Title', 'pledgeball-client' ); ?></label>
				<input type="text" class="pledgeball_main_input pledgeball_input_title" name="pledgeball_title" id="pledgeball_title" value="">
			</p>
			<p>
				<label class="pledgeball_main_label" for="pledgeball_description"><?php esc_html_e( 'Description', 'pledgeball-client' ); ?></label>
				<textarea class="pledgeball_textarea_description" name="pledgeball_description" id="pledgeball_description" rows="8" columns="80"></textarea>
			</p>
			<p>
				<label class="pledgeball_main_label" for="pledgeball_start"><?php esc_html_e( 'Start', 'pledgeball-client' ); ?></label>
				<input type="datetime-local" class="pledgeball_main_input pledgeball_input_start" name="pledgeball_start" id="pledgeball_start" value="">
			</p>
			<p>
				<label class="pledgeball_main_label" for="pledgeball_end"><?php esc_html_e( 'End', 'pledgeball-client' ); ?></label>
				<input type="datetime-local" class="pledgeball_main_input pledgeball_input_end" name="pledgeball_end" id="pledgeball_end" value="">
			</p>
		</fieldset>

		<div class="pledgeball_notice pledgeball_error"></div>

		<p class="event_create_button">
			<input type="submit" value="<?php echo esc_html__( 'Create Event', 'pledgeball-client' ); ?>" id="event_create_button" data-security="<?php echo esc_attr( wp_create_nonce( $this->nonce_ajax ) ); ?>">
			<span class="spinner"></span>
		</p>

	</div>

</form>
