<?php

$options = $this->get_option( 'mailchimp.campaigns', [] );

?>
<p class="description"><?php esc_html_e( 'This plugin can automate the creation of rss campaigns using power sets of interest grouping.', 'chimplet' ); // @todo link to more explanation ?></p>
<fieldset>
		<legend><span class="h4"><?php esc_html_e( 'General', 'chimplet' ); ?></span></legend>
		<div class="chimplet-mc">
			<?php
			$id          = 'mailchimp-campaigns-automage';
			$field_name  = 'chimplet[mailchimp][campaigns]';
			$match       = ( empty( $options['automate'] ) || ! is_array( $options ) ) ? false : array_key_exists( 'automate', $options );
			?>
			<label for="<?php echo esc_attr( $id ); ?>">
				<input type="checkbox"
				       name="<?php echo esc_attr( $field_name ); ?>[automate]"
				       id="<?php echo esc_attr( $id ); ?>"
				       value="on" <?php checked( $match ); ?>>
				<span><?php esc_html_e( 'Automate campaigns creation', 'chimplet' ); ?></span>
			</label>
		</div>
</fieldset>
<fieldset>
	<legend><span class="h4"><?php esc_html_e( 'Frequency', 'chimplet' ); ?></span></legend>
	<div class="chimplet-mc">
		<?php
		$id    = 'mailchimp-campaigns-frequency';
		$frequencies = [
			'monthly' => __( 'Monthly', 'chimplet' ),
			'weekly'  => __( 'Weekly', 'chimplet' ),
			'daily'   => __( 'Daily', 'chimplet' ),
		];

		printf(
			'<select id="%s" name="%s" autocomplete="off">',
			esc_attr( $id ),
			esc_attr( $field_name ) . '[frequency]'
		);

		$options['frequency'] = isset( $options['frequency'] ) ? $options['frequency'] : '';

		foreach ( $frequencies as $key => $name ) {
			$key = $key;
			printf(
				'<option value="%s"%s>%s</option>',
				esc_attr( $key ),
				selected( $key, $options['frequency'], false ),
				esc_html( $name )
			);
		}

		echo '</select>';
		?>
	</div>
</fieldset>
<fieldset>
	<legend><span class="h4"><?php esc_html_e( 'RSS template in MailChimp', 'chimplet' ); ?></span></legend>
	<div class="chimplet-mc">
		<?php
		$id    = 'mailchimp-campaigns-frequency';
		$templates = $this->mc->get_user_template();

		if ( empty( $templates ) ) {
			esc_html_e( 'No template found.', 'chimplet' );
		} else {
			printf(
				'<select id="%s" name="%s" autocomplete="off">',
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
	</div>
</fieldset>