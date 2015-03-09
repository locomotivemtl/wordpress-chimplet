<?php

/**
 * File: Chimplet Settings
 *
 * @package Locomotive\Chimplet\Views
 * @version 2015-03-03
 * @since   0.0.0 (2015-02-07)
 */

?>

<form action="options.php" method="POST" class="panel-body">
	<div class="chimplet-hidden">
		<?php $this->wp->settings_fields( $args['settings_group'] ); ?>
	</div>

	<?php

	$this->render_sections( $args['menu_slug'] );

	if ( $this->get_option( 'mailchimp.api_key' ) ) {

		$this->render_submit_button();

	}

	?>
</form>
