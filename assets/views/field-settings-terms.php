<?php

/**
 * File: Chimplet Taxonomy Settings
 *
 * @package Locomotive\Chimplet\Views
 * @version 2015-03-06
 * @since   0.0.0 (2015-02-28)
 */

?>

<p class="description"><?php
	printf(
		__( 'Select one or more terms, across available taxonomies, to be added&mdash;accordingly&mdash;as Groups and Interest Groupings (%s, across groupings, per List).', 'chimplet' ),
		'<a target="_blank" href="http://kb.mailchimp.com/quick-answers/lists-answers/how-many-groups-can-i-have-in-my-list">' . esc_html__( 'Maximum of 60 groups', 'chimplet' ) . '</a>'
	);
?></p>

<?php

$local_grouping = $this->get_option( 'mailchimp.terms', [] );
$taxonomies     = get_taxonomies( [ 'object_type' => $this->excluded_post_types ], 'objects', 'NOT' );

if ( empty( $taxonomies ) ) {
	return;
}

foreach ( $taxonomies as $taxonomy ) :

	if ( in_array( $taxonomy->name, $this->excluded_taxonomies ) ) {
		continue;
	}

	$taxonomy_in_grouping = null;
	$grouping_status      = '<span class="chimplet-sync dashicons dashicons-no" title="' . esc_attr( __( 'Grouping isn’t synced with MailChimp List.', 'chimplet' ) ) . '"></span>';

	if ( $grouping = $this->mc->get_grouping( $taxonomy->label ) ) {
		$taxonomy_in_grouping = $grouping;
		$grouping_status      = '<span class="chimplet-sync dashicons dashicons-yes" title="' . esc_attr( __( 'Grouping is synced with MailChimp List.', 'chimplet' ) ) . '"></span>';
	}

	$args = [ 'hide_empty' => false ];
	$args = apply_filters( 'chimplet/get_terms', $args, $taxonomy, $grouping );
	$args = apply_filters( "chimplet/get_terms/name={$taxonomy->name}", $args, $taxonomy, $grouping );

	$terms = get_terms( $taxonomy->name, $args );

	if ( empty( $terms ) ) {
		continue;
	}

	?>
	<fieldset>
		<legend><span class="h4"><?php echo $taxonomy->label . $grouping_status; //xss ok ?></span></legend>
		<div class="chimplet-item-list chimplet-mc chimplet-1/3">
			<?php
			$id    = "cb-select-$taxonomy->name-all";
			$name  = "chimplet[mailchimp][terms][$taxonomy->name][]";
			$match = ( empty( $local_grouping[ $taxonomy->name ] ) || ! is_array( $local_grouping[ $taxonomy->name ] ) ? false : in_array( 'all', $local_grouping[ $taxonomy->name ] ) );
			?>
			<label for="<?php echo esc_attr( $id ); ?>">
				<input type="checkbox"
				       name="<?php echo esc_attr( $name ); ?>"
				       id="<?php echo esc_html( $id ); ?>"
				       value="all" <?php checked( $match ); ?>>
				<span><?php esc_html_e( 'Select All/None', 'chimplet' ); ?></span>
			</label>
			<?php
			foreach ( $terms as $term ) :
				$id    = 'cb-select-' . $taxonomy->name . '-' . $term->term_id;
				$match = ( empty( $local_grouping[ $taxonomy->name ] ) || ! is_array( $local_grouping[ $taxonomy->name ] ) ? false : in_array( $term->term_id, $local_grouping[ $taxonomy->name ] ) );
				$group_status = sprintf(
					'<span class="chimplet-sync dashicons dashicons-no" title="%s"></span>',
					esc_attr__( 'Term isn’t synced with MailChimp Grouping.', 'chimplet' )
				);

				$is_synced = false;

				if ( isset( $taxonomy_in_grouping['groups'] ) ) {
					$grouping_names = $this->wp->wp_list_pluck( $taxonomy_in_grouping['groups'], 'name' );
					$is_synced = in_array( $term->name, $grouping_names );
					if ( $is_synced ) {
						$group_status = sprintf(
							'<span class="chimplet-sync dashicons dashicons-yes" title="%s"></span>',
							esc_attr__( 'Term is synced with MailChimp Grouping.', 'chimplet' )
						);
					}
				}
				?>
				<label for="<?php echo esc_attr( $id ); ?>">
					<input type="checkbox"
					       name="<?php echo esc_attr( $name ); ?>"
					       id="<?php echo esc_attr( $id ); ?>"
					       value="<?php echo esc_attr( $term->term_id ); ?>" <?php checked( $match || $is_synced ); ?>>
					<span><?php echo esc_html( $term->name ); ?></span>
					<?php echo $group_status; //xss ok ?>
				</label>
			<?php endforeach; ?>
		</div>
	</fieldset>
<?php

endforeach;

?>

<hr>

<p class="description"><?php
	esc_html_e( 'From the selected terms, Chimplet will create a collection of segments. Each segment represents a combination of one or more terms.', 'chimplet' );
	echo esc_html( ' ' );
	printf(
		esc_html__( 'More formally, a %1$s-%2$s is a subset of %1$s distinct terms. If the set has %3$s terms, the number of %1$s-combinations (segments) is equal to the binomial coefficient.', 'chimplet' ),
		'<samp>' . esc_html( 'k' ) . '</samp>',
		'<strong>' . esc_html( 'segment' ) . '</strong>',
		'<samp>' . esc_html( 'n' ) . '</samp>'
	);
?></p>
<p class="description"><?php esc_html_e( 'In smaller cases, this won’t amount to many segments. Although there is no limit to the number of segments which can be saved, it can become impractical to select a large set of terms.', 'chimplet' ); ?></p>
