<?php

/**
 * File: Chimplet Application
 *
 * @package Locomotive\Chimplet
 * @version 2015-02-09
 * @since   0.0.0 (2015-02-07)
 *
 * @uses    array $args
 */

extract( $args );

$options = $this->get_options();
$inputs  = $this->get_submitted_values();

$mailchimp_key = $this->get_option('mailchimp-api-key');

$active = ! empty( $mailchimp_key );

if ( $active ) {

	$button_label = null;

}
else {

	$button_label = __('Save API Key', 'chimplet');

}

?>

	<section class="chimplet-panel">
		<header class="panel-heading">
			<h3 class="panel-title"><?php _e('MailChimp API Management', 'chimplet'); ?></h3>
		</header>
		<form action="options.php" method="POST" class="panel-body">
			<div class="chimplet-hidden">
				<?php settings_fields( 'chimplet-mailchimp' ); ?>
			</div>
			<?php do_settings_sections( $menu_slug ); ?>
			<?php submit_button( $button_label ); ?>
		</form>
	</section>
