<?php

/**
 * File: Chimplet Subscriber List Settings
 *
 * @package Locomotive\Chimplet\Views
 * @version 2015-03-03
 * @since   0.0.0 (2015-02-28)
 */

$value = $this->get_option( 'mailchimp.list' );
$lists = $this->mc->get_all_lists();
$total = $this->mc->get_current_list_total_results();

if ( $lists instanceof \Mailchimp_Error ) {
	if ( $lists->getMessage() ) {
		echo '<p class="chimplet-alert alert-error">' . esc_html( $lists->getMessage() ) . '</p>';
	} else {
		echo '<p class="chimplet-alert alert-error">' . esc_html__( 'An unknown error occurred while fetching the Mailing Lists from your account.', 'chimplet' ) . '</p>';
	}

	$lists = [];
}

if ( empty( $value ) ) {
	$value = $readonly = '';
}
else {
	$value = esc_attr( $value );
	$readonly = ' readonly';
}

$readonly = '';
$selected = '';

if ( ! empty( $lists ) ) {

	if ( ! isset( $args['control'] ) ) {
		$args['control'] = 'radio-table';
	}

	if ( 'select' === $args['control'] ) {

		printf(
			'<select name="list" id="%s" name="chimplet[mailchimp][list]" %s>',
			esc_attr( $args['label_for'] ),
			esc_attr( $readonly )
		);

		foreach ( $lists['data'] as $list ) {
			printf(
				'<option value="%s" %s>%s</option>',
				esc_attr( $list['id'] ),
				selected( $value, $list['id'] ),
				esc_html( $list['name'] )
			);
		}

		echo '</select>';

	}
	else {

			?>

			<table class="wp-list-table widefat mailchimp-lists">
				<caption><?php echo esc_html( $args['title'] ); ?></caption>
				<thead>
					<tr>
						<th scope="col" id="chimplet-rb" class="manage-column column-rb check-column"><label class="screen-reader-text"><?php esc_html_e( 'Select One', 'chimplet' ); ?></label></th>
						<th scope="col" id="mailchimp-list-title" class="manage-column column-name"><?php esc_html_e( 'Title' ); ?></th>
						<th scope="col" id="mailchimp-list-groups" class="manage-column column-groups num"><?php esc_html_e( 'Groupings', 'chimplet' ); ?></th>
						<th scope="col" id="mailchimp-list-members" class="manage-column column-members num"><?php esc_html_e( 'Members', 'chimplet' ); ?></th>
						<th scope="col" id="mailchimp-list-rating" class="manage-column column-rating num"><?php esc_html_e( 'Rating' ); ?></th>
						<th scope="col" id="mailchimp-list-date" class="manage-column column-date"><?php esc_html_e( 'Date Created', 'chimplet' ); ?></th>
					</tr>
				</thead>
				<tbody>
				<?php
				$i = 0;
				foreach ( $lists as $list ) :
					$select_label = sprintf( __( 'Select %s' ), '&ldquo;' . $list['name'] . '&rdquo;' ); //xss ok
					$id = 'rb-select-' . $list['id'];
					?>
					<tr id="mailchimp-list-<?php echo esc_attr( $list['id'] ); ?>" class="mailchimp-list-<?php echo esc_attr( $list['id'] ); ?> mailchimp-list<?php echo ( $i % 2 === 0 ? ' alternate' : '' ); //xss ok ?>">
						<th scope="row" class="check-column">
							<label class="screen-reader-text" for="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $select_label ); ?></label>
							<input type="radio" id="<?php echo esc_attr( $id ); ?>" name="chimplet[mailchimp][list]" value="<?php echo esc_attr( $list['id'] ); ?>"<?php echo checked( $value, $list['id'] ); ?> />
						</th>
						<td class="column-title">
							<strong><label for="<?php echo esc_attr( $id ); ?>" title="<?php echo esc_attr( $select_label ); ?>"><?php echo esc_html( $list['name'] ); ?></label></strong>
						</td>
						<td class="column-groupings num"><?php echo esc_html( $list['stats']['grouping_count'] ); ?></td>
						<td class="column-members num"><?php echo esc_html( $list['stats']['member_count'] ); ?></td>
						<td class="column-rating num"><?php echo esc_html( $list['list_rating'] ); ?></td>
						<td class="column-date"><time datetime="<?php echo esc_attr( $list['date_created'] ); ?>"><?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $list['date_created'] ) ) ); ?></time></td>
					</tr>
					<?php
					$i++;
				endforeach;
				?>
			</table>
			<div class="tablenav bottom cf">
				<div class="alignleft tablenav-information">
					<span class="displaying-num"><?php printf( esc_html( _n( '1 list', '%s lists', $total, 'chimplet' ) ), $total ); ?></span>
				</div>
			</div>

			<?php

	}
}
