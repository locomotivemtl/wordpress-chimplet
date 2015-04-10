<?php

/**
 * File: Chimplet Campaign Templating Settings
 *
 * @package Locomotive\Chimplet\Views
 * @version 2015-03-03
 * @since   0.0.0 (2015-03-03)
 */

$options = $this->get_option( 'mailchimp.campaigns', [] );

// Use as basis for name attribute
$field_name = 'chimplet[mailchimp][campaigns]';

?>

<fieldset>
	<legend class="screen-reader-text"><span class="h4"><?php echo esc_html( $args['title'] ); ?></span></legend>
	<?php
	$id = 'mailchimp-campaigns-frequency';
	$templates = $this->mc->get_user_template();

	if ( empty( $templates ) ) {
		esc_html_e( 'No template found.', 'chimplet' );
	} else {
		printf(
			'<select id="%s" name="%s" autocomplete="off" data-condition-key="template">',
			esc_attr( $id ),
			esc_attr( $field_name ) . '[template]'
		);

		$options['template'] = ! empty( $options['template'] ) ? $options['template'] : '';

		foreach ( $templates as $template ) {
			printf(
				'<option value="%s"%s>%s</option>',
				esc_attr( $template['id'] ),
				selected( $options['template'], $template['id'], false ),
				esc_html( $template['name'] )
			);
		}

		echo '</select>';
	}
	?>
</fieldset>
