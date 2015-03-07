<?php

/**
 * File: Chimplet Campaign Scheduling Settings
 *
 * @package Locomotive\Chimplet\Views
 * @version 2015-03-03
 * @since   0.0.0 (2015-03-03)
 */

$options = $this->get_option( 'mailchimp.campaigns', [] );

// Use as basis for name attribute
$field_name = 'chimplet[mailchimp][campaigns]';

$options['schedule']['frequency'] = ( isset( $options['schedule']['frequency'] ) ? $options['schedule']['frequency'] : 'daily' );

$is_daily   = false;
$is_weekly  = false;
$is_monthly = false;

$hidden_class = ' hidden';

switch ( $options['schedule']['frequency'] ) {
	case 'daily':
		$is_daily = true;
		break;

	case 'weekly':
		$is_weekly = true;
		break;

	case 'monthly':
		$is_monthly = true;
		break;
}

?>

<fieldset>
	<legend class="screen-reader-text"><span class="h4"><?php echo esc_html( $args['title'] ); ?></span></legend>
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
				'<select id="%s" name="%s" autocomplete="off" data-condition-key="frequency">',
				esc_attr( $id ),
				esc_attr( $name )
			);

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
		<div class="chimplet-cell chimplet-1/4 chimplet-schedule-option chimplet-schedule-daily<?php echo ( $is_daily ? '' : $hidden_class ); ?>" data-condition-frequency="daily">
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
		<p class="description clear"><?php esc_html_e( 'We’ll only send if there’s new content.', 'chimplet' ); ?></p>
	</div>
	<div class="chimplet-schedule-option chimplet-schedule-daily<?php echo ( $is_daily ? '' : $hidden_class ); ?>" data-condition-frequency="daily">
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
		<p class="description"><?php esc_html_e( 'Send only on these days', 'chimplet' ); ?></p>
	</div>
</fieldset>
