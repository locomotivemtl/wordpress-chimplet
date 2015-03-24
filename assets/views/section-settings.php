<?php

/**
 * File: Chimplet Settings Section
 *
 * @package Locomotive\Chimplet\Views
 * @version 2015-03-24
 * @since   0.0.0 (2015-03-03)
 */

$before_fields = $after_fields = null;

if ( is_callable( $section['callback'] ) ) {
	$before_fields = $section['callback'];
}
else if ( is_array( $section['callback'] ) ) {

	if ( isset( $section['callback']['before'] ) && is_callable( $section['callback']['before'] ) ) {
		$before_fields = $section['callback']['before'];
	}

	if ( isset( $section['callback']['after'] ) && is_callable( $section['callback']['after'] ) ) {
		$after_fields = $section['callback']['after'];
	}
}

if ( isset( $wp_settings_fields[ $page ][ $section['id'] ] ) ) {

?>

<section class="chimplet-panel">
	<?php
	if ( $section['title'] ) {
	?>
	<header class="panel-heading">
		<h3 class="panel-title"><?php echo esc_html( $section['title'] ); ?></h3>
	</header>
	<?php
	}
	?>
	<div class="panel-body">
	<?php

	if ( is_callable( $before_fields ) ) {
		call_user_func( $before_fields, $section );
	}

		?>
		<table class="form-table">
			<tbody>
			<?php
				$this->render_fields( $page, $section['id'] );
			?>
			</tbody>
		</table>
		<?php

	if ( is_callable( $after_fields ) ) {
		call_user_func( $after_fields, $section );
	}

	?>
	</div>
</section>

<?php

}
