<?php

/**
 * File: Chimplet Campaign Scheduling Settings
 *
 * @package Locomotive\Chimplet\Views
 * @version 2015-03-30
 * @since   0.0.0 (2015-03-03)
 */

$options = $this->get_option( 'mailchimp.campaigns.schedule', [] );

// Use as basis for name attribute
$field_name = 'chimplet[mailchimp][campaigns]';

$options['frequency'] = ( isset( $options['frequency'] ) ? $options['frequency'] : 'daily' );

$is_daily   = false;
$is_weekly  = false;
$is_monthly = false;

$hidden_class = ' hidden';

switch ( $options['frequency'] ) {
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

$freq_options = $hourly_options = $monthday_options = $weekday_options = $weekday_checkboxes = '';

$date = new DateTime( 'next sunday' );
for ( $i = 1; $i <= 7; $i++ ) {

	$val = esc_attr( $date->format( 'N' ) );

	$weekday_options .= sprintf(
		'<option value="%s"%s>%s</option>',
		$val,
		selected( $val, ( $options['weekday'] || 1 ), false ),
		esc_html( $date->format( 'l' ) )
	);

	$match = ( empty( $options['days'] ) || ! is_array( $options ) ) ? ( 'daily' === $options['frequency'] ) : in_array( $val, $options['days'] );

	$weekday_checkboxes .= sprintf( '
		<label for="%2$s" title="%5$s">
			<input type="checkbox"
			       name="%3$s"
			       id="%2$s"
			       value="%1$s"%4$s>
			<span>%6$s</span>
		</label>' . "\n",
		$val,
		esc_attr( 'days' . $val ),
		esc_attr( $field_name . '[schedule][days][]' ),
		checked( $match, true, false ),
		esc_html( $date->format( 'l' ) ),
		esc_html( $date->format( 'D' ) )
	);

	$date->add( new DateInterval( 'P1D' ) );
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

			foreach ( $frequencies as $key => $name ) {
				$key = $key;
				$freq_options .= sprintf(
					'<option value="%s"%s>%s</option>',
					esc_attr( $key ),
					selected( $key, $options['frequency'], false ),
					esc_html( $name )
				);
			}

			printf(
				'<select id="%s" name="%s" autocomplete="off" data-condition-key="frequency">%s</select>',
				esc_attr( $id ),
				esc_attr( $name ),
				$freq_options
			);

			?>
		</div>
		<div class="chimplet-cell chimplet-1/4 chimplet-schedule-option chimplet-schedule-weekly<?php echo esc_attr( $is_weekly ? '' : $hidden_class ); ?>" data-condition-frequency="weekly">
			<?php

			$id   = 'mailchimp-campaigns-weekday';
			$name = $field_name . '[schedule][weekday]';

			printf(
				'<select id="%s" name="%s" autocomplete="off">%s</select>',
				esc_attr( $id ),
				esc_attr( $name ),
				$weekday_options
			);

			?>
		</div>
		<div class="chimplet-cell chimplet-1/4 chimplet-schedule-option chimplet-schedule-monthly<?php echo esc_attr( $is_monthly ? '' : $hidden_class ); ?>" data-condition-frequency="monthly">
			<?php

			$id   = 'mailchimp-campaigns-monthday';
			$name = $field_name . '[schedule][monthday]';

			$date = new DateTime( '2014-01-01' );
			for ( $i = 1; $i <= 29; $i++ ) { // 32

				if ( 29 === $i ) {
					$val  = -1;
					$text = esc_attr__( 'last day of the month', 'chimplet' );
				}
				else {
					$val  = $date->format( 'j' );
					$text = $date->format( 'jS' );
				}

				if ( $i > 29 ) {
					$text = sprintf( esc_attr__( '%s (not available in all months)', 'chimplet' ), $text );
				}

				$val = esc_attr( $val );

				$monthday_options .= sprintf(
					'<option value="%s"%s>%s</option>',
					$val,
					selected( $val, ( $options['monthday'] ?: 1 ), false ) . ( $i > 29 ? ' disabled' : '' ),
					esc_html( $text )
				);

				if ( 29 !== $i ) {
					$date->add( new DateInterval( 'P1D' ) );
				}
			}

			printf(
				'<select id="%s" name="%s" autocomplete="off">%s</select>',
				esc_attr( $id ),
				esc_attr( $name ),
				$monthday_options
			);

			?>
		</div>
		<div class="chimplet-cell chimplet-1/4 chimplet-schedule-option chimplet-schedule-hourly">
			<?php

			$id   = 'mailchimp-campaigns-hour';
			$name = $field_name . '[schedule][hour]';

			$date = new DateTime( 'midnight' );
			for ( $i = 0; $i <= 23; $i++ ) {

				$hourly_options .= sprintf(
					'<option value="%s"%s>%s</option>',
					esc_attr( $i ),
					selected( $i, ( $options['hour'] ?: 0 ), false ),
					esc_html( $date->format( 'H:i A' ) )
				);

				$date->add( new DateInterval( 'PT1H' ) );
			}

			printf(
				'<select id="%s" name="%s" autocomplete="off"></select>' . "\n" . $date->format( 'T' ),
				esc_attr( $id ),
				esc_attr( $name ),
				$hourly_options
			);

			?>
		</div>
		<p class="description clear"><?php esc_html_e( 'We’ll only send if there’s new content.', 'chimplet' ); ?></p>
	</div>
	<div class="chimplet-schedule-option chimplet-schedule-daily<?php echo esc_attr( $is_daily ? '' : $hidden_class ); ?>" data-condition-frequency="daily">
		<div class="chimplet-item-list chimplet-hl">
		<?php

		unesc( $weekday_checkboxes );

		?>
		</div>
		<p class="description"><?php esc_html_e( 'Send only on these days', 'chimplet' ); ?></p>
	</div>
</fieldset>
