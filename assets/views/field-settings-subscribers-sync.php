<?php

$options = $this->get_option( 'mailchimp.subscribers', [] );

$match = ( empty( $options['automate'] ) || ! is_array( $options ) ) ? false : array_key_exists( 'automate', $options );

echo '<fieldset>';
echo '<p class="description">' . esc_html__( 'Chimplet can automatically sync subscribers of the above user roles with the MailChimp list selected.', 'chimplet' ) . '</p>';


$field  = '<label for="%1$s">';
$field .= '<input type="checkbox" id="%1$s" name="%2$s" value="%3$s"' . checked( $match, true, false ) . ' autocomplete="off"/>' . ' ';
$field .= '<span>%4$s</span>';
$field .= '</label>';

printf(
	$field,
	esc_attr( $args['label_for'] ),
	esc_attr( 'chimplet[mailchimp][subscribers][automate]' ),
	esc_attr( 'on' ),
	esc_html__( 'Automate subscribers synchronization', 'chimplet' )
);

echo '</fieldset>';
