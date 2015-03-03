<?php

/**
 * File: Chimplet MailChimp Integration Guidance
 *
 * @package Locomotive\Chimplet\Views
 * @version 2015-03-03
 * @since   0.0.0 (2015-02-28)
 */

if ( empty( $args['mailchimp']['api_key'] ) ) {

?>
	<p><?php _e( 'To integrate your blog with your MailChimp account, you need to generate an API key.', 'chimplet' ); ?></p>
	<aside class="panel-assistance inset">
		<p><?php
			printf(
				esc_html__( 'Users with Admin or Manager permissions can generate and view API keys. You can %s from your Account Panel.', 'chimplet' ),
				'<a target="_blank" href="' . '//kb.mailchimp.com/accounts/management/about-api-keys#Find-or-Generate-Your-API-Key' . '">' . esc_html__( 'find or generate an API key', 'chimplet' ) . '</a>'
			);
			?></p>
		<ol>
			<li><?php printf( esc_html__( 'Click your profile name to expand the Account Panel, and choose %1$s.', 'chimplet' ), '<em>' . __( 'Account', 'chimplet' ) . '</em>' ); ?></li>
			<li><?php printf( esc_html__( 'Click the %1$s drop-down menu and choose %2$s.', 'chimplet' ), '<em>' . esc_html__( 'Extras', 'chimplet' ) . '</em>', '<em>' . __( 'API keys', 'chimplet' ) . '</em>' ); ?></li>
			<li><?php printf( esc_html__( 'Copy an existing API key or click the %1$s button.', 'chimplet' ), '<em>' . esc_html__( 'Create A Key', 'chimplet' ) . '</em>' ); ?></li>
			<li><?php esc_html_e( 'Name your key descriptively, so you know what application uses that key.', 'chimplet' ); ?></li>
		</ol>
	</aside>
	<p><?php esc_html_e( 'Once the API Key is integrated with Chimplet, you will be provided with additional options.', 'chimplet' ); ?></p>
<?php

}
else {

	?>
	<p><?php esc_html_e( 'With an integrated API Key, additional options are provided below.', 'chimplet' ); ?></p>
	<p><?php esc_html_e( 'Removing the API Key will disable Chimplet’s data synchronization features and no longer provides access to your account to manage your subscribers and campaigns. This does not delete any data from your MailChimp nor does it disable Post Category feeds and the active RSS-Driven Campaigns.', 'chimplet' ); ?></p>
<?php

}
