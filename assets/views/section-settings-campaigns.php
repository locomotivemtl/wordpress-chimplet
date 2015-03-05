<?php

/**
 * File: Chimplet Campaign Settings
 *
 * @package Locomotive\Chimplet\Views
 * @version 2015-03-03
 * @since   0.0.0 (2015-02-28)
 */

$options = $this->get_option( 'mailchimp.campaigns', [] );

// Use as basis for name attribute
$field_name = 'chimplet[mailchimp][campaigns]';

?>

<fieldset>
	<legend class="screen-reader-text"><span class="h4"><?php echo esc_html( $args['title'] ); ?></span></legend>
	<p class="description"><?php esc_html_e( 'We’ll only send if there’s new content.', 'chimplet' ); ?></p>
	<div class="chimplet-item-list chimplet-hl">
		<div class="chimplet-cell chimplet-1/4 chimplet-schedule-option chimplet-schedule-frequency">
			<?php
			$id   = 'mailchimp-campaigns-frequency';
			$name = $field_name . '[schedule][frequency]';

			$frequencies = [
				'daily'   => __( 'Every Day', 'chimplet' ),
				'weekly'  => __( 'Every Week', 'chimplet' ),
				'monthly' => __( 'Every Month', 'chimplet' ),
			];

			printf(
				'<select id="%s" name="%s" autocomplete="off" data-condition-set="frequency">',
				esc_attr( $id ),
				esc_attr( $name )
			);

			$options['schedule']['frequency'] = ( isset( $options['schedule']['frequency'] ) ? $options['schedule']['frequency'] : '' );

			foreach ( $frequencies as $key => $name ) {
				$key = $key;
				printf(
					'<option value="%s"%s>%s</option>',
					esc_attr( $key ),
					selected( $key, $options['schedule']['frequency'], false ),
					esc_html( $name )
				);
			}

			echo '</select>';
			?>
		</div>
		<div class="chimplet-cell chimplet-1/4 chimplet-schedule-option chimplet-schedule-daily /*hide-if-js*/" data-condition-frequency="daily">
			<?php

			$id   = 'mailchimp-campaigns-schedule_hour';
			$name = $field_name . '[schedule][schedule_hour]';

			printf(
				'<select id="%s" name="%s" autocomplete="off">',
				esc_attr( $id ),
				esc_attr( $name )
			);

			$date = new DateTime( 'midnight' );
			for ( $i = 0; $i <= 23; $i++ ) {

				printf(
					'<option value="%s"%s>%s</option>',
					esc_attr( $i ),
					selected( $i, $options['schedule']['schedule_hour'], false ),
					esc_html( $date->format( 'H:i A' ) )
				);

				$date->add( new DateInterval( 'PT1H' ) );
			}

			echo '</select>' . "\n" . $date->format( 'T' );

			?>
		</div>
	</div>
	<div class="chimplet-schedule-option chimplet-schedule-daily /*hide-if-js*/" data-condition-frequency="daily">
		<p class="description"><?php esc_html_e( 'Send only on these days', 'chimplet' ); ?></p>
		<div class="chimplet-item-list chimplet-hl">
		<?php

		$timestamp = strtotime( 'next Sunday' );
		for ( $i = 1; $i <= 7; $i++ ) {

			$id    = 'days' . $i;
			$name  = esc_attr( $field_name . '[schedule][days][]' );
			$match = ( empty( $options['schedule']['days'] ) || ! is_array( $options ) ) ? ( 'daily' === $options['schedule']['frequency'] ) : in_array( $i, $options['schedule']['days'] );
			?>
			<label for="<?php echo esc_attr( $id ); ?>" title="<?php echo esc_attr( strftime( '%A', $timestamp ) ); ?>">
				<input type="checkbox"
				       name="<?php echo esc_attr( $name ); ?>"
				       id="<?php echo esc_attr( $id ); ?>"
				       value="<?php echo esc_attr( $i ); ?>" <?php checked( $match ); ?>>
				<span><?php echo esc_html( strftime( '%a', $timestamp ) ); ?></span>
			</label>
			<?php

			$timestamp = strtotime( '+1 day', $timestamp );
		}

		?>
		</div>
	</div>
</fieldset>

<fieldset>
	<legend><span class="h4"><?php esc_html_e( 'RSS Template in MailChimp', 'chimplet' ); ?></span></legend>
	<div class="chimplet-mc chimplet-1/3">
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
