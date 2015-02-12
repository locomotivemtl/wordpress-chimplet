<?php

namespace Locomotive\Chimplet;

/**
 * File: Abstract Base Class
 *
 * @package Locomotive\Chimplet
 */

/**
 * Class: Abstract Base
 *
 * @version 2015-02-09
 * @since   0.0.0 (2015-02-06)
 */

abstract class Base
{
	use BaseInfo;
	use BaseOption;

	/**
	 * Retrieve path to Chimplet directory
	 *
	 * @version 2015-02-09
	 * @since   0.0.0 (2015-02-05)
	 *
	 * @param   string  $path
	 * @return  string
	 */

	public function get_path( $path )
	{
		return $this->get_info('path') . $path;
	}

	/**
	 * Retrieve path to Chimplet directory
	 *
	 * @version 2015-02-09
	 * @since   0.0.0 (2015-02-05)
	 *
	 * @param   string  $path
	 * @return  string
	 */

	public function get_url( $path )
	{
		return $this->get_info('url') . $path;
	}

	/**
	 * Retrieve path to Chimplet assets directory
	 *
	 * @version 2015-02-09
	 * @since   0.0.0 (2015-02-06)
	 *
	 * @param   string  $path
	 * @return  string
	 */

	public function get_asset( $path )
	{
		return $this->get_info('url') . 'assets/' . $path;
	}

	/**
	 * Verify which Chimplet page is currently viewed
	 *
	 * @version 2015-02-07
	 * @since   0.0.0 (2015-02-07)
	 *
	 * @param   string  $path
	 * @return  bool
	 */

	public function is_page( $page )
	{
		return ( isset( $_GET['page'] ) && $_GET['page'] === $page );
	}

	/**
	 * Verify if we are viewing the Plugins page or a Chimplet page
	 *
	 * @version 2015-02-07
	 * @since   0.0.0 (2015-02-07)
	 *
	 * @return  bool
	 */

	public function is_related_page()
	{
		return ( ( isset( $_SERVER['REQUEST_URI'] ) && false !== strpos( $_SERVER['REQUEST_URI'], 'plugins.php' ) ) || ( isset( $_GET['page'] ) && false !== strpos( $_GET['page'], 'chimplet-' ) ) );
	}

	/**
	 * Render View
	 *
	 * Load template from `views/` directory and allow
	 * variables to be passed through.
	 *
	 * @version 2015-02-09
	 * @since   0.0.0 (2015-02-05)
	 *
	 * @param   string  $template
	 * @param   array   $args
	 */

	public function render_view( $template, $args = [] )
	{
		$path = $this->get_path( "assets/views/{$template}.php" );

		$title = ( isset( $args['page_title'] ) ? $args['page_title'] : $this->get_info('name') );

		$classes = [ 'wrap', 'chimplet-wrap' ];

		if ( isset( $args['menu_slug'] ) ) {
			$classes[] = $args['menu_slug'] . '-wrap';
		}

		if ( file_exists( $path ) ) {
			?>

			<div class="<?php echo implode( ' ', $classes ); ?>">

				<h2>
					<strong class="screen-reader-text"><?php esc_html_e( 'Chimplet', 'chimplet' ); ?>: </strong>
					<?php echo esc_html( $title ); ?>
				</h2>
				<?php include $path; ?>

			</div>
			<?php
		}
	}
}
