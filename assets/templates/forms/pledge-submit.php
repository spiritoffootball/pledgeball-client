<?php
/**
 * "Submit Pledge" Form template.
 *
 * Handles markup for the "Submit Pledge" Form.
 *
 * @package Pledgeball_Client
 * @since 1.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

?><form id="pledge_submit" method="post" action="">

	<h3><?php esc_html_e( 'Submit a Standalone Pledge', 'pledgeball-client' ); ?></h3>

	<div class="pledge_submit_inner">

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
		</fieldset>

		<fieldset>
			<h4><?php esc_html_e( 'Choose Your Pledges', 'pledgeball-client' ); ?></h4>
			<p><?php esc_html_e( 'Thank you for supporting PledgeBall and helping the planet!', 'pledgeball-client' ); ?></p>
			<div class="pledgeball_pledges">
				<?php foreach ( $build as $heading => $items ) : ?>
					<h5><?php echo $heading; ?></h5>
					<ul>
						<?php foreach ( $items as $item ) : ?>
							<li>
								<?php echo $item; ?>
								<?php if ( $heading === 'Other' ) : ?>
									<br><input type="text" class="pledgeball_main_input pledgeball_other_input" name="pledgeball_other" id="pledgeball_other" value="">
								<?php endif; ?>
							</li>
						<?php endforeach; ?>
					</ul>
				<?php endforeach; ?>
			</div>
		</fieldset>

		<fieldset>
			<h4><?php esc_html_e( 'Almost There', 'pledgeball-client' ); ?></h4>
			<p>
				<input type="checkbox" class="pledge_submit_consent" name="pledgeball_consent" id="pledgeball_consent" value="1">
				<label for="pledgeball_consent"><?php echo $consent; ?></label>
			</p>
			<p class="pledgeball_updates">
				<input type="checkbox" class="pledge_submit_consent" name="pledgeball_updates" id="pledgeball_updates" value="1">
				<label for="pledgeball_updates"><?php echo $updates; ?></label>
			</p>
		</fieldset>

		<div class="pledgeball_notice pledgeball_error"></div>

		<p class="pledge_submit_button">
			<input type="submit" value="<?php echo esc_html__( 'Submit Pledge', 'pledgeball-client' ); ?>" id="pledge_submit_button" data-security="<?php echo esc_attr( wp_create_nonce( $this->nonce_ajax ) ); ?>">
			<span class="spinner"></span>
		</p>

	</div>

</form>
