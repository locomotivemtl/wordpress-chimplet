<?php

/**
 * File: Chimplet User Role Settings
 *
 * @package Locomotive\Chimplet\Views
 * @version 2015-03-06
 * @since   0.0.0 (2015-02-28)
 */

use Locomotive\Chimplet\SettingsPage;

?>

<p class="description"><?php
	printf(
		esc_html__( 'All users of the chosen roles will be synced with MailChimp and added as %s merge field.', 'chimplet' ),
		'<samp>' . esc_html__( 'WP_ROLE', 'chimplet' ) . '</samp>'
	);
?></p>

<?php

$local_roles = $this->get_option( 'mailchimp.user_roles', [] );
$roles       = $this->wp->get_editable_roles();

if ( empty( $roles ) ) {
	return;
}
else {
	$roles_key = array_keys( $roles );
}

$merge_var         = $this->mc->get_merge_var( SettingsPage::USER_ROLE_MERGE_VAR );
$merge_var_choices = isset( $merge_var['choices'] ) ?  $merge_var['choices'] : [];
?>
<fieldset>
	<div class="chimplet-item-list chimplet-mc chimplet-1/3">
		<?php
		$id    = 'cb-select-user-roles-all';
		$name  = 'chimplet[mailchimp][user_roles][]';
		$match = in_array( 'all', $local_roles );
		?>
		<label for="cb-select-user-roles-all">
			<input type="checkbox"
			       name="<?php echo esc_attr( $name ); ?>"
			       id="<?php echo esc_html( $id ); ?>"
			       value="all" <?php checked( $match ); ?>>
			<span><?php esc_html_e( 'Select All/None', 'chimplet' ); ?></span>
		</label>
		<?php
		foreach ( $roles_key as $role ) :
			$id    = "cb-select-user-roles-$role";
			$name  = 'chimplet[mailchimp][user_roles][]';
			$match = in_array( $role, $local_roles );

			$is_synced = in_array( $role, $merge_var_choices );
			$group_status = sprintf(
				'<span class="chimplet-sync dashicons dashicons-%s" title="%s"></span>',
				$is_synced ? 'yes' : 'no',
				$is_synced ? esc_attr__( 'Role is synced with MailChimp merge var.', 'chimplet' ) : esc_attr__( "Role isn't synced with MailChimp merge var field.", 'chimplet' )
			);
			?>
			<label for="<?php echo esc_attr( $id ); ?>">
				<input type="checkbox"
				       name="<?php echo esc_attr( $name ); ?>"
				       id="<?php echo esc_attr( $id ); ?>"
				       value="<?php echo esc_attr( $role ); ?>" <?php checked( $match || $is_synced ); ?>>
				<span><?php echo esc_html( $roles[ $role ]['name'] ); ?></span>
				<?php echo $group_status; //xss ok ?>
			</label>
		<?php endforeach; ?>
	</div>
</fieldset>
