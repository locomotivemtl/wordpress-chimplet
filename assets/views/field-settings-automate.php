<?php

if ( empty( $args['chimplet_option'] ) ) {
	return;
}

$options = $this->get_option( $args['chimplet_option'], [] );

$match = ( empty( $options['automate'] ) || ! is_array( $options ) ) ? false : array_key_exists( 'automate', $options );

echo '<fieldset>';
echo '<p class="description">' . esc_html( $args['description'] ) . '</p>';

$field  = '<label for="%1$s">';
$field .= '<input type="checkbox" id="%1$s" name="%2$s" value="%3$s"' . checked( $match, true, false ) . ' autocomplete="off"%5$s />' . ' ';
$field .= '<span>%4$s</span>';
$field .= '</label>';

$value = esc_attr( 'on' );

printf(
	$field,
	esc_attr( $args['label_for'] ),
	esc_attr( $args['input_name'] ),
	$value,
	esc_html( $args['label_text'] ),
	( (string) $args['input_attr'] )
);

if ( $match && ( isset( $args['button_condition'] ) ? $args['button_condition'] : true ) ) {
	printf(
		'<p%5$s><button type="button" class="button" id="%1$s" data-automation="sync" data-xhr-action="%2$s" data-xhr-nonce="%3$s">%4$s</button></p>',
		esc_attr( $args['button_id'] ),
		esc_attr( $args['xhr_action'] ),
		esc_attr( $args['xhr_nonce'] ),
		esc_html( $args['button_text'] ),
		( (string) $args['button_attr'] )
	);

	if ( isset( $args['counter_value'] ) && isset( $args['counter_label'] ) ) {

		echo '<p class="chimplet-counter">';
		printf( $args['counter_label'], $args['counter_value'] );
		echo '</p>';

	}
}

echo '</fieldset>';
