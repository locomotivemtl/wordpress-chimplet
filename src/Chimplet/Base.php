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
 * @version 2015-02-06
 * @since   0.0.0 (2015-02-06)
 */

abstract class Base
{

	/**
	 * Retrieve a value from the settings array
	 *
	 * @uses    Application::$settings
	 * @version 2015-02-05
	 * @since   0.0.0 (2015-02-05)
	 *
	 * @param   string  $name     Name of setting to retrieve.
	 * @param   mixed   $default  Optional. Default value to return if the option does not exist.
	 * @return  mixed   $value    Value set for the setting.
	 */

	public function get_setting( $name, $default = false, $allow_filter = true )
	{
		global $chimplet;

		$value = null;

		$name = trim( $name );

		if ( empty( $name ) ) {
			return false;
		}

		if ( isset( $chimplet->settings[ $name ] ) ) {
			$value = $chimplet->settings[ $name ];

			if ( $allow_filter ) {
				$value = apply_filters( "chimplet/settings/{$name}", $value );
			}
		}
		else {
			$value = $default;
		}

		return $value;
	}

	/**
	 * Update a value to the settings array
	 *
	 * @uses    Application::$settings
	 * @version 2015-02-05
	 * @since   0.0.0 (2015-02-05)
	 *
	 * @param   string  $name
	 * @param   mixed   $value
	 */

	public function update_setting( $name, $value )
	{
		global $chimplet;

		$chimplet->settings[ $name ] = $value;
	}

	/**
	 * Add a value to the settings array
	 *
	 * @uses    Application::$settings
	 * @version 2015-02-05
	 * @since   0.0.0 (2015-02-05)
	 *
	 * @param   string  $name
	 * @param   mixed   $value
	 */

	public function append_setting( $name, $value )
	{
		global $chimplet;

		if ( ! isset( $chimplet->settings[ $name ] ) )
		{
			$chimplet->settings[ $name ] = [];
		}

		$chimplet->settings[ $name ][] = $value;
	}

	/**
	 * Retrieve path to Chimplet directory
	 *
	 * @version 2015-02-05
	 * @since   0.0.0 (2015-02-05)
	 *
	 * @param   string  $path
	 * @return  string
	 */

	public function get_path( $path )
	{
		return $this->get_setting('path') . $path;
	}

	/**
	 * Retrieve path to Chimplet directory
	 *
	 * @version 2015-02-05
	 * @since   0.0.0 (2015-02-05)
	 *
	 * @param   string  $path
	 * @return  string
	 */

	public function get_url( $path )
	{
		return $this->get_setting('url') . $path;
	}

	/**
	 * Retrieve path to Chimplet assets directory
	 *
	 * @version 2015-02-06
	 * @since   0.0.0 (2015-02-06)
	 *
	 * @param   string  $path
	 * @return  string
	 */

	public function get_asset( $path )
	{
		return $this->get_setting('url') . 'assets/' . $path;
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
	 * @version 2015-02-07
	 * @since   0.0.0 (2015-02-05)
	 *
	 * @param   string  $template
	 * @param   array   $args
	 */

	public function render_view( $template, $args = [] )
	{
		$path = $this->get_path("assets/views/{$template}.php");

		$title = ( isset( $args['title'] ) ? $args['title'] : $this->get_setting('name') );

		$classes = [ 'wrap', 'chimplet-wrap' ];

		if ( isset( $args['slug'] ) ) {
			$classes[] = $args['slug'] . '-wrap';
		}

		if ( file_exists( $path ) ) {

?>

<div class="<?php echo implode( ' ', $classes ); ?>">

	<h2><?php echo esc_html( $title ); ?></h2>

<?php

			include $path;

?>

</div>

<?php

		}
	}

}
