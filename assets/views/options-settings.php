<?php

/**
 * File: Chimplet Application
 *
 * @package Locomotive\Chimplet
 * @version 2015-02-07
 * @since   0.0.0 (2015-02-07)
 *
 * @uses    array $args
 */

extract( $args );

$mailchimp_key = '';

$active   = ! empty( $mailchimp_key );
$nonce    = ( $active ? 'deactivate_mailchimp_api_key' : 'activate_mailchimp_api_key' );
$button   = ( $active ? __('Deactivate Key', 'acf') : __('Activate Key', 'acf') );
$readonly = ( $active ? ' readonly="readonly"' : '' );

?>

	<div class="chimplet-box">
		<div class="title">
			<h3><?php _e('MailChimp API Key', 'chimplet'); ?></h3>
		</div>
		<div class="inner">
			<p><?php _e('Save your Mailchimp API Key in the box below. You can find this special key by logging into the MailChimp website.  Once logged in, click on your username (on the left, under the MailChimp image).  From the popup menu, choose <em>Account Settings</em>, then <em>Extras</em>, then <em>API Keys</em>.', 'chimplet'); ?></p>
			<p><?php _e('Once the API Key is saved, you will see the various options that AutoChimp provides.', 'chimplet'); ?></p>
			<form action="" method="post">
				<div class="chimplet-hidden">
					<input type="hidden" name="_chimpletnonce" value="<?php echo wp_create_nonce( $nonce ); ?>" />
				</div>
				<table class="form-table">
					<tbody>
						<tr>
							<th>
								<label for="chimplet-field-acf_pro_licence"><?php _e('License Key', 'chimplet'); ?></label>
							</th>
							<td>
								<input type="text" id="chimplet-mailchimp-api-key" name="chimplet[mailchimp][api][key]"<?php echo $readonly; ?> />
							</td>
						</tr>
						<tr>
							<th></th>
							<td>
								<input type="submit" value="<?php echo $button; ?>" class="chimplet-button blue">
							</td>
						</tr>
					</tbody>
				</table>
			</form>
		</div>
	</div>
