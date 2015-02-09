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

	$button = (object) [
		'label' => __('Disable API Key', 'chimplet'),
		'name'  => 'deactivate',
		'id'    => 'chimplet-save-mailchimp-api-key'
	];

	$readonly = ' readonly="readonly"';

}
else {

	$button = (object) [
		'label' => __('Save API Key', 'chimplet'),
		'name'  => 'activate',
		'id'    => 'chimplet-remove-mailchimp-api-key'
	];

	$readonly = '';

}

?>

	<section class="chimplet-panel">
		<header class="panel-heading">
			<h3 class="panel-title"><?php _e('MailChimp API Management', 'chimplet'); ?></h3>
		</header>
		<div class="panel-body">
<?php

if ( $active ) {

?>
			<p><?php _e('With an integrated API Key, additional options are provided below.', 'chimplet'); ?></p>
			<p><?php _e('Removing the API Key will disable Chimplet’s features and no longer have access to your account to manage your subscribers and campaigns.', 'chimplet'); ?></p>
<?php

}
else {

?>
			<p><?php _e('To integrate your blog with your MailChimp account, you need to generate an API key.', 'chimplet'); ?></p>
			<aside class="panel-assistance inset">
				<p><?php
					printf(
						__('Users with Admin or Manager permissions can generate and view API keys. You can %s from your Account Panel.', 'chimplet'),
						'<a target="_blank" href="' . '//kb.mailchimp.com/accounts/management/about-api-keys#Find-or-Generate-Your-API-Key' . '">' . __('find or generate an API key', 'chimplet') . '</a>'
					);
				?></p>
				<ol>
					<li><?php printf( __('Click your profile name to expand the Account Panel, and choose %1$s.', 'chimplet'), '<em>' . __('Account') . '</em>' ); ?></li>
					<li><?php printf( __('Click the %1$s drop-down menu and choose %2$s.', 'chimplet'), '<em>' . __('Extras') . '</em>', '<em>' . __('API keys') . '</em>' ); ?></li>
					<li><?php printf( __('Copy an existing API key or click the %1$s button.', 'chimplet'), '<em>' . __('Create A Key') . '</em>' ); ?></li>
					<li><?php _e('Name your key descriptively, so you know what application uses that key.', 'chimplet'); ?></li>
				</ol>
			</aside>
			<p><?php _e('Once the API Key is integrated with Chimplet, you will be provided with additional options.', 'chimplet'); ?></p>
<?php

}

?>

			<form action="" method="post">
				<div class="chimplet-hidden">
					<input type="hidden" name="_chimpletnonce" value="<?php echo wp_create_nonce( $this->nonce ); ?>" />
				</div>
				<table class="form-table">
					<tbody>
						<tr>
							<th>
								<label for="chimplet-field-mailchimp-api_key"><?php _e('API Key', 'chimplet'); ?></label>
							</th>
							<td>
								<input type="text" class="regular-text" id="chimplet-field-mailchimp-api_key" name="chimplet[mailchimp][api_key]"<?php echo $readonly; ?> />
							</td>
						</tr>
						<tr>
							<th></th>
							<td>
								<input type="submit" name="<?php echo $button->name; ?>" id="<?php echo $button->id; ?>" class="chimplet-button button button-primary" value="<?php echo $button->label; ?>">
							</td>
						</tr>
					</tbody>
				</table>
			</form>
		</div>
	</section>
