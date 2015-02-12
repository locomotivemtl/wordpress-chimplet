<?php

/**
 * File: Chimplet Application
 *
 * @package Locomotive\Chimplet
 * @version 2015-02-10
 * @since   0.0.0 (2015-02-07)
 *
 * @uses    array $args
 */
use Locomotive\Chimplet\SettingsPage;

extract( $args );

?>

	<section class="chimplet-panel">
		<header class="panel-heading">
			<h3 class="panel-title"><?php esc_html_e( 'MailChimp API Management', 'chimplet' ); ?></h3>
		</header>
		<form action="options.php" method="POST" class="panel-body">
			<div class="chimplet-hidden">
				<?php settings_fields( SettingsPage::SETTINGS_KEY ); ?>
			</div>
			<?php do_settings_sections( $menu_slug ); ?>
			<?php submit_button( $button_label ); ?>
		</form>
	</section>
