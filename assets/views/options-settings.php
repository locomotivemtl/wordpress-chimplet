<?php

/**
 * File: Chimplet Application
 *
 * @package Locomotive\Chimplet
 * @version 2015-02-12
 * @since   0.0.0 (2015-02-07)
 *
 * @uses    array $args
 */

?>

<section class="chimplet-panel">
	<header class="panel-heading">
		<h3 class="panel-title"><?php esc_html_e( 'MailChimp API Management', 'chimplet' ); ?></h3>
	</header>
	<form action="options.php" method="POST" class="panel-body">
		<div class="chimplet-hidden">
			<?php $this->wp->settings_fields( $args['settings_group'] ); ?>
		</div>
		<?php $this->wp->do_settings_sections( $args['menu_slug'] ); ?>
		<?php $this->wp->submit_button( $args['button_label'] ); ?>
	</form>
</section>
